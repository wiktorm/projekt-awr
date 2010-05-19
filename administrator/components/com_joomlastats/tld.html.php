<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


class js_JSTldTpl
{
	function getTldTabTpl($ip_tld_info) {
		
		$html = '
		<div style="font-size: 1px;">&nbsp;</div><!-- This div is needed to show content of tab correctly in \'IE 7.0\' in \'j1.5.6 Legacy\'. Tested in: FF, IE, j1.0.15, j1.5.6 and works OK -->
		';
		
		if ( strlen($ip_tld_info) > 0) {
			$html .= '<div style="border: 2px; border-style: solid; border-color: #0000FF; background-color: #F0F0FF;">'.$ip_tld_info.'</div>';
		}
		
		$html .= '
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
			<b>'.JTEXT::_( 'Perform WHOIS query for provided IP or host address' ).'</b><br/>
			'.JTEXT::_( 'Perform WHOIS query for provided IP or host address - DETAILED DESCRIPTION' ).'<br/>
			<br/>
			<small>'.JTEXT::_( 'eg.' ).' "97.102.244.231", "googlebot.com"</small><br/>
			<input type="text" name="address_to_check" value="" class="text_area" />
			<input type="button" name="js_tld_view_tld_check" value="'.JTEXT::_( 'Check' ).'" onclick="newWin = window.open(\'index.php?option=com_joomlastats&amp;task=js_view_whois_popup&amp;address_to_check=\'+document.adminForm.address_to_check.value+\'&amp;no_html=1\',\'whois\',\'resizable=yes,status=no,toolbar=no,location=no,scrollbars=yes,width=690,height=560\'); newWin.focus(); return false;" />
		</td></tr></table>
		';

		/** code prepared for later
		$html .= '
		<br/>
		
		<table class="adminform" width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
			'.JTEXT::_( 'AAAAAAA' ).'<br/>
			<br/>
			<input type="button" name="js_do_resolve_all_unknown_nslookups" value="'.JTEXT::_( 'AAAA' ).'" onclick="submitbutton(\'js_do_resolve_all_unknown_nslookups\');" />
			<br/>
			<br/>
			<br/>
			<b>'.JTEXT::_( 'AAAA' ).'</b><br/>
			<br/>
			<input type="button" name="js_do_resolve_all_unknown_tlds" value="'.JTEXT::_( 'TLD-Check' ).'" onclick="submitbutton(\'js_do_resolve_all_unknown_tlds\');" />
		</td></tr></table>
		';
		*/
		
		return $html;
	}
}

