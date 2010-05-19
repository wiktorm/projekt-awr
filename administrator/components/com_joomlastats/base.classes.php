<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



/**
 * This is file with basic classes
 *
 * It is used also in non-joomla environment
 *
 * Basic classes should:
 *	  - be small
 *	  - be well comented
 *	  - not generate any HTML code
 *    - should provide constants, defines
 *    - no bussines logic
 *    - no compatibility
 */
if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}



require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'db.constants.php' );





/**
 * 'Joomla Stats' class that contain SYSTEM CONSTANTS (this class replace define('AAAA'); that are globals)
 *
 * All members are READ ONLY!
 */
class js_JSSystemConst
{
	/**
	 * below string will be written to joomla front page if JS are activated for this particular page
	 * it is written just before counting that page
	 * NOTE: do not add \n or any invicible characters
	 * - they produce additional verical space in IE when they are in <td></td> tag without any other content
	 */
	var $htmlFrontPageJSActivatedString = '<!-- JoomlaStatsActivated -->';
	
	/**
	 *  List of all JS tables
	 *  Use this list to uninstall datbase, optimize database etc.
	 */
	var $allJSDatabaseTables = array( '#__jstats_browsers', '#__jstats_configuration', '#__jstats_ipaddresses', '#__jstats_iptocountry', '#__jstats_keywords', '#__jstats_impressions', '#__jstats_impressions_sums', '#__jstats_pages', '#__jstats_referrer', '#__jstats_searchers', '#__jstats_systems', '#__jstats_topleveldomains', '#__jstats_visits' );
	
	var $defaultPathToImagesTld     = 'tld-png-16x11-1';
	var $defaultPathToImagesOs      = 'os-png-14x14-1';
	var $defaultPathToImagesBrowser = 'browser-png-14x14-1';
}

/**
 * 'Joomla Stats' class that contain DEFAULT 'Joomla Stats' configuration
 *
 * All members are READ ONLY!
 */
class js_JSConfDef
{
	/** constructor do nothing. Only for PHP4.0 */
	function __construct() {
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
	function js_JSConfDef()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}

		
	/**
	 *	Members initialization values are system default values!
	 */

	/**
	 * this constant was hold by define('_JoomlaStats_V','2.3.0_dev2008-08-12'); in previous releases version of script
	 * this member is not stored to database by function storeConfigurationToDatabase() (security)
	 * version x.y.w.z  z - is SVN version
	 *
	 * NOTICE:
	 *   - Always should be 4 nuber sections!!! - see method JSVersionCompare(...)
	 *   - space is separation character to. Space differ development and release versions!!!
	 * 
	 * eg.: '2.3.0.151 dev' - for development snapshot
	 * eg.: '2.3.0.194'     - for release
	 * 
	 */
	var $JSVersion = '3.0.3.699';// eg '2.3.0.151 dev' 

	/** time online in [minutes] before new visitor */
	var $onlinetime = 15;

	/** option for starting statistics */
	var $startoption = 'r02';

	/** option for selecting 1 day or whole month at JoomlaStats start */
	var $startdayormonth = 'd';

	/** show statistics including summarized/purged data */
	var $include_summarized = true;

	/** show statistics with summarized/purged data in brackets [23244] //$show_summarized HAVE TO be set to false if $include_summarized = false */
	var $show_summarized = true;

	/** enable Whois queries */
	var $enable_whois = true;

	/** enable Joom!Fish i18n support */
	var $enable_i18n = true;

	/** Date when JS was installed first time in GMT time zone. It always have format 'YYYY-MM-DD h:m:s' and it is never empty. To divide string use '-', ' ' and ':' */
	var $first_installation_date = '1971-01-01 01:01:01';
}

/**
 * 'Joomla Stats' class that contain CURRENT 'Joomla Stats' configuration
 */
class js_JSConf extends js_JSConfDef
{
	/** Constructor load current configuration */
	function __construct( $initializeFromDatabase = true ) {
		parent::__construct();
		if( $initializeFromDatabase ) {
			$this->initializeByConfigurationFromDatabase();
		}
	}

