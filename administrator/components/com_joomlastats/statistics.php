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
require_once( dirname(__FILE__) .DS. 'statistics.common.php' );
require_once( dirname(__FILE__) .DS. 'statistics.html.php' );
require_once( dirname(__FILE__) .DS. 'filters.php' );


/**
 *  This class generate statistics and show them in joomla back end (administrator panel)
 *
 *  NOTICE: methods from class JoomlaStats_Engine will be moved here
 *
 *  NOTICE: This class should contain only argument less functions that are called by task/action
 */
class js_JSStatistics
{
	/** hold JoomlaStats configuration */
	var $JSConf = null;

	/** hold TimePeriod control (it is used on statistic pages) */
	var $FilterTimePeriod = null;

	/** hold FilterSearch control (it is used on statistic pages) */
	var $FilterSearch = null;

	/** hold FilterDomain control (it is used on statistic pages) */
	var $FilterDomain = null;
	
	/** hold class with common methods to all statistics pages */
	var $JSStatisticsCommon = null;

	/** hold pointer to class with templates */
	var $JSStatisticsTpl = null;



	function __construct($JSConf = null) {

		$this->JSConf = $JSConf;
		if ($this->JSConf == null)
			$this->JSConf = new js_JSConf();

		$this->FilterTimePeriod = new js_JSFilterTimePeriod();
		$this->FilterTimePeriod->readTimePeriodFromRequest( $this->JSConf->startdayormonth );

		$this->FilterSearch = new js_JSFilterSearch();
		$this->FilterSearch->readSearchStringFromRequest();
		
		$this->FilterDomain = new js_JSFilterDomain();
		$this->FilterDomain->readDomainStringFromRequest();
		
		$this->JSStatisticsCommon = new js_JSStatisticsCommon( $this->JSConf );

        $this->JSStatisticsTpl = new js_JSStatisticsTpl();
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
	function js_JSStatistics()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	
	
	/**
	 * this function return HTML table with 'Page Hits'
	 * (case r06)
	 *
	 * old function name 'getPageHits();'
	 *
	 * @param $JSConf - only for performance
	 * @return html page
	 */
	function viewPageHits() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.many.rows.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'general.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'filters.php' );
		
			
		global $mainframe;
		
		
		// ###  Filters
		$date_from = '';
		$date_to = '';
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );
		
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		$vid = '';
		$moreinfo = '';
		
