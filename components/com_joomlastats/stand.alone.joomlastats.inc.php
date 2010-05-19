<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


/** 
 *    HOW TO RUN JOOMLASTATS FOR NON-JOOMLA PAGES
 *
 * Make a) or b):
 *
 * a) Use JoomlaStats API
 *
 * b) Include this file to non-joomla CMS *.php files
 *      (paste below line to pages that You want to count visitors and fix path to this file)
 *      include(dirname(__FILE__) .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'stand.alone.joomlastats.inc.php');
 *
 * Testing:
 *	  step 1)  Open activation file in web browser, eg:
 *           http://my.domain.com/joomla/components/com_joomlastats/stand.alone.joomlastats.inc.php
 *    step 2) If something goes wrong You should see error (if Your PHP settings are set in that way)
 *    step 3) If You see blank page probalby every thing is OK. Go to Joomla administration panel
 *           to JoomlaStats statistics page and chek if You were counted.
 *
 *
 * NOTICE:
 *   If You activate JoomlaStats by using 'Stand Alone' method, some JoomlaStats features will be 
 *   unavailable (like determine if user was logged to Joomla CMS or not)
 */
 

 
//this file must have direct access!! - It is stand alone version!
//defined('_JEXEC') or die ('Direct Access to this location is not allowed.');


/** _JS_STAND_ALONE define tell Us that is stand alone version */
define('_JS_STAND_ALONE', true);



//include JoomlaStats count classes
require_once(dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'administrator' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'count.classes.php');



//perform count action
$html_content = '';//will contain activation string (<!-- JoomlaStatsActivated -->)
$js_visit_id = js_gCountVisitor( $html_content );//this is JS global function that count visitor and pages impressions
echo $html_content;//print "<!-- JoomlaStatsActivated -->"

	