	/**
	 *	This function read configuration stored in database and fill this class members
	 */
	function initializeByConfigurationFromDatabase() {

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$query = 'SELECT *'
		. ' FROM #__jstats_configuration';
		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadAssocList();
		if ($JSDatabaseAccess->db->getErrorNum() > 0) {
			$err_msg = 'Function: initializeByConfigurationFromDatabase() ' . $JSDatabaseAccess->db->getErrorMsg();
			js_echoJSDebugInfo($err_msg, '');
			return false;
		}


		foreach( $rows as $row ) {
			if( $row['description'] == 'version' ) {
				$this->JSVersion = $row['value'];
			}

			if( $row['description'] == 'onlinetime' ) {
				$this->onlinetime = $row['value'];
			}

			if( $row['description'] == 'startoption' ) {
				$this->startoption = $row['value'];
			}

			if( $row['description'] == 'startdayormonth' ) {
				$this->startdayormonth = $row['value'];
			}

			if( $row['description'] == 'language' ) {
				$this->language = $row['value'];
			}

			if( $row['description'] == 'include_summarized' ) {
				$this->include_summarized = ( $row['value'] === 'true' ) ? true : false;
			}

			if( $row['description'] == 'show_summarized' ) {
				$this->show_summarized = ( $row['value'] === 'true' ) ? true : false;
			}

			if( $row['description'] == 'enable_whois' ) {
				$this->enable_whois = ( $row['value'] === 'true' ) ? true : false;
			}

			if( $row['description'] == 'enable_i18n' ) {
				$this->enable_i18n = ( $row['value'] === 'true' ) ? true : false;
			}

			if( $row['description'] == 'first_installation_date' ) {
				$this->first_installation_date = $row['value'];
			}
		}

		return true;
	}

	/**
	 * This function write configuration (this class members) to database
	 *
	 * @param string $err_msg
	 * @return string
	 */
	function storeConfigurationToDatabase( &$err_msg ) {

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . $this->onlinetime . '\''
		. ' WHERE description = \'onlinetime\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . $this->startoption . '\''
		. ' WHERE description = \'startoption\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . $this->startdayormonth . '\''
		. ' WHERE description = \'startdayormonth\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . ( ( $this->include_summarized ) ? 'true' : 'false' ) . '\''
		. ' WHERE description = \'include_summarized\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . ( ( $this->show_summarized ) ? 'true' : 'false' ) . '\''
		. ' WHERE description = \'show_summarized\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . ( ( $this->enable_whois ) ? 'true' : 'false' ) . '\''
		. ' WHERE description = \'enable_whois\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . ( ( $this->enable_i18n ) ? 'true' : 'false' ) . '\''
		. ' WHERE description = \'enable_i18n\''
		;

		$queri[] = 'UPDATE #__jstats_configuration'
		. ' SET value = \'' . $this->first_installation_date . '\''
		. ' WHERE description = \'first_installation_date\''
		;


		$err_msg = '';
		foreach( $queri as $query ) {
			$JSDatabaseAccess->db->setQuery( $query );
			$JSDatabaseAccess->db->query();
			if ($JSDatabaseAccess->db->getErrorNum() > 0) {
				$err_msg .= $JSDatabaseAccess->db->getErrorMsg();
			}
		}

		if( strlen( $err_msg ) > 0 ) {
			js_echoJSDebugInfo('Function: storeConfigurationToDatabase() ' . $err_msg, '');
			return false;
		}

		return true;
	}

}



/**
 *  This class contain (hold) data about visitor
 *
 *  This class is only container for data - to pass data through methods etc.
 *
 *  Members of this class corespond to database table #__jstats_ipaddresses (will be renamed to #__jstats_visitors) column names
 *
 *  NOTICE:
 *     Creating new object create unknown Visitor. This is proper feature.
 */
class js_Visitor
{
	/** visitor ID */
	var $visitor_id         = 0;

	/** visitor IP address //value directly taken from visitor //@todo: example is missing (v6 also?) //@todo: missing value initialization */
	var $visitor_ip         = null;

	/** hold string //value directly taken from visitor //eg.: "mozilla/5.0 (windows; u; windows nt 5.1; en-gb; rv:1.8.1.15) gecko/20080623 firefox/2.0.0.15" */
	var $visitor_useragent  = '';	// User agent (i.e. browser)

	/** Requested page URL //value directly taken from visitor //@todo: example is missing */
	//var $RequestedPage    = null;

