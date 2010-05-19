<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


require_once( dirname(__FILE__) .DS. 'base.classes.php' );
require_once( dirname(__FILE__) .DS. 'template.html.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );

jimport( 'joomla.error.profiler' );

define( '_JSAdminImagePath',	JURI::base(true) . '/components/com_joomlastats/images/' ); //use function getUrlPathToJSAdminImages() instead of this define


/**
 * NOTICE: This class will be divided to 2 classes: js_JSStatistics and js_JSStatisticsTpl
 *         Maybe the code, that You are looking for, already has been moved there!
 */
class JoomlaStats_Engine
{
	var $FilterTimePeriod = null;	//hold TimePeriod control (it is used on all pages)
	//var $d = null; 				// screenselection - day
	//var $m = null; 				// screenselection - month
	//var $y = null; 				// screenselection - year
	var $dom = null; 			// screenselection - domain
	var $vid = null; 			// screenselection - visitors id
	var $moreinfo = null;		// screenselection - moreinfo //not used. Should be removed!!!
	var $updatemsg= null;		// update message used for purge
	var $task = null;			// task for JoomlaStats_Engine //@todo this member should be removed!!

	// internal
	var $add 		= array(); // holds purged datas
	

	//use getStyleForDetailView() instead of below line
	var $add_dstyle	= '<span style="font-weight:normal; font-style:italic; color:#007BBD">%s</span>';	// style 4 detail view
	
	//use getStyleForSummarizedNumber() instead of below line
	var $add_style	= '&nbsp;<span style="font-weight:normal; font-style:italic;">[ %s ]</span>';		// style 4 summary view

	
	var $JSConf		= null; // 'JS' configuration object. Holds system and user settings

	var $JSDatabaseAccess = null;
	/** database placeholder */
	var $db;



	/** @todo $task argument should be removed */
	function __construct( $task = '', $JSConf = null ) {

		$this->JSDatabaseAccess = new js_JSDatabaseAccess();
		$this->db = $this->JSDatabaseAccess->db;

		if ( $JSConf == null ) {
			$this->JSConf = new js_JSConf();
		}else{
			$this->JSConf = $JSConf;
		}

		$this->task = $task;

		$this->FilterTimePeriod = new js_JSFilterTimePeriod();
		$this->FilterTimePeriod->readTimePeriodFromRequest( $this->JSConf->startdayormonth );


		//@at 2 bugs were here - now should be OK
		//  - $this->dom = 'all'; - $this->dom could not have value all (becouse $this->dom is used in SQL querys)
		//  - value of $this->dom could not depend DIRECTLY on $this->JSConf->startdayormonth option (Compare SVN revision 102 and 103 for details)

		// new mic (better compatibility to J.1.5
		$this->dom = JRequest::getVar( 'dom' );
		$this->vid = JRequest::getVar( 'vid' );
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
	function JoomlaStats_Engine()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	


	/**
	 * returns first id from current table page_request for checking inside page_request_c
	 * used where queries should be done and result is shown/included with purged data
	 *
	 * @return integer
	 */
	function buid() {
		require_once( dirname( __FILE__ ) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR.'select.one.value.php' );

		$buid = 0;
		
		$JSDbSOV = new js_JSDbSOV();
		$JSDbSOV->getBuid($buid);
		
		return $buid;
	}


	/**
	 * Shows the summary for a year
	 * case r01
	 *
	 * @return string
	 */
	function ysummary() {

		$prof = & JProfiler::getInstance( 'JS' );
		js_echoJSDebugInfo($prof->mark('begin'), '');

		require_once( dirname(__FILE__) .DS. 'database' .DS.'select.one.value.php' );
		require_once( dirname(__FILE__) .DS. 'api' .DS. 'general.php' );
		require_once( dirname(__FILE__) .DS. 'template.html.php' );
		require_once( dirname(__FILE__) .DS. 'statistics.html.php' );
 
		$JSUtil = new js_JSUtil();

		$JSTemplate = new js_JSTemplate();
		$JSStatisticsTpl = new js_JSStatisticsTpl();

		$JSDbSOV = new js_JSDbSOV();
		$JSApiGeneral = new js_JSApiGeneral();

		$buid = 0;
		$JSDbSOV->getBuid( $buid );

		$junk  = '%';
		$junk2 = '%';
		$year  = '%';
		$this->FilterTimePeriod->getDMY( $junk, $junk2, $year );


		$where = array();

		$retval = '';
		if( $year == '%' ) {
			$year	= js_gmdate( 'Y' );

			$retval .= '<div class="jsInfoItem" style="margin-left:150px; text-align:left;">'
			. JTEXT::_( 'You have not choosen a year displaying data of' )
			.': <strong>'. $year . '</strong></div>';

			$retval = '<div class="jsinfo" style="text-align:center; background-color:#FFFFDF; margin:3px; padding:3px">'
			. $retval
			. '</div>';
		}

		$v			= 0; // visitor;
		$uv			= 0; // unique visitor
		$b			= 0; // bots
		$ub			= 0; // unique bots
		$p 			= 0; // pages
		$r 			= 0; // referrers
		$tuv		= 0; // total unique visitors
		$tv			= 0; // total visitors
		$tub		= 0; // total unique bots
		$tb			= 0; // total bots
		$tp			= 0; // total pages
		$tr			= 0; // total referrers
		$ppurge		= 0; // purged pages
		$vpurge 	= 0; // purged visitors
		$uvpurge	= 0; // unique visitors purged
		$tuvpurge	= 0; // total unique visitors purged
		$tvpurge	= 0; // total visitors purged
		$tppurge	= 0; // total pages purged
		$bpurge		= 0; // bots purged
		$tbpurge	= 0; // total bots purged
		$ubpurge	= 0; // unique bots purged
		$tubpurge	= 0; // total unique bots purged
		$niv		= 0; // not identified visitors
		$tniv		= 0; // total not identified visitors
		$nivpurge	= 0; // not identified visitors purged
		$tnivpurge	= 0; // total not identified visitors purged
		$univ		= 0; // unique not identified visitors
		$tuniv		= 0; // total unique not identified visitors
		$univpurge	= 0; // unique not identified visitors purged
		$tunivpurge	= 0; // total unique not identified visitors purged
		$sum		= 0; // sum
		$tsum		= 0; // total sum
		$usum		= 0; // unique sum
		$tusum		= 0; // total unique sum
		$tusumpurge = 0; // total unique sum purged
		$tsumpurge	= 0; // total sum purged


		$resolution = 'month';
		$include_summarized = $this->JSConf->include_summarized;
		$date_from = $year.'-01-01';
		$date_to = $year.'-12-31';

		js_echoJSDebugInfo($prof->mark('after includes and creating variables'), '');


		$v_arr = array();
		{ //get visitors
			$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
			$arr_obj_result = null;
			$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $include_summarized, $date_from, $date_to, $arr_obj_result );
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$v_arr[$obj->month] = $obj->nbr_visitors;
			}
		}

		$vpurge_arr = array();
		{ //get summarized visitors
			if( $this->JSConf->show_summarized ) {
				$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
				$tmp_include_summarized = false;
				$arr_obj_result = null;
				$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $tmp_include_summarized, $date_from, $date_to, $arr_obj_result );
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$vpurge_arr[$obj->month] = $obj->nbr_visitors;
				}
			}
		}


		$b_arr = array();
		{ //get bots
			$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
			$arr_obj_result = null;
			$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $include_summarized, $date_from, $date_to, $arr_obj_result );
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$b_arr[$obj->month] = $obj->nbr_visitors;
			}
		}

		$bpurge_arr = array();
		{ //bots purged
			if( $this->JSConf->show_summarized ) {
				$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
				$tmp_include_summarized = false;
				$arr_obj_result = null;
				$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $tmp_include_summarized, $date_from, $date_to, $arr_obj_result );
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$bpurge_arr[$obj->month] = $obj->nbr_visitors;
				}
			}
		}


		$niv_arr = array();
		{ // not identified visitors
			$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
			$arr_obj_result = null;
			$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $include_summarized, $date_from, $date_to, $arr_obj_result );
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$niv_arr[$obj->month] = $obj->nbr_visitors;
			}
		}

		$nivpurge_arr = array();
		{ // not identified visitors purged
			if( $this->JSConf->show_summarized ) {
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$tmp_include_summarized = false;
				$arr_obj_result = null;
				$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $tmp_include_summarized, $date_from, $date_to, $arr_obj_result );
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$nivpurge_arr[$obj->month] = $obj->nbr_visitors;
				}
			}
		}

		$p_arr = array();
		{ // pages
			$query = ''
			. ' SELECT SQL_BIG_RESULT'
			. '   count(*) AS nbr_impressions,'
			. '   YEAR(v.visit_date) AS year,'
			. '   MONTH(v.visit_date) AS month'
			. ' FROM '
			. '   #__jstats_impressions i'
			. '   LEFT JOIN #__jstats_visits v ON (v.visit_id=i.visit_id)'//optimized
			. ' WHERE '
			. '   '.$this->JSDatabaseAccess->getConditionStringFromDates($date_from, $date_to)
			. ' GROUP BY'
			. '   YEAR(v.visit_date), MONTH(v.visit_date)'
			;
			$this->db->setQuery( $query );
			$arr_obj_result = $this->db->loadObjectList();
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$p_arr[$obj->month] = $obj->nbr_impressions;
			}
		}

		$pp_arr = array();
		{ // pages purged (only purged)
			if( $this->JSConf->include_summarized ) {
				$query = ''
				. ' SELECT SQL_BIG_RESULT' //it increase performance in this query
				. '   SUM(j.`impression_number`) AS nbr_impressions,'
				. '   MONTH(j.`impression_date`) AS month'
				. ' FROM #__jstats_impressions_sums j'
				. ' WHERE '
				. '   j.`impression_date`>=\''.$date_from.'\''
				. '   AND j.`impression_date`<=\''.$date_to.'\''
				. ' GROUP BY'
				. '   month'
				;
				$this->db->setQuery( $query );
				$arr_obj_result = $this->db->loadObjectList();
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$pp_arr[$obj->month] = $obj->nbr_impressions;
				}
			}
		}

		js_echoJSDebugInfo($prof->mark('after getting first data'), '');

		$isMonth = false;
		$retval .= $JSStatisticsTpl->viewSummaryMonthAndSummaryYearHeadTpl( $isMonth );
		/*
		$retval .= '<table class="adminlist" cellspacing="0" width="100%">' . "\n" . '<tr>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Month' ) . '</th>'
		. '<th align="center" nowrap="nowrap" colspan="2">' . JTEXT::_( 'Unique visitors' ) . '</th>'
		. '<th align="center" colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of visitors' ) .'">' . JTEXT::_( 'Visitors' ) . '</th>'
		. '<th align="center" nowrap="nowrap" colspan="2">' . JTEXT::_( 'Visits average' ) . '</th>'
		. '<th align="center" nowrap="nowrap" colspan="2">' . JTEXT::_( 'Pages' ) . '</th>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Referrers' ) . '</th>'
		. '<th align="center" nowrap="nowrap" colspan="2">' . JTEXT::_( 'Unique bots/spiders' ) . '</th>'
		. '<th align="center" nowrap="nowrap" colspan="2">' . JTEXT::_( 'Number of bots/spiders' ) . '</th>'
		. '<th align="center" nowrap="nowrap" title="' . JTEXT::_( 'Number of unique not identified visitors' ) .'">' . JTEXT::_( 'Unique NIV' ) .'</th>'
		. '<th align="center" nowrap="nowrap" title="' . JTEXT::_( 'Number of not identified visitors' ) .'">' . JTEXT::_( 'NIV' ) .'</th>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Unique sum' ) . '</th>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Sum' ) . '</th>'
		. '</tr>' . "\n";
		*/


		$dm = array(0,31,28 + date('L',mktime(0,0,0,(int)1,(int)1,(int)$year)),31,30,31,30,31,31,30,31,30,31);
		
		$k = 0;
		for( $i = 1; $i < 13; $i++ ) {

			//$month = $i;
			$date_from = $year .'-'. $i .'-01';
			$date_to   = $year .'-'. $i .'-'. $dm[$i];

			{ // get Unique visitors
				$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $uv );
				$tuv += $uv;

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$uvpurge = $uv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
					$tuvpurge += $uvpurge;
				}
			}


			{ // get visitors
				$v = (isset($v_arr[$i])) ? $v_arr[$i] : 0;
				$tv += $v;
	
				if( $this->JSConf->show_summarized ) {
					$v_summ  = (isset($vpurge_arr[$i])) ? $vpurge_arr[$i] : 0;
					$vpurge  = $v - $v_summ;
	                $tvpurge += $vpurge;
				}
			}

			{ // get bots
				$b = (isset($b_arr[$i])) ? $b_arr[$i] : 0;
				$tb += $b;

				if( $this->JSConf->show_summarized ) {
					$b_summ  = (isset($bpurge_arr[$i])) ? $bpurge_arr[$i] : 0;
					$bpurge  = $b - $b_summ;
	                $tbpurge += $bpurge;
				}
			}

			{ // get Unique bots
				$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $ub );
				$tub += $ub;

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$ubpurge = $ub - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
					$tubpurge += $ubpurge;
				}
			}

			{ // get Pages
				$p = (isset($p_arr[$i])) ? $p_arr[$i] : 0;
				$tp += $p;
				if( $this->JSConf->include_summarized ) {
					$ppurge  = (isset($pp_arr[$i])) ? $pp_arr[$i] : 0;
	                $tppurge += $ppurge;
					$tp += $ppurge;
				}
			}


			// get Referrers
			$query = 'SELECT count(*)'
			. ' FROM #__jstats_referrer'
			. ' WHERE month = ' . $i
			. ' AND year = ' . $year
			;
			$this->db->setQuery( $query );
			$r = $this->db->loadResult();

			$tr += $r;


			{ // not identified visitors
				$niv = (isset($niv_arr[$i])) ? $niv_arr[$i] : 0;
				$tniv += $niv;

				if( $this->JSConf->show_summarized ) {
					$niv_summ  = (isset($nivpurge_arr[$i])) ? $nivpurge_arr[$i] : 0;
					$nivpurge  = $niv - $niv_summ;
	                $tnivpurge += $nivpurge;
				}
			}


			/* performance test code       DO NOT REMOVE IT ! 
			{// not identified visitors
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$JSDbSOV->selectNumberOfVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $niv );
				$tniv += $niv;

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$nivpurge = $niv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
					$tnivpurge += $nivpurge;
				}
			}
			*/
			
			{// unique not identified visitors
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $univ );
				$tuniv += $univ;

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$univpurge = $univ - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
					$tunivpurge += $univpurge;
				}
			}

						
			// sums
			$sum  = $v + $b + $niv;
			$usum = $uv + $ub + $univ;
			$tsum  += $sum;
			$tsumpurge += $vpurge + $bpurge + $nivpurge;
			



			if( $this->JSConf->include_summarized ) {
				$add = null;
			    if( $uvpurge ) {
                    $add['uvpurge']	= sprintf( $this->add_dstyle, $uvpurge );
			    }
                if( $vpurge ) {
			        $add['vpurge']	= sprintf( $this->add_dstyle, $vpurge );
                }
                if( $ppurge ) {
			        $add['ppurge']	= sprintf( $this->add_dstyle, $ppurge );
                }
                if( $bpurge ) {
			        $add['bpurge']	= sprintf( $this->add_dstyle, $bpurge );
                }
                if( $ubpurge ) {
			        $add['ubpurge']	= sprintf( $this->add_dstyle, $ubpurge );
                }
            }



			// Now we have all data, let's show the lines of each month
			

			$retval .= '<tr class="row' . $k . '">'
			. '<td align="center">'	. $JSTemplate->monthToString($i, true) . '</td>'
			. '<td align="right">'	. ( $uv ? $uv : '.' ) . '</td>'
			. '<td align="left">'	. ( !empty( $add['uvpurge'] ) ? $add['uvpurge'] : '&nbsp;' ) . '</td>'
			. '<td align="right">'	. ( $v  ? $v  : '.' ) . '</td>'
			. '<td align="left">'	. ( !empty( $add['vpurge'] ) ? $add['vpurge'] : '&nbsp;' ) . '</td>'
			. '<td align="center">';

			if( ( $uv != 0 ) && ( $v != 0 ) ) {
				$retval .= number_format( round( ( $v / $uv ), 1), 1);
			}else{
				$retval .= '.';
			}
			$retval .= '</td>';

			/*
			$retval .= '<td>';
			if( ( $uvpurge != 0 ) && ( $vpurge != 0 ) ) {
				$retval .= sprintf( $this->add_dstyle, number_format( round( ( $vpurge / $uvpurge ), 1), 1 ) );
			}else{
				$retval .= '&nbsp;';
			}
			$retval .= '</td>'
			*/

			$retval .= ''
			. '<td align="center">' . ( $p ? $p : '.' ) . ' ' . '</td>'
			. '<td>' . ( !empty( $add['ppurge'] ) ? $add['ppurge'] : '' ) . '</td>'
			. '<td align="center">' . ( $r ? $r : '.' ). '</td>'
			. '<td align="center">' . ( $ub ? $ub : '.' ) . ' ' . '</td><td>'
			. ( !empty( $add['ubpurge'] ) ? $add['ubpurge'] : '' ) . '</td>'
			. '<td align="center">' . ( $b ? $b : '.' ) . ' ' . '</td><td>'
			. ( !empty( $add['bpurge'] ) ? $add['bpurge'] : '' ). '</td>'
			. '<td>' . ( ($univ) ? ($univ . ' ['.$univpurge .']') : '.' ) . '</td>'
			. '<td>' . ( ($niv) ? '<a href="javascript:SelectDay('.$i.');submitbutton(\'r11\');" title="' . JTEXT::_( 'Click for additional details' ) . '">' . $niv . '</a> ['.$nivpurge .']' : '.' ) . '</td>'
			. '<td>' . ( $usum ? $usum : '.' ) . '</td>'
			. '<td>' . ( $sum ? $sum : '.' ) . '</td>'
			. '</tr>' . "\n";

			$k = 1 - $k;
		}

        if( $this->JSConf->include_summarized ) {
			if( $tuvpurge ) {
				$add['tuvpurge']	= sprintf( $this->add_style, $tuvpurge );
			}
			if( $tppurge ) {
				$add['tppurge']		= sprintf( $this->add_style, $tppurge );
			}
			if( $tvpurge ) {
				$add['tvpurge']		= sprintf( $this->add_style, $tvpurge );
			}
			if( $tbpurge ) {
				$add['tbpurge']		= sprintf( $this->add_style, $tbpurge );
			}
			if( $tubpurge ) {
				$add['tubpurge']	= sprintf( $this->add_style, $tubpurge );
			}
		}

		js_echoJSDebugInfo($prof->mark('after loop'), '');

		{ // Get the values for the totals line
			// RB: values acuired higher in this function are wrong - remove them
			// RB: change to new database method
			// AT: v3.0 changed to new method once again

			$date_from = $year.'-01-01';
			$date_to = $year.'-12-31';

			{ // get Unique visitors
				$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $tuv );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$tuvpurge = $tuv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}

			{ // get Unique bots
				$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $tub );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$tubpurge = $tub - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}
			
			{// unique not identified visitors
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $tuniv );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$tunivpurge = $tuniv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}
			
			$tusum = $tuv + $tub + $tuniv;
			$tusumpurge = $tuvpurge + $tubpurge + $tunivpurge;
		}

		js_echoJSDebugInfo($prof->mark('after total'), '');

		$total = new stdClass(); 		
			$total->month_or_year = $year;
			$total->tuv           = $tuv;
			$total->tuvpurge      = $tuvpurge;
			$total->tv            = $tv;
			$total->tvpurge       = $tvpurge;
			$total->tp            = $tp;
			$total->tppurge       = $tppurge;
			$total->tr            = $tr;
			$total->tub           = $tub;
			$total->tubpurge      = $tubpurge;
			$total->tb            = $tb;
			$total->tbpurge       = $tbpurge;
			$total->tuniv         = $tuniv;
			$total->tunivpurge    = $tunivpurge;
			$total->tniv          = $tniv;
			$total->tnivpurge     = $tnivpurge;
			$total->tusum         = $tusum;
			$total->tusumpurge    = $tusumpurge;
			$total->tsum          = $tsum;
			$total->tsumpurge     = $tsumpurge;

		$isMonth = false;
        $show_summarized = $this->JSConf->show_summarized;
		$rows = array();
		$retval .= $JSStatisticsTpl->viewSummaryMonthAndSummaryYearTpl( $isMonth, $show_summarized, $rows, $total );

