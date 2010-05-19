<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

//require_once( dirname(__FILE__).'/base.classes.php' );
require_once( dirname( __FILE__ ) .DS. 'template.html.php' );
require_once( dirname( __FILE__ ) .DS. 'util.classes.php' );

/**
 *  Joomla Stats Tools class
 *
 *  This contain features from 'Tools' tab in 'Joomla Stats' Configuration panel.
 *  Basicly contain maintenance functions
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSMaintenance
{
    /**
     * This function optimize all JoomlaStats database tables
     * new from v2.3.0.170, tested - OK
     *
     * return true on success
     */
	function doOptimizeDatabase() {
		global $mainframe;

		$JSUtil = new js_JSUtil();
		$res = $JSUtil->optimizeAllJSTables();
		
		if ($res == false) {
			$msg = JTEXT::_( 'Database optimization failed' );
			$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_tools', $msg, 'notice' );//notice is enough - database is not broken, so red is too hard, I think
			return;
		}

		$msg = JTEXT::_( 'Database successfully optimized' );
		$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_tools', $msg, 'message' );
	}
	
	/**
	 *  backup database
	 *
	 *  function removed due to to many deprecated and not working code. Previus version do not make a backup! (in many cases it brake database!)
	 */
	function backupDatabase() {
    }
}