	/** true if user is excluded from counting statistics */
	var $visitor_exclude    = 0;//probably there must be int //@todo: define should be used

	/** Visitor type: _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR, _JS_DB_IPADD__TYPE_REGULAR_VISITOR, _JS_DB_IPADD__TYPE_BOT_VISITOR; Defines are in db.constants.php file
	 *
	 *  Visitor type depend on $this->Browser->browser_id
	 *    RANGES (browser_id):
	 *               0  - unknown
	 *       1 -   511  - JS defined internet browsers (1 - unknown browser)
	 *     512 -  1023  - user defined internet browsers (user can add here own browsers)
	 *    1024 -  2047  - JS defined bots/spiders/crawlers (1024 - unknown bot)
	 *    2048 - 65535  - user defined internet bots/spiders/crawlers (user can add here own bots/spiders/crawlers)
	 */
	var $visitor_type       = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;

	/** It holds object of class js_OS */
	var $OS                 = null;
	
	/** it contain object of class js_Browser (Visitor internet browser or Bot) - one data hold in two member - @todo)*/
	var $Browser            = null;

	/** It holds object of class js_Tld */
	var $Tld                = null;
	
	/** Valid only when $Type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR; eg.: "7.0" //(Connected with $BrowserName gives "Internet Explorer 7.0") */
	//var $browser_version  = '';
	
	/** ? URL @todo: example is missing. See JS trackers for details */
	//var $screen_x		= 0;
	//var $screen_y		= 0;

	/** String returned by PHP method gethostbyaddr( $visitor_ip ); If gethostbyaddr( $visitor_ip ); return $visitor_ip this member will contain empty string (''). eg.: "crawl-66-249-70-72.googlebot.com", "sewer.com.eu", but not "66.249.70.72" */
	var $nslookup		= '';//in PHP documentation it is called 'Internet host name'
}


/**
 *  This class contain (hold) data about visitor that are known by JS
 *
 *  This class is only container for data - to pass data through methods etc.
 */
class js_VisitorEx extends js_Visitor
{
	/** User ID if user is logged into 'Joomla CMS'. If user is not logged value is 0 */
	//var $joomla_userid			= null; this member should belong to other class

	/** Valid url to image. It can be used in <img src. Path could be without top level domain but it will work! eg.: "/components/com_joomlastats/images/os-png-16x16-1/windowsxp.png" */
	var $os_img_url			= null;

	/** HTML image tag. Ready to using in template eg.: "<img src="/components/com_joomlastats/images/os-png-16x16-1/windowsxp.png" alt="Windows XP" />" */
	var $os_img_html     	= null;

	/** Valid url to image. It can be used in <img src. Path could be without top level domain but it will work! eg.: "/components/com_joomlastats/images/os-png-16x16-1/pda.png" */
	var $ostype_img_url  	= null;

	/** HTML image tag. Ready to using in template eg.: "<img src="/components/com_joomlastats/images/os-png-16x16-1/pda.png" alt="PDA or Phone" />" */
	var $ostype_img_html 	= null;

	/** Valid url to image. It can be used in <img src. Path could be without top level domain but it will work! eg.: "/components/com_joomlastats/images/browser-png-16x16-1/explorer.png" */
	var $browser_img_url	= null;

	/** HTML image tag. Ready to using in template eg.: "<img src="/components/com_joomlastats/images/browser-png-16x16-1/explorer.png" alt="Internet Explorer" />" */
	var $browser_img_html	= null;

	var $browsertype_img_url  	= null;//probably null up to v2.5.0 - not enough time to implement
	var $browsertype_img_html 	= null;//probably null up to v2.5.0 - not enough time to implement
}

/**
 *  This class contain (hold) data about Operating System (OS)
 *
 *  This class is only container for data - to pass data through methods etc.
 *
 *  Members of this class corespond to database table #__jstats_systems (will be renamed to #__jstats_os) column names
 *     and virtual table #__jstats_ostype (those tables will be merged soon)
 *
 *  NOTICE:
 *     Creating new object create unknown OS. This is proper feature.
 */
class js_OS
{
	/** Primary Key from table #__jstats_os from column sys_id */
	var $os_id          = _JS_DB_OS__ID_UNKNOWN;//_JS_DB_OS__ID_UNKNOWN is equeal 0

