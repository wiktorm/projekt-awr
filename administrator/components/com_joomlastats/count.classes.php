<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'base.classes.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'db.constants.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );


class js_JSCountVisitor
{
	/** 'JS' configuration object. Holds system and user settings */
	var $JSConf			= null; 

	/** database placeholder */
	var $db;

	/** below members hold current date and time in Joomla Local timezone */
	var $now_timestamp = null;
	var $now_date_str = null;
	var $now_time_str = null;


	function __construct() {
		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$this->db = $JSDatabaseAccess->db;

		if (js_isJSDebugOn()) { //show on front page if Joomla CMS cache is ON
			$cache_txt = 'Joomla CMS cache is OFF';
			if ( !defined('_JS_STAND_ALONE') ) {//in stand alone version cache is always off
				global $mainframe;
				if ($mainframe->getCfg('caching'))
					$cache_txt = 'Joomla CMS cache is <span style="color: red;">ON</span>';
			}
			js_echoJSDebugInfo($cache_txt);
		}

		{//set current JS time (time for anonymous front page users For details see http://www.joomlastats.org:8080/display/JS/FAQ+Wrong+time+in+JoomlaStats and http://www.joomlastats.org:8080/display/JS/FAQ+Time+and+Time+Zones+in+JoomlaStats )
			$this->now_timestamp = js_getJSNowTimeStamp();
			$this->now_date_str = js_gmdate('Y-m-d', $this->now_timestamp);
			$this->now_time_str = js_gmdate('H:i:s', $this->now_timestamp);

			js_echoJSDebugInfo('Visit time in Joomla Local timezone: '.$this->now_date_str.' '.$this->now_time_str, '');
		}


		
		$this->JSConf = new js_JSConf();
	}

	/**
	 * A hack to support __construct() on PHP 4
	 *
	 * Hint: descendant classes have no PHP4 class_name() constructors,
	 * so this constructor gets called first and calls the top-layer __construct()
	 * which (if present) should call parent::__construct()
	 *
	 * code from Joomla CMS 1.5.10 (thanks!)
	 *
	 * @access	public
	 * @return	Object
	 * @since	1.5
	 */
	function js_JSCountVisitor()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	
	

		
	/**
	 *  Count visitor that visit 'Joomla CMS': 
	 *    - recognize visitor
	 *    - update JS DB about visitor
	 *    - update JS DB about page that visior request
	 *
	 *  @param out    $html_content   - currently it return "<!-- JoomlaStatsActivated -->" and this string should be printed on front site
	 *  @return mixed $visit_id       - false on failure; integer visit_id on success; 0 when page is excluded from counting; greater than 0 is valid visit_id
	 */
	function countVisitor( &$html_content ) {
		js_echoJSDebugInfo('Perform Visitor counting process (function: countVisitor())', '');
		
		$html_content = $this->getHtmlFrontPageJSActivatedString();//'<!-- JoomlaStatsActivated -->'

		$requested_url = $this->getRequestedUri();
		
		if ($requested_url != '') {
			$ignore = strpos($requested_url, 'jstatsIgnore=true');
			// Do not make counting on marked pages
			if ($ignore > 0) {
				js_echoJSDebugInfo('This page is excluded from counting', '');
				return 0;
			}
		}		

		
		// get user agent of visitor
		$VisitorUserAgent = $this->getVisitorUserAgent();


		// get IP adress of visitor
		$VisitorIp = null;
		$this->getVisitorIp($VisitorIp);

		

		$isKnownVisitor = false;
		$visitor_id = null;
		$visitor_exclude = null;
		$bResult = $this->isKnownVisitor( $VisitorIp, $VisitorUserAgent, $isKnownVisitor, $visitor_id, $visitor_exclude );
		if ($bResult == false)
			return false;

		if ( $isKnownVisitor == false ) {
			js_echoJSDebugInfo('New Visitor', '');
			//new unique visitor
			
			// get visitor ------------------------------------------------
			$Visitor = null;
			$updateTldInJSDatabase = true;
			$bResult = $this->recognizeVisitor( $VisitorIp, $VisitorUserAgent, $updateTldInJSDatabase, $Visitor );
			if ($bResult == false)
				return false;
			
			//additional members (I am not sure if we need them)
			$Visitor->RequestedPage = $requested_url;
			$Visitor->joomla_userid = $this->getJoomlaCmsUserId();
			
			// insert new unique visitor ------------------------------------------------
			$this->insertNewVisitor( $Visitor );
			
			$visitor_id = $Visitor->visitor_id;
			$visitor_exclude = $Visitor->visitor_exclude;
		} else {
			js_echoJSDebugInfo('Visitor already known', '');
		}
		
		if ($visitor_id == 0) {
			js_echoJSDebugInfo('Something is wrong with Visitor recognition or storing data about Visitor', '');
			return false;
		}
		
		if ($visitor_exclude == 1) {
			js_echoJSDebugInfo('This Visitor is excluded from counting', '');
			return 0;
		}
		
					
		$visit_id = $this->registerVisit( $visitor_id );
		
		if ($visit_id == 0) {
			js_echoJSDebugInfo('Something is wrong with registerVisit', '');
			return false;
		}

		$imprssion_id = $this->registerPageImpression( $visit_id, $requested_url );
		
		$was_keyword_registered  = 0;
		$was_referrer_registered = 0;
		$referrer_or_key_words_status = $this->registerReferrerOrKeyWords( $visit_id, $was_keyword_registered, $was_referrer_registered );

		//if ($referrer_or_key_words_status === false)
		//	return false;  //even if something goes wrong with referrer or keywords we do not exit with error status - user was succesfully counted 

		
		return $visit_id;
	}

	/** return "<!-- JoomlaStatsActivated -->" string */
	function getHtmlFrontPageJSActivatedString() {
		$JSSystemConst = new js_JSSystemConst();
		return $JSSystemConst->htmlFrontPageJSActivatedString;//"<!-- JoomlaStatsActivated -->"
	}


	/**
	 * Get user agent from Visitor (user that refresh page)
	 *   eg.: "mozilla/5.0 (windows; u; windows nt 5.1; en-gb; rv:1.8.1.15) gecko/20080623 firefox/2.0.0.15"
	 *
	 * @return string - '' (empty) string for failure
	 */
	function getVisitorUserAgent() {
		$user_agent = '';

		if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			if( $_SERVER['HTTP_USER_AGENT'] != NULL ) {
				$user_agent = trim( strtolower( $_SERVER['HTTP_USER_AGENT'] ) );
			}
		}

		js_echoJSDebugInfo('UserAgent string', $user_agent);

