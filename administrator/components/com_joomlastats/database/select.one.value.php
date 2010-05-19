<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */



if( !defined( '_JS_STAND_ALONE' ) && !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}



require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'access.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'db.constants.php' );





/**
 * This class contain database query selects that return one value
 *
 * All methods are static
 * 
 * js_JSDbSOV JoomlaStats Database Select One Value
 */
class js_JSDbSOV extends js_JSDatabaseAccess
{
	/** constructor initialize database access */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Return JS database version (it could differ from JS PHP version)
	 *
	 * @param out string $JSDatabaseVersion_result - eg.: '2.3.0.113 dev' (in case of development snapshot), '2.2.3.150' (in case of release) 
	 * @return true on success
	 */
	function getJSDatabaseVersion( &$JSDatabaseVersion_result ) {

		$query = 'SELECT a.value'
		. ' FROM #__jstats_configuration AS a'
		. ' WHERE description = \'version\''
		;
		$this->db->setQuery( $query );
		$JSDatabaseVersion_result = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
			
		return true;
	}
	
	/**
	 * returns first id from current table page_request for checking inside page_request_c
	 * used where queries should be done and result is shown/included with purged data
	 *
	 * Do we realy need that value - if data are purged/summarized they not exist in general tables?
	 *
	 * @param out integer
	 * @return true on success
	 */
	function getBuid( &$buid_result ) {
		$query = 'SELECT MIN(i.visit_id)'
		. ' FROM #__jstats_impressions i'
		;
		$this->db->setQuery( $query );
		$buid_result = (int)$this->db->loadResult(); //must be (int)
		if ($this->db->getErrorNum() > 0)
			return false;
			
		return true;
	}

		
	/**
	 * Gets JoomlaStats database size (without backuped tables, migration tables etc.)
	 *
	 * @param out integer in bytes
	 * @return true on success
	 */
	function getJSDatabaseSize( &$JSDatabaseSize_result ) {
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. 'base.classes.php' );
		
		global $mainframe;

		$JSSystemConst = new js_JSSystemConst();
	
		$query = 'SHOW TABLE STATUS FROM `' . $mainframe->getCfg( 'db' ) . '`'
		. ' LIKE \'' . $mainframe->getCfg('dbprefix') . 'jstats_%\''
		//. ' WHERE '.$JSTableName
		//. ' LIKE \''.$TableName.'\''
		;
		$this->db->setQuery( $query );
		$rows = $this->db->LoadObjectList();
		if ($this->db->getErrorNum() > 0)
			return false;

		$TableNameArr = array();
		foreach( $JSSystemConst->allJSDatabaseTables as $JSTableName ) {
			$TableNameArr[] = $this->db->replacePrefix($JSTableName);
		}

		$nbr = 0;
		$total = 0;
		foreach( $rows as $row ) {
			//we must not count backuped tables, migration tables etc. - we check if table name is exacly the same!
			if (in_array($row->Name, $TableNameArr)) {
				$total += ((int)$row->Data_length) + ((int)$row->Index_length);
				$nbr++;
			}
		}

		$JSDatabaseSize_result = $total;
		if ( $nbr != count($JSSystemConst->allJSDatabaseTables))
			return false;//we return value, but we also return false