	/** Primary Key from table #__jstats_ostype from column sys_id */
	var $ostype_id      = _JS_DB_OSTYP__ID_UNKNOWN;

	/** String that idetify OS eg.: "winme"; "windows nt 6.0"; "linux"; */
	var $os_key         = _JS_DB_OS__KEY_UNKNOWN;

	/** Human friendly OS name eg.: "Windows XP"; "Windows Vista"; "Mac OS"; "Linux"; */
	var $os_name        = _JS_DB_OS__NAME_UNKNOWN;

	/** Name of image file without extension eg.: "windowsxp"; "windowsvista"; "mac"; Extension is taken from directory name */
	var $os_img         = _JS_DB_OS__IMG_UNKNOWN;

	/** Human friendly OS Type name eg.: "Windows"; "PDA or Phone"; "Other"; */
	var $ostype_name    = _JS_DB_OSTYP__NAME_UNKNOWN;

	/** Name of image file without extension eg.: "unknown"; "windowsxp"; "linux"; "other"; "pda"; See defines _JS_DB_OSTYP for all available names. Extension is taken from directory name. */
	var $ostype_img     = _JS_DB_OSTYP__IMG_UNKNOWN;
}


/**
 *  This class contain (hold) data about Browsers
 *
 *  This class is only container for data - to pass data through methods etc.
 *
 *  Members of this class corespond to database table #__jstats_browsers merged with #__jstats_browserstype (virtual table) column names
 *
 *  NOTICE:
 *     Creating new object create unknown Browser. This is proper feature.
 */
class js_Browser
{
	/** Primary Key from table #__jstats_browser from column browser_id */
	var $browser_id        = _JS_DB_BRWSR__ID_UNKNOWN;//_JS_DB_BRWSR__ID_UNKNOWN is equeal 0

	/** Primary Key from table #__jstats_browsertype from column browsertype_id */
	var $browsertype_id    = _JS_DB_BRTYP__ID_UNKNOWN;

	/** String that idetify browser eg.: "msie"; "firefox" */
	var $browser_key       = _JS_DB_BRWSR__KEY_UNKNOWN;

	/** Human friendly Browser name eg.: "Internet Explorer"; "Google Chrome"; "FireFox"; "Netscape" */
	var $browser_name      = _JS_DB_BRWSR__NAME_UNKNOWN;

	/** Name of image file without extension eg.: "explorer"; "netscape"; "noimage"; "firefox"; Extension is taken from directory name. */
	var $browser_img       = _JS_DB_BRWSR__IMG_UNKNOWN;

	/** not enough time to implement - @todo */
	var $browsertype_name  = _JS_DB_BRTYP__NAME_UNKNOWN;

	/** not enough time to implement - @todo */
	/** Name of image file without extension eg.: "unknown"; "explorer"; "other"; "pda"; See defines _JS_DB_BRWSR__TYPE_ for all available names. Extension is taken from directory name. */
	var $browsertype_img   = _JS_DB_BRTYP__IMG_UNKNOWN;
}



/**
 *  This class contain (hold) data about Top Level Domains (TLD)
 *
 *  This class is only container for data - to pass data through methods etc.
 *
 *  Members of this class corespond to database table #__jstats_topleveldomains (will be renamed to #__jstats_tlds) column names
 *
 *  NOTICE:
 *     Creating new object create unknown TLD. This is proper feature.
 */
class js_Tld
{
	/** Primary Key from #__jstats_tldstable - integer. */
	var $tld_id    = _JS_DB_TLD__ID_UNKNOWN;

	/** Shortcuted name. Always lowercase eg.: "us", "de", "pl", "" (empty for unknown) */
	var $tld       = _JS_DB_TLD__TLD_UNKNOWN;

	/** Human redable country name eg.: "United States", "Germany", "Unknown" */
	var $tld_name  = _JS_DB_TLD__NAME_UNKNOWN;

	/** NOTICE: This variable is only for code clarity - it contains the same as $tld! Name of image file without extension eg.: "us"; "de"; "pl"; "unknown"; Extension is taken from directory name. */
	var $tld_img   = _JS_DB_TLD__TLD_UNKNOWN;
}