/*			
		// Display the totals line
		$retval .= '<tr>'
		// Month
		. '<th align="center">' . $year . '</th>'
		// Unique visitors
		. '<th align="right">'. $tuv .'</th>'
		. '<th align="left">'. ( !empty( $add['tuvpurge'] ) ? $add['tuvpurge'] : '&nbsp;' ) . '</th>'
		// Number of visits
		. '<th align="right">'. $tv .'</th>'
		. '<th align="left">'. ( !empty( $add['tvpurge'] ) ? $add['tvpurge'] : '&nbsp;' ) . '</th>'
		// Visits average
		. '<th align="center">';

		if( ( $tuv != 0 ) && ( $tv != 0 ) ) {
			$retval .= number_format( round( ( $tv / $tuv ), 1), 1);
		}else{
			$retval .= '0.0';
		}

		$retval .= '</th><th align="left">';

		if( ( $tuvpurge != 0 ) && ( $tvpurge != 0 ) ) {
			$retval .= $add['tvpurge'] = sprintf( $this->add_style, number_format( round( ( $tvpurge / $tuvpurge ), 1) , 1 ) );
		}else{
			$retval .= '';
		}

		$retval .= '</th>';
		// Pages
		$retval .= '<th align="center">'. $tp . '</th>'
		. '<th align="center">' . ( !empty( $add['tppurge'] ) ? $add['tppurge'] : '&nbsp;' ) . '</th>'
		// Referrers
		. '<th align="center">' . $tr . '</th>'
		// Unique bots
		. '<th align="center">' . $tub . '</th>'
		. "<th align='center'>" . ( !empty( $add['tubpurge'] ) ? $add['tubpurge'] : '&nbsp;' ) . '</th>'
		// Number of bots
		. '<th align="center">' . $tb . '</th>'
		. '<th align="center">' . ( !empty( $add['tbpurge'] ) ? $add['tbpurge'] : '&nbsp;')  .'</th>'
		. '<th>' . $tuniv . ' ['.$tunivpurge .']</th>'
		. '<th>' . $tniv . ' ['.$tnivpurge .']</th>'
		. '<th>' . $tusum . ' ['.$tusumpurge .']</th>'
		. '<th>' . $tsum . ' ['.$tsumpurge .']</th>'
		. '</tr>' . "\n"
		. '</table>' . "\n";
*/
		js_echoJSDebugInfo($prof->mark('end'), '');

		return $retval;
	}

	/**
	 * displays a month summary
	 * case r02
	 *
	 * @return string
	 */
	function msummary() {

		$prof = & JProfiler::getInstance( 'JS' );
		js_echoJSDebugInfo($prof->mark('begin'), '');

		require_once( dirname(__FILE__) .DS. 'database' .DS.'select.one.value.php' );
		require_once( dirname(__FILE__) .DS. 'api' .DS. 'general.php' );
		require_once( dirname(__FILE__) .DS. 'template.html.php' );
		require_once( dirname(__FILE__) .DS. 'statistics.html.php' );
 
		$JSUtil = new js_JSUtil();

		$JSTemplate = new js_JSTemplate();
		$JSStatisticsTpl = new js_JSStatisticsTpl();

		$JSDbSOV = new js_JSDbSOV();
		$JSApiGeneral = new js_JSApiGeneral();

		$buid = 0;
		$JSDbSOV->getBuid( $buid );
		

		$where	= array();
		$retval = '';
		$info	= ''; // new mic

		$junk  = '%';
		$month = '%';
		$year  = '%';
		$this->FilterTimePeriod->getDMY( $junk, $month, $year );

		{//set month and year when not selected
			$JSNowTimeStamp = js_getJSNowTimeStamp();
			if( $month == '%' ) {
				// user selected whole month ('-')
				$month	= js_gmdate( 'n', $JSNowTimeStamp );
	
				$info .= '<div class="jsInfoItem" style="margin-left:150px; text-align:left;">'
				. JTEXT::_( 'You have not choosen a month displaying data of' )
				.': <strong>'. $JSTemplate->monthToString($month, false) . '</strong></div>';
			}
	
			if( $year == '%' ) {
				$year	= js_gmdate( 'Y', $JSNowTimeStamp );
	
				$info .= '<div class="jsInfoItem" style="margin-left:150px; text-align:left;">'
				. JTEXT::_( 'You have not choosen a year displaying data of' )
				.': <strong>' . $year . '</strong></div>';
			}
		}

		if( $info ) {
			$retval .= '<div class="jsinfo" style="text-align:center; background-color:#FFFFDF; margin:3px; padding:3px">'
			. $info
			. '</div>';

			$info = '';
		}

		$dm = array(0,31,28 + date('L',mktime(0,0,0,(int)$month,(int)1,(int)$year)),31,30,31,30,31,31,30,31,30,31);

		$v 			= 0; // visitors
		$b 			= 0; // bots
		$p			= 0; // pages
		$r			= 0; // referrer
		$ub 		= 0; // unique bots
		$tub		= 0; // total unique bots
		$uv 		= 0; // unique visitors
		$tv 		= 0; // total visitors
		$tuv		= 0; // total unique visitors
		$tb 		= 0; // total bots
		$tp 		= 0; // total pages
		$tr 		= 0; // total referrers
		$ppurge 	= 0; // purged pages
		$tppurge	= 0; // total pages purged
		$vpurge		= 0; // visitor purged
		$tvpurge	= 0; // total visitor purged
		$uvpurge	= 0; // unique visitor purged
		$tuvpurge	= 0; // total unique visitor purged
		$bpurge		= 0; // bots purged
		$tbpurge	= 0; // total bots purged
		$ubpurge	= 0; // unique bots purged
		$tubpurge	= 0; // total unique bots purged
		$niv		= 0; // not identified visitors
		$tniv		= 0; // total not identified visitors
		$nivpurge	= 0; // not identified visitors purged
		$tnivpurge	= 0; // total not identified visitors purged
		$univ		= 0; // unique not identified visitors
		$tuniv		= 0; // total unique not identified visitors
		$univpurge	= 0; // unique not identified visitors purged
		$tunivpurge	= 0; // total unique not identified visitors purged
		$sum		= 0; // sum
		$tsum		= 0; // total sum
		$usum		= 0; // unique sum
		$tusum		= 0; // total unique sum
		$tusumpurge = 0; // total unique sum purged
		$tsumpurge	= 0; // total sum purged


		$resolution = 'day';
		$include_summarized = $this->JSConf->include_summarized;
		$date_from = $year.'-'.$month.'-01';
		$date_to = $year.'-'.$month.'-'.$dm[$month];

		js_echoJSDebugInfo($prof->mark('after includes and creating variables'), '');


		$v_arr = array();
		{ //get visitors
			$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
			$arr_obj_result = null;
			$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $include_summarized, $date_from, $date_to, $arr_obj_result );
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$v_arr[$obj->day] = $obj->nbr_visitors;
			}
		}

		$vpurge_arr = array();
		{ //get summarized visitors
			if( $this->JSConf->show_summarized ) {
				$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
				$tmp_include_summarized = false;
				$arr_obj_result = null;
				$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $tmp_include_summarized, $date_from, $date_to, $arr_obj_result );
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$vpurge_arr[$obj->day] = $obj->nbr_visitors;
				}
			}
		}


		$b_arr = array();
		{ //get bots
			$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
			$arr_obj_result = null;
			$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $include_summarized, $date_from, $date_to, $arr_obj_result );
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$b_arr[$obj->day] = $obj->nbr_visitors;
			}
		}

		$bpurge_arr = array();
		{ //bots purged
			if( $this->JSConf->show_summarized ) {
				$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
				$tmp_include_summarized = false;
				$arr_obj_result = null;
				$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $tmp_include_summarized, $date_from, $date_to, $arr_obj_result );
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$bpurge_arr[$obj->day] = $obj->nbr_visitors;
				}
			}
		}


		$niv_arr = array();
		{ // not identified visitors
			$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
			$arr_obj_result = null;
			$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $include_summarized, $date_from, $date_to, $arr_obj_result );
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$niv_arr[$obj->day] = $obj->nbr_visitors;
			}
		}

		$nivpurge_arr = array();
		{ // not identified visitors purged
			if( $this->JSConf->show_summarized ) {
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$tmp_include_summarized = false;
				$arr_obj_result = null;
				$JSApiGeneral->getVisitorsNumberWithResolution( $resolution, $visitors_type, $tmp_include_summarized, $date_from, $date_to, $arr_obj_result );
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$nivpurge_arr[$obj->day] = $obj->nbr_visitors;
				}
			}
		}

		$p_arr = array();
		{ // pages
			$query = ''
			. ' SELECT SQL_BIG_RESULT'
			. '   count(*) AS nbr_impressions,'
			. '   YEAR(v.visit_date) AS year,'
			. '   MONTH(v.visit_date) AS month,'
			. '   DAYOFMONTH(v.visit_date) AS day'
			. ' FROM '
			. '   #__jstats_impressions i'
			. '   LEFT JOIN #__jstats_visits v ON (v.visit_id=i.visit_id)'//optimized
			. ' WHERE '
			. '   '.$this->JSDatabaseAccess->getConditionStringFromDates($date_from, $date_to)
			. ' GROUP BY'
			. '   YEAR(v.visit_date), MONTH(v.visit_date), DAYOFMONTH(v.visit_date)'
			;
			$this->db->setQuery( $query );
			$arr_obj_result = $this->db->loadObjectList();
			if ($arr_obj_result) {
				foreach($arr_obj_result as $obj)
					$p_arr[$obj->day] = $obj->nbr_impressions;
			}
		}

		$pp_arr = array();
		{ // pages purged (only purged)
			if( $this->JSConf->include_summarized ) {
				$query = ''
				. ' SELECT SQL_BIG_RESULT' //it increase performance in this query
				. '   SUM(j.`impression_number`) AS nbr_impressions,'
				. '   DAYOFMONTH(j.`impression_date`) AS day'
				. ' FROM #__jstats_impressions_sums j'
				. ' WHERE '
				. '   j.`impression_date`>=\''.$date_from.'\''
				. '   AND j.`impression_date`<=\''.$date_to.'\''
				. ' GROUP BY'
				. '   day'
				;
				$this->db->setQuery( $query );
				$arr_obj_result = $this->db->loadObjectList();
				if ($arr_obj_result) {
					foreach($arr_obj_result as $obj)
						$pp_arr[$obj->day] = $obj->nbr_impressions;
				}
			}
		}

		js_echoJSDebugInfo($prof->mark('after getting first data'), '');

		$isMonth = true;
		$retval .= $JSStatisticsTpl->viewSummaryMonthAndSummaryYearHeadTpl( $isMonth );
		/*
		$retval .= '<table class="adminlist" cellspacing="0" cellpadding="0" width="100%">' . "\n" . '<tr>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Day' ) . '</th>'
		. '<th align="center" colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of unique visitors' ) .'">' . JTEXT::_( 'Unique visitors' ) . '</th>'
		. '<th align="center" colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of visitors' ) .'">' . JTEXT::_( 'Visitors' ) . '</th>'
		. '<th align="center" nowrap="nowrap" title="' . JTEXT::_( 'Number of visitors' ) . ' / ' . JTEXT::_( 'Number of unique visitors' ) . '">' . JTEXT::_( 'Visits average' ) . '</th>'
		. '<th align="center" colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of visited pages' ) .'">' . JTEXT::_( 'Page impressions' ) . '</th>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Referrers' ) . '</th>'
		. '<th align="center" colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of unique bots/spiders' ) .'">' . JTEXT::_( 'Unique bots/spiders' ) .'</th>'
		. '<th align="center" colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of bots/spiders' ) .'">' . JTEXT::_( 'Bots/spiders' ) .'</th>'
		. '<th align="center" nowrap="nowrap" title="' . JTEXT::_( 'Number of unique not identified visitors' ) .'">' . JTEXT::_( 'Unique NIV' ) .'</th>'
		. '<th align="center" nowrap="nowrap" title="' . JTEXT::_( 'Number of not identified visitors' ) .'">' . JTEXT::_( 'NIV' ) .'</th>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Unique sum' ) . '</th>'
		. '<th align="center" nowrap="nowrap">' . JTEXT::_( 'Sum' ) . '</th>'
		. '</tr>' . "\n";
		*/

		js_echoJSDebugInfo($prof->mark('before loop'), '');

		for( $i = 1; $i <= $dm[$month]; $i++) {

			$day = $i;
			$date_from = $year .'-'. $month .'-'. $day;
			$date_to   = $year .'-'. $month .'-'. $day;

			{ // get Unique visitors
				$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $uv );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$uvpurge = $uv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}


			{ // get visitors
				$v = (isset($v_arr[$i])) ? $v_arr[$i] : 0;
				$tv += $v;
	
				if( $this->JSConf->show_summarized ) {
					$v_summ  = (isset($vpurge_arr[$i])) ? $vpurge_arr[$i] : 0;
					$vpurge  = $v - $v_summ;
	                $tvpurge += $vpurge;
				}
			}

			{ // get bots
				$b = (isset($b_arr[$i])) ? $b_arr[$i] : 0;
				$tb += $b;

				if( $this->JSConf->show_summarized ) {
					$b_summ  = (isset($bpurge_arr[$i])) ? $bpurge_arr[$i] : 0;
					$bpurge  = $b - $b_summ;
	                $tbpurge += $bpurge;
				}
			}


			{ // get Unique bots
				$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $ub );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$ubpurge = $ub - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}

			{ // get Pages
				$p = (isset($p_arr[$i])) ? $p_arr[$i] : 0;
				$tp += $p;
				if( $this->JSConf->include_summarized ) {
					$ppurge  = (isset($pp_arr[$i])) ? $pp_arr[$i] : 0;
	                $tppurge += $ppurge;
					$tp += $ppurge;
				}
			}


			// get Referrers
			$where = null;
			$where[] = 'day = ' . $i;
			$where[] = 'month = ' . $month;
			$where[] = 'year = ' . $year;

			$query = 'SELECT count(*)'
			. ' FROM #__jstats_referrer'
			. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
			;
			$this->db->setQuery( $query );
			$r = $this->db->loadResult();

			$tr += $r;


			{ // not identified visitors
				$niv = (isset($niv_arr[$i])) ? $niv_arr[$i] : 0;
				$tniv += $niv;

				if( $this->JSConf->show_summarized ) {
					$niv_summ  = (isset($nivpurge_arr[$i])) ? $nivpurge_arr[$i] : 0;
					$nivpurge  = $niv - $niv_summ;
	                $tnivpurge += $nivpurge;
				}
			}


			/* performance test code       DO NOT REMOVE IT ! 
			{// not identified visitors
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$JSDbSOV->selectNumberOfVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $niv );
				$tniv += $niv;

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$nivpurge = $niv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
					$tnivpurge += $nivpurge;
				}
			}
			*/
			
			{// unique not identified visitors
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $univ );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$univpurge = $univ - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}

						
			// sums
			$sum  = $v  + $b  + $niv;
			$usum = $uv + $ub + $univ;
			$tsum  += $sum;
			$tsumpurge += $vpurge + $bpurge + $nivpurge;

			
			// now we have all values, now draw the row (day)
			if( date( 'w', strtotime( $year.'-'.$month.'-'.$day ) ) == 6 ) {
				$cls = 'row0'; // info: background-color: #F9F9F9;
			}elseif (date( 'w', strtotime( $year.'-'.$month.'-'.$day ) ) == 0 ) {
				$cls = 'row2" style="background-color:#efefef; border-bottom: 1px dotted #ff0000';
			}else{
				$cls = 'row1'; // info: background-color: #F1F1F1;
			}

			$retval .= '<tr class="' . $cls . '">' . "\n";


			// show also purged data
			if( $this->JSConf->include_summarized ) {
				$add = null;
			    if ( $vpurge ) {
                    $add['vpurge'] = sprintf( $this->add_dstyle, $vpurge );
			    }
                if( $uvpurge ) {
                    $add['uvpurge'] = sprintf( $this->add_dstyle, $uvpurge );
                }
                if( $ppurge ) {
                    $add['ppurge'] = sprintf( $this->add_dstyle, $ppurge );
                }
                if( $ubpurge ) {
                    $add['ubpurge'] = sprintf( $this->add_dstyle, $ubpurge );
                }
                if( $bpurge ) {
                    $add['bpurge'] = sprintf( $this->add_dstyle, $bpurge );
                }
            }
            
            $show_summarized = $this->JSConf->show_summarized;
			//$JSStatisticsTpl->addSummStyleLine( $show_summarized, $uv, $vpurge )

            $retval .= ''
            . '<td align="center">'.$i.'</td>'
			. '<td align="right">' . ($uv ? $uv : '.') . '</td>'
			. '<td align="left">' . ( ($show_summarized==true && $uvpurge>0) ? $uvpurge : '&nbsp;' ) . '</td>'
			. '<td align="right">'
				. '<a href="javascript:SelectDay('.$i.');submitbutton(\'r03\');" title="' . JTEXT::_( 'Click for visitors details' ). '">'
					. ($v ? $v : '.')
				. '</a>'
			. '</td>'
			. '<td align="left">' . ( ($show_summarized==true && $vpurge>0) ? $vpurge : '&nbsp;' ) . '</td>'
			. '<td align="center">';

			if( ( $uv != 0 ) && ( $v != 0 ) ) {
				$format_token = '%01.2f';
				$retval .= sprintf($format_token, ( $v / $uv ));
			}else{
				$retval .= '.';
			}

			$retval .= '</td>'
			. '<td align="right">'
				. ( $p ? '<a href="javascript:SelectDay('.$i.');submitbutton(\'r06\');" title="'
					. JTEXT::_( 'Click for page details' ) . '">' . $p . '</a>' : '.' )
			. '</td>'
			. '<td align="left">' . ( ($show_summarized==true && $ppurge>0) ? $ppurge : '&nbsp;' ) . '</td>'
			. '<td align="center">'
				. ( $r ? '<a href="javascript:SelectDay('.$i.');submitbutton(\'r16\');" title="'
					. JTEXT::_( 'Click for referrer details' ) . '">' . $r . '</a>' : '.' ) . '</td>'
			. '<td align="right">' . ( $ub ? $ub : '.' ) . '</td>'
			. '<td align="left">' . ' ' . ( !empty( $add['ubpurge'] ) ? $add['ubpurge'] : '&nbsp;' ) .'</td>'
			. '<td align="right">'
			. ( $b ? '<a href="javascript:SelectDay('.$i.');submitbutton(\'r09\');" title="'
				. JTEXT::_( 'Click for additional details' ) . '">' . $b . '</a>' : '.' ). '</td>'
			. '<td>' . ( !empty( $add['bpurge'] ) ? $add['bpurge'] : '&nbsp;' ) . '</td>'
			. '<td>' . ( ($univ) ? ($univ . ' ['.$univpurge .']') : '.' ) . '</td>'
			. '<td>' . ( ($niv) ? '<a href="javascript:SelectDay('.$i.');submitbutton(\'r11\');" title="' . JTEXT::_( 'Click for additional details' ) . '">' . $niv . '</a> ['.$nivpurge .']' : '.' ) . '</td>'
			. '<td>' . ( $usum ? $usum : '.' ) . '</td>'
			. '<td>' . ( $sum ? $sum : '.' ) . '</td>'
			. '</tr>' . "\n";
		}

		js_echoJSDebugInfo($prof->mark('after loop'), '');




		{ // Get the values for the totals line
			// RB: values acuired higher in this function are wrong - remove them
			// RB: change to new database method
			// AT: v3.0 changed to new method once again

			$date_from = $year .'-'. $month .'-01';
			$date_to   = $year .'-'. $month .'-'. $dm[$month];

			{ // get Unique visitors
				$visitors_type = _JS_DB_IPADD__TYPE_REGULAR_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $tuv );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$tuvpurge = $tuv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}

			{ // get Unique bots
				$visitors_type = _JS_DB_IPADD__TYPE_BOT_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $tub );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$tubpurge = $tub - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}
			
			{// unique not identified visitors
				$visitors_type = _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR;
				$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $this->JSConf->include_summarized, $buid, $date_from, $date_to, $tuniv );

		    	if( $this->JSConf->show_summarized ) {
					$tmp_include_summarized = false;
					$tmp = 0;
					$JSDbSOV->selectNumberOfUniqueVisitors( $visitors_type, $tmp_include_summarized, $buid, $date_from, $date_to, $tmp );
					$tunivpurge = $tuniv - $tmp;//in previus query $include_summarized was true! (if not, show_summarized will be false and this code will not be executed)
				}
			}
			
			$tusum = $tuv + $tub + $tuniv;
			$tusumpurge = $tuvpurge + $tubpurge + $tunivpurge;
		}


		js_echoJSDebugInfo($prof->mark('after total'), '');


        $show_summarized = $this->JSConf->show_summarized;
		//$JSStatisticsTpl->addSummStyleLine( $show_summarized, $tv, $tvpurge )

		$total = new stdClass(); 		
			$total->month_or_year = $month;
			$total->tuv           = $tuv;
			$total->tuvpurge      = $tuvpurge;
			$total->tv            = $tv;
			$total->tvpurge       = $tvpurge;
			$total->tp            = $tp;
			$total->tppurge       = $tppurge;
			$total->tr            = $tr;
			$total->tub           = $tub;
			$total->tubpurge      = $tubpurge;
			$total->tb            = $tb;
			$total->tbpurge       = $tbpurge;
			$total->tuniv         = $tuniv;
			$total->tunivpurge    = $tunivpurge;
			$total->tniv          = $tniv;
			$total->tnivpurge     = $tnivpurge;
			$total->tusum         = $tusum;
			$total->tusumpurge    = $tusumpurge;
			$total->tsum          = $tsum;
			$total->tsumpurge     = $tsumpurge;

		$isMonth = true;
        $show_summarized = $this->JSConf->show_summarized;
		$rows = array();
		$retval .= $JSStatisticsTpl->viewSummaryMonthAndSummaryYearTpl( $isMonth, $show_summarized, $rows, $total );

		/*
		$retval .= '<tr>'
			// Day
		. '<th align="center">' . $JSTemplate->monthToString($month, true) . '</th>'
			// Unique visitors
		. '<th colspan="2" align="right">' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tuv, $tuvpurge ) . '</th>'
			// Number of visits
		. '<th colspan="2" align="right">' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tv, $tvpurge ) . '</th>'
			// Visits average
		. '<th align="center">';

		if( ( $uv != 0 ) && ( $v != 0 ) ) {
			$format_token = '%01.2f';
			$retval .= sprintf($format_token, ( $tv / $tuv ));
		}else{
			$retval .= '0.0';
		}
		
		$retval .= '</th>'
			// Pages
		. '<th colspan="2" align="right">' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tp, $tppurge ) . '</th>'
			// Referrers
		. '<th align="center">' . $tr . '</th>'
			// Unique bots
		. '<th colspan="2" align="right">' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tub, $tubpurge ) . '</th>'
			// Number of bots
		. '<th colspan="2" align="right">' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tb, $tbpurge ) . '</th>'
		. '<th>' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tuniv, $tunivpurge ) . '</th>'
		. '<th>' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tniv, $tnivpurge ) . '</th>'
		. '<th>' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tusum, $tusumpurge ) . '</th>'
		. '<th>' . $JSStatisticsTpl->addSummStyleLine( $show_summarized, $tsum, $tsumpurge ) . '</th>'
		. '</tr>' . "\n"
		. '</table>' . "\n";
*/
		js_echoJSDebugInfo($prof->mark('end'), '');

		return $retval;
	}



	/** 
	 *  
	 */
	function getUserLinksToJoomlaCmsAndOtherCmsExtensions($joomla_userid, $joomla_username) {

		if ($joomla_userid == 0)
			return '<em><small>&lt;' . JTEXT::_('Not logged in') . '&gt;</small></em>';

		$url_to_images = JURI::base(true) . '/components/com_joomlastats/images/'; //in j1.0 should be JURI::base()
		$url_base = JURI::base(true) . '/index.php'; //in j1.0 should be JURI::base()

		$user_link_array = array();

		$popup_pattern = 'target="popup" href="%s" onclick="window.open(\'\',\'popup\',\'resizable=yes,status=no,toolbar=no,location=no,scrollbars=yes,width=690,height=560\')"';
		$blank_pattern = 'target="_blank" href="%s"';
		$link_pattern = $blank_pattern;

		{//Community Builder CB    //checked with CB v1.2  //LOGO
			//eg. of link to CB user page     http://127.0.0.1/j1512_2009-06-03/administrator/index.php?option=com_comprofiler&task=showusers#edit
	        if( file_exists( JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_comprofiler' .DS. 'admin.comprofiler.php' ) ) {
				$url = $url_base . '?option=com_comprofiler&amp;task=edit&amp;cid=' . $joomla_userid; //'&amp;hidemainmenu=1';   // &amp;no_html=1 // mic: optional, but should then opened with own css!
				$user_link_to_community_builder = ''
				. '<a ' . sprintf($link_pattern, $url)
				. ' title="' . JTEXT::sprintf( 'Click to view %s profile', 'Community Builder' ) . '">'
					. '<img src="'. $url_to_images .'icon-16-js_cb-logo.png" border="0" />'
				. '</a>'
				;

				$user_link_array[] = $user_link_to_community_builder;
			}
		}


		{//VirtueMart VM     //checked with VM v1.1.4  //LOGO
			//eg. of link to VM user page    http://127.0.0.1/j1512_2009-06-03/administrator/index2.php?page=admin.user_form&user_id=62&option=com_virtuemart
	        if( file_exists( JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_virtuemart' .DS. 'admin.virtuemart.php' ) ) {
				$url = $url_base . '?page=admin.user_form&amp;user_id='.$joomla_userid.'&amp;option=com_virtuemart';
				$user_link_to_virtuemart = ''
				. '<a ' . sprintf($link_pattern, $url)
				. ' title="' . JTEXT::sprintf( 'Click to view %s profile', 'VirtueMart' ) . '">'
					. '<img src="'. $url_to_images .'icon-16-js_vm-logo.png" border="0" />'
				. '</a>'
				;
				$user_link_array[] = $user_link_to_virtuemart;
			}
		}


		{//Joomla CMS     //checked with v1.5   //LOGO + user login
			$url = $url_base . '?option=com_users&amp;view=user&amp;task=edit&amp;cid[]=' . $joomla_userid;

			$user_link_to_joomla_cms_logo = ''
			. '<a ' . sprintf($link_pattern, $url)
			. ' title="'. JTEXT::_( 'Click to view profile' ) .'">'
				. '<img src="'. $url_to_images .'icon-16-js_cms-user.png" border="0" />'
			. '</a>'
			;

			$user_link_to_joomla_cms_login = ''
			. '<a ' . sprintf($link_pattern, $url)
			. ' title="'. JTEXT::_( 'Click to view profile' ) .'">'
				. ( ( strlen( $joomla_username ) > 14 ) ? '<span class="editlinktip hasTip" title="' . $joomla_username . '">' . substr( $joomla_username, 0, 12 ) . '</span>' : $joomla_username )
			. '</a>'
			;

			$user_link_array[] = $user_link_to_joomla_cms_logo . '&nbsp;' . $user_link_to_joomla_cms_login;
		}

		return implode('&nbsp;', $user_link_array);
	}

	/**
	 * shows more infos for a selected visitor
	 * case r03
	 *
	 * vid = nr of 1 selected visitor
	 * @return string
	 */
	function VisitInformation() {
		global $mainframe, $option;

		$JSTemplate = new js_JSTemplate();
		$JSSystemConst = new js_JSSystemConst();
		$JSUtil = new js_JSUtil();

		$JSTemplate->jsLoadToolTip();


		$retval = '';

		$limit		= intval($mainframe->getUserStateFromRequest("viewlistlimit", 'limit', $mainframe->getCfg('list_limit')));
        $limitstart	= intval($mainframe->getUserStateFromRequest("viewlimitstart", 'limitstart', 0));
		$search		= $mainframe->getUserStateFromRequest("search{$option}", 'search', '');
		$search		= $this->db->getEscaped( trim( strtolower( $search ) ) );

		$date_from;
		$date_to;
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );


		$where	= array();
		$where[] = 'a.type = 1';		// RoBo: only display real visitors
		$where[] = $this->JSDatabaseAccess->getConditionStringFromDates( $date_from, $date_to);

		/* mic: show only actual data (without already archived/purged)
		 * a.table : jstats_ipadresses
		 * c.table : jstats_visits
		 */
		if( !$this->JSConf->include_summarized ) {
			$where[] = ' v.visit_id >= ' . $this->buid();
		}

		//RB: todo: add also username to the search >> mic: table users is NOT in query!! @todo: add users table
		if( $search ) {
			$where[] = '('
			. ' a.ip LIKE \'%' . $search . '%\''
			. ' OR LOWER(a.browser) LIKE \'%' . $search . '%\''
			. ' OR LOWER(a.system) LIKE \'%' . $search . '%\''
			. ' OR LOWER(a.nslookup) LIKE \'%' . $search . '%\''
			. ' OR LOWER(b.tld) LIKE \'%' . $search . '%\''
			. ' OR LOWER(b.fullname) LIKE \'%' . $search . '%\''
			. ' OR v.visit_date LIKE \'%' . $search . '%\''
			. ' OR v.visit_time LIKE \'%' . $search . '%\''
			. ' OR ju.name LIKE \'%' . $search . '%\''
			.')';
			//RB: is LOWER needed? 'like' should check case insensitive? mic: NO, like IS case sensitive!
		}


		$prof = & JProfiler::getInstance( 'JS' );
		js_echoJSDebugInfo($prof->mark('begin'), '');


		// select total
		$query = 'SELECT COUNT(*)'
		. ' FROM #__jstats_ipaddresses AS a'
		. ' LEFT JOIN #__jstats_topleveldomains AS b ON (a.tld = b.tld)'
		. ' LEFT JOIN #__jstats_visits AS v ON (a.id = v.visitor_id)'
		. ' LEFT JOIN #__users AS ju ON (ju.id = v.joomla_userid)'  //joining with #__users table make this query 5% slower
		. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		$this->db->setQuery ($query );
		$total = $this->db->loadResult();

		jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $total, $limitstart, $limit );


		$query  = 'SELECT a.id AS aid, a.tld, a.nslookup, a.system, a.browser, a.ip, a.exclude, b.fullname, v.joomla_userid, ju.name AS joomla_username, v.visit_date, v.visit_time, v.visit_id'
		. ' FROM #__jstats_ipaddresses AS a'
		. ' LEFT JOIN #__jstats_topleveldomains AS b ON (a.tld = b.tld)'
		. ' LEFT JOIN #__jstats_visits AS v ON (a.id = v.visitor_id)'
		. ' LEFT JOIN #__users AS ju ON (ju.id = v.joomla_userid)'   //joining with #__users table make this query 5% slower
		. ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' )
		. ' ORDER BY v.visit_date DESC, v.visit_time DESC'
		;
		$this->db->setQuery( $query, $pagination->limitstart, $pagination->limit );
		$rows = $this->db->loadObjectList();

        $whois = '<a target="whois" href="index.php?option=com_joomlastats&amp;task=js_view_whois_popup&amp;address_to_check=%s'
        . '&amp;no_html=1"'
        . ' onclick="window.open(\'\',\'whois\''
        . ',\'resizable=yes,status=no,toolbar=no,location=no,scrollbars=yes,width=690,height=560\')"'
        . ' title="'. JTEXT::_( 'WHOIS query' ) .'">'
        . '<img src="'. _JSAdminImagePath .'whois.png" border="0" /></a>';

		$retval .= '<table class="adminlist">'
		. '<thead>' . "\n"
		. '<tr>' . "\n"
		. '<th style="width: 1%;">#</th>'
		. '<th align="left">' . JTEXT::_( 'Time' ) . '</th>'
		. '<th align="left">'.JTEXT::_( 'Username' ).'</th>'
		. '<th align="left">' . JTEXT::_( 'TLD' ) . '</th>'
		. '<th align="left">' . JTEXT::_( 'Country' ) . '</th>'
		. '<th align="left">'.JTEXT::_( 'IP' ).'</th>'
		. '<th align="left">' . JTEXT::_( 'NS-Lookup' ) . '</th>'
		. '<th align="left">' . JTEXT::_( 'Pages' ) . '</th>'
		. '<th align="left">' . JTEXT::_( 'OS' ) . '</th>'
		. '<th align="left">' . JTEXT::_( 'Browser' ) . '</th>'
		. '<th align="left">'.JTEXT::_( 'Actions' ).'</th>'
		. '</tr>' . "\n"
		. '</thead>' . "\n"
		;

		if( $rows ) {
			$k = 0;
			$n = count( $rows );

			$browser_name_to_image_arr = array();//@todo this is hack to get image name. Database should be redesigned!
			{
				$query  = 'SELECT browser_name, browser_img FROM #__jstats_browsers';
				$this->db->setQuery($query);
				$rowst = $this->db->loadObjectList();
				
				foreach($rowst as $rowt) {
					$browser_name_to_image_arr[$rowt->browser_name] = $rowt->browser_img;
				}
			}
			
			$os_name_to_image_arr = array();//@todo this is hack to get image name. Database should be redesigned!
			{
				$query  = 'SELECT sys_fullname, sys_img FROM #__jstats_systems';
				$this->db->setQuery($query);
				$rowst = $this->db->loadObjectList();
				
				foreach($rowst as $rowt) {
					$os_name_to_image_arr[$rowt->sys_fullname] = $rowt->sys_img;
				}
			}
					
			for( $i = 0; $i < $n; $i++ ) {
				$row = &$rows[$i];
				$vid = $row->visit_id;
				$order_nbr = $i+1+$limitstart;

				$time =& JFactory::getDate($row->visit_date.' '.$row->visit_time);
				//$time->setOffset($time_zone_offset);//no we are in local time zone
				$time_str = $time->toFormat();

                // for excluding user
		        $img	= $row->exclude ? 'tick.png' : 'publish_x.png';
		        $task   = $row->exclude ? 'js_do_ip_include' : 'js_do_ip_exclude';
		        $alt    = $row->exclude ? JTEXT::_( 'Click to include' ) : JTEXT::_( 'Click to exclude' );

				$query = 'SELECT count(*) AS count'
				. ' FROM #__jstats_impressions i'
				. ' WHERE i.visit_id = ' . $vid
				;
                $this->db->setQuery( $query );
				$count = $this->db->loadResult();

		        $html_tld_img = '<img src="'.$JSUtil->getImageWithUrl($row->tld, $JSSystemConst->defaultPathToImagesTld).'" border="0" />';
	
				$retval .= '<tr class="row' . $k . '"'
				. ( $count ? '' : ' style="color:#666666; background-color:#EFFFFF"'
				. ' title="' . JTEXT::_( 'Data already purged' ) . '"' ) . '>'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td align="left" nowrap="nowrap">' . $time_str . '</td>'
				. '<td align="left" nowrap="nowrap">' . $this->getUserLinksToJoomlaCmsAndOtherCmsExtensions($row->joomla_userid, $row->joomla_username) . '</td>'
				. '<td align="left" nowrap="nowrap">.' . $row->tld . '</td>'
                . '<td align="left" nowrap="nowrap">' . $html_tld_img . ' '
                . ( $row->ip == '127.0.0.1' ? JTEXT::_( 'Local' ): JTEXT::_( $row->tld ) ) .'</td>' // this output translated $row->fullname
                . '<td align="left" nowrap="nowrap">' . $row->ip . '</td>'
                . '<td align="left">';

                if( strlen( $row->nslookup ) > 20 ) {
                    //$retval .= '<acronym title="' . $row->nslookup . '">'
                    $retval .= '<span class="editlinktip hasTip" title="' . $row->nslookup . '">'
                    . substr( $row->nslookup, 0, 19 )
                    //. '<strong style="color:#FF0000">&raquo;</strong>'
					//. '</acronym>';
					. '</span>'
					. '<strong style="color:#FF0000">&raquo;</strong>';
				}else{
                	$retval .= $row->nslookup;
				}


	        	$html_browser_img = '<img src="'.$JSUtil->getImageWithUrl('unknown', $JSSystemConst->defaultPathToImagesBrowser).'" border="0" />';
	        	{
		        	$brow_with_ver = $row->browser;//could be also without version
					if (isset($browser_name_to_image_arr[$brow_with_ver])) {
		        		$html_browser_img = '<img src="'.$JSUtil->getImageWithUrl($browser_name_to_image_arr[$brow_with_ver], $JSSystemConst->defaultPathToImagesBrowser).'" border="0" />';
	        		} else {
		        		$pos = strrpos( $brow_with_ver, ' ' );
		        		if( $pos !== false ) {
		        			$brow_without_ver = substr($brow_with_ver, 0, $pos);//could be also broken name
							if (isset($browser_name_to_image_arr[$brow_without_ver])) {
				        		$html_browser_img = '<img src="'.$JSUtil->getImageWithUrl($browser_name_to_image_arr[$brow_without_ver], $JSSystemConst->defaultPathToImagesBrowser).'" border="0" />';
			        		}
		        		}
		        		
	        		}
        		}

		        	
	        	$html_os_img = '<img src="'.$JSUtil->getImageWithUrl('unknown', $JSSystemConst->defaultPathToImagesOs).'" border="0" />';
				if (isset($os_name_to_image_arr[$row->system]))
		        	$html_os_img = '<img src="'.$JSUtil->getImageWithUrl($os_name_to_image_arr[$row->system], $JSSystemConst->defaultPathToImagesOs).'" border="0" />';
		        	
				
				$retval .= '</td>';

				// PAGES column
				if ($count <= 0) {
	                $retval .= '<td style="text-align: center;">***</td>'; //*** placeholder for archived/purged items
				} else {
	                $retval .= ''
					. '<td style="text-align: right;" title="' .JTEXT::_( 'Click for additional details' ). '">'
					. '<a href="javascript:document.adminForm.moreinfo.value=\'' . $vid . '\';submitbutton(\'r18\');">'
					. $count
					. '&nbsp;&nbsp;'
			        . '<img src="'. _JSAdminImagePath .'pathinfo.png" border="0" alt="' . JTEXT::_( 'Path info' ) . '" />'
					. '</a>'
	                . '</td>'
					;
				}

				$retval .= ''
                . '<td align="left" nowrap="nowrap">' . $html_os_img . ' ' . $row->system . '</td>'
                . '<td align="left" nowrap="nowrap">' . $html_browser_img . ' ' . $row->browser . '</td>'
                . '<td>'
            	. '<a href="javascript:document.adminForm.vid.value=\''
            	. $row->aid . '\';submitbutton(\'' . $task . '\');" title="' . $alt . '">'
            	. '<img src="images/' . $img . '" border="0" alt="' . $alt . '" /></a>'
            	. ( ( $row->nslookup && ( $row->nslookup != '127.0.0.1' && $row->nslookup != 'localhost' ) )
            		? '&nbsp;' . sprintf( $whois, $row->nslookup )
            		: ( ( $row->ip && $row->ip != '127.0.0.1' ) ? '&nbsp;' . sprintf( $whois, $row->ip ) : '' )
            	)
            	. '</td>'
            	. '</tr>' . "\n";

				$k = 1 - $k;
			}
		}else{
           	$retval .= '<tr><td colspan="12" style="text-align:center">'
           	. JTEXT::_( 'No data' )
          	. '</td></tr>' . "\n";
        }

		js_echoJSDebugInfo($prof->mark('after'), '');


        $retval .= ''
		. '<tfoot><tr><td colspan="12">'.$pagination->getListFooter().'</td></tr></tfoot>'
        . '</table>' . "\n"
		;

		return $retval;
	}

	/**
	 * show visitors by country/TLD
	 *
	 * case r05
	 * @return string
	 */
	function getVisitorsByTld() {
		global $mainframe;
		global $option;

		require_once( dirname(__FILE__) .DS. 'util.classes.php' );
		require_once( dirname(__FILE__) .DS. 'base.classes.php' );

		$JSUtil = new js_JSUtil();
		$JSSystemConst = new js_JSSystemConst();


        // mic: search not activated as of 2006.12.23, prepared for later
        //$search		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
        //$search		= $this->db->getEscaped( trim( strtolower( $search ) ) );


		$date_from;
		$date_to;
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );


		$query = 'SELECT count(*) AS numbers, a.tld, b.fullname'
		. ' FROM '
		. '   #__jstats_visits AS v'
		. '   LEFT JOIN #__jstats_ipaddresses a ON (a.id = v.visitor_id)'
		. '   LEFT JOIN #__jstats_topleveldomains b ON (a.tld = b.tld)'
		. ' WHERE '
		. '   a.type='._JS_DB_IPADD__TYPE_REGULAR_VISITOR
		. '   AND '.$this->JSDatabaseAccess->getConditionStringFromDates( $date_from, $date_to)
		.     ( ($this->JSConf->include_summarized == true) ? '' : (' AND v.visit_id>='.$this->buid()) )
		. ' GROUP BY a.tld'
		. ' ORDER BY numbers DESC, b.fullname ASC'
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadObjectList();

		$total = 0;
		$max_value = 0;
		$sum_all_values = 0;
		if ( $rows ) {
			$total = count( $rows );

            foreach( $rows as $row ) {
                $sum_all_values   += $row->numbers;

                if( $row->numbers > $max_value ) {
                    $max_value = $row->numbers;
                }
            }
		}

		$JSStatisticsCommonTpl = new js_JSStatisticsCommonTpl();

		$retval  = '<table class="adminlist">' . "\n"
		. '<thead>' . "\n"
		. '<tr>' . "\n"
		. '<th style="width: 1%;">#</th>'
		. '<th style="width: 2%;">' . JTEXT::_ ('Flag' ) . '</th>'
		. '<th style="width: 3%;">' . JTEXT::_( 'Code' ) . '</th>'
		. '<th style="width: 10%; white-space: nowrap;" title="' . JTEXT::_( 'Number of visitors' ) .'">' . JTEXT::_( 'Visitors' ) . '</th>'
		. '<th style="width: 20%;">' . JTEXT::_( 'Percent' ) . '</th>'
		. '<th style="width: 65%; text-align: left;">' . JTEXT::_( 'Country/Domain' ) . '</th>'
		. '</tr>' . "\n"
		. '</thead>' . "\n"
		;

		if( $rows ) {

		    $k		= 0;
			$order_nbr	= 0;
            foreach( $rows as $row ) {
				$order_nbr++;

				$style = '';
				if( $row->tld == '' ) {
					$style = ' style="background-color:#FFEFEF;"';
				}

                $retval .= '<tr class="row' . $k . '"' . $style . '>' . "\n"
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
                . '<td align="center"><img src="';
                
                if( $row->tld == '' ) {
                    $retval .= $JSUtil->getImageWithUrl('unknown', $JSSystemConst->defaultPathToImagesTld) .'"'
                    . ' alt="' . JTEXT::_( 'Unknown' ) . '"'
                    . ' title="' . JTEXT::_( 'Unknown' ) . '"';
                }else{
                    $retval .= $JSUtil->getImageWithUrl($row->tld, $JSSystemConst->defaultPathToImagesTld) . '"'
                    . ' alt="'. $row->tld .'"'
                    . ' title="'. $row->tld .'"';
                }


                $retval .= '" />'
				. '</td>'
        		. '<td align="left">&nbsp;' . $row->tld . '</td>'
        		. '<td align="center">&nbsp;' . $row->numbers . '</td>'
        		. '<td align="left">' . $JSStatisticsCommonTpl->getPercentBarWithPercentNbr( $row->numbers, $max_value, $sum_all_values ) . '</td>'
                . '<td align="left">&nbsp;'
                . ( ( ( $row->tld == 'localhost' ) || $row->tld == '127.0.0.1' )
                	? JTEXT::_( 'Local' )
                	: ( $row->tld ? JTEXT::_( $row->tld ) : '<span style="color:#FF0000;">' . JTEXT::_( 'Unknown' ) . '</span>' ) ) // $row->fullname
                . '</td>'
                . '</tr>' . "\n";

				$k = 1 - $k;
            }
        }else{
        	$retval .= '<tr>' . "\n"
        	. '<td colspan="6" style="text-align:center">'
        	. JTEXT::_( 'No data' )
        	. '</td></tr>' . "\n";
        }

		//total line
		$retval .= ''
		. '<thead>' . "\n"
		. '<tr>' . "\n"
		;

		if( $total == 0 ) {
			$retval .= '<th colspan="6" align="left">&nbsp;' . JTEXT::_( 'No countries/domains' ) . '</th>';
		} else {
			$retval .= ''
			. '<th>&nbsp;</th>'
			. '<th colspan="2">' . JTEXT::_( 'Total' ) . '</th>'
			. '<th>' . $sum_all_values . '</th>'
			. '<th>&nbsp;</th>'
			. '<th style="text-align: left;">'
				. $total . '&nbsp;'
				. ( $total == 1 ? JTEXT::_( 'Country' ) : JTEXT::_( 'Countries' ) )
			. '</th>';
		}

		$retval .= ''
		. '</tr>' . "\n"
		. '</thead>' . "\n"
		. '</table>' . "\n"
		;

		return $retval;
	}


		
	/**
	 * show browsers
	 * case r08
	 *
	 * @return string
	 */
	function getBrowsers() {

		$JSStatisticsCommonTpl = new js_JSStatisticsCommonTpl();

		$totalbrowsers 	= 0;
		$totalnmb		= 0;
		$totalmax 		= 0;

		$date_from;
		$date_to;
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );

		$query = ''
		. ' SELECT'
		. '   count(*)   AS numbers,'
		. '   w.browser  AS browser'
		. ' FROM'
		. '   #__jstats_ipaddresses AS w'
		. '   LEFT JOIN #__jstats_visits AS v ON (v.visitor_id = w.id)'
		. ' WHERE'
		. '   w.type = '._JS_DB_IPADD__TYPE_REGULAR_VISITOR
		. '   AND '.$this->JSDatabaseAccess->getConditionStringFromDates($date_from, $date_to)
		.     ( (!$this->JSConf->include_summarized) ? (' AND v.visit_id>=' . $this->buid()) : '' )
		. ' GROUP BY'
		. '   w.browser'
		. ' ORDER BY'
		. '   numbers DESC,'
		. '   w.browser ASC'
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadObjectList();

		if( count( $rows ) > 0 ) {
			foreach( $rows as $row ) {
            	$totalbrowsers++;
                $totalnmb += $row->numbers;

            	if( $row->numbers > $totalmax ) {
                    $totalmax = $row->numbers;
            	}
        	}
		}
		
		$total = 0;
		$max_value = 0;
		$sum_all_values = 0;
		if ( $rows ) {
			$total = count( $rows );

            foreach( $rows as $row ) {
                $sum_all_values   += $row->numbers;

                if( $row->numbers > $max_value ) {
                    $max_value = $row->numbers;
                }
            }
		}

		$retval = '<table class="adminlist">' . "\n"
		. '<thead>' . "\n"
		. '<tr>'
		. '<th style="width: 1px;">#</th>'
		. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Count' ).'</th>'
		. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Percent' ).'</th>'
		. '<th style="width: 100%;">' . JTEXT::_( 'Browser' ).'</th>'
		. '</tr>'
		. '</thead>' . "\n";

        if( $totalnmb != 0 ) {
        	$k = 0;
			$order_nbr = 0;
			if( count( $rows ) > 0 ) {
            	foreach( $rows as $row ) {
					$order_nbr++;

            		$style = '';
					if( !$row->browser ) {
						$style = ' style="background-color:#FFEFEF"';
					}

        			$retval .= '<tr class="row' . $k . '"' . $style . '>'
				  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
					. '<td style="text-align: center;">' . $row->numbers . '</td>'
                    . '<td>' . $JSStatisticsCommonTpl->getPercentBarWithPercentNbr( $row->numbers, $max_value, $sum_all_values ) . '</td>'
					. '<td>' . $row->browser . '</td>'
					. '</tr>' . "\n";

					$k = 1 - $k;
				}
			}
		}

		// Summary Bar
		$retval .= ''
		. '<thead>' . "\n"
		. '<tr>'
		. '<th>&nbsp;</th>'
		. '<th style="text-align: center;">' . $totalnmb . '</th>'
		. '<th>&nbsp;</th>'
		. '<th>' . $totalbrowsers . '&nbsp;' . ( ($totalbrowsers<=1) ? JTEXT::_( 'Browser type' ) : JTEXT::_( 'Browser types' ) ) . '</th>'
		. '</tr>'
		. '</thead>' . "\n"
		. '</table>' . "\n"
		;

		return $retval;
	}


	/**** case r11 see statistics.php ***/

	/**
	 * shows unknown bots
	 * case r12
	 *
	 * @return string
	 */
	function getUnknown() {
		global $mainframe;
		global $option;

		$limit	= intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' )));
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$where = array();

		$date_from;
		$date_to;
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );

		$where[] = 'a.tld = b.tld';
		$where[] = 'v.visitor_id = a.id';
		$where[] = '(a.browser LIKE \'Unknown%\' OR a.browser = \'\')';
		$where[] = $this->JSDatabaseAccess->getConditionStringFromDates( $date_from, $date_to);

		/* mic: show only actual data (without already archived/purged)
		 * a.table : jstats_ipadresses
         * c.table : jstats_visits
         */
        if( !$this->JSConf->include_summarized ) {
            $where[] = 'v.visit_id >= ' . $this->buid();
        }

        // get total records
		$query = 'SELECT COUNT(*)'
		. ' FROM #__jstats_ipaddresses AS a, #__jstats_topleveldomains AS b, #__jstats_visits AS v'
		. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
		;
		$this->db->setQuery( $query );
		$total = $this->db->loadResult();

		jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $total, $limitstart, $limit );

		$query = 'SELECT a.tld, b.fullname, a.useragent, v.visit_date, v.visit_time'
		. ' FROM #__jstats_ipaddresses AS a, #__jstats_topleveldomains AS b, #__jstats_visits AS v'
		. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
		. ' ORDER BY v.visit_date, v.visit_time DESC'
		;
		$this->db->setQuery( $query, $pagination->limitstart, $pagination->limit );
		$rows = $this->db->loadObjectList();


		$JSStatisticsTpl = new js_JSStatisticsTpl();

		return $JSStatisticsTpl->viewNotIdentifiedVisitorsPageTpl( $rows, $pagination );
	}
}