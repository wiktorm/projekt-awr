<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}





/**
 *  This class contain API (application programming interface) to JoomlaStats.
 *
 *  Eg. of including JoomlaStats API
 * 	  require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'administrator' .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_joomlastats' .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'simple.php' );
 *
 *
 *  NOTICE: 
 *      This class contain simplified versions of other JS API methods.
 *      For more methods and options, see other JS API files
 *
 *
 *  All methods are static
 */
class js_JSApiSimple
{
	/**
	 * This method return number of all visitors (regular visitors + bots and spiders + unknown visitors)
	 *    with summarized visitors also
	 *
	 * @return integer
	 */
	function getTotalVisitorsNumberWithBotsAndUnknown() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'general.php' );
		$VisitorsNumber_result = 0;
		$visitors_type         = 'all';
		$include_summarized    = true;
		$date_from             = '';
		$date_to               = '';
		$JSApiGlobal = new js_JSApiGeneral();
		$JSApiGlobal->getVisitorsNumber( $visitors_type, $include_summarized, $date_from, $date_to, $VisitorsNumber_result );
		
		return $VisitorsNumber_result;
	}


	/**
	 * This method return number of regular (real) visitors: visitors without bots, spiders and unknown visitors
	 *    with summarized visitors also
	 *
	 * @return integer
	 */
	function getTotalVisitorsNumberWithoutBotsAndUnknown() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'general.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'db.constants.php' );
		$VisitorsNumber_result = 0;
		$visitors_type         = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
		$include_summarized    = true;
		$date_from             = '';
		$date_to               = '';
		$JSApiGlobal = new js_JSApiGeneral();
		$JSApiGlobal->getVisitorsNumber( $visitors_type, $include_summarized, $date_from, $date_to, $VisitorsNumber_result );
		
		return $VisitorsNumber_result;
	}
	
	/**
	 * This method return number of all unique visitors (unique regular visitors + unique bots and spiders + unique unknown visitors)
	 *    with summarized visitors also
	 *
	 * @return integer
	 */
	function getTotalUniqueVisitorsNumberWithBotsAndUnknown() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'general.php' );
		$VisitorsNumber_result = 0;
		$visitors_type         = 'all';
		$include_summarized    = true;
		$date_from             = '';
		$date_to               = '';
		$JSApiGlobal = new js_JSApiGeneral();
		$JSApiGlobal->getUniqueVisitorsNumber( $visitors_type, $include_summarized, $date_from, $date_to, $VisitorsNumber_result );
		
		return $VisitorsNumber_result;
	}
	
	
	/**
	 * This method return unique number of regular (real) visitors: visitors without bots, spiders and unknown visitors
	 *    with summarized visitors also
	 *
	 * @return integer
	 */
	function getTotalUniqueVisitorsNumberWithoutBotsAndUnknown() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'general.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'db.constants.php' );
		$VisitorsNumber_result = 0;
		$visitors_type         = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
		$include_summarized    = true;
		$date_from             = '';
		$date_to               = '';
		$JSApiGlobal = new js_JSApiGeneral();
		$JSApiGlobal->getUniqueVisitorsNumber( $visitors_type, $include_summarized, $date_from, $date_to, $VisitorsNumber_result );
		
		return $VisitorsNumber_result;
	}
	
}


