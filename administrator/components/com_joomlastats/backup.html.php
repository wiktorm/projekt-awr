<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


class js_JSBackupTpl
{
	function getBackupTabTpl() {
		$not_available = JTEXT::_( 'Not available' );
		$save_only = JTEXT::_( 'Save only visitors table' );
		$save_whole = JTEXT::_( 'Save whole JS database' );
		$partial_backup = JTEXT::_( 'Partial backup' );
		$shall = JTEXT::_( 'Shall the database be backuped' );
		$full = JTEXT::_( 'Full backup' );

				
		$html = '
		<div style="font-size: 1px;">&nbsp;</div><!-- This div is needed to show content of tab correctly in \'IE 7.0\' in \'j1.5.6 Legacy\'. Tested in: FF, IE, j1.0.15, j1.5.6 and works OK -->
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
			'.$save_only.'<br/>
           	<input type="button" name="backup" disabled="disabled" style="width:165px" value="'.$partial_backup.'" onclick="if(confirm(\''.$shall.'\'))submitbutton(\'js_maintenance_do_database_backup_partial\');" />
		</td></tr></table>
		
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
			'.$save_whole.'<br/>
            <input type="button" name="backup" disabled="disabled" style="width:165px" value="'.$full.'" onclick="if(confirm(\''.$shall.'\'))submitbutton(\'js_maintenance_do_database_backup_full\');" />
		</td></tr></table>
		';
		
		
		return $html;
	}
}

