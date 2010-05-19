<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


require_once( dirname( __FILE__ ) .DIRECTORY_SEPARATOR. 'backup.html.php' );


/**
 *  This class contain set of static functions that allow perform actions connected with TLD.
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSBackup
{
	/**
	 * Export JS to .csv (Coma Separated Values) file
	 *
	 * @access static, public
	 * @return error message if error occure or if success end of this function never is reached!!
	 */
	function getBackupTab() {
		
		$JSBackupTpl = new js_JSBackupTpl();
		$html = $JSBackupTpl->getBackupTabTpl();
		return $html;
	}
}
