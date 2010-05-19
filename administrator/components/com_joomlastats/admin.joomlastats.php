<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

global $mainframe;


{//check user autorization (code from 'com_banners' from j1.5.6)
	// Make sure the user is authorized to view this page
	global $mainframe;
	$user = & JFactory::getUser();
	//if( !$user->authorize( 'com_config', 'manage' ) ) {//if we use this line only 'super administrators' will be able to view JoomlaStats. Mic suggest to use that way - it is most restricted access
	//if (!$user->authorize( 'com_joomlastats', 'manage' )) { //this line is wrong!!! ACL has not got JoomlaStats registered! This line always fail
	if (!$user->authorize( 'com_components', 'manage' )) { //this line allow all (that have permission to login to joomla back-end) to view JoomlaStats
		$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
	}
}



require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'base.classes.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'util.classes.php' );





{//add css style sheets to page <head>
	$cssFile = JURI::base() . 'components/com_joomlastats/assets/' . 'icon.css';
	$doc =& JFactory::getDocument();
	$doc->addStyleSheet( $cssFile );
}


$task = JRequest::getVar( 'task', 'js_view_statistics_default' );

$JSConf = new js_JSConf();
$JSUtil = new js_JSUtil();

// 'js_view_statistics_default' means that we should display task that user select as 'default start page' in configuration (user selection)
if( $task == 'js_view_statistics_default' ) {
	$task = $JSConf->startoption;
	JRequest::setVar( 'task', $task );
}

js_echoJSDebugInfo('task: \''.$task.'\'<br/>', '');