		return $user_agent;
	}

	/** If User is logged into 'Joomla CMS', Joomla CMS UserId is returned. //If user is not logged into 'Joomla CMS', 0 is returned */
	function getJoomlaCmsUserId()
	{
		if ( defined( '_JEXEC' ) ) {//outside joomla we can not check if user is logged
			global $mainframe;

			//works on j1.0.15 and j1.5.6 //if user is not logged $user->id return 0
			$user = &$mainframe->getUser();
			return (int)$user->id;
		}

		return 0; //JS stand alone version (defined('_JS_STAND_ALONE'))
	}

	function getRequestedUri()
	{
		$request_uri = '';

		if ((isset($_SERVER['REQUEST_URI'])) && ($_SERVER['REQUEST_URI'] != NULL)) {
			$request_uri = $_SERVER['REQUEST_URI'];
		} else if ((isset($_SERVER['PHP_SELF'])) && ($_SERVER['PHP_SELF'] != NULL))	{
			$request_uri = $_SERVER['PHP_SELF'];
			if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != NULL))
				$request_uri .= '?'.$_SERVER['QUERY_STRING'];
		} else if ((isset($_SERVER['SCRIPT_NAME'])) && ($_SERVER['SCRIPT_NAME'] != NULL)) {
			$request_uri = $_SERVER['SCRIPT_NAME'];
			if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != NULL))
				$request_uri .= '?'.$_SERVER['QUERY_STRING'];
		}

		if (($request_uri == "/") || ($request_uri == "\\"))
			$request_uri .= "index.php";

		if ((strtolower(substr($request_uri, -4)) == '.ico') ||
		    (strtolower(substr($request_uri, -4)) == '.png') ||
		    (strtolower(substr($request_uri, -4)) == '.gif') ||
		    (strtolower(substr($request_uri, -4)) == '.jpg'))
			return '';

		if ($request_uri == '')
			return '';


		// Search Engine Friendly url
		if (defined( '_JEXEC' )) {
			$app =& JFactory::getApplication();
			if ( $app->getCfg('sef') ) { //	if (($app->getCfg('sef')) && ($app->getCfg('sef_rewrite')) && !($app->getCfg('sef_suffix'))) {
				$request_uri = $this->sefRelToAbs('index.php?' . $_SERVER['QUERY_STRING']);
			}
		}

		$request_uri = str_replace('http://', ':#:', $request_uri);
		$request_uri = str_replace('//', '/', $request_uri);
		$request_uri = str_replace(':#:', 'http://', $request_uri);
		
		return $request_uri;
	}

	/**
	 * Legacy function to convert an internal Joomla URL to a humanly readible URL.
	 *
	 * @deprecated	As of Joomla CMS version 1.5 (this is oryginal Joomla CMS function from v1.5.11)
	 */
	function sefRelToAbs($value)
	{
		// Replace all &amp; with & as the router doesn't understand &amp;
		$url = str_replace('&amp;', '&', $value);
		if(substr(strtolower($url),0,9) != "index.php") return $url;
		$uri    = JURI::getInstance();
		$prefix = $uri->toString(array('scheme', 'host', 'port'));
		return $prefix.JRoute::_($url);
	}


	/**
	 *
	 *  @param $VisitorIp - valid only when true is returned
	 *  @return true on success
	 */
	function getVisitorIp(&$VisitorIp)
	{
		$Ip_tmp = null;
		// get usefull vars:
		$client_ip       = isset($_SERVER['HTTP_CLIENT_IP'])       ? $_SERVER['HTTP_CLIENT_IP']	      : NULL;
		$x_forwarded_for = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : NULL;
		$remote_addr     = isset($_SERVER['REMOTE_ADDR'])          ? $_SERVER['REMOTE_ADDR']	      : NULL;

		// then the script itself
		if (!empty($x_forwarded_for) && strrpos($x_forwarded_for, '.') > 0)
		{
			$arr = explode(',', $x_forwarded_for);
			$Ip_tmp = trim(end($arr));
		}

		if (!$this->isIpAddressValidRfc3330($Ip_tmp) && !empty($client_ip))
		{
			$ip_expl = explode('.', $client_ip);
			$referer = explode('.', $remote_addr);

			if ($referer[0] != $ip_expl[0])
			{
				$Ip_tmp = trim(implode('.', array_reverse($ip_expl)));
			}
			else
			{
				$arr = explode(',', $client_ip);
				$Ip_tmp = trim(end($arr));
			}
		}

		if (!$this->isIpAddressValidRfc3330($Ip_tmp) && !empty($remote_addr))
		{
			$arr = explode(',', $remote_addr);
			$Ip_tmp = trim(end($arr));
		}

		unset($client_ip, $x_forwarded_for, $remote_addr, $ip_expl, $referer);

		$VisitorIp = $Ip_tmp;
		return true;//@todo false never is returned but should be (I think it is possible to configure PHP that IP is unable to possess)
	}


	/**
	 * This function check if such visitor visit Joomla CMS any time before
	 *
	 * @param        $VisitorIp
	 * @param string $VisitorUserAgent  values: '', null, "mozilla/5.0 (windows; u;..." //if null this UserAgent is not considered during comparation
	 * @param bool   $isKnownVisitor
	 * @return bool - true on success
	 */
	function isKnownVisitor( $VisitorIp, $VisitorUserAgent, &$isKnownVisitor, &$visitor_id, &$visitor_exclude ) {

		$query = 'SELECT exclude, type, tld, id, useragent'
		. ' FROM #__jstats_ipaddresses'
		. ' WHERE ip = \'' . $VisitorIp . '\''
		//. ' AND useragent = \'' . $VisitorUserAgent . '\'' for performance we do this in PHP (MySQL very bad deal with something like this. Additional column user_agent is not indexed (and it should not be indexed)). In main cases there shoud be one entry so PHP better
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadObjectList();
		if ($this->db->getErrorNum() > 0)
			return false;

		if (!$rows) {
			$isKnownVisitor = false;
			return true;
		}
		
		//In main cases there shoud be one entry so PHP better			
		foreach( $rows as $row) {
			if( $row->useragent == $VisitorUserAgent) {
				$isKnownVisitor = true;//yes we found
				$visitor_id = $row->id;
				$visitor_exclude = $row->exclude;
				return true;
			}
		}
		
		$isKnownVisitor = false;
		return true;
	}

	/**
	 * Find and return TLD. This function operate on string.
	 *
	 * @param string $visitor_nslookup   - string returned by PHP method gethostbyaddr( $visitor_ip ); eg.: "crawl-66-249-70-72.googlebot.com", "sewer.com.eu", "66.249.70.72" (for this false will be returned)
	 * @param string $tld                - eg.: "us", "de", "pl"
	 * @return bool - true on success
	 */
	function getTldFromNslookupString( $visitor_nslookup, &$tld ) {

		$pos = strrpos( $visitor_nslookup, '.' ) + 1;
		
		if( $pos > 1 ) {
			$xt = trim( substr( $visitor_nslookup, $pos ) );
		
			if( ereg( '([a-zA-Z])', $xt ) ) {
				$tld = strtolower( $xt );
				return true;
			}
		}

		return false;
	}

	/**
	 * Find and return TLD. This function get Visitor IP and check it in JS database.
	 *
	 * @param string $visitor_ip    - eg.: "66.249.70.72"
	 * @param string $country_code  - eg.: "us", "de", "pl"     NOTICE: this is not TLD!!!
	 * @return bool - return false if there is no entry in JS DB for such IP
	 */
	function getCountryCodeFromJSDatabase( $visitor_ip, &$country_code ) {

		$query = 'SELECT country_code2'
		. ' FROM #__jstats_iptocountry'
		. ' WHERE inet_aton(\'' . $visitor_ip . '\') >= ip_from'
		. ' AND inet_aton(\'' . $visitor_ip . '\') <= ip_to'
		;
		$this->db->setQuery( $query );
		$tmp_country_code = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;

		if( $tmp_country_code ) {
			$country_code = $tmp_country_code;
			return true;
		}

		return false;
	}

	/**
	 * Query RIPE servers in internet about tld for given IP address
	 *
	 *    EU is used as the IANA generic country code; it is always returned
	 *      for 0.0.0.0 to 255.255.255.255 and some other generic IANA networks
	 *    
	 *    AP is used as the APNIC generic country code; the real
	 *      country code can be obtained from the 'route' entry
	 *
	 * @param string $visitor_ip   - eg.: "66.249.70.72"
	 * @param string $tld          - eg.: "us", "de", "pl"
	 *
	 * NOTICE:
	 *    This function rise PHP warning very often! eg.:
	 *
	 * @return bool - return false if something goes wrong or could not determine tld
	 */
	function getTldFromRipeServers( $ip_to_check, &$ipFrom, &$ipTo, &$tld ) {
		$visitor_tld	= '';
		$countryCode	= '';
		$ipFrom			= '0.0.0.0';
		$ipTo			= '255.255.255.255';
		$whois			= array();
		$whoisResult	= array();

		// do RIPE Whois lookup for the IP address

		// Andreas: removed VERIO added AFRINIC,NTTCOM
		// mic 20081014: IMPORTANT the \n at the end of the query!
		$query		= '-s RIPE,ARIN,APNIC,RADB,JPIRR,AFRINIC,NTTCOM -T inetnum -G ' . $ip_to_check . "\n";
		$countryCode = $this->queryWhois( $ip_to_check, 'whois.ripe.net', $query, $ipFrom, $ipTo, $whoisResult );

		if( $countryCode === 'LACNIC' || $countryCode === 'EU' || $countryCode === 'AP' || $countryCode ===''){
			$query			= $ip_to_check . "\n";
			$countryCode	= $this->queryWhois( $ip_to_check, 'whois.lacnic.net', $query, $ipFrom, $ipTo, $whoisResult );
		}else{
            $whois = $whoisResult;
		}

		if( $countryCode === 'AfriNIC' || $countryCode === 'EU' || $countryCode === 'AP' || $countryCode===''){
			$query = '-T inetnum -r ' . $ip_to_check . "\n";
			$countryCode = $this->queryWhois( $ip_to_check, 'whois.afrinic.net', $query, $ipFrom, $ipTo, $whois );
		}else{
            $whois = $whoisResult;
		}

		js_echoJSDebugInfo('Answer from RIPE server', $whois);

        //if( array_key_exists( 'descr', $whois ) ) {
        //	$visitor_nslookup .= "\n" . $whois['descr'];
        //}
        //if( array_key_exists( 'role', $whois ) ) {
        //	$visitor_nslookup .= "\n" . $whois['role'];
        //}

		$tld = strtolower( $countryCode );
		return true;//@todo false should be returned on fail
	}

	/**
	 * update TLDs in JS database
	 *
	 * @param string $
	 * @param string $
	 * @return bool - return false on fail
	 */
	function updateTldInJSDatabase( $ipFrom, $ipTo, $countryCode ) {

		// EU is used as the IANA generic country code; it is always returned
		// for 0.0.0.0 to 255.255.255.255 and some other generic IANA networks

		// AP is used as the APNIC generic country code; the real
		// country code can be obtained from the 'route' entry

		if( $countryCode !== '' && $countryCode !== 'eu' && $countryCode !== 'ap' ) {
			// found country code, enter it into iptocountry
			$query = 'INSERT INTO #__jstats_iptocountry (ip_from, ip_to, country_code2)'
			. ' VALUES (' . sprintf( "%u", ip2long( $ipFrom ) ) . ',' . sprintf( "%u", ip2long( $ipTo ) ) . ',\''	. $countryCode . '\')'
			;
			$this->db->setQuery( $query );
			$this->db->query();
			if ($this->db->getErrorNum() > 0)
				return false;
		}

		return true;
	}


	/**
	 * This function make visitor recognition. Basing on $IpAddress and $UserAgent it return information about
	 *   operationg system, browser, user type etc.
	 *
	 * recognize because data are taken directly from function arguments (not from user browser, PHP settings, cookies, javascript etc)
	 *
	 * @param out $Visitor - object of class js_Visitor
	 *
	 * @return bool - true on success
	 */
	function recognizeVisitor( $IpAddress, $UserAgent, $updateTldInJSDatabase, &$Visitor ) {

		js_echoJSDebugInfo('Recognizing visitor', '');
		
		$visitor_tld		= '';//@todo define or whole object of class js_Tld should be here
		$visitor_nslookup	= $IpAddress;

		if( $this->isIpAddressIntranet( $IpAddress ) ) {
			$visitor_tld		= 'intranet';//@todo define or whole object of class js_Tld should be here
			js_echoJSDebugInfo('This IP address is INTRANET. We do not search TLD for this address', '');
		} else if( $this->isIpAddressLocalHost( $IpAddress ) ) {
			$visitor_tld		= 'localhost';//@todo define or whole object of class js_Tld should be here
			js_echoJSDebugInfo('This IP address is LOCALHOST. We do not search TLD for this address', '');
		} else if( !$this->isIpAddressValidRfc3330( $IpAddress ) ) {
			js_echoJSDebugInfo('This IP address is NOT VALID according to RFC3330. We do not search TLD for this address', '');
		} else {
			js_echoJSDebugInfo('This IP address is valid.', '');

			$visitor_nslookup = gethostbyaddr( $IpAddress );
			$this->getTldFromNslookupString( $visitor_nslookup, $visitor_tld );

			if( $visitor_tld === '' || $visitor_tld === 'eu' || strlen( $visitor_tld ) > 2 ) {

				//below function return CountryCode not TLD. Is below code correct?
				$tld_res = $this->getCountryCodeFromJSDatabase( $IpAddress, $visitor_tld );

				// Enzo: enable_whois should control the WHOIS only, not nslookup and database query
				if( $tld_res == false && $this->JSConf->enable_whois) {
					$ipFrom	= '0.0.0.0';
					$ipTo	= '255.255.255.255';
					$this->getTldFromRipeServers( $IpAddress, $ipFrom, $ipTo, $visitor_tld );
					$this->updateTldInJSDatabase( $ipFrom, $ipTo, $visitor_tld );
				} else {
					js_echoJSDebugInfo('WHOIS option is turned OFF', '');
				}

				// GB is the only country code not matching the country TLD
				if( strcasecmp($visitor_tld, 'gb') == 0 ) {
					$visitor_tld = 'uk';
				}
			}
		}

		$Tld = $this->getTldFromTld( $visitor_tld );

		// determine if bot or browser
		$type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;

		// get browser --------------------------------------------------------------------------
		$BrowserVersion = '';
		$Browser = $this->getBrowserFromUserAgent( $UserAgent, $BrowserVersion );
		if ($Browser == null)
			return false;

		if ($Browser->browser_id == 0) { // look for bot if this is not regular visitor (if still unknown)
			$this->checkUnknownBotFromUserAgent($UserAgent, $Browser /*in-out*/ );
		}

		$type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
		if ($Browser->browser_id == 0)
			$type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
		else if ($Browser->browser_id >= 1024)
			$type = _JS_DB_IPADD__TYPE_BOT_VISITOR;


		// get OS version -----------------------------------------------------------------------
		$OS = $this->getOsFromUserAgent($UserAgent);


		// mic 20081014: get screen resolution
		//$this->getJSScreenresolution();

		


		// create visitor object ------------------------------------------------

		if ($OS == null) {
			//create unknown system
			$OS = new js_OS();
		}

		if ($Tld == null) {
			//create unknown tld
			$Tld = new js_Tld();
		}

		
		$Visitor = new js_Visitor();
		$Visitor->visitor_id = 0;
		$Visitor->visitor_ip = $IpAddress;
		$Visitor->visitor_useragent = $UserAgent;
		//$Visitor->visitor_exclude = 0;//@todo define should be here //member not set, so default value will be used
		$Visitor->visitor_type = $type; //_JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR, _JS_DB_IPADD__TYPE_REGULAR_VISITOR, _JS_DB_IPADD__TYPE_BOT_VISITOR;
		$Visitor->OS = $OS;
		$Visitor->Browser = $Browser;
		$Visitor->Tld = $Tld;
			$Visitor->browser_version = $BrowserVersion;//additional parameter
		$Visitor->nslookup = $visitor_nslookup;

		js_echoJSDebugInfo('Visitor', $Visitor);

		return true;
	}

	/**
	 * Add new Visitor to #__jstats_ipaddresses table
	 *
	 * @param object $Visitor - object of class $js_Visitor
	 * @return bool - return true on success and object $Visitor has set member $Visitor->visitor_id
	 */
	function insertNewVisitor( &$Visitor ) {

		$browser = $Visitor->Browser->browser_name;
		if ($Visitor->visitor_type == _JS_DB_IPADD__TYPE_REGULAR_VISITOR)
			$browser .= ' '. $Visitor->browser_version;

		$query = 'INSERT INTO #__jstats_ipaddresses'
		. ' (ip, nslookup, useragent, tld, system, browser, type)'
		. ' VALUES (\'' . $Visitor->visitor_ip . '\','
			. ' \'' . $Visitor->nslookup . '\','
			. ' \'' . $Visitor->visitor_useragent . '\','
			. ' \'' . $Visitor->Tld->tld . '\','
			. ' \'' . $Visitor->OS->os_name . '\','
			. ' \'' . $browser . '\','
			. $Visitor->visitor_type
		. ')'
		;

		$this->db->setQuery( $query );
		if (!$this->db->query())
			return false;

		$Visitor->visitor_id = $this->db->insertid();

		return true;
	}

	/**
	 * Update Visitor to #__jstats_ipaddresses table
	 *
	 * //@todo: Is it a mistake that we need to update prevoiusly entered entry? (I am unsure but maybe this this is mistake in logic)
	 *
	 * @param object $Visitor - object of class $js_Visitor
	 * @return bool - true on success
	 */
	function updateVisitor( $Visitor ) {

		$browser = $Visitor->Browser->browser_name;
		if ($Visitor->visitor_type == _JS_DB_IPADD__TYPE_REGULAR_VISITOR)
			$browser .= ' '. $Visitor->browser_version;

		$query = 'UPDATE #__jstats_ipaddresses'
		. ' SET nslookup = \'' . $Visitor->nslookup . '\','
		. ' tld = \'' . $Visitor->Tld->tld . '\','
		. ' system = \'' . $Visitor->OS->os_name . '\','
		. ' browser = \'' . $browser . '\','
		. ' type = ' . $Visitor->visitor_type . ','
		. ' WHERE ip = \''. $Visitor->visitor_ip . '\''
		. ' AND useragent = \'' . $Visitor->visitor_useragent . '\''
		;

		$this->db->setQuery( $query );
		$this->db->query();

		if ($this->db->getErrorNum() > 0)
			return false;
			
		return true;
	}

	/**
	 * @param string  $UserAgent  eg.: "mozilla/5.0 (windows; u; windows nt 5.1; en-gb; rv:1.8.1.15) gecko/20080623 firefox/2.0.0.15"
	 *
	 * @return object of class js_OS or null when fail
	 */
	function getOsFromUserAgent( $UserAgent ) {

		if (strlen($UserAgent) == 0)//if ($UserAgent == '') - this not always works!
			return null;

		$query = ''
		. ' SELECT'
		. '   LENGTH(o.sys_string) AS strlen,'
		. '   o.sys_id        AS os_id,'
		. '   o.sys_type      AS ostype_id,'
		. '   o.sys_string    AS os_key,'
		. '   o.sys_fullname  AS os_name,'
		. '   o.sys_img       AS os_img'
		. ' FROM'
		. '   #__jstats_systems o'
		. ' WHERE'
		. '   o.sys_id > 0'
		. ' ORDER BY'
		. '   strlen DESC'
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadObjectList();
		if ($this->db->getErrorNum() > 0)
			return null;
		
		foreach( $rows as $row) {
			if( strpos( $UserAgent, $row->os_key, 0 ) !== false) {
				$OS = new js_OS();//we copy each member manualy to be sure about that what is inside. Additional we use getEscaped() method
				$OS->os_id = $row->os_id;
				$OS->ostype_id = $row->ostype_id;
				$OS->os_key = $row->os_key;
				$OS->os_name = $this->db->getEscaped( $row->os_name );
				$OS->os_img = $row->os_img;

				#__jstats_ostype (with entries)
				$__jstats_ostype = unserialize(_JS_DB_TABLE__OSTYPE);//whole table
				//fill missing entries in $OS object
				$OS->ostype_name = $__jstats_ostype[$OS->ostype_id]['ostype_name'];
				$OS->ostype_img = $__jstats_ostype[$OS->ostype_id]['ostype_img'];

				return $OS;
			}
		}
	
		return null;
	}

	/**
	 * @param string  $tld_str  eg.: "localhost"; "us", "de"
	 *
	 * @return object of class js_Tld or null when fail
	 */
	function getTldFromTld( $tld_str ) {

		$query = ''
		. ' SELECT'
		. '   t.tld_id        AS tld_id,'
		. '   t.tld           AS tld,'
		. '   t.fullname      AS tld_name'
		. ' FROM'
		. '   #__jstats_topleveldomains t'
		. ' WHERE'
		. '   t.tld=\''.$tld_str.'\''
		;
		$this->db->setQuery( $query );
		$obj = $this->db->loadObject();
		if ($this->db->getErrorNum() > 0)
			return null;

		if (!$obj)
			return null;

		$Tld = new js_Tld();
		$Tld->tld_id = $obj->tld_id;
		$Tld->tld = $obj->tld;
		$Tld->tld_name = $obj->tld_name;
		$Tld->tld_img = $obj->tld;
			
		return $Tld;		
	}


	/**
	 * @param in  string  $UserAgent      eg.: "mozilla/5.0 (windows; u; windows nt 5.1; en-gb; rv:1.8.1.15) gecko/20080623 firefox/2.0.0.15"
	 * @param out string  $BrowserVersion eg.: "7.0" (Connected with $BrowserName gives "Internet Explorer 7.0") //could be empty
	 *
	 * @return object of class js_Browser if visitor has browser (if visitor is bot/spider null is returned)
	 */
	function getBrowserFromUserAgent( $UserAgent, &$BrowserVersion ) {

		if (strlen($UserAgent) == 0)//if ($UserAgent == '') - this not always works!
			return new js_Browser();//return unknown

		$query = ''
		. ' SELECT'
		. '   b.browser_id        AS browser_id,'
		. '   b.browsertype_id    AS browsertype_id,'
		. '   b.browser_key       AS browser_key,'
		. '   b.browser_name      AS browser_name,'
		. '   b.browser_img       AS browser_img'
		. ' FROM'
		. '   #__jstats_browsers b'
		. ' WHERE'
		. '   b.browser_id > 0'
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadObjectList();
		if ($this->db->getErrorNum() > 0)
			return null;

		foreach( $rows as $row ) {
			if( strpos( $UserAgent, $row->browser_key, 0 ) !== false ) {
				//this is browser, set and return arguments
				$Browser = new js_Browser();//we copy each member manualy to be sure about that what is inside. Additional we use getEscaped() method
				$Browser->browser_id = $row->browser_id;
				$Browser->browsertype_id = $row->browsertype_id;
				$Browser->browser_key = $row->browser_key;
				$Browser->browser_name = $this->db->getEscaped( $row->browser_name );
				$Browser->browser_img = $row->browser_img;
				
				#__jstats_browserstype (with entries)
				$__jstats_browserstype = unserialize(_JS_DB_TABLE__BROWSERSTYPE);//whole table
				//fill missing entries in $Browser object
				$Browser->browsertype_name = $__jstats_browserstype[$Browser->browsertype_id]['browsertype_name'];
				$Browser->browsertype_img  = $__jstats_browserstype[$Browser->browsertype_id]['browsertype_img'];

				{//try to get browser version
					$version = array();
					if( preg_match( '/' . $Browser->browser_key . '[\/\sa-z]*([\d\.]*)/i', $UserAgent, $version ) ) {
						if (isset($version[1])) {
							$BrowserVersion = $version[1];
						}
					}
				}

				return $Browser;
			}
		}
			
		return new js_Browser();//return unknown
	}
	
	/**
	 * @param string  $UserAgent  eg.: "mozilla/5.0 (windows; u; windows nt 5.1; en-gb; rv:1.8.1.15) gecko/20080623 firefox/2.0.0.15"
	 * @param integer $Browser   
	 *
	 * @return bool - true on success
	 */
	function checkUnknownBotFromUserAgent($UserAgent, &$Browser /*in-out*/ ) {

		if (
				( strpos( $UserAgent, 'robot',  0 ) !== false )
			|| 	( strpos( $UserAgent, 'crawl',  0 ) !== false )
			|| 	( strpos( $UserAgent, 'spider', 0 ) !== false )
			|| 	( strpos( $UserAgent, 'bot',    0 ) !== false )
		) {
			$Browser->browser_id   = _JS_DB_BRWSR__ID_BOT_UNKNOWN;
			$Browser->browser_key  = _JS_DB_BRWSR__KEY_BOT_UNKNOWN;
			$Browser->browser_name = _JS_DB_BRWSR__NAME_BOT_UNKNOWN;
			$Browser->browser_img  = _JS_DB_BRWSR__IMG_BOT_UNKNOWN;

			return true;
		}

		return false;
	}

	/**
	 * checks if ip.address is a local address, therefore we do not check the whois or make a tld-lookup!
	 * needed for e.g. intranet cms
	 *
	 * @param string $ip
	 * @return bool
	 */
	function isIpAddressIntranet( $ipAddressStr ) {

		// mic: ONLY FOR DEBUG SET TO FALSE
		//return false;

		$local = '/^10|^169\.254|^172\.16|^172\.17|^172\.18|^172\.19|^172\.20|^172\.21|^172\.22|^172\.23|^172\.24|^172\.25|^172\.26|^172\.27|^172\.28|^172\.29|^172\.30|^172\.31|^192|0:0:0:0:0:0:0:1/';

		if( preg_match( $local, $ipAddressStr ) ) {
			return true;
		}

		return false;
	}

	/**
	 * checks if ip.address is a local address, therefore we do not check the whois or make a tld-lookup!
	 * needed for e.g. intranet cms
	 *
	 * @param string $ip
	 * @return bool
	 */
	function isIpAddressLocalHost( $ipAddressStr ) {

		$substr4 = substr( $ipAddressStr, 0, 4 );

		if ( $substr4 === '127.' )
			return true;
		
		return false;
	}
	
	/**
	 * checks if the given ip-address is valid
	 *
	 * From where we should get list of reserved blocks?
	 *    1) http://www.rfc-editor.org/rfc/rfc3330.txt
	 *    2) http://www.iana.org/assignments/ipv4-address-space/
	 * 	        "Many of the IP blocks which were formally unallocated are allocated now", so we use RFC3330
	 * 	 
	 * 	 
	 * 	 
	 * Part of: http://www.rfc-editor.org/rfc/rfc3330.txt
	 * 	 
	 *    Address Block             Present Use                       Reference
	 *    ---------------------------------------------------------------------
	 *    0.0.0.0/8            "This" Network                 [RFC1700, page 4]
	 *    10.0.0.0/8           Private-Use Networks                   [RFC1918]
	 *    14.0.0.0/8           Public-Data Networks         [RFC1700, page 181]
	 *    24.0.0.0/8           Cable Television Networks                    --
	 *    39.0.0.0/8           Reserved but subject
	 *                            to allocation                       [RFC1797]
	 *    127.0.0.0/8          Loopback                       [RFC1700, page 5]
	 *    128.0.0.0/16         Reserved but subject
	 *                            to allocation                             --
	 *    169.254.0.0/16       Link Local                                   --
	 *    172.16.0.0/12        Private-Use Networks                   [RFC1918]
	 *    191.255.0.0/16       Reserved but subject
	 *                            to allocation                             --
	 *    192.0.0.0/24         Reserved but subject
	 *                            to allocation                             --
	 *    192.0.2.0/24         Test-Net
	 *    192.88.99.0/24       6to4 Relay Anycast                     [RFC3068]
	 *    192.168.0.0/16       Private-Use Networks                   [RFC1918]
	 *    198.18.0.0/15        Network Interconnect
	 *                            Device Benchmark Testing            [RFC2544]
	 *    223.255.255.0/24     Reserved but subject
	 *                            to allocation                             --
	 *    224.0.0.0/4          Multicast                              [RFC3171]
	 *    240.0.0.0/4          Reserved for Future Use        [RFC1700, page 4]
	 * 	 
	 *
	 * @param string $ipAddress
	 * @return string
	 */
	function isIpAddressValidRfc3330( $ipAddress ) {

		$substr2 = substr( $ipAddress, 0, 2 );
		$substr3 = substr( $ipAddress, 0, 3 );
		$substr4 = substr( $ipAddress, 0, 4 );
		$substr6 = substr( $ipAddress, 0, 6 );
		$substr8 = substr( $ipAddress, 0, 8 );
		$substr10 = substr( $ipAddress, 0, 10 );
		$substr12 = substr( $ipAddress, 0, 12 );
		$IpAsLong = sprintf( "%u", ip2long( $ipAddress ) );
		
		return ( ( $ipAddress != NULL ) &&
			( $substr2 !== '0.' )     // Reserved IP block
			&& ( $substr3 !== '10.' ) // Reserved for private networks
			&& ( $substr3 !== '14.' ) // IANA Public Data Network
			&& ( $substr3 !== '24.' ) // Reserved IP block
			&& ( $substr3 !== '27.' ) // Reserved IP block
			&& ( $substr3 !== '39.' ) // Reserved IP block
			&& ( $substr4 !== '127.' ) // Reserved IP block
			&& ( $substr6 !== '128.0.' ) // Reserved IP block
			&& ( $substr8 !== '169.254.' ) // Reserved IP block
			&& ( ( $IpAsLong < sprintf( "%u", ip2long( '172.16.0.0' ) ) ) // Private networks
				|| $IpAsLong > sprintf( "%u", ip2long( '172.31.255.255' ) ) ) 
			&& ( $substr8 !== '191.255.' ) // Reserved IP block
			&& ( $substr8 !== '192.0.0.' ) // Reserved IP block
			&& ( $substr8 !== '192.0.2.' ) // Reserved IP block
			&& ( $substr10 !== '192.88.99.' ) // Reserved IP block
			&& ( $substr8 !== '192.168.' ) // Reserved IP block
			&& ( ( $IpAsLong < sprintf( "%u", ip2long( '198.18.0.0' ) ) ) // Multicast addresses
				|| ( $IpAsLong > sprintf( "%u", ip2long( '198.19.255.255' ) ) ) )
			&& ( $substr12 !== '223.255.255.' ) // Reserved IP block
			&& ( ( $IpAsLong < sprintf( "%u", ip2long( '224.0.0.0' ) ) ) // Multicast addresses
				|| ( $IpAsLong > sprintf( "%u", ip2long( '239.255.255.255' ) ) ) )
			&& ( ( $IpAsLong < sprintf( "%u", ip2long( '240.0.0.0' ) ) ) // Reserved IP blocks
				|| ( $IpAsLong > sprintf( "%u", ip2long( '255.255.255.255' ) ) ) )
		);
		
				
		/* code from v2.2.3
		return ( ( $ipAddress != NULL ) &&
			// Reserved IP blocks
			( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '0.0.0.0' ) ) )
			|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '2.255.255.255' ) ) ) )
			&& ( substr( $ipAddress, 0, 2 ) !== '5.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 2 ) !== '7.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 3 ) !== '10.' ) // Reserved for private networks
			&& ( substr( $ipAddress, 0, 3 ) !== '14.' ) // IANA Public Data Network
			&& ( substr( $ipAddress, 0, 3 ) !== '23.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 3 ) !== '27.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 3 ) !== '31.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 3 ) !== '36.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 3 ) !== '37.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 3 ) !== '42.' ) // Reserved IP block
			&& ( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '92.0.0.0') ) ) // Reserved IP blocks
				|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '95.255.255.255' ) ) ) )
			&& ( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '100.0.0.0' ) ) ) // Reserved IP blocks
				|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '120.255.255.255' ) ) ) )
			&& ( substr( $ipAddress, 0, 4 ) !== '127.' ) // Loop-back interfaces
			&& ( substr( $ipAddress, 0, 8 ) !== '169.254.' ) // Link-local addresses
			&& ( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '172.16.0.0' ) ) ) // Private networks
				|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '172.31.255.255' ) ) ) )
			&& ( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '173.0.0.0' ) ) ) // Reserved IP blocks
				|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '187.255.255.255' ) ) ) )
			&& ( substr( $ipAddress, 0, 8 ) !== '192.168.' ) // Private networks
			&& ( substr( $ipAddress, 0, 4 ) !== '197.' ) // Reserved IP block
			&& ( substr( $ipAddress, 0, 4 ) !== '223.' ) // Reserved IP block
			&& ( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '224.0.0.0' ) ) ) // Multicast addresses
				|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '239.255.255.255' ) ) ) )
			&& ( ( sprintf( "%u", ip2long( $ipAddress ) ) < sprintf( "%u", ip2long( '240.0.0.0' ) ) ) // Reserved IP blocks
				|| ( sprintf( "%u", ip2long( $ipAddress ) ) > sprintf( "%u", ip2long( '255.255.255.255' ) ) ) )
		);
		*/
	}

   /**
    * Executes a WHOIS query
    *
    * Appended By DVW
    *
    * @param string $server
    * @param string $query
    * @return array
    *
    * @since 2.3.x: added maximum time to fsockopen from server config
    * @todo mic 20081013: maybe moving ths function into a own class AND into backend?
    */
    function executeWhois( $server, $query ) {
    	global $mainframe;

        $resultList = array();

        // mic 20081013: get maximum time for fsockopen - AT: NO, NO, NO!!!! We are on front page!
        //$timeout = ini_get( 'max_execution_time' );
		$timeout = 1;//value 0.1 is better but I am not sure if it is allowed

		js_echoJSDebugInfo('server', $server);
		js_echoJSDebugInfo('query', $query);

        if( ( $socket = fsockopen( gethostbyname( $server ), 43, $errno, $errstr, $timeout ) ) != false ) {
                // send the query string to the socket
                fputs( $socket, $query, strlen( $query ) );

                $result		= array();
                $appended	= false;
                while( !feof( $socket ) ) {
                    $contents = fgets( $socket, 4096 );
                    $contents = trim( $contents );
                    if( empty( $contents ) ) {
                        continue;
                    }

                    $first = $contents[0];

                    if( $first == '%' || $first == '<' || $first == '#' ) {
                        continue;
                    }

                    $comment = strstr( $contents, '//' );

                    if( $comment ) {
                        continue;
                    }

                    $seperatorIndex = strpos($contents, ':');

                    if( $seperatorIndex <= 0 ) {
                        continue;
                    }

                    $key	= trim( substr( $contents, 0, $seperatorIndex ) );
                    $value	= trim( substr( $contents, $seperatorIndex + 1 ) );
                    // Make sure we just have single spaces
                    $value	= preg_replace( '/\s+/', ' ', $value );

                    if( $key == 'inetnum') {
                        $appended	= false;
                        $result		= array();
                    }elseif( $key == 'source' ) {
                        if( !$appended ) {
                            $resultList[] = $result;
                        }
                        $appended = true;
                    }
                    if( array_key_exists( $key, $result ) ) {
                        $entry = $result[$key];
                        if( $entry ) {
                            $value = $entry . "\n" . $value;
                        }
                    }
                    $result[$key] = $value;
                }

                fclose( $socket );
        }

        //filter Results - We are only interested in first result using status ASSIGNED
        //Some results do not have "status", but this is could be our "ASSIGNED" result
        $returnList = array();

        foreach ( $resultList as $result ) {
            if( array_key_exists( 'status', $result ) ) {
                $status = $result['status'];
                //@at stripos() function is not supported by PHP 4.0 //@todo could we here use strpos(); function?
                // mic 20081013: re-added it, because stripos is a function in base.classes.php since 2.3.x
                $pos = stripos( $status, 'SSIGNED' );
                //$pos = strpos( strtolower( $status ), strtolower( 'SSIGNED' ) );

                if( $pos == false ) {
                    continue;
                }else{
                   return array( $result );
                }
            }
            $returnList[] = $result;
        }

        return $returnList;
    }


    /**
    *   Overworked by DVW
    */
    //function queryWhois( $server, $query, &$ipFrom = "0.0.0.0", &$ipTo  = "255.255.255.255", &$result ) {//problem in PHP 4.0 (probably with defalut argument value)
    function queryWhois( $ip_to_check, $server, $query, &$ipFrom, &$ipTo, &$result ) {
        $countryCode	= '';
        $resultList		= $this->executeWhois( $server, $query );

        if( !empty( $resultList ) ) {
            //$line	    = '';
            $prevline   = '';
            $getCountry = false;
            $getRange   = false;
            $result		= null;

            foreach ( $resultList as $whois) {
                // process the result of the Whois lookup
                if( empty( $whois ) ) {
                	continue;
                }

                if( array_key_exists( 'inetnum', $whois ) ) {
                    $inetnum = $whois['inetnum'];
                    // get IP range and see if it's narrower than the current range
                    // note: ip2long gives signed results, so we convert to unsigned using sprintf

					$getCountry = false;

					$values = null;

					if (substr_count($inetnum, ' - ') > 0)	// Netblock notation
					{
						$values = explode(' - ', $inetnum);
					}
					else if (substr_count($inetnum, '/') > 0)	// CIDR block notation
					{
						/* - Begin CIDR notation parser, heavily based on code from Leo Jokinen <legetz81@yahoo.com> - */

						$values = explode('/', $inetnum);

						if (is_array($values))
						{
							if (count($values) == 2)
							{
								$values[0] = trim($values[0]);
								$values[1] = trim($values[1]);
								if (strlen($values[0])>0 && strlen($values[1])>0)
								{
									$bin = '';
									for ($i = 1; $i <= 32; $i++)
										$bin .= $values[1] >= $i ? '1' : '0';
									for ($i = substr_count($values[0], "."); $i < 3; $i++)
										$values[0] .= ".0";
		
									$nm = ip2long(bindec($bin));
									$v0 = ip2long($values[0]);
									if (is_int($nm) && is_int($v0))
									{
										$nw = ($v0 & $nm);
										$bc = $nw | (~$nm);
			
										$values[0] = long2ip($nw);
										$values[1] = long2ip($bc);
									}
								}
							}
						}

						/* - End CIDR notation parser ---------------------------------------------------------------- */
					}

					if (is_array($values))
					{
						if (count($values) == 2)
						{
							$values[0] = trim($values[0]);
							$values[1] = trim($values[1]);
							if (strlen($values[0])>0 && strlen($values[1])>0)
							{
								if (sprintf("%u", ip2long($values[0])) >= sprintf("%u", ip2long($ipFrom)) &&
								    sprintf("%u", ip2long($values[1])) <= sprintf("%u", ip2long($ipTo)))
								{
									$ipFrom = $values[0];
									$ipTo = $values[1];
		
									$getCountry = true;
								}
							}
						}
					}
                }

                if( array_key_exists( 'netname', $whois ) && $getCountry ) {
                    $netname = $whois['netname'];
                    // filter some of the generic networks

                    $ipA	= explode( '.', $ip_to_check );
                    $ipNet	= 'NET' . $ipA[0];

                    if( substr( $netname, 0, 6 )	=== 'LACNIC'
                    || substr( $netname, 0, 7 )		=== 'AFRINIC'
                    || substr( $netname, 0, 9 )		=== 'RIPE-CIDR'
                    || substr( $netname, 0, 9 )		=== 'ARIN-CIDR'
                    || substr( $netname, 0, 10 )	=== 'IANA-BLOCK'
                    || substr( $netname, 0, 13 )	=== 'IANA-NETBLOCK'
                    || substr( $netname, 0, 12 )	=== 'ERX-NETBLOCK'
                    || substr( $netname, 0, strlen( $ipNet ) ) === $ipNet ) {
                        $getCountry = false;
                    }

                    if( $server === 'whois.ripe.net' ) {
                        if( substr( $netname, 0, 6 ) === 'LACNIC' ) {
                        	$countryCode = 'LACNIC';
                        }
                        if( ( substr( $netname, 0, 7 ) === 'AFRINIC') || ( $ipA[0] === '41' ) ) {
                        	$countryCode = 'AfriNIC';
                        }
                    }
                }
                if( array_key_exists( 'OrgName', $whois ) ) {
                    // LACNIC Joint Whois entry, get country and IP range now
                    $result		= $whois;
                    $getCountry	= true;
                    $getRange	= true;
                }
                if( array_key_exists( 'role', $whois )  && $result == null ) {
                    // LACNIC Joint Whois entry, get country and IP range now
                    $result = $whois;
                }
                if( array_key_exists( 'NetRange', $whois ) && $getRange ) {
                    $NetRange = $whois['NetRange'];
                    // get IP range from LACNIC Joint Whois entry

                    $values = explode( ' - ', $NetRange );

                    if( sprintf( "%u", ip2long( $values[0] ) ) >= sprintf( "%u", ip2long( $ipFrom ) )
                    && sprintf( "%u", ip2long( $values[1] ) ) <= sprintf( "%u", ip2long( $ipTo ) ) ) {
                        $ipFrom	= $values[0];
                        $ipTo	= $values[1];
                    }

                    $getRange = false;
                }
                if( array_key_exists( 'country', $whois ) && $getCountry ) {
                    $country = $whois['country'];
                    // the last ip range was narrower than the ones before and we found
                    // a country entry; now extract the country entry

                    $countryCode = $country;

                    if( $countryCode !== 'AP') {
                    	$getCountry = false;
                    }
                }
                if( array_key_exists( 'nserver', $whois ) && $getCountry ) {
                    $nserver = $whois['nserver'];
                    // if there is no country entry, try to get the TLD from the name
                    // server entry (e.g. registro.br does not include a country code)

                    $pos = strrpos( $nserver, '.' ) + 1;

                    if( $pos > 1) {
                        $xt = trim( substr( $nserver, $pos ) );

                        if( ereg( '([a-zA-Z])', $xt ) ) {
                            $countryCode = $xt;
                        }
                    }
                }
                //RB: question for mic: why did you remove the .br part?
                // mic 20081013: because they use now a capture code for accessing
                /*
                else if ( array_key_exists("nserver", $whois) strstr($line, "registro.br") !== false && $getCountry)
                {
                	registro.br does neither include a country code nor a name
                    server entry for some entries, so find these entries here

                  	$countryCode = "br";
                }
                */
            }
            $result = $resultList[0];
        }

        return $countryCode;
    }

	/**
	 *  Create or update visits table and return its id
     *
     *  @return mixed - false on failure; integer visit_id on success
     */
	function registerVisit( $visitor_id ) {

		js_echoJSDebugInfo('Perform Visit counting process', '');

		//@todo perf
		$query = 'SELECT visit_id'
		. ' FROM #__jstats_visits'
		. ' WHERE visitor_id = ' . $visitor_id
		. '   AND CAST(CONCAT(visit_date, \' \', visit_time) AS DATETIME) >= DATE_ADD(\''.$this->now_date_str .' '. $this->now_time_str.'\', INTERVAL -' . $this->JSConf->onlinetime . ' MINUTE)'
		;
		$this->db->setQuery( $query );
		$visit_id = (int)$this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;

		$joomla_userid = $this->getJoomlaCmsUserId();

		if( $visit_id ) {
			js_echoJSDebugInfo('Visit already in progress. Continue this Visit', '');
			// it's not the 1st page request, so update visits row

			$query = 'UPDATE #__jstats_visits SET'
			. '  visit_date = \'' . $this->now_date_str . '\'' //time last visit
			. ', visit_time = \'' . $this->now_time_str . '\'' //time last visit
			;
			
			if( $joomla_userid != 0 ) {
				// if User login to Joomla CMS at least once in entire sesion, store his 'Joomla Id' //do not clear the UserId if the user logs out.
				$query .= ', joomla_userid = ' . $joomla_userid . ' ';
			}
			$query .= ' WHERE visit_id = \'' . $visit_id . '\'';

			$this->db->setQuery( $query );
			if (!$this->db->query())
				return false;
		} else {
			// this is 1st page request, lets create a visits entry
			js_echoJSDebugInfo('This is new Visit', '');

			$query = 'INSERT INTO #__jstats_visits (visitor_id, joomla_userid, visit_date, visit_time)'
			. ' VALUES ('
			. ' ' . $visitor_id . ','
			. ' ' . $joomla_userid . ','
			. ' \'' . $this->now_date_str . '\','
			. ' \'' . $this->now_time_str . '\''
			. ' )'
			;
			$this->db->setQuery( $query );
			if (!$this->db->query())
				return false;

			$visit_id = $this->db->insertid();
		}

		return $visit_id;
	}
	
	function getPageTitle() {
		global $mainframe;
		
		$page_title = '';
		
		//outside joomla we can not check page title
		if( defined( '_JS_STAND_ALONE' ) )
			$page_title = _JS_PHP__PAGE_TITLE_FOR_PAGES_OUTSIDE_JOOMLA_CMS;
		else
			$page_title = $this->db->getEscaped( $mainframe->getPageTitle() );

		// trim page title if longer than 255 characters
		if( strlen( $page_title ) > 255 ) {
			$page_title = substr( $page_title, 0, 254 );
		}
		
		return $page_title;
	}
	
	/** This function remove lang setting from page URL to treat multi language versions of one page as the same
	 *  
	 *  It is used when $this->JSConf->enable_i18n == true; "I18n Support"; "Multiple translations as one"
	 *
	 *  @todo we should check if SEF or i18n is enabled before we remove anything!
	 */
	function removeLanguageFromUrl( $url ) {
		
		// @todo mic 20081013: check if position 8 is correct ???
		if( strpos( $url, '?lang=' ) !== false ) {
			$url = str_replace( substr( $url, strpos( $url, '?lang=' ), 8 ), '', $url );
		} else if( strpos( $url, '&lang=' ) !== false ) {
			$url = str_replace( substr( $url, strpos( $url, '&lang=' ), 8 ), '', $url );
		} else if( strpos( $url, 'lang,' ) !== false ) { //for SEF urls
			$url = str_replace( substr( $url, strpos( $url, 'lang,' ), 8 ), '', $url );
		}
		
		return $url;
	}

	/** return page imprssion_id or 0 on fail */
	function registerPageImpression( $visit_id, $page_url ) {

		js_echoJSDebugInfo('Perform Page counting process', '');
		
		if( $page_url == '' )
			return 0;
		
		$page_title = $this->getPageTitle();

		if( $this->JSConf->enable_i18n ) {
			$page_url = $this->removeLanguageFromUrl( $page_url );
		}

		$query = ''
		. ' SELECT'
		. '   page_id,'
		. '   page_title'
		. ' FROM'
		. '   #__jstats_pages'
		. ' WHERE'
		. '   page = \'' . $this->db->getEscaped( $page_url ) . '\''
		. ' LIMIT 1'
		;
		$this->db->setQuery( $query );
		$row = $this->db->loadObject();

		$page_id = 0;
		if ( $row ) {
			$page_id = $row->page_id;

			if( $row->page_title == '' ) {
				$query = 'UPDATE #__jstats_pages'
				. ' SET page_title = \'' . $page_title . '\''
				. ' WHERE page_id = \'' . $page_id . '\''
				;
				$this->db->setQuery( $query );
				if (!$this->db->query())
					return false;
			}
		} else {
			$query = 'INSERT INTO #__jstats_pages (page, page_title)'
			. ' VALUES (\'' . $this->db->getEscaped( $page_url ) . '\', \'' . $page_title . '\')'
			;
			$this->db->setQuery( $query );
			if (!$this->db->query())
				return false;

			$page_id = $this->db->insertid();
		}


		$query = 'INSERT INTO `#__jstats_impressions` (`page_id`, `visit_id`)'
		. ' VALUES ('.$page_id.','.$visit_id.')'
		;
		$this->db->setQuery( $query );
		if (!$this->db->query())
			return false;

		//imprssion_id not implemented yet
		//$imprssion_id = $this->db->insertid();
		//js_echoJSDebugInfo('This is imprssion_id=\''.$imprssion_id.'\'', '');
		//return $imprssion_id; 

		js_echoJSDebugInfo('Page counting process successful', '');

		return true;
	}

	/** return ref_url only when there was redirection from different domain or '' when there this is not redirection */
	function getReferrer() {
		
		if( !isset( $_SERVER['HTTP_REFERER'] ) ) {
			//js_echoJSDebugInfo('Referrer not set, nothing to register', '');
			return '';
		}

		$ref_url = trim( $_SERVER['HTTP_REFERER'] );

		if( $ref_url == '' ) {
			//js_echoJSDebugInfo('Referrer is empty, nothing to register', '');
			return '';
		}

		if( !isset( $_SERVER['HTTP_HOST'] ) ) {
			//js_echoJSDebugInfo('HTTP_HOST is not set, unable to determine if this is redirection or not. RegisterRefferer process ended', '');
			return '';
		}

		//why We allow only http:// and https://?
		if ( (substr( $ref_url, 0, 7 ) != 'http://') && (substr( $ref_url, 0, 8 ) != 'https://') ) {
			//js_echoJSDebugInfo('This is not http:// nor https:// - refferer not registered', '');
			return '';
		}
		
		return $ref_url;
	}
	
	function getDomainFromUrl($url) {
		$dom = $url;
		
		//remove prefixes
		if ( substr( $dom, 0, 7 ) == 'http://' )
			$dom = substr( $dom, 7 );
		else if ( substr( $dom, 0, 8 ) == 'https://' )
			$dom = substr( $dom, 8 );

		if ( strtolower(substr( $dom, 0, 4 )) == 'www.' )
			$dom = substr( $dom, 4 );

		//cut domain
		$pos = strpos( $dom, '/' );
		if( $pos !== false )
			$dom = substr( $dom, 0, $pos );

		return $dom;
	}
	
	/** return visit_id or 0 on fail */
	function registerReferrer( $visit_id, $ref_url, $ref_domain ) {

		js_echoJSDebugInfo('Perform RegisterReferrer process', '');

 		/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!) */
		$query = 'INSERT INTO #__jstats_referrer (referrer, domain, day, month, year, visit_id)'
		. ' VALUES (\'' . $this->db->getEscaped($ref_url) . '\','
		. ' \'' . $ref_domain . '\''
		. ','. js_gmdate('d', $this->now_timestamp)
		. ','. js_gmdate('m', $this->now_timestamp)
		. ','. js_gmdate('Y', $this->now_timestamp)
		. ','. $visit_id
		. ')'
		;
		$this->db->setQuery( $query );
		if (!$this->db->query())
			return false;
	
		js_echoJSDebugInfo('RegisterReferrer success!', '');

		return $visit_id; //$imprssion_id should be primary for #__jstats_referrer table and it should be returned here
	}

	// TEST CODE
	//$keys = 'p=|we=|q=|wy=';
	//$str_start = getmicrotime();
	//getKeyWordsStr("http://google.com/search", $keys);
	//getKeyWordsStr("http://google.com/search?sourceid=navclient", $keys);
	//getKeyWordsStr("http://google.com/search?sourceid=navclient&none", $keys);
	//getKeyWordsStr("http://google.com/search?q=firstkeyword+secondkeyword+thirdkeyword", $keys);
	//getKeyWordsStr("http://google.com/search?sourceid=navclient&q=firstkeyword+secondkeyword+thirdkeyword", $keys);
	//getKeyWordsStr("http://google.com/search?sourceid=navclient&aq=0h&oq=firstkeyword+&hl=en&ie=UTF-8&q=firstkeyword+secondkeyword+thirdkeyword", $keys);
	//getKeyWordsStr("http://google.com/search?sourceid=navclient&aq=0h&oq=firstkeyword+&hl=en&ie=UTF-8&q=firstkeyword+secondkeyword+thirdkeyword&additional", $keys);
	//$str_end = getmicrotime();
	//echo sprintf('%.5f', $str_end - $str_start) . ' seconds' . '<br/>';
	//echo sprintf('%0.3f', memory_get_usage() / 1024 ).' kB' . '<br/>';
	function getKeyWordsStr($url, $keys)
	{
		//below code is working correctly, but it is slower than str_replace()
		//$url_arr = parse_url($url);
		//if (!isset($url_arr['query']))
		//	return ''; //there is not query or query is empty
		//$query = '&'.$url_arr['query'];
		$query = str_replace('?', '&', $url); //str_replace is faster than parse_url() function
	
		$ar = explode("|", $keys);
		for ($i = 0; $i < count($ar); $i++) {
			$key = $ar[$i];
	
			$pos = strpos( $query, '&'.$key );
			if( $pos !== false ) {
				$pos_begin = $pos+strlen($key)+1;
				$pos_end = strpos( $query, '&', $pos_begin );
	
				if( $pos_end !== false )
					return substr( $query, $pos_begin, $pos_end-$pos_begin );
				else
					return substr( $query, $pos_begin );
			}
		}
	
		// 1) below method is working correctly but it is above two times slower than operating on strings
		// 2) preg_match CRASHES! on some machines!! For details see [#18344] *** glibc detected *** double free or corruption (fasttop) with AOL serach results links
		//if( preg_match( '/[\?&]('.$keys.')(.+?)(&|$)/i', $url, $matches ) ) {
		//	for ($i=2; count($matches); $i++) { //we must start from 2 (not 0)
		//		if( $matches[$i] != null ) {
		//			return $matches[$i];
		//		}
		//	}
		//}
	
		return '';//no keywords in query
	}
	
	
	function getKeyWords( $ref_url, $ref_domain, &$kwrds ) {
		
		$kwrds = '';

		$query = ''
		. ' SELECT'
		. '   searcher_id      AS searcher_id,'
		. '   searcher_name    AS searcher_name,'
		. '   searcher_domain  AS searcher_domain,'
		. '   searcher_key     AS searcher_key'
		. ' FROM'
		. '   #__jstats_searchers'
		. ' WHERE'
		. '   searcher_id>'._JS_DB_SERCH__ID_SEARCH_JOOMLA_CMS //this is special entry, we must omit it (entry for future use)
		. '   AND \''.$ref_domain.'\' LIKE CONCAT(\'%\', `searcher_domain` , \'%\')'
		. ' LIMIT 1'
		;
		$this->db->setQuery( $query );
		$row = $this->db->loadObject();
		if ($this->db->getErrorNum() > 0) {
			//echo $this->db->getErrorMsg();
			// mic: NO ERROR MESSAGES HERE, WE ARE AT THE FRONTEND!!
			// OR WE CREATE A LOGFILE ..... which i suggest
			return 0;
		}
		
		if (!$row)
			return 0;
			

		$kwrds = $this->getKeyWordsStr( $ref_url, $row->searcher_key );
		$kwrds = urldecode( $kwrds );
		//$kwrds = trim( $kwrds ); NO do not make trim!! We store exactly what user write in searcher! ('ove' and ' ove' - 'love' / ' oven')
		
		if ( strlen($kwrds) == 0 )
			return 0;
		
		$searcher_id = $row->searcher_id; //keywords not empty, so assign searcher_id

		return $searcher_id;
	}

	
	/**
	 * adds serach items from search engines into database
	 *
	 * @param string $ref_domain
	 * @param string $ref_url
	 * @return visit_id or 0. On fail false is returned
	 */
	function registerKeyWords( $visit_id, $ref_url, $ref_domain ) {

		js_echoJSDebugInfo('Perform Register Key Words process', '');
		
		$kwrds = '';
		$searcher_id = $this->getKeyWords( $ref_url, $ref_domain, $kwrds );
		
		if ( ( $kwrds == '' ) || ( $searcher_id == 0 ) ) {
			js_echoJSDebugInfo('Search engine (searcher) not recognized or empty keywords', '');
			return 0;
		}

		/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!) */
		$query = 'INSERT INTO #__jstats_keywords (kwdate, searcher_id, keywords, visit_id)'
		. ' VALUES (\''.$this->now_date_str.' '.$this->now_time_str.'\','
		. $searcher_id . ','
		. ' \'' . $this->db->getEscaped( $kwrds ) . '\','.$visit_id.')'
		;
		$this->db->setQuery( $query );
		if (!$this->db->query())
			return false;

		js_echoJSDebugInfo('Register Key Words success!', '');
		
		return $visit_id; //$imprssion_id should be primary for #__jstats_keywords table and it should be returned here
	}
	
	/**
	 * This function recognize and register when visitor get to Your (Joomla CMS) pages
	 *    from other pages (like searches (eg. google.com) etc.)
	 *
	 * NOTICE: 
	 *   To get full information about entries and redirections from other sites,
	 *   You must sum results from 2 tables: #__jstats_keywords and #__jstats_referrer
	 *
	 * @param in  int $visit_id                - current visit_id. Needed to store data in database (visit_id store time, visitor data etc.)
	 * @param out int $was_keyword_registered  - when key words were registered returned integer will be greather then 0 (in fact $visit_id will be returned)
	 * @param out int $was_referrer_registered - when there was redirection (excluding search engines) registered returned integer will be greather then 0 (in fact $visit_id will be returned)
	 *
	 * @return bool - false when that was not redirection or on failure
	 */
	function registerReferrerOrKeyWords( $visit_id, &$was_keyword_registered, &$was_referrer_registered ) {

		$was_keyword_registered  = 0;
		$was_referrer_registered = 0;

		$ref_url = $this->getReferrer();
		if ($ref_url == '') {
			js_echoJSDebugInfo('Refferer not set.', '');
			return false;
		}
		
		$ref_domain = $this->getDomainFromUrl($ref_url);
		if ($ref_domain == '') {
			js_echoJSDebugInfo('Empty domain! Registering Referrer or/and Keywords fail!', '');
			return false;
		}

		$was_keyword_registered = $this->registerKeyWords( $visit_id, $ref_url, $ref_domain );

		if ( $was_keyword_registered === false )
			return false;
		
		if ( $was_keyword_registered > 0 )
			return true;

		$hst = trim( $_SERVER['HTTP_HOST'] );
		if ( strpos( $ref_url, $hst ) !== false ) {
			js_echoJSDebugInfo('This is not redirection from other domain - do not register refferer', '');
			return false;
		}

		js_echoJSDebugInfo('KeyWords were not registered so we register Referrer', '');
		$was_referrer_registered = $this->registerReferrer( $visit_id, $ref_url, $ref_domain );

		if ( $was_referrer_registered === false )
			return false;

		if ( $was_referrer_registered > 0 )
			return true;

		return true;
	}
}









/**
 *  Count visitor and pages impressions. This global function count user that call this function.
 *
 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 *               ALL VISITORS COUNTINGS SHOULD BE THROUGH THIS FUNCTION
 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 *
 *
 *  NOTICE:
 *     It also works for pages outside Joomla CMS!
 *        but some features will be unavailable: 
 *            - determination if user is logged to Joomla CMS
 *            - page title is unavailable
 *
 *
 *  require_once(dirname(__FILE__) .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'administrator' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'count.classes.php');
 *
 *  //@param in  bool   $PrintActivatedString  - string that should be printed (echo $html_content;)
 *  @param out string $html_content          - string that should be printed (echo $html_content;)
 *  @return mixed     $visit_id              - when $visit_id > 0 then success; Details: return false on failure; integer visit_id on success; 0 when page is excluded from counting; greater than 0 is valid visit_id
 */
function js_gCountVisitor( &$html_content ) {

	$JSCountVisitor = new js_JSCountVisitor();
	$js_visit_id = $JSCountVisitor->countVisitor( $html_content );

	return $js_visit_id;
}
