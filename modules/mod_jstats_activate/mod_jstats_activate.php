<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


defined('_JEXEC') or die ('JS: Direct access to this location is not allowed.');



//include JoomlaStats API
//to increase performance we include 'count.classes.php' instead of 'api.include.php'
//$js_PathToJoomlaStatsApi = JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_joomlastats' .DS. 'api' .DS. 'api.include.php';
$js_PathToJoomlaStatsCountClasses = JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_joomlastats' .DS. 'count.classes.php';
if ( !is_readable($js_PathToJoomlaStatsCountClasses) || !include_once($js_PathToJoomlaStatsCountClasses) ) {
	$this_js_extension_homepage = 'http://www.joomlastats.org/index.php?option=com_content&amp;task=view&amp;id=70&amp;Itemid=38';//remeber to replace '&' by '&amp;'
	$this_js_extension_install_problem_text = '<div style="color: red;">It seams that <a href="'.$this_js_extension_homepage.'" target="_blank"><b>module Activation (mod_jstats_activate)</b></a> is not installed correctly. Please refer to <a href="http://www.joomlastats.org/entry/installation_noengine.php" target="_blank"><b>JoomlaStats extension installation problem</b></a> page.</div><br/><br/>';
	echo $this_js_extension_install_problem_text;
	return false; //this will end of this script //this also solve problem require_once (include_once now is enough and it generate only warning)
}



$html_content = '';//will contain activation string (<!-- JoomlaStatsActivated -->)
$js_visit_id = js_gCountVisitor( $html_content );//this is JS global function that count visitor and pages impressions
$content = $html_content;//we do not want to echo anything! - it could send headers too soon! We add html_content to content

