<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname( __FILE__ ) .DS. 'toolbar.joomlastats.html.php' );

$JSToolBarMenu = new js_JSToolBarMenu();

switch( $task ) {

	case 'js_view_configuration':
		$JSToolBarMenu->CONFIG_MENU();
		break;

	case 'js_view_tools':
		$JSToolBarMenu->TOOLS_MENU();
		break;

	case 'js_view_uninstall':
	case 'js_do_uninstall'://this page is never shown except uninstall errors
		$JSToolBarMenu->UNINSTALL_MENU();
		break;

	case 'js_view_summarize':
	case 'js_do_summarize': //this page is never shown except summarize errors
		$JSToolBarMenu->SUMMARISE_MENU();
		break;

	case 'js_maintenance_do_database_backup_partial':
	case 'js_maintenance_do_database_backup_full':
	case 'js_maintenance_do_database_initialize_with_sample_data':
		$JSToolBarMenu->BACK_TO_MAINTENANCE_MENU( JTEXT::_( 'Tools' ) );
		break;

	case 'js_view_status':
		$JSToolBarMenu->DEFAULT_MENU( JTEXT::_('Status') );
		break;

	//@todo 'js_view_exclude' should have own menu due to issue: 'Missing action buttons at 'Exclude' option'
	case 'js_view_exclude':
		//$JSToolBarMenu->BACK_TO_STAT_MENU( JTEXT::_('Exclude Manager') );//@todo 'js_view_exclude' should have own menu due to issue: 'Missing action buttons at 'Exclude' option'
		$JSToolBarMenu->DEFAULT_MENU( JTEXT::_('Exclude Manager') );
		break;

	case 'js_graphics':
		$JSToolBarMenu->DEFAULT_MENU( JTEXT::_('Graphics') );
		break;

	//all statistic pages
	default:
		$JSToolBarMenu->DEFAULT_MENU( JTEXT::_('Statistics') );
		break;
}