		return true;
	}
	
	/**
	 * Gets JoomlaStats last summarization/purge date
	 *
	 * Previously this parateter was hold by $js_JSConf->last_purge parameter
	 *
	 * @param out date - string like '2008-11-03', or 'false/null' when summarization was never done
	 * @return true on success
	 */
	function getJSLastSummarizationDate( &$LastSummarizationDate_result ) {
		global $mainframe;

		$query = ''
		. ' SELECT'
		. '   MAX(`impression_date`)'
		. ' FROM'
		. '   #__jstats_impressions_sums s';
		$this->db->setQuery( $query );
		$LastSummarizationDate_result = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
			
		//if (strlen($LastSummarizationDate_result) == 0)
		//	$JSLastSummarizationDate_result = false;
		
		return true;
	}

	/**
	 * Gets total number of identified visitors with bots/spiders/engines
	 *
	 * @todo I think query could be optimized (should be great gain)
	 *
	 * @param out integer
	 * @return true on success
	 */
	function selectTotalNumberVisitorsWithBots( &$TotalNumberVisitorsWithBots_result ) {
		
		$query = ''
		. ' SELECT SQL_BIG_RESULT'
		. '   COUNT(*) AS nbr_visitors_without_bots' //@todo we need SQL_BIG_RESULT? If Yes, other db queries should be fixed
		. ' FROM'
		. '   #__jstats_visits AS v'
		. '   LEFT JOIN #__jstats_ipaddresses w ON (v.visitor_id=w.id)'
		;
		$this->db->setQuery( $query );
		$TotalNumberVisitorsWithBots_result = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
		
		return true;
	}
	
	/**
	 * Gets total number of identified visitors without bots/spiders/engines
	 *
	 * @todo I think query could be optimized (should be great gain)
	 *
	 * @param out integer
	 * @return true on success
	 */
	function selectTotalNumberVisitorsWithoutBots( &$TotalNumberVisitorsWithoutBots_result ) {
		
		$query = ''
		. ' SELECT SQL_BIG_RESULT'
		. '   COUNT(*) AS nbr_visitors_without_bots' //@todo we need SQL_BIG_RESULT? If Yes, other db queries should be fixed
		. ' FROM'
		. '   #__jstats_visits AS v'
		. '   LEFT JOIN #__jstats_ipaddresses w ON (v.visitor_id=w.id)'
		. ' WHERE'
		. '   w.type='._JS_DB_IPADD__TYPE_REGULAR_VISITOR
		;
		$this->db->setQuery( $query );
		$TotalNumberVisitorsWithoutBots_result = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
		
		return true;
	}

	
	/**       MAIN FUNCTION TO SELECT NUMBER OF VISITORS - ALL KINDS
	 *            ALL JS SHOULD GET DATA THROUGH THIS FUNCTION
	 *                     (directly or indirectly)
	 *          (this function has twin brother - for performance)
	 *
	 *
	 * Gets number of visitors
	 *
	 * @todo I think query could be optimized (should be gain) - could be done only in MySql40
	 *
	 * @param $visitors_type       values - one of: ''; _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR; _JS_DB_IPADD__TYPE_REGULAR_VISITOR; _JS_DB_IPADD__TYPE_BOT_VISITOR
	 * @param $include_summarized  values: true; false; //get this value from $JSConf->include_summarized; ($JSConf = new js_JSConf();)
	 * @param $buid				   required only if $include_summarized = false //get this value from $JSDbSOV->getBuid($buid); ($JSDbSOV = js_JSDbSOV();)
	 * @param $date                formats: ''; '2009-03-25'; '2009-3-9'; '2009-03-25 16:42:56' (NOT RECOMENDED); //use '' to omit time limitation 
	 *
	 * NOTICE:
	 *   Faster version of this function is selectNumberOfVisitorsForYMD() (we should check it in MySQL 5.x)
	 *
	 * @param out integer
	 * @return true on success
	 */
	function selectNumberOfVisitors( $visitors_type, $include_summarized, $buid, $date_from, $date_to, &$NumberOfVisitors_result ) {
		
		$query = ''
		. ' SELECT SQL_BIG_RESULT'
		. '   COUNT(*)' //In this case we do not know if SQL_BIG_RESULT speed up this query? (profiler does not work proprely for this case)
		. ' FROM'
		. '   #__jstats_visits AS v'
		. '   LEFT JOIN #__jstats_ipaddresses w ON (v.visitor_id=w.id)'
		. ' WHERE'
		. '   '.( ($visitors_type!=='') ? 'w.type='.$visitors_type : '1=1' )
		. '   '.( ($include_summarized==false) ? 'AND v.visit_id >= '.$buid : '')
		. '   AND '.$this->getConditionStringFromDates($date_from, $date_to)
		;
		$this->db->setQuery( $query );
		$NumberOfVisitors_result = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
		
		return true;
	}


	/**       MAIN FUNCTION TO SELECT UNIQUE NUMBER OF VISITORS - ALL KINDS
	 *               ALL JS SHOULD GET DATA THROUGH THIS FUNCTION
	 *                       (directly or indirectly)
	 *            (this function has twin brother - for performance)
	 *
	 *
	 * Gets number of unigue visitors
	 *
	 * @todo This could (and should) be optimized (should be big gain) - in MySql40 optimization could be made, in MySql30 I do not know.
	 *
	 * @param $visitors_type       values - one of: ''; _JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR; _JS_DB_IPADD__TYPE_REGULAR_VISITOR; _JS_DB_IPADD__TYPE_BOT_VISITOR
	 * @param $include_summarized  values: true; false; //get this value from $JSConf->include_summarized; ($JSConf = new js_JSConf();)
	 * @param $buid				   required only if $include_summarized = false //get this value from $JSDbSOV->getBuid($buid); ($JSDbSOV = js_JSDbSOV();)
	 * @param $date                formats: ''; '2009-03-25'; '2009-3-9'; '2009-03-25 16:42:56' (NOT RECOMENDED); //use '' to omit time limitation //both date are inclusive ( $date_from =< result =< $date_to)
	 *
	 * NOTICE:
	 *   a) Currently faster version of this function not exist. See @todo
	 *   b) We should not merge this function with selectNumberOfVisitors() due to optimization (see @todo)
	 *
	 * @param out integer
	 * @return true on success
	 */
	function selectNumberOfUniqueVisitors( $visitors_type, $include_summarized, $buid, $date_from, $date_to, &$NumberOfUniqueVisitors_result ) {
		
		$query = ''
		. ' SELECT SQL_BIG_RESULT' //with SQL_BIG_RESULT query is 20% faster - @todo: other queries need to be reworked
		. '   COUNT(*)'
		. ' FROM'
		. '   #__jstats_visits AS v'
		. '   LEFT JOIN #__jstats_ipaddresses w ON (v.visitor_id=w.id)'
		. ' WHERE'
		. '   '.( ($visitors_type==='') ? '1=1' : ('w.type='.$visitors_type) )
		. '   '.( ($include_summarized==true) ? '' : ('AND v.visit_id >= '.$buid) )
		. '   AND '.$this->getConditionStringFromDates($date_from, $date_to)
		. ' GROUP BY'
		. '   v.visitor_id'
		;
		$this->db->setQuery( $query );
		$rows = $this->db->loadObjectList();
		if ($this->db->getErrorNum() > 0)
			return false;
		
		if ($rows)
			$NumberOfUniqueVisitors_result = count( $rows );
		else
			$NumberOfUniqueVisitors_result = 0;
			
		return true;
	}
	
	
	/**
	 * Gets total number
	 *
	 * @param out integer
	 * @return true on success
	 */
	function selectTotalNumberPageImpressionsWithBotsWithSummarized( &$TotalNumberPageImpressionsWithBotsWithSummarized_result ) {
		
		$current = 0;
		$sumarized = 0;
		
		$query = 'SELECT SQL_BIG_RESULT count(*) FROM #__jstats_impressions';
		$this->db->setQuery( $query );
		$current = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
			
		$query = 'SELECT SQL_BIG_RESULT SUM(s.count) FROM #__jstats_impressions_sums AS s';
		$this->db->setQuery( $query );
		$sumarized = $this->db->loadResult();
		if ($this->db->getErrorNum() > 0)
			return false;
		
		$TotalNumberPageImpressionsWithBotsWithSummarized_result = $current + $sumarized;
		return true;
	}

	
	
		
////////
//
// miscellaneous    section
//
////////

	/**
	 * Optimize table
	 *
	 * @param in  string $db_table_name - eg. '#__jstats_page_request'
	 * @return true on success
	 */
	function optimizeTable( $db_table_name ) {
		$query = 'OPTIMIZE TABLE `'.$db_table_name.'`';
		$this->db->setQuery( $query );
		$this->db->query();
		if ($this->db->getErrorNum() > 0)
			return false;
			
		return true;
	}
	
////////
//
// END: miscellaneous    section
//
////////


}

