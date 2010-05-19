<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( ( !defined( '_JS_STAND_ALONE' ) ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'base.classes.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'api.base.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'general.php' );

/**
 * Object of this class fill variable data in status template
 *
 * @todo Database querys should be moved to database layer
 */
class js_JSStatusTData
{
	/**
	 * if array is empty, error section will not be displayed
	 * elements should be arrays like: array( 'name' => $name, 'description' => $desc);
	 */
	var $errorMsg = array();

	/**
	 * if array is empty, string 'It seams that Your JoomlaStats works correctly.
	 * No Warnings at this moment.' will be printed
	 * elements should be arrays like: array( 'name' => $name, 'description' => $desc);
	 */
	var $warningMsg = array();

	/** if array is empty, string 'It seams that Your JoomlaStats works correctly.
	 * No Recommendations at this moment.' will be printed
	 * elements should be arrays like: array( 'name' => $name, 'description' => $desc);
	 */
	var $recommendationMsg = array();

	/**
	 * if array is empty, no string will be printed
	 * elements should be arrays like: array( 'name' => $name, 'description' => $desc);
	 * */
	var $infoMsg = array();

	/**
	 * if set to true 'Database Info Table' will be shown at info section
	 * */
	var $showDbInfoTable = false;

	/** data from database */
	var $LastSummarizationDate = false;
	var $totalbots		= '';
	var $totalbrowser	= '';
	var $totalse		= '';
	var $totalsys		= '';
	var $totaltld		= '';
	var $totalipc		= '';

	function initalizeMembersFromDatabase() {

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$JSDbSOV = new js_JSDbSOV();
		$JSDbSOV->getJSLastSummarizationDate($this->LastSummarizationDate);
		
		$query = 'SELECT count(*) AS totalbots'
		. ' FROM #__jstats_browsers'
		. ' WHERE browser_id>1024'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalbots = $JSDatabaseAccess->db->loadResult();

		$query = 'SELECT count(*) AS totalbrowser'
		. ' FROM #__jstats_browsers'
		. ' WHERE browser_id>1 AND browser_id<1024'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalbrowser = $JSDatabaseAccess->db->loadResult();

		$query = 'SELECT count(*) AS totalse'
		. ' FROM #__jstats_searchers';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalse = $JSDatabaseAccess->db->loadResult();

		$sql = 'SELECT count(*) AS totalsys'
		. ' FROM #__jstats_systems';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalsys = $JSDatabaseAccess->db->loadResult();

		$query = 'SELECT count(*) AS totaltld'
		. ' FROM #__jstats_topleveldomains';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totaltld = $JSDatabaseAccess->db->loadResult();

		$query = 'SELECT count(*) AS totalipc'
		. ' FROM #__jstats_iptocountry';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalipc = $JSDatabaseAccess->db->loadResult();

		// user called pages
		$query = 'SELECT count(*) totalpages'
		. ' FROM #__jstats_pages';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalpages = $JSDatabaseAccess->db->loadResult();

		// user page requests
		$query = 'SELECT count(*) totalpagerequest'
		. ' FROM #__jstats_impressions';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalpagerequest = $JSDatabaseAccess->db->loadResult();

		// user page requests (backup)
		$query = 'SELECT count(*) bu_totalpagerequest'
		. ' FROM #__jstats_impressions_sums';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->bu_totalpagerequest = $JSDatabaseAccess->db->loadResult();

		// user referer
		$query = 'SELECT count(*) totalreferrer'
		. ' FROM #__jstats_referrer';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalpagereferrer = $JSDatabaseAccess->db->loadResult();

		// user visits
		$query = 'SELECT count(*) totalvisits'
		. ' FROM #__jstats_visits';
		$JSDatabaseAccess->db->setQuery( $query );
		$this->totalvisits = $JSDatabaseAccess->db->loadResult();
	}
}

/**
 *  This class examin JS condition and generate status/info page
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSStatus
{
	var $JSConf = null;

	function __construct( $JSConf=null ) {
		if( $JSConf == null ) {
			$this->JSConf = new js_JSConf();
		} else {
			$this->JSConf = $JSConf;
		}
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
	function js_JSStatus()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}
	
	function getActivationWarningMsg( &$warningMsg ) {

	}

	/**
	 * checks the activation method
	 *
	 * @param string $activeMethodType
	 * @return string
	 */
	function activeMethodTypeToText( $activeMethodType ) {
		switch( $activeMethodType) {
			case 'mod':
				return JTEXT::_( 'module <strong>JoomlaStats Activation Module</strong> (mod_jstats_activate)' );
				break;

			case 'plg':
				return JTEXT::_( 'plugin <strong>JoomlaStats Activation Plugin</strong> (bot_jstats_activate)' );
				break;

			case 'tpl':
				return JTEXT::_( 'site template modification' );
				break;

			case 'sa_tpl':
				return JTEXT::_( 'page modification for pages outside Joomla (activation for <strong>Stand Alone</strong> pages)' );
				break;
			default:
				return JTEXT::_( 'Unknown method!' );
				break;
		}
	}

	/**
	 * Checks if anonymous user (not logged user on front page) have set the same time zone as currently logged user
	 *
	 * Function can be called only on backend (it is propper performing)
	 *
	 * @return double - time offset in hours
	 */
	function getTimeZoneOffsetBetweenAnonymousUserAndCurrentlyLoggedUser() {

		$AUTZ = 0;
		{//get anonymous user time zone
			$AUTZ = js_getJSTimeZone();
			js_echoJSDebugInfo('Anonymous User Time Zone is', $AUTZ);
		}
		
		$CUTZ = 0;
		{//get current user time zone
			$user =& JFactory::getUser();
			$CUTZ = $user->getParam('timezone');
			js_echoJSDebugInfo('Currently Logged User Time Zone is', $CUTZ);
			if (is_null($CUTZ))
			{
				js_echoJSDebugInfo('Error: could not get Time Zone for current user');
				return 0; //could not get time zone
			}
		}

		//$config =& JFactory::getConfig();
		//$tzoffset = $config->getValue('config.offset');
		//$date =& JFactory::getDate($row->created, $tzoffset);

		return ($AUTZ - $CUTZ);
	}

	function checkAnonymousUserTimeZoneTheSameAsCurrentlyLoggedUserTimeZone( &$recommendationMsg ) {
		$TZOffset = $this->getTimeZoneOffsetBetweenAnonymousUserAndCurrentlyLoggedUser();
		if ($TZOffset != 0) {
			$txt = ''
			. JTEXT::sprintf( 'Time zone for anonymous users, who are browsing Your front page, is different from time zone of currently logged user.<br/>Your statistics are correct and are counted correctly, but they are displayed in anonymous user time zone, so You see dates moved in time by %s hours.', $TZOffset )
			. '<br />'
			. '<a href="http://www.joomlastats.org/entry/timezone.php" target="_blank">'
				. JTEXT::_( 'More Help' )
			. '</a>'
			;

			$recommendationMsg[] = array( 'name' => JTEXT::_( 'Time Zones' ), 'description' => $txt );
		}
	}

	function showAnonymousUserCurrentTime( $isInstallPage, &$infoMsg ) {
		if ($isInstallPage)
			return;

		$now_date_time_str = js_gmdate('Y-m-d H:i:s');

		$txt = ''
		. JTEXT::sprintf( 'Anonymous users (not logged users on front page) current date and time is %s', $now_date_time_str )
		. '<br />'
		. '<a href="http://www.joomlastats.org/entry/timezone.php" target="_blank">'
			. JTEXT::_( 'More Help' )
		. '</a>'
		;

		$infoMsg[] = array( 'name' => JTEXT::_( 'JoomlaStats current time' ), 'description' => $txt );
	}

	/**
	 * Checks if module is installed and activated
	 *
	 * @todo check if module is activated on all pages and send message about it as recommendation
	 *
	 * @param unknown_type $isModInstalled
	 * @param unknown_type $isModActivated
	 * @param unknown_type $isModActivatedTwice
	 * @return bool
	 */
	function isJSActivationModuleInstalledAndActivated( &$isModInstalled, &$isModActivated, &$isModActivatedTwice ) {
		$isModInstalled			= false;
		$isModActivated			= false;
		$isModActivatedTwice	= false;

		$path = JPATH_SITE .DS. 'modules' .DS. 'mod_jstats_activate' .DS. 'mod_jstats_activate.php';

		if ( file_exists( $path ) ) {
			$isModInstalled = true;
		}

		if( !$isModInstalled ) {
			//function success, but module not installed - there is no need to check if it is activated
			return true;
		}

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$query = 'SELECT published'
		. ' FROM #__modules'
		. ' WHERE module = \'mod_jstats_activate\''
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rowList = $JSDatabaseAccess->db->loadAssocList();
		//user can have more than 1 activation module (one module and many copies)
		// >> mic: AND WHAT FOR????? how should this makes sense????
		// >> at: of course it not have sense, that is why we must check it!

		if( count( $rowList ) > 0 ) {
			foreach( $rowList as $row ) {
				if( ( $isModActivated == true ) && ( $row['published'] ) ) {
					$isModActivatedTwice = true;
				}
				if( $row['published'] ) {
					$isModActivated = true;
				}
			}
		}

		return true;
	}

	/**
	 * checks for installed plugin and if activated
	 *
	 * @param bool $isPlgInstalled
	 * @param bool $isPlgActivated
	 * @param bool $isPlgActivatedTwice
	 * @return bool
	 */
	function isJSActivationPluginInstalledAndActivated( &$isPlgInstalled, &$isPlgActivated, &$isPlgActivatedTwice ) {
		$isPlgInstalled			= false;
		$isPlgActivated			= false;
		$isPlgActivatedTwice	= false;

		$path = JPATH_SITE .DS. 'plugins' .DS. 'system' .DS. 'bot_jstats_activate.php';

		if ( file_exists( $path ) ) {
			$isPlgInstalled = true;
		}

		if( !$isPlgInstalled ) {
			// function success, but plugin not installed - there is no need to check if it is activated
			return true;
		}

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$query = 'SELECT published'
		. ' FROM #__plugins'
		. ' WHERE element = \'bot_jstats_activate\''
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rowList = $JSDatabaseAccess->db->loadAssocList(); // plugin could be only one but for code clarity...

		if( count( $rowList ) > 0 ) {
			foreach( $rowList as $row ) {
				if( ( $isPlgActivated == true ) && ( $row['published'] ) ) {
					$isPlgActivatedTwice = true;
				}
				if( $row['published'] ) {
					$isPlgActivated = true;
				}
			}
		}

		return true;
	}

	/**
	 * return false when could not read template
	 *
	 * @param string $StrToFind
	 * @param integer $numberOfOcurances
	 * @return bool false
	 */
	function findStringInJoomlaActiveTemplate( $StrToFind, &$numberOfOcurances ) {
		$cur_template_file_name = '';

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$clientId = 0; // site template (not admin template)
		// Get the current default template
		$query = ' SELECT template'
		. ' FROM #__templates_menu'
		. ' WHERE client_id = ' . (int) $clientId
		. ' AND menuid = 0 ' //@At @bug? I do not know what it meas so I remove it - mic: NO, this means the FIRST template
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$cur_template_name = $JSDatabaseAccess->db->loadResult();

		if( $cur_template_name == '' ) {
			//@todo trigger error. How it could be that there is no default template
			return false;
		}

		$cur_template_file_name = JPATH_SITE .DS. 'templates' .DS. $cur_template_name .DS. 'index.php';

		if( !is_readable( $cur_template_file_name ) ) {
			//@todo trigger error. Could not read template
		    return false;
		}

		$template_serialized = file_get_contents( $cur_template_file_name );

		if( $template_serialized === false ) {
			//@todo trigger error. Could not read template 2
		    return false;
		}

		$numberOfOcurances	= 0;
		$start_pos			= 0;

		// for security max ten times
		for( $i = 0; $i < 10; $i++ ) {
			$pos = strpos( $template_serialized, $StrToFind, $start_pos );
			if( $pos !== false ) {
				//found
				$numberOfOcurances++;
				$start_pos = $pos+1;
			}
		}

		return true;
	}

	/**
	 * checks for activation inside a template
	 *
	 * @param bool $isActivatedInTemplate
	 * @param bool $isActivatedInTemplateTwice
	 * @return bool true/false
	 */
	function isJSActivationDoneInTemplate( &$isActivatedInTemplate, &$isActivatedInTemplateTwice ) {
		$StrToFind			= 'joomlastats.inc.php';
		$numberOfOcurances	= 0;
		$Res				= $this->findStringInJoomlaActiveTemplate( $StrToFind, $numberOfOcurances );

		if( $Res == false ) {
			return false;
		}

		if( $numberOfOcurances > 0 ) {
			$isActivatedInTemplate = true;
		}

		//strange sytuation but do not rise warrning
		//if ($numberOfOcurances == 1)

		//One activation should have 2 file names, first in 'if', second in 'include'
		if( $numberOfOcurances > 2 ) {
			$isActivatedInTemplateTwice = true;
		}

		return true;
	}

	/**
	 * checks for a stand-a-lone activation
	 *
	 * @param bool $isStandAloneActivatedInTemplate
	 * @param bool $isStandAloneActivatedInTemplateTwice
	 * @return bool true/false
	 */
	function isJSStandAloneActivationDoneInTemplate( &$isStandAloneActivatedInTemplate, &$isStandAloneActivatedInTemplateTwice){
		$StrToFind			= 'sa.joomlastats.inc.php';
		$numberOfOcurances	= 0;
		$Res				= $this->findStringInJoomlaActiveTemplate( $StrToFind, $numberOfOcurances );

		if( $Res == false ) {
			return false;
		}

		if( $numberOfOcurances > 0 ) {
			$isActivatedInTemplate = true;
		}

		//strange sytuation but do not rise warrning
		//if ($numberOfOcurances == 1)

		//One activation should have 2 file names, first in 'if', second in 'include'
		if( $numberOfOcurances > 2 ) {
			$isActivatedInTemplateTwice = true;
		}

		return true;
	}

	/**
	 * This function analize front page - this make that JS count itself -
	 * because of that body of that function should be performed only once by one page refresh
	 *
	 * @param bool $isJSActivCheckFrontPage
	 * @return bool true/false
	 */
	function isJSActivCheckFrontPage( &$isJSActivCheckFrontPage, &$iMsg ) {

		$url_to_front_site = JURI::root(false);

		$allow_url_fopen = (bool)ini_get('allow_url_fopen');
		if ( $allow_url_fopen == false ) {
			//We are unable to parse front page to test JS itself
			$iMsg = ''
			. JTEXT::_( 'JoomlaStats is unable to perform self test because Your PHP have option "allow_url_fopen" turned off.' )
			. '<br/>'
			. JTEXT::_( 'It does NOT mean that JoomlaStats is not working correctly, only that JoomlaStats is unable test itself.' )
			;
			return false;
		}

		$front_site_serialized = file_get_contents( $url_to_front_site );

		if( $front_site_serialized === false ) {
			$iMsg = ''
			. JTEXT::_( 'JoomlaStats is unable verify front site.' )
			. '<br/>'
			. JTEXT::_( 'It does NOT mean that JoomlaStats is not working correctly, only that JoomlaStats is unable test itself.' )
			;
		    return false;
		}

		$JSSystemConst = new js_JSSystemConst();
		//there are many methods to activate JS. If anyone will be performed Write below string to page
		$strToCheck = $JSSystemConst->htmlFrontPageJSActivatedString; //  "<!-- JoomlaStatsActivated -->"
		//$strToCheck = preg_replace('/[^A-Za-z]/', '', $strToCheck);

		//$strToCheck = str_ireplace(array('<!--', '-->'), '', $strToCheck);
		$strToCheck = trim( $strToCheck );

		//check if something like JoomlaStatsActivated is in HTML code on front site
		$pos		= strpos( $front_site_serialized, $strToCheck );

		if( $pos === false ) {
			// not found
			$isJSActivCheckFrontPage = false;
		}else{
			// found
			$isJSActivCheckFrontPage = true;
		}

		return true;
	}

	/**
	 * generates some messages
	 *
	 * @param array $JSActivationStatus
	 */
	function getJSActivationStatus( &$JSActivationStatus ) {
		$JSActivationStatus = array(
			'mod'	=> array(
				'type'				=> 'mod',
				'is_installed'		=> false,
				'is_acivated'		=> false,
				'is_acivated_twice'	=> false
			),
			'plg'	=> array(
				'type'				=> 'plg',
				'is_installed'		=> false,
				'is_acivated'		=> false,
				'is_acivated_twice'	=> false
			),
			'tpl'	=> array(
				'type'				=> 'tpl',
				'is_installed'		=> false,
				'is_acivated'		=> false,
				'is_acivated_twice'	=> false
			),
			'sa_tpl'	=> array(
				'type'				=> 'sa_tpl',
				'is_installed'		=> false,
				'is_acivated'		=> false,
				'is_acivated_twice'	=> false
			)
		);

		$this->isJSActivationModuleInstalledAndActivated(
			$JSActivationStatus['mod']['is_installed'],
			$JSActivationStatus['mod']['is_acivated'],
			$JSActivationStatus['mod']['is_acivated_twice']
		);

		$this->isJSActivationPluginInstalledAndActivated(
			$JSActivationStatus['plg']['is_installed'],
			$JSActivationStatus['plg']['is_acivated'],
			$JSActivationStatus['plg']['is_acivated_twice']
		);

		$isStandAloneActivatedInTemplate = false;

		$this->isJSStandAloneActivationDoneInTemplate(
			$isStandAloneActivatedInTemplate,
			$JSActivationStatus['sa_tpl']['is_acivated_twice']
		);

		$JSActivationStatus['sa_tpl']['is_installed'] = $isStandAloneActivatedInTemplate;
		$JSActivationStatus['sa_tpl']['is_acivated']  = $isStandAloneActivatedInTemplate;

		$isActivatedInTemplate = false;
		$this->isJSActivationDoneInTemplate(
			$isActivatedInTemplate,
			$JSActivationStatus['tpl']['is_acivated_twice']
		);

		$JSActivationStatus['tpl']['is_installed'] = $isActivatedInTemplate;
		$JSActivationStatus['tpl']['is_acivated']  = $isActivatedInTemplate;

		return true;
	}

	/**
	 * $isJSActivated return true only if EVERY thing seems to be OK (e.g. JS is activated only once)
	 *
	 * @param in  array $JSActivationStatus
	 * @param out bool  $isJSActivated
	 * @return bool
	 */
	function isJSActivated( $JSActivationStatus, &$isJSActivated ) {
		$isJSActivated		= false;

		foreach( $JSActivationStatus as $method ) {
			if ( $method['is_acivated_twice'] == true ) {
				$isJSActivated = false;
				break;
			}
			if( ( $isJSActivated == true ) && ( $method['is_acivated'] == true ) ) {
				//JS are activated by 2 methods at the same time!
				$isJSActivated = false;
				break;
			}
			if( $method['is_acivated'] == true ) {
				$isJSActivated = true;
			}
		}

		return true;
	}

	function checkDatabaseAndPhpFilesVersions( &$warningMsg ) {
		$JSVersion_result = '';
		$JSApiGlobal = new js_JSApiGeneral();
		$JSVerResult = $JSApiGlobal->getJSVersion($JSVersion_result);
		if ($JSVerResult == false)
			$warningMsg[] = array( 'name' => JTEXT::_( 'JoomlaStats PHP and Database version' ), 'description' => JTEXT::_( 'JoomlaStats PHP version differ from JoomlaStats Database version. Probably You have JoomlaStats database from different version than JoomlaStats PHP files.' ) );
	}

	/**
	 * checks for activation and generates message
	 *
	 * @param array $warningMsg
	 * @return bool
	 */
	function checkJSActivation( &$warningMsg, &$recommendationMsg, &$infoMsg ) {

		$bRes = true;

		$JSActivationStatus = array();
		$bRes &= $this->getJSActivationStatus( $JSActivationStatus );

		$isJSActivated = false;
		$bRes &= $this->isJSActivated( $JSActivationStatus, $isJSActivated );

		//additionaly we check if activation is visible on front page
		//sometimes it happens that module is activated but still not working (joomla has such bug :( )
		$isJSActivCheckFrontPage = false;
		$isJSActivCheckFrontPageiMsg = '';
		$isJSActivCheckFrontPageRes = true;
		if( $isJSActivated ) {
			$isJSActivCheckFrontPageRes = $this->isJSActivCheckFrontPage( $isJSActivCheckFrontPage, $isJSActivCheckFrontPageiMsg );
			$bRes &= $isJSActivCheckFrontPageRes;
		}
		
		if ( ($bRes == true) && ($isJSActivated == true) && ($isJSActivCheckFrontPage == true) ) {
			//everything is OK, so exit
			return true;
		}


		//something is wrong


		{//A: check if user acivated JS in joomla by using 'Stand Alone' Activation method
	
			$txt = '';
			if( $JSActivationStatus['sa_tpl']['is_acivated'] == true ) {
				$txt .= JTEXT::sprintf( 'Activation method stand alone %s', $this->activeMethodTypeToText( $method['type'] ) )
				. '<br />'
				. '<a href="http://www.joomlastats.org/entry/activation.php" target="_blank">'
					. JTEXT::_( 'More Help' )
				. '</a>'
				;
			}
	
			if ( $txt != '' ) {
				$recommendationMsg[] = array( 'name' => JTEXT::_( 'JoomlaStats Acivation' ), 'description' => $txt );
			}
		}

		//B: Generate more detailed info
		$activationWarningMsg = '';
		{
			//'check if JS is twice activated by the same method'
			foreach ( $JSActivationStatus as $method ) {
				if ( $method['is_acivated_twice'] == true ) {
					$activationWarningMsg .= JTEXT::sprintf( 'Activation method multiple %s',
									$this->activeMethodTypeToText( $method['type'] ) )
					. '<br />'
					. '<a href="http://www.joomlastats.org/entry/activation.php" target="_blank">'
						. JTEXT::_( 'More Help' )
					. '</a>'
					;
	
					return true;
				}
			}
	
			// check if JS is activated twice by 2 diferent methods' or 'check if it is shown at front page'
			$isJSActivatedTwice	= false;
			$isJSActivated		= false;
			$isJSActivatedBy	= array();
	
			foreach( $JSActivationStatus as $method ) {
				if( ( $isJSActivated == true ) && ( $method['is_acivated'] == true ) ) {
					//JS is activated by 2 methods at the same time!
					$isJSActivatedTwice = true;
				}
				if( $method['is_acivated'] == true ) {
					$isJSActivatedBy[]	= $method['type'];
					$isJSActivated		= true;
				}
			}
	
			// parse front page to check if JS is activated'
			if( $isJSActivated == true ) {
				if ( $isJSActivCheckFrontPageRes == false) {
					$infoMsg[] = array( 'name' => JTEXT::_( 'JoomlaStats Acivation' ), 'description' => $isJSActivCheckFrontPageiMsg );
				} else {
					if( $isJSActivCheckFrontPage == false ) {
						$activationWarningMsg .= JTEXT::_( 'You activated JoomlaStats by method (or methods)' ) . ': ';
						foreach ( $isJSActivatedBy as $activeMethodType ) {
							$activationWarningMsg .= '['.$this->activeMethodTypeToText($activeMethodType).'], ';
						}
						$activationWarningMsg .= JTEXT::_( 'but your HomePage visitors are not counted!' )
						. '<br />'
						. '<a href="http://www.joomlastats.org/entry/activation.php" target="_blank">'
							. JTEXT::_( 'More Help' )
						. '</a>'
						;
		
						return true;
					}
				}
			}
	
			//'check if JS is twice activated by 2 different methods'
			if ( $isJSActivatedTwice == true ) {
				$activationWarningMsg .= JTEXT::_( 'Activated multiple times' );
				foreach( $isJSActivatedBy as $activeMethodType ) {
					$activationWarningMsg .= ' ['.$this->activeMethodTypeToText($activeMethodType).']';
				}
				$activationWarningMsg .= '.'
				. '<br />'
				. '<a href="http://www.joomlastats.org/entry/activation.php" target="_blank">'
					. JTEXT::_( 'More Help' )
				. '</a>'
				;
	
				return true;
			}
	
			//check if user install activation method
			$isJSActivationTypeInstalled = array();
			foreach( $JSActivationStatus as $method ) {
				if( $method['is_installed'] == true ) {
					$isJSActivationTypeInstalled[] = $method['type'];
				}
	
				if( $method['is_acivated'] == true ) {
					//@todo throw JS error here (what? we checked activated modules, if there are, function return earlier!)
					return false;
				}
			}
	
			if( count( $isJSActivationTypeInstalled ) > 0 ) {
	
				$meths = '';
				foreach( $isJSActivationTypeInstalled as $installedMethodType ) {
					$meths .= '['. $this->activeMethodTypeToText( $installedMethodType ) .'] ';
				}
	
				$activationWarningMsg .= JTEXT::sprintf( 'It seams that you applied that %s activation method.', $meths )
				. '<br />'
				. JTEXT::_( 'To activate JoomlaStats you have to enable/publish' ) . ' ';
	
				if( count( $isJSActivationTypeInstalled ) > 1 ) {
					$activationWarningMsg .= JTEXT::_( 'one of' ) . ' ';
				}
	
				foreach( $isJSActivationTypeInstalled as $installedMethodType ) {
					$activationWarningMsg .= ' ['. $this->activeMethodTypeToText($installedMethodType) .']';
				}
	
				$activationWarningMsg .= '.'
				. '<br />'
				. '<a href="http://www.joomlastats.org/entry/activation.php" target="_blank">'
					. JTEXT::_( 'More Help' )
				. '</a>'
				;
			} else {
				// user did not install mod nor bot nor insert code to template
	
				$activationWarningMsg .= JTEXT::_( 'You have already installed JoomlaStats engine (component com_joomlastats), but JoomlaStats needs to be activated to work correctly!' )
				.'<br /><br />'
				. JTEXT::_( 'Choose one of below methods to activate JoomlaStats' )
				. ':<br />';
	
				//we make a list in case of line break //we numerate list manualy to be sure that order is constant
				$activationWarningMsg .= ''
				. '<ul style="margin: 0px 0px 0px 30px; padding: 0px;">'
				. '<li style="list-style-type: none;"><b>A)</b> ' . JTEXT::_( 'Install <strong>JoomlaStats Activation Module</strong> (mod_jstats_activate) <strong>(recommended)</strong>' ) . '</li>'
				. '<li style="list-style-type: none;"><b>B)</b> ' . JTEXT::_( 'Install <strong>JoomlaStats Activation Plugin</strong> (bot_jstats_activate)' ) . '</li>'
				. '<li style="list-style-type: none;"><b>C)</b> ' . JTEXT::sprintf( 'Modify your template by inserting below code under %s tag', '&lt;body&gt;')
					. '<pre style="margin: 0px; padding: 0px;">'
					. "   &lt;?php\n"
					. "   if (file_exists(JPATH_SITE.DS.'components'.DS.'com_joomlastats'.DS.'joomlastats.inc.php'))\n"
					. "       include_once(JPATH_SITE.DS.'components'.DS.'com_joomlastats'.DS.'joomlastats.inc.php');\n"
					. "    ?&gt;"
					. '</pre>'
				. '</li>'
				. '</ul>'
				. '<br />'
				. '<a href="http://www.joomlastats.org/entry/activation.php" target="_blank">'
					. JTEXT::_( 'More Help' )
				. '</a>'
				;
			}
		}
		$warningMsg[] = array( 'name' => JTEXT::_( 'JoomlaStats Activation' ), 'description' => $activationWarningMsg );

		return true;
	}

	/**
	 *  Check if WhoIs option is working.
	 *  Check access to RIPE servers. We check 3 different servers, if at least one connection was successful, that mean WhoIs will work!
	 *
	 *  NOTICE:
	 *    This function could not be called during installation process - performing it could take even 20[s]!
	 *
	 *  @return bool true when WhoIs is working
	 */
	function isWhoIsOptionWorking() {

		$isWorking = false;
		$server_arr = array('whois.ripe.net', 'whois.apnic.net', 'whois.arin.net');

		// fsockopen rise PHP warning. The only way to avid it is turn of rising warnings in PHP (for a while)
	    $err_rep_ory = error_reporting();
	    $err_rep = $err_rep_ory ^ E_WARNING;
	    error_reporting($err_rep);


		$errno = 0;
		$errstr = '';
		$timeout = 3;//give 3 [s] to connect - server could be bussy
		foreach( $server_arr as $server ) {
			$fp = fsockopen( gethostbyname( $server ), 43, $errno, $errstr, $timeout );
			if (!$fp) {
				// to avoid 100 questions in JoomlaStats support better do not echo enything here - notice with direct link to help, will be displayed later
				//js_echoJSDebugInfo('Connection to server \''.$server.'\' failed during checking if WhoIs option is accessable - it means that WhoIs option is not accessable on this machine.', '');
			    //echo "ERROR: $errno - $errstr<br />\n";
			} else {
			    fclose($fp);
				$isWorking = true;
				break;
			}
		}

		// bring back oryginal settings
	    error_reporting($err_rep_ory);

		return $isWorking;
	}

	function checkDevelopmentSnapshot( &$recommendationMsg ) {
		$JSVersion_result = '';
		$JSApiGlobal = new js_JSApiGeneral();
		$JSVerResult = $JSApiGlobal->getJSVersion($JSVersion_result);
		if ($JSVerResult == true) {
			$pos = strpos($JSVersion_result, ' '); //see description of JS version format
			if ($pos !== false) 				
				$recommendationMsg[] = array( 'name' => JTEXT::_( 'JoomlaStats development snapshot' ), 'description' => JTEXT::_( 'This is JoomlaStats development snapshot. Please install release version.' ) );
		}
	}

	function checkMySqlVersion( &$recommendationMsg ) {
		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$is40 = $JSDatabaseAccess->isMySql40orGreater();
		if ($is40 == false) {
			$recommendationMsg[] = array( 'name' => JTEXT::_( 'Database' ), 'description' => JTEXT::_( 'Database MySQL 3x' ) );
		}
	}

	function checkWhoIsSupport( $isInstallPage, &$infoMsg, &$recommendationMsg ) {
		if ($isInstallPage == false) { //we can not check WHOIS option during installation - it takes too much time!
			if ( $this->JSConf->enable_whois == true ) {
				$isWhoIsOptionWorking = $this->isWhoIsOptionWorking();
				if( $isWhoIsOptionWorking == false ) {
					$TLDrecommendationMsg = JTEXT::_( 'WhoIs option is not working' ) . '<br/>'
					. JTEXT::sprintf( 'JoomlaStats works correctly without this feature but %s will be unavailable or limited', JTEXT::_( 'recognition visitor countries' ) )
					. '<br />'
					. '<a href="http://www.joomlastats.org/entry/whois.php" target="_blank">'
						. JTEXT::_( 'More Help' )
					. '</a>'
					;
	
					$recommendationMsg[] = array( 'name' => JTEXT::_( 'Find visitor country' ), 'description' => $TLDrecommendationMsg );
				}
	
				
				if ( $isWhoIsOptionWorking == false ) {
					$desc = JTEXT::sprintf( 'Because WHOIS option is not working, You should turn off "%s" option in JoomlaStats configuration', JTEXT::_( 'WhoIs Support' ) );
					$recommendationMsg[] = array( 'name' => JTEXT::_( 'WHOIS Support' ), 'description' => $desc );
				}
			}
		}

		if ( $this->JSConf->enable_whois == false ) {
			$desc = JTEXT::sprintf( 'Option "%s" is turned off', JTEXT::_( 'WHOIS Support' ) ) . '<br/>'
			. JTEXT::sprintf( 'JoomlaStats works correctly without this feature but %s will be unavailable or limited', JTEXT::_( 'recognition visitor countries' ) );
			$infoMsg[] = array( 'name' => JTEXT::_( 'WHOIS Support' ), 'description' => $desc );
		}
	}

	function getDatabaseDetails( $isInstallPage, &$infoMsg ) {
		if ($isInstallPage == false) {
			$JSUtil = new js_JSUtil();
			$DatabaseSizeHtmlCode = $JSUtil->getJSDatabaseSizeHtmlCode();
		
			$infoMsg[] = array( 'name' => JTEXT::_( 'Database' ), 'description' => JTEXT::_( 'Database size' ) . ':&nbsp;<b>' . $DatabaseSizeHtmlCode . '</b>&nbsp;[' . JTEXT::_( 'MB' ) . ']' );
		}
	}

	function getTranslationDetails( $isInstallPage, &$infoMsg ) {
		if ($isInstallPage == false) {
			$TranslationAuthorDetails = JTEXT::_( 'Translation Author details' );
			
			if ( strtolower($TranslationAuthorDetails) == strtolower('Translation Author details') )
				$TranslationAuthorDetails = ''; //do not display 'Translation Author details' when author of translation do not fill it
			
			$infoMsg[] = array( 'name' => JTEXT::_( 'Translation by Author' ), 'description' => $TranslationAuthorDetails );
		}
	}

	/**
	 *  list of available/recognized Operating Systems
	 */
	function getSupportedOSList( $isInstallPage, &$infoMsg ) {
		if ($isInstallPage == false) {
			$JSApiBase = new js_JSApiBase();
			$AvailableOperatingSystemListForHuman = array();
			$JSApiBase->getAvailableOperatingSystemListForHuman( $AvailableOperatingSystemListForHuman );
		
			$html_list = '<div style="padding-top: 3px">';
			foreach($AvailableOperatingSystemListForHuman as $row) {
				$html_list .= '<img src="'.$row->os_img_url.'" alt="'.$row->os_name.'" title="'.$row->os_name.'"/>' . '&nbsp;&nbsp;';
			}
			$html_list .= '</div>';
			
			$infoMsg[] = array( 'name' => JTEXT::_( 'Operating Systems' ), 'description' => $html_list );
		}
	}


	//update is performed on status page (just after install) because domain and Joomla CMS portal title could change at any time
	//table #__jstats_searchers
	//   - positions 1 and 2 are special! `searcher_name` and `searcher_domain` are replaced by 'user portal name' and by domain of server that Joomla CMS is installed
	//   - position 1 is special in additional way: It stores keywords that user use in "Joomla CMS search" feature even if no articles were found to this keywords (and even if user do not open those articles)
	//   - position 2 works like all others
	function updateJoomlaCmsEntriesInSearchersTable() {

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$portal_title = 'Joomla CMS';
		$portal_domain = 'your.domain.com';

		{
			$conf =& JFactory::getConfig();
			$portal_title = $conf->getValue('config.sitename');


			require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'count.classes.php' );
			$JSCountVisitor = new js_JSCountVisitor();
			$url_to_front_site = JURI::root(false);
			$portal_domain = $JSCountVisitor->getDomainFromUrl($url_to_front_site);
		}


		$query = 'UPDATE `#__jstats_searchers` SET'
		. '   `searcher_name`   = \''.$portal_title.'\', '
		. '   `searcher_domain` = \''.$portal_domain.'\' '
		. ' WHERE'
		. '  `searcher_id` = '._JS_DB_SERCH__ID_SEARCH_JOOMLA_CMS
		. '  OR `searcher_id` = '._JS_DB_SERCH__ID_JOOMLA_CMS
		;

		$JSDatabaseAccess->db->setQuery( $query );
		$JSDatabaseAccess->db->query();
		if ($JSDatabaseAccess->db->getErrorNum() > 0) {
			$err_msg = $JSDatabaseAccess->db->getErrorMsg();
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	function viewJSStatusPageBase( $isInstallPage, $StatusTData ) {

		$this->updateJoomlaCmsEntriesInSearchersTable();

		$this->checkMySqlVersion( $StatusTData->recommendationMsg );
		$this->checkDevelopmentSnapshot( $StatusTData->recommendationMsg );

		$this->checkJSActivation( $StatusTData->warningMsg, $StatusTData->recommendationMsg, $StatusTData->infoMsg );
		$this->checkDatabaseAndPhpFilesVersions( $StatusTData->warningMsg );

		$this->checkWhoIsSupport( $isInstallPage, $StatusTData->infoMsg, $StatusTData->recommendationMsg );
		$this->checkAnonymousUserTimeZoneTheSameAsCurrentlyLoggedUserTimeZone( $StatusTData->recommendationMsg );

		$this->getDatabaseDetails( $isInstallPage, $StatusTData->infoMsg );
		$this->showAnonymousUserCurrentTime( $isInstallPage, $StatusTData->infoMsg );
		$this->getTranslationDetails( $isInstallPage, $StatusTData->infoMsg );
		$this->getSupportedOSList( $isInstallPage, $StatusTData->infoMsg );

		return true;
	}



	/**
	 * $prevTask this member is used to back to stats to the same subpage
	 *
	 * @param string $prevTask
	 */
	function viewJSStatusPage( $prevTask ) {
		$isInstallPage = false;

		$StatusTData = new js_JSStatusTData();

		$StatusTData->initalizeMembersFromDatabase();
		$StatusTData->showDbInfoTable = true;

		$this->viewJSStatusPageBase( $isInstallPage, $StatusTData );

		include( dirname(__FILE__) .DS. 'status.html.php' );
	}

	/**
	 * shows messages just after installation process
	 *
	 * @param string $errorMsg
	 * @param string $warningMsg
	 * @param string $infoMsg
	 */
	function viewJSStatusForInstallProcess( $errorMsg, $warningMsg, $infoMsg ) {
		$isInstallPage = true;

		$StatusTData = new js_JSStatusTData();

		//messages from install process are more important, add them at begining
		foreach( $errorMsg as $msg ) {
			$StatusTData->errorMsg[] = $msg;
		}

		foreach( $warningMsg as $msg ) {
			$StatusTData->warningMsg[] = $msg;
		}

		foreach( $infoMsg as $msg ) {
			$StatusTData->infoMsg[] = $msg;
		}

		$this->viewJSStatusPageBase( $isInstallPage, $StatusTData );

		include( dirname(__FILE__) .DS. 'install.com_joomlastats.html.php' );
	}
}
