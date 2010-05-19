<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
} 


class js_JSMaintenanceTpl
{
	function getMaintenanceTabTpl() {
		$html = '
		<div style="font-size: 1px;">&nbsp;</div><!-- This div is needed to show content of tab correctly in \'IE 7.0\' in \'j1.5.6 Legacy\'. Tested in: FF, IE, j1.0.15, j1.5.6 and works OK -->
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
		        <td>
					<b>'.JTEXT::_( 'Optimize JoomlaStats database' ).'</b><br/>
					'.JTEXT::_( 'Optimize JoomlaStats database - DETAILED DESCRIPTION' ).'<br/>
					<br/>
		            <input type="button" name="optimize_database" style="width:165px" value="' . JTEXT::_( 'Optimize database' ) . '" onclick="submitbutton(\'js_maintenance_do_optimize_database\');" />
		        </td>
			</tr>
		</table>
		';

		return $html;
	}
}

