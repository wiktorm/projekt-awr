<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



/**
 *  Including this file to other *.php files makes that visitor is counted
 *
 *  Eg.
 *   - include(JPATH_SITE .DS. 'components' .DS. 'com_joomlastats' .DS. 'joomlastats.inc.php');
 *   - include(dirname(__FILE__) .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'joomlastats.inc.php');
 */
 
 
 

// no direct access
if( !defined( '_JEXEC' ) && !defined( '_JS_STAND_ALONE' ) ) {
	die( 'JS: No Direct Access' );
}

//require_once(dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'administrator' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'count.classes.php');//works, but below is smaller
require_once(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_joomlastats' .DS. 'count.classes.php');


$html_content = '';//will contain activation string (<!-- JoomlaStatsActivated -->)
$js_visit_id = js_gCountVisitor( $html_content );//this is JS global function that count visitor and pages impressions
echo $html_content;//print "<!-- JoomlaStatsActivated -->"
	