		$limit	= intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mainframe->getCfg( 'list_limit' )));
        $limitstart	= intval( $mainframe->getUserStateFromRequest( "viewlimitstart", 'limitstart', 0 ) );

        

		// ###  Content
		$nbr_visited_pages 			= 0;
		$sum_all_pages_impressions	= 0;
		$max_page_impressions		= 0;
		$result_arr					= array();
		$summarized_info 			= array('count' => '', 'pages' => '');
		

		$include_summarized = $this->JSConf->include_summarized;
		
		$sums = null;
		$JSApiGlobal = new js_JSApiGeneral();
		$JSApiGlobal->getPagesImpressionsSums( $date_from, $date_to, $include_summarized, $sums );
		
		$nbr_visited_pages = $sums->nbr_visited_pages;
		$sum_all_pages_impressions = $sums->sum_all_pages_impressions;
		$max_page_impressions = $sums->max_page_impressions;

				
		$total = $nbr_visited_pages;
		jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $total, $limitstart, $limit );
		
		$JSApiGlobal->getPagesImpressionsArr($pagination->limitstart, $pagination->limit, $date_from, $date_to, $include_summarized, $result_arr );
		
		if ($include_summarized) {
			//additional processing for page with summarized data
			$summarized_info['count'] = $sums->sum_all_pages_impressions_only_summarized;
			$summarized_info['pages'] = $sums->nbr_visited_pages_only_summarized;
		}


		// ###  Template
		$result_html  = '';
		$result_html .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);
        $result_html .= $this->JSStatisticsTpl->viewPageHitsPageTpl( $nbr_visited_pages, $sum_all_pages_impressions, $max_page_impressions, $result_arr, $summarized_info, $pagination );
        $result_html .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result_html;
	}
	
	
	/**
	 *  This function return HTML table with 'Operating Systems' (show all operating systems)
	 *  (case r07)
	 *
	 *  old function name 'getSystems();'
	 *
	 *  There is no pagination - max number is less than 40
	 *
	 *  @param $JSConf - only for performance
	 *  @return html page
	 */
	function viewSystems() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'general.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'db.constants.php' );
		
			
		global $mainframe;
		
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		$vid = '';
		$moreinfo = '';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);

		$date_from = '';
		$date_to = '';
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );

		$include_summarized = $this->JSConf->include_summarized;
		
		$result_arr = array();
		$JSApiGeneral = new js_JSApiGeneral();
		$JSApiGeneral->getOperatingSystemVisistsArr( $date_from, $date_to, $include_summarized, '', $result_arr );
		
		
		$sum_all_system_visits	= 0;
		$max_system_visits		= 0;

		if( count( $result_arr ) > 0 ) {
			foreach( $result_arr as $row ) {
            	$sum_all_system_visits += $row->os_visits;

            	if( $row->os_visits > $max_system_visits ) {
                    $max_system_visits = $row->os_visits;
            	}
        	}
		}

		$ostype_name_arr = array();
		{
			$__jstats_ostype = unserialize(_JS_DB_TABLE__OSTYPE);
			foreach( $__jstats_ostype as $ostype )
				$ostype_name_arr[] = $ostype['ostype_name'];
		}
		
        $result .= $this->JSStatisticsTpl->viewSystemsPageTpl( $sum_all_system_visits, $max_system_visits, $ostype_name_arr, $result_arr );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}

		
	/**
	 * this function return HTML table with 'Not identified visitors'
	 * (case r11)
	 *
	 * old function name 'getNotIdentified();'
	 *
	 * @param $JSConf - only for performance
	 * @return html page
	 */
	function viewNotIdentifiedVisitors() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'general.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.many.rows.php' );
		
		global $mainframe;

		
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		$vid = '';
		$moreinfo = '';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);

		
		$limit	= intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) ) );
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$date_from = '';
		$date_to = '';
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );
        
		$NumberOfNotIdentifiedVisitors = 0;
		$JSApiGeneral = new js_JSApiGeneral();
		$JSApiGeneral->getVisitorsNumber( _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR, $this->JSConf->include_summarized, $date_from, $date_to, $NumberOfNotIdentifiedVisitors );

		

		jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $NumberOfNotIdentifiedVisitors, $limitstart, $limit );

		$JSDbSMR = new js_JSDbSMR();
		$rows = null;
		$JSDbSMR->selectNotIdentifiedVisitorsArr($pagination->limitstart, $pagination->limit, $date_from, $date_to, $this->JSConf->include_summarized, $rows );

		
        $result .= $this->JSStatisticsTpl->viewNotIdentifiedVisitorsPageTpl( $rows, $pagination );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}
	
	

	
	/**
	 *  this function return HTML table with 'Search Engines' and 'Keywords'
	 *  (case r14 and r15)
	 *
	 *  old function name 'getSearches();'
	 *
	 *  @return html page
	 */
	function viewSearchEnginesAndKeywords($isKeywords) {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		global $mainframe;
		

		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		if( $isKeywords ) {
			$this->FilterDomain->show_domain_filter = true;
			$this->FilterDomain->setDomainHint( '' ); //@todo
		}
		$vid = '';
		$moreinfo = '';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);

		
		$limit	    = intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) ) );
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$day   = '%';
		$month = '%';
		$year  = '%';
		$this->FilterTimePeriod->getDMY( $day, $month, $year );
		
		
		
		/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)*/
		$where = ''
		. ' YEAR(a.kwdate) LIKE \'' . $year . '\''
		. ' AND MONTH(a.kwdate) LIKE \'' . $month . '\''
		. ' AND DAYOFMONTH(a.kwdate) LIKE \'' . $day . '\''
		;
		$domain_str = $this->FilterDomain->getDomainString();
		if( ($isKeywords) && ($domain_str!='')  )
			$where .= ' AND b.searcher_name LIKE \''. $domain_str . '\'';
		

		if( $isKeywords ) {
			// Search Keyphrases
			/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)*/
			$query = 'SELECT a.keywords, count(*) AS count'
			. ' FROM #__jstats_keywords AS a'
			. ' LEFT JOIN #__jstats_searchers AS b ON (a.searcher_id = b.searcher_id)'
			. ' WHERE'
			. $where
			. ' GROUP BY a.keywords'
			. ' ORDER BY count DESC'
			;
		} else {
			// Search Engines
			/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)*/
			$query = 'SELECT b.searcher_name, count(*) AS count'
			. ' FROM #__jstats_keywords AS a'
			. ' LEFT JOIN #__jstats_searchers AS b ON (a.searcher_id = b.searcher_id)'
			. ' WHERE'
			. $where
			. ' GROUP BY b.searcher_name'
			. ' ORDER BY count DESC'
			;
		}

		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadObjectList();

		
		$total = 0;
		$max_value = 0;
		$sum_all_values = 0;
		if ( $rows ) {
			$total = count( $rows );

            foreach( $rows as $row ) {
                $sum_all_values   += $row->count;

                if( $row->count > $max_value ) {
                    $max_value = $row->count;
                }
            }
		}
		
		jimport( 'joomla.html.pagination' );
		//pagination is not dealed in right way ( a) MySQL 3.0 do not have nested queries (so unable to do this)  b) probably there was not gain from this - use profiler to check)
		$pagination = new JPagination( $total, $limitstart, $limit );
		
        $result .= $this->JSStatisticsTpl->viewSearchEnginesAndKeywordsTpl( $isKeywords, $rows, $pagination, $total, $max_value, $sum_all_values );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}
	
	
	/**
	 *  this function return HTML table with 'Referrers by domain' and 'Referrers by page'
	 *  (case r16 and r17)
	 *
	 *  old function name 'getReferrers();'
	 *
	 *  @return html page
	 */
	function viewReferrers( $byPage ) {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		global $mainframe;
		

		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		if( $byPage ) {
			$this->FilterDomain->show_domain_filter = true;
			$this->FilterDomain->setDomainHint( '' ); //@todo
		}
		$vid = '';
		$moreinfo = '';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);

		
		$limit	    = intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) ) );
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$day   = '%';
		$month = '%';
		$year  = '%';
		$this->FilterTimePeriod->getDMY( $day, $month, $year );
		
		
		
		/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)*/
		$where = ''
			. ' year LIKE \'' . $year . '\''
			. ' AND month LIKE \'' . $month . '\''
			. ' AND day LIKE \'' . $day . '\''
			;
		$domain_str = $this->FilterDomain->getDomainString();
		if( ($byPage) && ($domain_str!='')  )
			$where .= ' AND domain LIKE \'' . $domain_str . '\'';
		

		if( $byPage ) {
			// 'Referrers by page'
			/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)*/
			$query = ''
			. ' SELECT'
			. '   COUNT(*) AS counter,'
			. '   referrer'
			. ' FROM'
			. '   #__jstats_referrer r'
			. ' WHERE'
			.     $where
			. ' GROUP BY'
			. '   r.referrer'
			. ' ORDER BY'
			. '   counter DESC'
			;
		} else {
			// 'Referrers by domain'
			/*  NOTICE: visit_id was introduced in v3.0.0.372 - old data were NOT converted to this value, so it can not be used!! It is introduced to collect data for the future!! (not all data could be converted to new format, that is why now we duplicate data!)*/
			$query = ''
			. ' SELECT'
			. '   COUNT(*) AS counter,'
			. '   domain'
			. ' FROM'
			. '   #__jstats_referrer r'
			. ' WHERE'
			.     $where
			. ' GROUP BY'
			. '   r.domain'
			. ' ORDER BY'
			. '   counter DESC'
			;
		}
		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadObjectList();

		
		$total = 0;
		$max_value = 0;
		$sum_all_values = 0;
		if ( $rows ) {
			$total = count( $rows );

            foreach( $rows as $row ) {
                $sum_all_values   += $row->counter;

                if( $row->counter > $max_value ) {
                    $max_value = $row->counter;
                }
            }
		}
		
		jimport( 'joomla.html.pagination' );
		//pagination is not dealed in right way ( a) MySQL 3.0 do not have nested queries (so unable to do this)  b) probably there was not gain from this - use profiler to check)
		$pagination = new JPagination( $total, $limitstart, $limit );
		
        $result .= $this->JSStatisticsTpl->viewReferrersTpl( $byPage, $rows, $pagination, $total, $max_value, $sum_all_values );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}


	
	/**
	 *  this function return HTML table with 'Bots by domain'
	 *  (case r09)
	 *
	 *  old function name 'getBots();'
	 *
	 *  @return html page
	 */
	function viewBotsByDomain() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );
		global $mainframe;
		

		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		$vid = '';
		$moreinfo = '';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);

		
		$limit	    = intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) ) );
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$date_from = '';
		$date_to   = '';
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );

		$buid = 0;
		$JSDbSOV = new js_JSDbSOV();
		$JSDbSOV->getBuid( $buid );


		$query = ''
		. ' SELECT'
		. '   COUNT(*)   AS numbers,'
		. '   w.browser'
		. ' FROM'
		. '   #__jstats_ipaddresses w'
		. '   LEFT JOIN #__jstats_visits v ON (v.visitor_id = w.id)'
		. ' WHERE'
		. '   w.browser!=\'\''
		. '   AND w.type='._JS_DB_IPADD__TYPE_BOT_VISITOR
		. '   AND '.$JSDatabaseAccess->getConditionStringFromDates( $date_from, $date_to)
		.     (($this->JSConf->include_summarized) ? ('') : (' AND v.visit_id>='.$buid) )
		. ' GROUP BY'
		. '   w.browser'
		. ' ORDER BY'
		. '   numbers DESC,'
		. '   w.browser ASC'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadObjectList();

		
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
		
		jimport( 'joomla.html.pagination' );
		//pagination is not dealed in right way ( a) MySQL 3.0 do not have nested queries (so unable to do this)  b) probably there was not gain from this - use profiler to check)
		$pagination = new JPagination( $total, $limitstart, $limit );
		
        $result .= $this->JSStatisticsTpl->viewBotsByDomainTpl( $rows, $pagination, $total, $max_value, $sum_all_values );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}


	/**
	 *  this function return HTML table with 'Bots'
	 *  (case r10)
	 *
	 *  old function name 'getBots();'
	 *
	 *  @return html page
	 */
	function viewBots() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );
		global $mainframe;
		

		$JSDatabaseAccess = new js_JSDatabaseAccess();
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = true;
		$this->FilterDomain->setDomainHint( '' ); //@todo
		$domain_str = $this->FilterDomain->getDomainString();
		$vid = '';
		$moreinfo = '';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $moreinfo, $this->FilterDomain);

		
		$limit	    = intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) ) );
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$date_from = '';
		$date_to   = '';
		$this->FilterTimePeriod->getTimePeriodsDates( $date_from, $date_to );

		$buid = 0;
		$JSDbSOV = new js_JSDbSOV();
		$JSDbSOV->getBuid( $buid );

		$query = ''
		. ' SELECT'
		. '   w.tld,'
		. '   w.browser,'
		. '   w.useragent, b.fullname, v.visit_date, v.visit_time, v.visit_id'
		. ' FROM'
		. '   #__jstats_ipaddresses w'
		. '   LEFT JOIN #__jstats_visits v ON (v.visitor_id = w.id)'
		. '   LEFT JOIN #__jstats_topleveldomains b ON (w.tld = b.tld)'
		. ' WHERE'
		. '   w.browser!=\'\'' //@bug> Is this OK?
		. '   AND w.type='._JS_DB_IPADD__TYPE_BOT_VISITOR
		.     ( ($domain_str!='') ? (' AND w.browser LIKE \'' . $domain_str . '\'') : '' )
		. '   AND '.$JSDatabaseAccess->getConditionStringFromDates( $date_from, $date_to)
		.     (($this->JSConf->include_summarized) ? ('') : (' AND v.visit_id>='.$buid) )
		. ' ORDER BY'
		. '   v.visit_date DESC,'
		. '   v.visit_time DESC'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadObjectList();

		
		$total = 0;
		if ( $rows ) {
			$total = count( $rows );
		}

		if ( $total > 0 ) {
			for ($i=$limitstart; ($i<$total && $i<($limitstart+$limit)); $i++) {
				$vid = $rows[$i]->visit_id;

				$query = 'SELECT count(*) AS pages_nbr'
				. ' FROM #__jstats_impressions i'
				. ' WHERE i.visit_id = ' . $vid
				;
				$JSDatabaseAccess->db->setQuery( $query );
				$pages_nbr = $JSDatabaseAccess->db->loadResult();
				$rows[$i]->pages_nbr = $pages_nbr;
			}
		}

		
		jimport( 'joomla.html.pagination' );
		//pagination is not dealed in right way ( a) MySQL 3.0 do not have nested queries (so unable to do this)  b) probably there was not gain from this - use profiler to check)
		$pagination = new JPagination( $total, $limitstart, $limit );
		
        $result .= $this->JSStatisticsTpl->viewBotsTpl( $rows, $pagination, $total );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}

	/**
	 * this function return table with 'Resolutions'
	 * case r
	 *
	 *  ############################################################
	 *        THIS FUNCTION NOT WORKING AND IT IS NOT USED!
	 *  ############################################################
	 *
	 * @param $JSConf - only for performance
	 * @param object $TimePeriod
	 * @param integer $buid
	 * @return html
	 * @since 2.3.x
	 */
	function viewResolutions( $TimePeriod, $buid ) {
		global $mainframe;

		$limit	= intval( $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) ) );
        $limitstart	= intval( $mainframe->getUserStateFromRequest( 'viewlimitstart', 'limitstart', 0 ) );

		$where				= array();
		$summary			= array();
		$summary['screens']		= 0;
		$summary['number']		= 0;
		$summary['maximum']		= 0;

		$this->resetVar(1);

		$where[] = 'c.ip_id = a.id';
		//$where[] = 'a.type = 1';
		//$where[] = 'c.day LIKE \'' . $this->d . '\'';
		//$where[] = 'c.month LIKE \'' . $this->m . '\'';
		//$where[] = 'c.year LIKE \'' . $this->y . '\'';

		//echo 'JSengine->d [' . $this->d . ']<br />';

		if( !$JSConf->include_summarized ) {
			$where[] = 'a.id = c.ip_id AND c.id >= ' . $this->buid();
		}

		// get total records
		/*
		// mic 20081015: not used, but ready to use
		$query = 'SELECT COUNT(*)'
		. ' FROM #__jstats_ipaddresses AS a, #__jstats_visits AS c'
		. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
		;
		$this->db->setQuery( $query );
		$total = $this->db->loadResult();

		jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $total, $limitstart, $limit );
		*/
		$pagination = null; // mic: set here to null ONLY if pagination IS NOT USED!

		$query = 'SELECT a.screen, count(*) AS numbers'
		. ' FROM #__jstats_ipaddresses AS a, #__jstats_visits AS c'
		. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
		. ' GROUP BY a.screen'
		. ' ORDER BY numbers DESC, a.screen ASC'
		;
		$this->db->setQuery( $query ); // , $pagination->limitstart, $pagination->limit ); // mic: ready to use
		$rows = $this->db->loadObjectList();

		$this->resetVar( 0 ); // mic: why is this crazy thing here ?????

		if( count( $rows ) > 0 ) {
			foreach( $rows as $row ) {
            	++$summary['screens'];
                $summary['number'] += $row->numbers;

            	if( $row->numbers > $summary['maximum'] ) {
                    $summary['maximum'] = $row->numbers;
            	}
        	}
		}

        return $this->JSStatisticsTpl->viewResolutionsTpl( $rows, $pagination, $summary );
	}


	/**
	 *  This function return HTML content (texts + tables) with 'Details about Visit' (date, time, user etc.)
	 *  (case r18)
	 *
	 *  old function name 'moreVisitInfo();'
	 *  old task case r03a
	 *
	 *
//	 *  @param $JSConf - only for performance
	 *  @return html page
	 */
	function viewDetailVisitInformation() {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		//require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'api' .DIRECTORY_SEPARATOR. 'general.php' );
		//require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'db.constants.php' );
		
		global $mainframe;
		
		$result = '';
				
		$this->FilterSearch->show_search_filter = false;
		$this->FilterDomain->show_domain_filter = false;
		$this->FilterTimePeriod->show_time_period_filter = false;
		$vid = '';
		$visit_id = intval( $mainframe->getUserStateFromRequest( 'moreinfo', 'moreinfo', '' ) );
		if ($visit_id <= 0)
			return '<br/><br/><br/><div style="color: red;"><b>Please report this problem to developers - Thank You!<br/><br/>This page can be viewed only for particular "visit_id"!</b></div><br/><br/><br/>';
		$result .= $this->JSStatisticsCommon->getJSStatisticsHeaderHtmlCode($this->FilterSearch, $this->FilterTimePeriod, $vid, $visit_id, $this->FilterDomain);



		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$query = ''
		. ' SELECT'
		. '   count(*) AS impresions,'
		. '   p.page,'
		. '   p.page_title'
		. ' FROM'
		. '   #__jstats_impressions i'
		. '   LEFT JOIN #__jstats_pages AS p ON (p.page_id = i.page_id)'
		. ' WHERE'
		. '   i.visit_id = ' . $visit_id
		. ' GROUP BY'
		. '   p.page'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$impressions_result_arr = $JSDatabaseAccess->db->loadObjectList();


		$impressions_sum_all	= 0;
		if( count( $impressions_result_arr ) > 0 ) {
			foreach( $impressions_result_arr as $row ) {
            	$impressions_sum_all += $row->impresions;
        	}
		}


		$query = ''
		. ' SELECT'
		. '   p.page,'
		. '   p.page_title'
		. ' FROM'
		. '   #__jstats_impressions i'
		. '   LEFT JOIN #__jstats_pages AS p ON (p.page_id = i.page_id)'
		. ' WHERE'
		. '   i.visit_id = ' . $visit_id
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$path_result_arr = $JSDatabaseAccess->db->loadObjectList();


		$query = ''
		. ' SELECT'
		. '   v.visit_id,'
		. '   v.visitor_id,'
		. '   v.joomla_userid,'
		. '   v.visit_date,'
		. '   v.visit_time'
		. ' FROM '
		. '   #__jstats_visits v'
		. ' WHERE'
		. '   v.visit_id = ' . $visit_id
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$VisitObj = $JSDatabaseAccess->db->loadObject();

		if ($VisitObj->joomla_userid > 0) {
			$query = ''
			. ' SELECT'
			. '   ju.name AS joomla_username'
			. ' FROM '
			. '   #__users ju'
			. ' WHERE'
			. '   ju.id = ' . $VisitObj->joomla_userid
			;
			$JSDatabaseAccess->db->setQuery( $query );
			$VisitObj->joomla_username = $JSDatabaseAccess->db->loadResult();
		}

		$query = ''
		. ' SELECT'
		. '   w.ip            AS visitor_ip,'
		. '   w.nslookup      AS visitor_nslookup,'
		. '   w.tld,'
		. '   w.system,'
		. '   w.browser,'
		. '   w.type,'
		. '   t.fullname      AS tld_name'
		. ' FROM'
		. '   #__jstats_ipaddresses w'
		. '   LEFT JOIN #__jstats_topleveldomains AS t ON (w.tld = t.tld)'
		. ' WHERE'
		. '   w.id = ' . $VisitObj->visitor_id
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$VisitorObj = $JSDatabaseAccess->db->loadObject();


        $result .= $this->JSStatisticsTpl->viewDetailVisitInformationTpl( $VisitObj, $VisitorObj, $impressions_sum_all, $impressions_result_arr, $path_result_arr );
        
        $result .= $this->JSStatisticsCommon->getJSStatisticsFooterHtmlCode();
        
        return $result;
	}


}