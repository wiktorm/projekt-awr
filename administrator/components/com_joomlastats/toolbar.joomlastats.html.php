<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

class js_JSToolBarMenu
{

	function CONFIG_MENU() {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ ' . JTEXT::_( 'Configuration' ) . ' ]</small></small>', 'js_js-logo.png' ); // this generate demand for css style 'icon-48-js_js-logo'

		JToolBarHelper::custom('js_do_configuration_set_default', 'js_default.png', 'js_default.png', JTEXT::_( 'Default' ), false);
		JToolBarHelper::custom('js_do_configuration_save', 'js_save.png', 'js_save.png', JTEXT::_( 'Save' ), false);
		JToolBarHelper::custom('js_do_configuration_apply', 'js_apply.png', 'js_apply.png', JTEXT::_( 'Apply' ), false);
		JToolBarHelper::custom('js_view_statistics_default', 'js_cancel.png', 'js_cancel.png', JTEXT::_( 'Cancel' ), false);
	}

	function TOOLS_MENU() {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ ' . JTEXT::_( 'Tools' ) . ' ]</small></small>', 'js_js-logo.png' ); // this generate demand for css style 'icon-48-js_js-logo'

		//summarization disabled since v2.5.0.313 (It increase DB size instead of decrease)
		//JToolBarHelper::custom('js_view_summarize', 'js_summarize.png', 'js_summarize.png', JTEXT::_( 'Summarize' ), false);
		JToolBarHelper::custom('js_view_uninstall', 'js_uninstall.png', 'js_uninstall.png', JTEXT::_( 'Uninstall' ), false);
		JToolBarHelper::custom('js_view_statistics_default', 'js_back.png', 'js_back.png', JTEXT::_( 'Back' ), false);
	}

	function UNINSTALL_MENU() {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ ' . JTEXT::_( 'Uninstall' ) . ' ]</small></small>', 'js_js-logo.png' ); //this generate demand for css style 'icon-48-js_js-logo'

		JToolBarHelper::custom('js_do_uninstall', 'js_uninstall.png', 'js_uninstall.png', JTEXT::_( 'Uninstall' ), false);
		JToolBarHelper::custom('js_view_tools', 'js_back.png', 'js_back.png', JTEXT::_( 'Back' ), false);
	}

	//summarization disabled since v2.5.0.313 (It increase DB size instead of decrease)
	function SUMMARISE_MENU() {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ '. JTEXT::_( 'Summarize' ) .' ]</small></small>', 'js_js-logo.png' ); // this generate demand for css style 'icon-48-js_js-logo'

		JToolBarHelper::custom('js_do_summarize', 'js_summarize.png', 'js_summarize.png', JTEXT::_( 'Summarize' ), false);
		JToolBarHelper::custom('js_view_tools', 'js_back.png', 'js_back.png', JTEXT::_( 'Back' ), false);
	}

	function BACK_TO_STAT_MENU( $task_name ) {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ '.$task_name.' ]</small></small>', 'js_js-logo.png' ); // this generate demand for css style 'icon-48-js_js-logo'

		JToolBarHelper::custom('js_view_statistics_default', 'js_back.png', 'js_back.png', JTEXT::_( 'Back' ), false);
	}

	function BACK_TO_MAINTENANCE_MENU( $task_name ) {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ '.$task_name.' ]</small></small>', 'js_js-logo.png' ); //this generate demand for css style 'icon-48-js_js-logo'

		JToolBarHelper::custom('js_view_tools', 'js_back.png', 'js_back.png', JTEXT::_( 'Back' ), false);
	}

	function DEFAULT_MENU( $task_name ) {

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ '.$task_name.' ]</small></small>', 'js_js-logo.png' ); //this generate demand for css style 'icon-48-js_js-logo'

		JToolBarHelper::custom('js_view_statistics_default', 'js_statistics.png', 'js_statistics.png', JTEXT::_( 'Statistics' ), false);
		JToolBarHelper::custom('js_graphics', 'js_graphics.png', 'js_graphics.png', JTEXT::_( 'Graphics' ), false);
		JToolBarHelper::custom('js_view_exclude', 'js_exclude.png', 'js_exclude.png', JTEXT::_( 'Exclude' ), false);
		JToolBarHelper::custom('js_view_configuration', 'js_configuration.png', 'js_configuration.png', JTEXT::_( 'Configuration' ), false);
		JToolBarHelper::custom('js_view_tools', 'js_tools.png', 'js_tools.png', JTEXT::_( 'Tools' ), false);
		JToolBarHelper::custom('js_view_status', 'js_status.png', 'js_status.png', JTEXT::_( 'Status' ), false);
		JToolBarHelper::custom('js_view_help', 'js_help.png', 'js_help.png', JTEXT::_( 'Help' ), false);
	}
}