switch( $task ) {
	
	/** 
	 *  STATICTIC PAGES
	 * 
	 *  NEW ENGINE - we should write code like in those pages! 
	 */
	case 'r06':
	case 'r07':
	case 'r09':
	case 'r10':
	case 'r11':
	case 'r14':
	case 'r15':
	case 'r16':
	case 'r17':
	case 'r18':
	{
			
		require_once( dirname( __FILE__ ) .DS. 'statistics.php' );
		$JSStatistics = new js_JSStatistics();
			
		switch( $task ) {
			case 'r06': // 'Page Hits'
				echo $JSStatistics->viewPageHits();
				break;
			case 'r07': // 'Systems'
				echo $JSStatistics->viewSystems();
				break;
			case 'r09': // 'Bots by domain'
				echo $JSStatistics->viewBotsByDomain();
				break;
			case 'r10': // 'Bots'
				echo $JSStatistics->viewBots();
				break;
			case 'r11': // 'Not identified visitors'
				echo $JSStatistics->viewNotIdentifiedVisitors();
				break;
			case 'r14': // 'Search Engines'
				$isKeywords = false;
				echo $JSStatistics->viewSearchEnginesAndKeywords($isKeywords);
				break;
			case 'r15': // 'Keywords'
				$isKeywords = true;
				echo $JSStatistics->viewSearchEnginesAndKeywords($isKeywords);
				break;
			case 'r16': // 'Referrers by domain'
				$byPage = false;
				echo $JSStatistics->viewReferrers($byPage);
				break;
			case 'r17': // 'Referrers by page'
				$byPage = true;
				echo $JSStatistics->viewReferrers($byPage);
				break;
			case 'r18': // 'Detail visit information'
				echo $JSStatistics->viewDetailVisitInformation();
				break;
				
			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;
	
	
	
	/** 
	 *  STATICTIC PAGES
	 * 
	 *  - OLD STYLE - will be moved to new engine 
	 */
	case 'r01':
	case 'r02':
	case 'r03':
	//case 'r04':
	case 'r05':
	case 'r08':
	case 'r12':
		{
		//@At I know, a lot of messy code here. It will disappear if JoomlaStats_Engine move to js_JSStatistics classes
		require_once( dirname( __FILE__ ) .DS. 'admin.joomlastats.html.php' );//deprecated
		require_once( dirname( __FILE__ ) .DS. 'statistics.php' );
		require_once( dirname( __FILE__ ) .DS. 'statistics.common.php' );
		require_once( dirname( __FILE__ ) .DS. 'filters.php' );

		$show_search_filter = false;

		if( $task == 'r03' ) {
			// we are on the visitors table
			$show_search_filter = true;
		}

		$FilterSearch = new js_JSFilterSearch();
		$FilterSearch->readSearchStringFromRequest();
		$seach_hint = JTEXT::_('Date').'/'.JTEXT::_('Time').'/'.JTEXT::_('Username').'/'.JTEXT::_('TLD').'/'.JTEXT::_('IP').'/'.JTEXT::_('NS-Lookup').'/'.JTEXT::_('OS').'/'.JTEXT::_('Browser'); //this is hint for 'r03'
		$FilterSearch->setSearchHint( JTEXT::sprintf('Search (%s)', $seach_hint) );
		$FilterSearch->show_search_filter = $show_search_filter;

		$FilterDomain = new js_JSFilterDomain();
		$FilterDomain->readDomainStringFromRequest();
		$FilterDomain->show_domain_filter = false;

		$JoomlaStatsEngine = new JoomlaStats_Engine( $task, $JSConf );//deprecated

		$DatabaseSizeHtmlCode = $JSUtil->getJSDatabaseSizeHtmlCode();

		$JSStatistics = new js_JSStatistics();
		//$JoomlaStatsEngine->JoomlaStatsHeader($FilterSearch, $show_search_filter, $TimePeriod, $JoomlaStatsEngine->vid, $JoomlaStatsEngine->moreinfo, $DatabaseSizeHtmlCode, $JSVersion, $FilterDomain);

		$JSStatisticsCommon = new js_JSStatisticsCommon($JSConf);

		echo $JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($FilterSearch, $JoomlaStatsEngine->FilterTimePeriod, $JoomlaStatsEngine->vid, $JoomlaStatsEngine->moreinfo, $FilterDomain);

		switch( $task ) {
			case 'r01':
				echo $JoomlaStatsEngine->ysummary();
				break;

			case 'r02':
				echo $JoomlaStatsEngine->msummary();
				break;

			case 'r03':
				echo $JoomlaStatsEngine->VisitInformation();
				break;

			///RB: Is this one (r04) added by mic or should it be removed?
			//case 'r04':
			//	echo $JoomlaStatsEngine->botsInformation();
			//	break;
			case 'r05':
				echo $JoomlaStatsEngine->getVisitorsByTld();
				break;
			case 'r08':
				echo $JoomlaStatsEngine->getBrowsers();
				break;
			case 'r12':
				echo $JoomlaStatsEngine->getUnknown();
				break;

			// new mic 20081016: resolution
			case 'rNotUsed':
				$buid = $JoomlaStatsEngine->Buid();
				echo $JSStatistics->viewResolutions( $TimePeriod, $buid );
				break;

			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}

		echo $JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
	}
	break;

	// new mic 2008.10.25
	case 'js_graphics':
		require_once( dirname( __FILE__ ) .DS. 'tools.php' );
		$JSTools = new js_JSTools();
		$JSTools->doGraphic();
		break;
	
	/** tools options from tool bar (without options from tabs) */
	case 'js_view_tools':
	case 'js_view_uninstall':
	case 'js_do_uninstall':
	case 'js_view_summarize':
	case 'js_do_summarize':
		{
		require_once( dirname( __FILE__ ) .DS. 'tools.php' );
		$JSTools = new js_JSTools();

		switch( $task ) {
			case 'js_view_tools':
				$JSTools->viewJSToolsPage();
				break;

			case 'js_view_uninstall':
				$JSTools->viewJSUninstallPage();
				break;

			case 'js_do_uninstall':
				$JSTools->doJSUninstall();
				break;

			case 'js_view_summarize':
				$JSTools->viewJSSummarizePage();
				break;

			case 'js_do_summarize':
				$JSTools->doJSSummarize();
				break;

			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;

	/** configuration page options */
	case 'js_view_configuration':
	case 'js_do_configuration_save':
	case 'js_do_configuration_apply':
	case 'js_do_configuration_set_default':
		{
		require_once( dirname( __FILE__ ) .DS. 'configuration.php' );
		$JSConfiguration = new js_JSConfiguration();

		switch( $task ) {
			case 'js_view_configuration':
				$JSConfiguration->viewJSConfigurationPage();
				break;

			case 'js_do_configuration_save':
				$JSConfiguration->SetConfiguration( $JSConf->startoption );
				break;

			case 'js_do_configuration_apply':
				$JSConfiguration->SetConfiguration( 'js_view_configuration' );
				break;

			case 'js_do_configuration_set_default':
				$JSConfiguration->SetDefaultConfiguration();
				break;

			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;

	case 'js_view_status':
		require_once( dirname( __FILE__ ) .DS. 'status.php' );

		/** $prevTask this member is used to back to stats to the same subpage */
		$prevTask = 'r01';//@bug insted of 'r01' should be page from which user went to configuration
		$JSStatus = new js_JSStatus();
		$JSStatus->viewJSStatusPage( $prevTask );
		break;

	/** Exclude Manager page options */
	case 'js_view_exclude': //old js_view_ip_list
	case 'js_do_ip_exclude': //old exclude
	case 'js_do_ip_include': //old unexclude
		{
		require_once( dirname( __FILE__ ) .DS. 'exclude.php' );
		$JSExclude = new js_JSExclude();

		switch( $task ) {
			case 'js_view_exclude':
				$JSExclude->viewJSExcludeManager();
				break;

			case 'js_do_ip_exclude':
				$JSExclude->excludeIpAddressArr( 'exclude' );
				break;

			case 'js_do_ip_include':
				$JSExclude->excludeIpAddressArr( 'include' );
				break;

			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;

	/** options from maintenance tab */
	case 'js_maintenance_do_optimize_database':
	case 'js_maintenance_do_database_backup_partial':
	case 'js_maintenance_do_database_backup_full':
	case 'js_maintenance_do_database_initialize_with_sample_data':
	{
		require_once( dirname( __FILE__ ) .DS. 'maintenance.php' );
		$JSMaintenance = new js_JSMaintenance();

		switch( $task ) {
			case 'js_maintenance_do_optimize_database':
				$JSMaintenance->doOptimizeDatabase();
				break;

			case 'js_maintenance_do_database_backup_partial':
				$JSMaintenance->backupDatabase( false );
				break;

			case 'js_maintenance_do_database_backup_full':
				$JSMaintenance->backupDatabase( true );
				break;

			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;

	/** export tab in maintenance page */
	case 'js_export_do_js2csv':
		{
		require_once( dirname( __FILE__ ) .DS. 'export.php' );
		$JSExport = new js_JSExport();

		switch( $task ) {
			case 'js_export_do_js2csv':
				echo $JSExport->exportJSToCsv();
				break;

			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;
	

	/** options from TLD tab */
	case 'js_do_resolve_all_unknown_nslookups':
	case 'js_do_resolve_all_unknown_tlds':
	{
		require_once( dirname(__FILE__) .DS. 'tld.php' );
		$JSTld = new js_JSTld();

		switch( $task ) {
			case 'js_do_resolve_all_unknown_nslookups':
				$JSTld->doJSTldLookUp();
				break;

			case 'js_do_resolve_all_unknown_tlds':
				$JSTld->doJSTldLookUp();
				break;
				
			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;
		
	case 'js_view_help':
		require_once( dirname(__FILE__) .DS. 'template.html.php' );
		$JSTemplate = new js_JSTemplate();
		echo $JSTemplate->generateBeginingOfAdminForm( /*'js_view_help'*/ );
		echo JTEXT::_( 'JoomlaStats Help - Whole Page');
		echo $JSTemplate->generateEndOfAdminForm();
		break;

	/** popup options */
	case 'js_view_whois_popup':
	{
		switch( $task ) {
			case 'js_view_whois_popup':
				require_once( dirname(__FILE__) .DS. 'tools' .DS. 'whois.class.php' );
				$whois = new whois();
				require_once( dirname(__FILE__) .DS. 'tools' .DS. 'whois.popup.php' );
				break;
				
			default:
				$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
				JError::raiseError( '', JTEXT::_( $msg ) );
				break;
		}
	}
	break;

	default:
		/** this code should never be executed, if it is, something is wrong */
		$msg = JTEXT::_( 'Something went wrong, please inform the developer - thank you!');
		JError::raiseError( '', JTEXT::_( $msg ) );

		break;
}