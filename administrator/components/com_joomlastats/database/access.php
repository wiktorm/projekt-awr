<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



 
/**
 * This file provide access to JoomlaStats database in:
 *     - 'joomla v1.5.7 Native' environment
 *     - without joomla
 *
 *  To get access to database JoomlaStats use a little modified classes from Joomla CMS
 *
 *     require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
 */



if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}






class js_JSDatabaseAccess
{
	/** it hold reference to DB object. Object is holded in other place */
	var $db = null;
	
	/** constructor initialize database access */
	function __construct() {
		$this->_getDB();
	}
	
	/**
	 * A hack to support __construct() on PHP 4
	 *
	 * Hint: descendant classes have no PHP4 class_name() constructors,
	 * so this constructor gets called first and calls the top-layer __construct()
	 * which (if present) should call parent::__construct()
	 *
	 * code from Joomla CMS 1.5.10 (thanks!)
	 *
	 * @access	public
	 * @return	Object
	 * @since	1.5
	 */
	function js_JSDatabaseAccess()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	
	
	function _getDB() {

		if( defined( '_JEXEC' ) ) {
			//joomla 1.5
			$this->db =& JFactory::getDBO();
		} else if ( defined( '_JS_STAND_ALONE' ) ) {
			if (!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);

			//order is important!!!
			//get resources needed by JoomlaStats to access to database
				require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'stand.alone.configuration.php' );
				require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'res_joomla' .DIRECTORY_SEPARATOR. 'object.php' );
				require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'res_joomla' .DIRECTORY_SEPARATOR. 'database.php' );
				
			
			//create resources needed by JoomlaStats to work correctly
				//global $database;
				$JSStandAloneConfiguration = new js_JSStandAloneConfiguration();
				$this->db =& JDatabase::getInstance($JSStandAloneConfiguration->JConfigArr);

				//show error if occure 
				//it is	VERY important - without this is very hard to determine what is not working (we are ouside joomla!)
				if ( is_object($this->db) == false ) {
					echo $this->db;
					echo '<br/><br/><br/><br/>';
				}
		} else {
			//someone try to hack page? or author forgot apply define( '_JS_STAND_ALONE' )
		}
	}
	
	/** This function should not be here, but now there is no better place for it 
	 * @todo - make this function works for 'stand alone' version */
	function isMySql40orGreater() {
		$verParts = explode( '.', $this->db->getVersion() );
		//return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));// oryginal code from joomla - works in j1.0.15 and j1.5.8
		return (bool) ($verParts[0] >= 4);
		//return false;//to tests
	}

	/**
	 *  eg.
	 *      $datetime = '2009-03-25 16:42:56'
	 *          $date will be '2009-03-25'
	 *          $time will be '16:42:56'
	 *
	 *  return true when this is datetime, false when it is only date
	 */	
	function splitDateTime( $datetime, &$date, &$time ) {
		$pieces = explode(' ', $datetime);
		$date = $pieces[0];

		if( isset( $pieces[1] ) ) {
			$time = $pieces[1];
			return true;
		}

		return false;
	}
	
	/** 
	 *  This function convert dates to SQL WHERE condition
	 *     Both dates are inclusive
	 *  
	 *  Now this function works on colum of type 'DATETIME' and name 'time'
	 *
	 *  date formats: 
	 *      ''             - use '' to omit date and time limitation
	 *      '2009-03-25'
	 *      '2009-3-9'
	 *      '2009-03-25 16:42:56' (NOT RECOMENDED - much slower)
	 */
	function getConditionStringFromDates( $datetime_from, $datetime_to ) {
		if ( ($datetime_from === '') && ($datetime_to === '') )
			return '1=1';
			
		if ($datetime_from == $datetime_to) {
			$date = '';
			$time = '';
			$isDateTime = $this->splitDateTime( $datetime_from, $date, $time );

			if( $isDateTime == false)
				return 'v.visit_date=\''.$date.'\'';
			else
				return '(v.visit_date=\''.$date.'\' AND v.visit_time=\''.$time.'\')';
		}
			
		$res_from = '';
		if ($datetime_from !== '') {
			$date = '';
			$time = '';
			$isDateTime = $this->splitDateTime( $datetime_from, $date, $time );

			if( $isDateTime == true)
				$res_from .= 'CAST(CONCAT(v.visit_date, \' \', v.visit_time) AS DATETIME)>=\''.$date.' '.$time.'\''; //@todo maybe this line could be optimized. Tests are needed
			else
				$res_from .= 'v.visit_date>=\''.$date.'\'';
		}
		
		$res_to = '';
		if ($datetime_to !== '') {
			$date = '';
			$time = '';
			$isDateTime = $this->splitDateTime( $datetime_to, $date, $time );

			if( $isDateTime == true)
				$res_to .= 'CAST(CONCAT(v.visit_date, \' \', v.visit_time) AS DATETIME)<=\''.$date.' '.$time.'\''; //@todo maybe this line could be optimized. Tests are needed
			else
				$res_to .= 'v.visit_date<=\''.$date.'\'';
		}
			
		if ( ($res_from !== '') && ($res_to !== '') )
			return '('.$res_from.' AND '.$res_to.')';
		
		return $res_from.$res_to;//one of it always will be empty
	}


	/**
	 *  performing many DB queries
	 *
	 * @since 2.3.x (mic)
	 * @param array $queries_arr    holds all queries
	 * @param bool	$printErrorMsg  supress error message
	 * @return bool                 true if all queries were successful
	 */
	function populateSQL( $queries_arr, $printErrorMsg = true ) {
		$bResult = true;

		foreach( $queries_arr as $query) {
			$this->db->setQuery( $query );
			if( !$this->db->query() ) {
				$bResult &= false;
				if( $printErrorMsg ) {
					echo '<br/>' . $this->db->getErrorMsg() . '<br/>' . $query;
				}
			}
		}
		
		return $bResult;
	}
}