/** This function return timezone for JoomlaStats.
 *  Returned time zone is for anonymous front page users!
 *  @return double (eg. 1, 2, -9.5, 10.5)
 *
 *  Timezone should be always get through this function.
 *  For details see http://www.joomlastats.org:8080/display/JS/FAQ+Wrong+time+in+JoomlaStats and http://www.joomlastats.org:8080/display/JS/FAQ+Time+and+Time+Zones+in+JoomlaStats
 */
function js_getJSTimeZone() {

	$TZOffset = 0;
	
	//one of this HAVE TO be defined - if not this is serious bug
	if( defined( '_JEXEC' ) ) {
		// Joomla! 1.5
		global $mainframe;
		$TZOffset = $mainframe->getCfg( 'offset' );

		//// code from JDate
		//$_date = strtotime(gmdate("M d Y H:i:s", time()));
		//$date_a = $_date + $offset*3600;
		//$date_str = date('Y-m-d H:i:s', $date_a);
		//js_echoJSDebugInfo('Loc:', $date_str);
		//
		//$gm_date = gmdate("M d Y H:i:s", time());
		//js_echoJSDebugInfo('GMT time:', $gm_date);
	} else if( defined( '_JS_STAND_ALONE' ) ) {
		//stand alone
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'stand.alone.configuration.php' );
		$JSStandAloneConfiguration = new js_JSStandAloneConfiguration();
		$TZOffset = $JSStandAloneConfiguration->JConfigArr['offset'];
	}

	return $TZOffset;
}

/** This function return timestamp for now for JoomlaStats.
 *  Current time should be always get through this function.
 *
 *  Returned timestamp is in timezone for anonymous front page users!
 *
 *  For details see http://www.joomlastats.org:8080/display/JS/FAQ+Wrong+time+in+JoomlaStats and http://www.joomlastats.org:8080/display/JS/FAQ+Time+and+Time+Zones+in+JoomlaStats
 */
function js_getJSNowTimeStamp() {
	return (time() + (js_getJSTimeZone() * 3600));
}


/** Use this function insted of PHP gmdate() to format date!!! 
 *
 *  This function is connected with js_getJSNowTimeStamp() and js_getJSTimeZone()
 *  and provided to easier and reliable change in case of replace gmdate() to date() etc.
 */
function js_gmdate($format, $timestamp=null) {
	if ($timestamp===null)
		return gmdate($format, js_getJSNowTimeStamp());

	return gmdate($format, $timestamp);
}


/** This function return true if debug mode is turned on */
function js_isJSDebugOn() {

	$isJSDebugOn = false;
	
	//one of this HAVE TO be defined - if not this is serious bug
	if( defined( '_JEXEC' ) ) {
		// Joomla! 1.5
		$conf =& JFactory::getConfig();
		$isJSDebugOn = (boolean) $conf->getValue('config.debug');
	} else if( defined( '_JS_STAND_ALONE' ) ) {
		//stand alone
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'stand.alone.configuration.php' );
		$JSStandAloneConfiguration = new js_JSStandAloneConfiguration();
		$isJSDebugOn = (boolean) $JSStandAloneConfiguration->JConfigArr['debug'];
	}

	return $isJSDebugOn;
}

/**
 *  Print info when Debug is turned on.
 *  $title - use '' to not display title in bold (<b></b>)
 *  $pre   - use '' to not display pre in preformated block (tabulations, spaces and end of lines are visible) (<pre></pre>)
 *
 *  $pre accept also objects!!
 */
function js_echoJSDebugInfo($title, $pre='') {
	
	if (js_isJSDebugOn() == true) {

		$msg = '<br/>DEBUG info JoomlaStats: <b>'.$title.'</b>';
		if ( $pre !== '' ) {
			if ( (is_object($pre) == true) || (is_array($pre) == true)) {
				$msg .= '<pre>'.print_r($pre, true).'</pre>';
			} else {
				$msg .= ': \''.$pre.'\'';
			}
		}
		$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			
		echo $msg;
	}
}




// needed if php4 is used, because stripos is a php5 > only function
if( !function_exists( 'stripos' ) ) {
	function stripos( $haystack, $needle, $offset = 0 ) {
		return strpos( strtolower( $haystack ), strtolower( $needle ), $offset );
	}
}


