<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

	
// ensure this file is being included by a parent file
defined('_JEXEC') or die ('JS: Direct Access to this location is not allowed.');
	
/**
 *   class for writing the HTML
 */ 
class HTML_joomlastats
{
	/**
 	 *   default component message 
	 */	 
	function defaultmessage()
	{
		?>
		<br />
		<br />
		<br />
    	<div style="color: red;">
			<b>
			Note to the administrator:
			<br />
			Public component statistics are not yet available in JoomlaStats.
			<br /><br />
			Please use one or more JoomlaStats module(s) to display statistics on the frontend.
			<br />
			</b>
		</div>
		<br />
		<br />
		<br />
		<?php		
	}
}
