<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author mic [http://www.joomlasupportdesk.com] 2009.03.17 07:30:31
 */

if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DS. 'base.classes.php' );
require_once( dirname(__FILE__) .DS. 'filters.php' );
require_once( dirname(__FILE__) .DS. 'util.classes.php' );
require_once( dirname(__FILE__) .DS. 'tools.html.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );


/**
 *  Object of this group all functionality of JS Maintenance tab
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSTools
{
	/** function from j1.5.6 from file 'j1.5.6\libraries\joomla\application\application.php' from class 'JApplication' */
	/** @access private */
	/** this function send token by SENT method */
	/** @bug - this function not working!! //@At need internet access to check HTML specification how to do it :( */
	function _redirect( $url, $msg='', $msgType='message' )
	{
		// check for relative internal links
		if (preg_match( '#^index[2]?.php#', $url )) {
			$url = JURI::base() . $url;
		}

		// Strip out any line breaks
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		/*
		// mic: not applicable in J.1.0.x
		// If the message exists, enqueue it
		if (trim( $msg )) {
			$this->enqueueMessage($msg, $msgType);
		}
		*/

		/*
		// @todo mic: check this, because JFactory is only avaliable in J.1.5.x!
		// Persist messages if they exist
		if (count($this->_messageQueue))
		{
			$session =& JFactory::getSession();
			$session->set('application.queue', $this->_messageQueue);
		}
		*/

		/*
		 * If the headers have been sent, then we cannot send an additional location header
		 * so we will output a javascript redirect statement.
		 */
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			//@ob_end_clean(); // clear output buffer
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $url );

			//$token = JHTML::_( 'form.token' )
			//$token = 'e8cf2710cb3963466b453cb84e9012f9';
			//$JSComponentId = 75;
			//$urlToUninstallJS = 'http://127.0.0.1/j156_2008-07-20/administrator/index.php?option=com_installer&type=components&task=remove&eid='.$JSComponentId.'&'.$token.'=1&boxchecked=1';//token not working when it is in GET method

			//header( 'HTTP/1.1 301 Moved Permanently' );
			//header("Method", "POST " + $urlToUninstallJS + " HTTP/1.5");
		}
		$this->close();
	}

	/**
	* Exit the application.
	* borrought from application.php J.1.5.x
	*
	* @access	public
	* @param	int	Exit code
	*/
	function close( $code = 0 ) {
		exit( $code );
	}

	/** @access private */
	function _returnBytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});

		switch($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	/** @todo function not finished - it has been only moved from admin.joomlastats.html.php file */
	function viewJSToolsPage() {

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$query = 'SELECT count(*)'
		. ' FROM #__jstats_impressions'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$pr_sum = $JSDatabaseAccess->db->loadResult();

		$JSDbSOV = new js_JSDbSOV();
		$LastSummarizationDate = false;
		$JSDbSOV->getJSLastSummarizationDate($LastSummarizationDate);

		$JSToolsTpl = new js_JSToolsTpl();
		$JSToolsTpl->viewJSToolsPageTpl( $pr_sum, $LastSummarizationDate );
	}

	function viewJSUninstallPage() {

		$warningMsg			= array();
		$recommendationMsg	= array();
		$infoMsg			= array();

		$warningMsg[] = array(
			'name'			=> JTEXT::_( 'Uninstall' ),
			'description'	=> JTEXT::_( 'All JoomlaStats database tables will be deleted!!!<br/><b>You will loose all stored statistcs with this action</b>!!' )
		);
		$recommendationMsg[] = array(
			'name'			=> JTEXT::_( 'Upgrade' ),
			'description'	=> JTEXT::_( 'To upgrade JoomlaStats, uninstall component in standard CMS uninstall method (Menu -> Extensions -> Install/Uninstall -> Components) and then install new JoomlaStats version.<br/>Previously collected statistics will be retained!' )
		);

		$JSToolsTpl = new js_JSToolsTpl();
		$JSToolsTpl->viewJSUninstallPageTpl( $warningMsg, $recommendationMsg, $infoMsg );

		return true;
	}

	/**
	 * Uninstall JoomlaStats Database (only)
	 *
	 * @return bool
	 */
	function doJSUninstall() {
		global $database;
		global $mainframe;

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$errors = array();

		//remove all JS tables from database
		$JSSystemConst = new js_JSSystemConst();
		foreach( $JSSystemConst->allJSDatabaseTables as $db_table_name) {
			$JSDatabaseAccess->db->setQuery('DROP TABLE `'.$db_table_name.'`');
			$JSDatabaseAccess->db->query();
			if ($JSDatabaseAccess->db->getErrorNum() > 0)
				$errors[] = $JSDatabaseAccess->db->getErrorMsg();
		}

		//common text for 2 cases
		$recommendationTextFinishUninstallationArr = array(
			'name'			=> JTEXT::_( 'Finish Uninstallation' ),
			'description'	=> JTEXT::_( 'To finish the uninstallation process use the standard CMS uninstalling method' )
		);

		if( count($errors) == count($JSSystemConst->allJSDatabaseTables) ) { //probalby user already uninstall database
			$noErrorMsgText				= ''; //this hide ColorInfoFrame
			$noWarningMsgText			= 'js_text_23432'; //this show ColorInfoFrame //this text will newer appear
			$noRecommendationMsgText	= 'js_text_23432'; //this show ColorInfoFrame //this text will newer appear

			$errorMsg					= array();
			$warningMsg					= array();
			$recommendationMsg			= array();

			$warningMsg[] = array(
				'name'			=> JTEXT::_( 'Probably JoomlaStats database already removed' ),
				'description'	=> JTEXT::_( 'It seems that you have already uninstalled the JoomlaStats database' )
			);
			$recommendationMsg[] = $recommendationTextFinishUninstallationArr;

			$JSToolsTpl = new js_JSToolsTpl();
			$JSToolsTpl->doJSUninstallFailTpl( $errorMsg, $noErrorMsgText, $warningMsg, $noWarningMsgText, $recommendationMsg, $noRecommendationMsgText );

			return false;
		}

		if ( count( $errors ) > 0 ) {
			$noErrorMsgText				= 'js_text_23432'; //this show ColorInfoFrame //this text will newer appear
			$noWarningMsgText			= ''; //this hide ColorInfoFrame
			$noRecommendationMsgText	= 'js_text_23432'; //this show ColorInfoFrame //this text will newer appear

			$db_errors_html  = JTEXT::_( 'List of errors:' ) . '<br/>';
			$db_errors_html .= implode( '<br/>', $errors );

			$errorMsg = array();
			$warningMsg = array();
			$recommendationMsg = array();

			$errorMsg[] = array(
				'name'			=> JTEXT::_( 'JoomlaStats database uninstall failed!' ),
				'description'	=> $db_errors_html
			);
			$recommendationMsg[] = array(
				'name'			=> JTEXT::_( 'Database Checkup' ),
				'description'	=> JTEXT::_( 'Serious errors occured' )
			);
			$recommendationMsg[] = array(
				'name'			=> JTEXT::_( 'Report' ),
				'description' 	=> JTEXT::_( 'Please report errors to JoomlaStats project website!' )
			);

			$JSToolsTpl = new js_JSToolsTpl();
			$JSToolsTpl->doJSUninstallFailTpl( $errorMsg, $noErrorMsgText, $warningMsg, $noWarningMsgText, $recommendationMsg, $noRecommendationMsgText );

			return false;
		}

		$bug_manualUninstal	= true; //this variable should be removed (first fix $this->_redirect() method)
		$JSComponentId		= -1;

		//get JS component Id
		// @At something is wrong with word 'option' I must add 'c.' alias
		$query = 'SELECT id'
		. ' FROM #__components AS c'
		. ' WHERE c.option = \'com_joomlastats\''
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rowList = $JSDatabaseAccess->db->loadAssocList();

		//if 2 or 0 components than error
		if( ( !$JSDatabaseAccess->db->query() ) || ( count( $rowList ) != 1 ) || $bug_manualUninstal ) {
			// this show ColorInfoFrame
			$noErrorMsgText				= JTEXT::_( 'No errors occured during JoomlaStats database uninstallation process.' );
			$noWarningMsgText			= ''; //this hide ColorInfoFrame
			$noRecommendationMsgText	= 'js_text_23432'; //this show ColorInfoFrame //this text will newer appear

			$errorMsg					= array();
			$warningMsg					= array();
			$recommendationMsg			= array();

			$recommendationMsg[]		= $recommendationTextFinishUninstallationArr;

			$JSToolsTpl = new js_JSToolsTpl();
			$JSToolsTpl->doJSUninstallFailTpl( $errorMsg, $noErrorMsgText, $warningMsg, $noWarningMsgText, $recommendationMsg, $noRecommendationMsgText );

			return false;
		}
		$JSComponentId = $rowList[0]['id'];

		//prepare link to uninstall component from joomla
		$urlToUninstallJS = '';
		//$token = JHTML::_( 'form.token' );
		//$urlToUninstallJS = 'index2.php?option=com_installer&type=components&task=remove&eid='.$JSComponentId.'&'.$token.'=1&boxchecked=1';//token not working when it is in GET method
		$urlToUninstallJS = 'index2.php?option=com_installer&type=components&task=remove&eid='
		. $JSComponentId . '&boxchecked=1'; //token not working when it is in GET method

		//after uninstall database go to joomla uninstall component menu
		// http://127.0.0.1/j156/administrator/index.php?option=com_installer&type=components&task=manage
		//$mainframe->redirect( 'index.php?option=com_installer&type=components&task=manage' );//third argument: 'message', 'notice', 'error'


		//try to uninstall from joomla
		//@at we can not do so simply :( (error 'Invalid Token' is returnded when try to execute below statement
		//Joomla checks is token was send by _POST method
		//$mainframe->redirect( 'index.php?option=com_installer&type=components&task=remove&eid='.$JSComponentId.'&'.$token.'=1&boxchecked=1' );//third argument: 'message', 'notice', 'error'

		$this->_redirect( $urlToUninstallJS );

		return true;
	}


	/**
	 * @todo function not finished - it has been only moved from admin.joomlastats.html.php file
	 * @todo mic: lang vars into JTEXT
	 *
	 *  summarization disabled since v2.5.0.313 (It increase DB size instead of decrease)
	 */
	function viewJSSummarizePage()
	{
		$JSNowTimeStamp = js_getJSNowTimeStamp();
		$last_month_ts = mktime(0, 0, 0, js_gmdate("m", $JSNowTimeStamp)-1, js_gmdate("d", $JSNowTimeStamp), js_gmdate("Y", $JSNowTimeStamp));

		$last_month_str = js_gmdate( 'Y-m-d', $last_month_ts );

		$FilterDate = new js_JSFilterDate();
		$FilterDate->year_min = 2003;
		$FilterDate->SetYMD( $last_month_str );


		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$query = 'SELECT count(*)'
		. ' FROM #__jstats_page_request'
		. ' WHERE CAST(CONCAT(year, \'-\', month, \'-\', day) AS date) <= \''.$last_month_str.'\''
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$entries_to_summarize = $JSDatabaseAccess->db->loadResult();

		$query = 'SELECT MIN(CAST(CONCAT(year, \'-\', month, \'-\', day) AS date))'
		. ' FROM #__jstats_page_request'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$oldest_entry_date = $JSDatabaseAccess->db->loadResult();

		if (strlen($oldest_entry_date) == 0) //no entries in __jstats_page_request - nothing to summarize
			$oldest_entry_date = $last_month_str; //setting the same date because I have no better idea


		$JSDbSOV = new js_JSDbSOV();
		$LastSummarizationDate = false;
		$JSDbSOV->getJSLastSummarizationDate($LastSummarizationDate);

		if (!$LastSummarizationDate)
			$LastSummarizationDate = JTEXT::_('Summarization never done');


		$JSToolsTpl = new js_JSToolsTpl();
		echo $JSToolsTpl->getJSSummarizePageTpl( $FilterDate, $oldest_entry_date, $entries_to_summarize, $LastSummarizationDate );

		return true;
	}


	/** Previusly this code update row in #__jstats_impressions_sums if such row exist - now it not could be done in this way because we removed hour - we summaraze for whole day)
	 *  reutrn: return false only when we loose conection with database, otherwise return true (regardles of input data are correct or not)
	 *
	 *  summarization disabled since v2.5.0.313 (It increase DB size instead of decrease)
	 */
	function summarizeDbForParticularPageAndDay($page_id, $year, $month, $day, $sum)
	{
		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		//check if summarised row exists and take current value
		$query = 'SELECT count(*)'
		. ' FROM #__jstats_page_request_c'
		. ' WHERE page_id = ' . $page_id
		. ' AND year = ' . $year
		. ' AND month = ' . $month
		. ' AND day = ' . $day
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$current_sum = $JSDatabaseAccess->db->loadResult();
		if ($JSDatabaseAccess->db->getErrorNum() > 0)
			return false;

		if( $current_sum ){
			//summarised row exists, so delete it (see comment above function header)

			$query = 'DELETE FROM #__jstats_page_request_c'
			. ' WHERE page_id = ' .	$page_id
			. ' AND year = ' . $year
			. ' AND month = ' .	$month
			. ' AND day = ' . $day
			;
			$JSDatabaseAccess->db->setQuery( $query );
			$JSDatabaseAccess->db->query();
			if ($JSDatabaseAccess->db->getErrorNum() > 0)
				return false;
		}

		// insert new value
		$query = 'INSERT INTO #__jstats_page_request_c (page_id, day, month, year, count)'
		. 'VALUES ('
			. $page_id .','. $day .','
			. $month   .','. $year .','. ($current_sum+$sum)
		. ')'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$JSDatabaseAccess->db->query();
		if ($JSDatabaseAccess->db->getErrorNum() > 0)
			return false;


		// delete old entries; If something would have went wrong,
		// then we used exit to quit the function and we won't delete the records
		$query = 'DELETE FROM #__jstats_page_request'
		. ' WHERE page_id = ' .	$page_id
		. ' AND year = ' . $year
		. ' AND month = ' .	$month
		. ' AND day = ' . $day
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$thJSDatabaseAccessis->db->query();
		if ($JSDatabaseAccess->db->getErrorNum() > 0)
			return false;

		return true;
	}


	/**
	 * - summarizes #__jstats_page_request
	 * - copies consolidaded data in #__jstats_impressions_sums
	 * - afterwards empties summarised records from #__jstats_page_request
	 *
	 * Note:
	 *    @AT: Summarization process is done through grouped records, but results
	 *    shown to users are in numbers berore summarization. This is correct.
	 *
	 * Note:
	 *    @AT: In the future We will be able to use 1 select (SELECT INTO ... FROM SELECT...)
	 *    instead of whole function, but NOW WE CAN NOT DO THIS!!!
	 *    One select also solve problem with memory and time limit.
	 *
	 * done 2009-01-20: Function is tested. It summarize correctly!!! :) Summarized data are correct!!!
	 * @todo: probably it is posibility to write that function in way to avoid memory limit
	 *
	 *
	 *  summarization disabled since v2.5.0.313 (It increase DB size instead of decrease)
	 */
	function doJSSummarize() {
		global $mainframe;

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		$FilterDate = new js_JSFilterDate();
		$FilterDate->readDateFromRequest();
		$date_str = $FilterDate->getDateStr();

		$JSConf = new js_JSConf();

		$nbr_records_successfuly_transfered = 0; //number before summarization
		$nbr_records_successfuly_transfered_s = 0; //number after summarization

		$total_entries_to_summarize = 0; //number before summarization
		$total_s = 0; //number after summarization


		$query = 'SELECT count(*)'
		. ' FROM #__jstats_page_request'
		. ' WHERE CAST(CONCAT(year, \'-\', month, \'-\', day) AS date) <= \''.$date_str.'\''
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$total_entries_to_summarize = $JSDatabaseAccess->db->loadResult();


		$memoryUsed			= ( function_exists( 'memory_get_usage' ) ) ? memory_get_usage() : 0;
		$memoryTotal		= ( 0 < ini_get( 'memory_limit' ) ) ? ini_get('memory_limit') : 0;
		$memoryFree			= $this->_returnBytes( $memoryTotal ) - $memoryUsed;
		// RB: 10000 works; should be possible to use 100000 | about 9,1 MB/10.000 records->
		// on forum someone had to decrease to 5000
		$NrOfRecordsPossible = ( $memoryFree == 0 ) ? 5000 : intval( ( $memoryFree / ( 6*1024*1024/5000 ) ) );


		$dbgMsg = ''
			. '<ul><li>start...</li>'
			. '<li>MemUsed: ' . $memoryUsed . '</li>'
			. '<li>MemTot: ' . $memoryTotal . '</li>'
			. '<li>MemFree: ' . $memoryFree . '</li>'
			. '<li>NrOfRecordsPossibleToProcess: ' . $NrOfRecordsPossible . '</li></ul>';
		js_echoJSDebugInfo( $dbgMsg, '');

		$query	= 'SELECT page_id, day, month, year, count(*) AS sum'
		. ' FROM #__jstats_page_request'
		. ' WHERE CAST(CONCAT(year, \'-\', month, \'-\', day) AS date) <= \''.$date_str.'\''
		. ' GROUP BY page_id, day, month, year'
		. ' ORDER BY year, month, day' //first the oldest
		;
		$JSDatabaseAccess->db->setQuery( $query, 0, $NrOfRecordsPossible );
		$rows = $JSDatabaseAccess->db->loadObjectList();
		if (!is_array($rows))
			$rows = array();

		$total_s = count( $rows );

		//set_time_limit(0);//set unlimited 'time limit'  //set_time_limit not working in safe mode (ini_get('safe_mode'))
		$max_execution_time = (int)ini_get('max_execution_time');
		set_time_limit($max_execution_time); //this is method to prolong script exectution time //it is working only in safe_mode off //if safe_mode is on, this method not works nor generate any warnings etc. (it is simply ignored)

		if( $total_s > 0 ) {
			// we have something to do
			require_once( JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_joomlastats' .DS. 'res.jlprogressbar.php' );

			//JL
			//JLCoreApi::import('JLProgressbar', 2,0);
			$title		= JTEXT::_( 'Summarizing' );
			$succesUrl	= 'index.php?option=com_joomlastats&task=js_view_summarize'; //is this url ever used? If Yes, when it is used?
			$errorUrl 	= 'index.php?option=com_joomlastats&task=js_view_summarize'; //is this url ever used? If Yes, when it is used?

			$pb = new JLProgressbarDual( $title, $succesUrl, $errorUrl );
			$pb->startStep( JTEXT::_( 'Summarizing' ), 0 );

			$DisplayProcesBarPer	= $total_s / 20; 	// RB: display per 5% increase

			if( $DisplayProcesBarPer < 1 ) {
				$DisplayProcesBarPer = 1;
			}
			if( $DisplayProcesBarPer > 10000 ) {
				$DisplayProcesBarPer=$DisplayProcesBarPer/2;
			}

			$pb->startProgress( sprintf( JTEXT::_( 'Summarizing %s of %s' ), $nbr_records_successfuly_transfered, $total_entries_to_summarize ) );

			$dbgMsg = ''
				. '<ul>'
				. '  <li>total_s: ' . $total_s . '</li>'
				. '  <li>total_entries_to_summarize: ' . $total_entries_to_summarize . '</li>'
				. '  <li>DisplayProcesBarPer: ' . $DisplayProcesBarPer . '</li>'
				. '</ul>';
			js_echoJSDebugInfo( $dbgMsg, '');

			$isSummarizationSuccesful = true;
			foreach( $rows as $row ) {
				$isSummarizationSuccesful &= $this->summarizeDbForParticularPageAndDay($row->page_id, $row->year, $row->month, $row->day, $row->sum);

				if( $isSummarizationSuccesful == false ) {
					$msg = JTEXT::_( 'Do not continue summarization process!' );
					$msg .= '&nbsp;&nbsp;&nbsp;';
					$msg .= JTEXT::_( 'There is problem with access to database!' );
					$msg_type = 'error'; //'message', 'notice', 'error' //do not remove this line ->redirect

					$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_summarize', $msg, $msg_type );//third argument: 'message', 'notice', 'error'
				}

				set_time_limit($max_execution_time);

				$nbr_records_successfuly_transfered_s++;	// increase record counter
				$nbr_records_successfuly_transfered += $row->sum; //in this case sum is also number of entries in #__jstats_page_request table (for that day)

				// reduce html updates
				if( $nbr_records_successfuly_transfered_s % $DisplayProcesBarPer == 0 ) {
					$complete = $nbr_records_successfuly_transfered_s / $total_s;

					$dbgMsg = ''
						.'<b>Complete: ' . ((int)($complete*100)) . '%</b>'
						.'&nbsp;&nbsp;&nbsp;&nbsp;'
						.'nbr_records_successfuly_transfered_s: ' . $nbr_records_successfuly_transfered_s . '&nbsp;&nbsp;&nbsp;'
						.'nbr_records_successfuly_transfered: ' . $nbr_records_successfuly_transfered . '&nbsp;&nbsp;&nbsp;'
						.'<br/>';
					js_echoJSDebugInfo( $dbgMsg, '');

					//JL
					$pb->updateProgress( $complete, sprintf( JTEXT::_( 'Summarizing %s of %s' ), $nbr_records_successfuly_transfered, $total_entries_to_summarize ) );
					$pb->updateStep( $complete, JTEXT::_( 'Summarizing Hit Counts' ) );
				}
			}

			//JL
			$pb->doneStep();
			$pb->doneProgress( JTEXT::sprintf( 'Affected Rows %s', $nbr_records_successfuly_transfered ) );

		}

		set_time_limit($max_execution_time);

		$err_num = 0;
		$query = 'OPTIMIZE TABLE `#__jstats_page_request`';
		$JSDatabaseAccess->db->setQuery( $query );
		$JSDatabaseAccess->db->query();
		$err_num += $JSDatabaseAccess->db->getErrorNum();

		$query = 'OPTIMIZE TABLE `#__jstats_impressions_sums`';
		$JSDatabaseAccess->db->setQuery( $query );
		$JSDatabaseAccess->db->query();
		$err_num += $JSDatabaseAccess->db->getErrorNum();

		if ($err_num > 0) {
			$not_used_here = JTEXT::_( 'Database optimization failed' );
		} else {
			$not_used_here = JTEXT::_( 'Database successfully optimized' );
		}


		$msg = JTEXT::_( 'Summarize successful' );
		$msg_type = 'message'; //'message', 'notice', 'error' //do not remove this line ->redirect
		if ($total_s == 0) {
			$msg = JTEXT::_( 'No data' );
			$msg_type = 'message';
		}
		if (($total_s > 0) && ($total_s >= $NrOfRecordsPossible)) {
			$msg = JTEXT::sprintf( '[%s] records successfully transfered. Please continue summarization process!', $nbr_records_successfuly_transfered);
			$msg_type = 'notice';
		}

		$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_summarize', $msg, $msg_type );
	}



	/**
	 * Shows an animated flash chart (top 10 links)
	 *
	 * actually there are 3 types of charts, only ampie1 is used
	 * to redefine the texts inside the chart, edit the file: ampie_settings.xml
	 * or add value (see ampie_settings.xml) to so.addVariable( 'additional_chart_settings'
	 * >> the flash itself builds the overall sum and calculates each percentage!
	 *
	 * as sample here the top 10 referrer
	 *
	 * @since 2.3.0.x
	 * @author mic
	 */
	function doGraphic() {
		global $option;

		require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'access.php' );
		$JSDatabaseAccess = new js_JSDatabaseAccess();

		JToolBarHelper::title( 'JoomlaStats'.': <small><small>[ ' . JTEXT::_( 'Charts' ) . ' ]</small></small>', 'js_js-logo.png' );

		$width		= 440;
		$height		= 300;
		$bgcolor	= '#EFEFEF'; // EFF9FF';
		$chartType	= 'ampie1'; // ampie1 - chart2 - default
		$results	= '';
		$flashPath	= JURI::base() . 'components/' . $option . '/tools/flashchart/'; ?>

		<script type="text/javascript" src="<?php echo $flashPath; ?>ampie/swfobject.js"></script>

		<?php
		$query = 'SELECT COUNT(referrer) AS referrer, MAX( domain) AS domain'
		. ' FROM #__jstats_referrer'
		. ' GROUP BY referrer'
		. ' ORDER BY referrer DESC'
		. ' LIMIT 10'
		;
		$JSDatabaseAccess->db->setQuery( $query );
		$rows = $JSDatabaseAccess->db->loadRowList(); ?>

		<div style="text-align:center; margin:2px auto; width:99%;">
			<?php
			if( $rows ) {
				// now build the data for the chart
				$chartData	= array();
				$results	= '';
				// check amount of rows - now we limit them to 10, but can be done otherwise (see ampie_settings.xml)
				$rowSum = count( $rows );
				if( $rowSum < 10 ) {
					$amount = $rowSum;
				}else{
					$amount = 10;
				}

				for( $i = 0; $i < $amount; ++$i ) {
					$chartData[$i][0] = $rows[$i][1]; // [text]		-> title
					$chartData[$i][1] = $rows[$i][0]; // [number]	-> hits
				}

				// assign values to chart data field
				foreach( $chartData as $cd ) {
					if( !empty( $cd[1] ) ) {
						$results .= $cd[0]  . ';' . $cd[1] . ';' .'\n'; // _smartSubstr
					}
				} ?>

				<div style="width:<?php echo $width; ?>; margin:2px auto; padding:5px; float:left;">
					<div style="font-weight:bold"><?php echo JTEXT::_( 'Top 10' ) . ' ' . JTEXT::_( 'Referrers' ); ?></div>

					<div id="flashcontent" style="font-weight:bold;">
						<?php echo JTEXT::_( 'You must update the Flashplayer' ); ?>
					</div>
					<script type="text/javascript">
						/* <![CDATA[ */
						var swf		= '<?php echo $flashPath; ?>ampie/ampie.swf';
						var id		= 'ampie';
						var width	= '<?php echo $width; ?>';
						var height	= '<?php echo $height; ?>';
						var version	= '8';
						var bgcolor = '<?php echo $bgcolor; ?>';
						var so = new SWFObject( swf, id, width, height, version, bgcolor );
						so.addVariable( 'path', '<?php echo $flashPath; ?>ampie/' );
						so.addVariable( 'settings_file', '<?php echo $flashPath . 'charts/' . $chartType . '/ampie_settings.xml'; ?>' );
						so.addVariable( 'chart_data', escape( '<?php echo $results; ?>' ) );
						so.addVariable( 'additional_chart_settings', '<settings><base_color>#08858F<\/base_color><\/settings>' );
						so.write( 'flashcontent' );
						/* ]]> */
					</script>
				</div>
				<?php
			}else{ ?>
				<div><?php echo JTEXT::_( 'No data' ); ?></div>
				<?php
			}

			// show 10 top ip.addressess
			$rows = null;
			$query = 'SELECT COUNT(ip) AS ipvisits, MAX( tld) AS tld, ip'
			. ' FROM #__jstats_ipaddresses'
			. ' WHERE tld != \'\''
			. ' GROUP BY ip'
			. ' ORDER BY ipvisits DESC'
			. ' LIMIT 10'
			;
			$JSDatabaseAccess->db->setQuery( $query );
			$rows = $JSDatabaseAccess->db->loadRowList();

			if( $rows ) {
				// now build the data for the chart
				$chartData	= array();
				$results	= '';
				// check amount of rows - now we limit them to 10, but can be done otherwise (see ampie_settings.xml)
				$rowSum = count( $rows );
				if( $rowSum < 10 ) {
					$amount = $rowSum;
				}else{
					$amount = 10;
				}

				for( $i = 0; $i < $amount; ++$i ) {
					$chartData[$i][0] = $rows[$i][2] . '<br />( ' . $rows[$i][1] . ' )'; // [text]		-> title
					$chartData[$i][1] = $rows[$i][0]; // [number]	-> visits
				}

				foreach( $chartData as $cd ) {
					if( !empty( $cd[1] ) ) {
						$results .= $cd[0]  . ';' . $cd[1] . ';' .'\n'; // _smartSubstr
					}
				} ?>

				<div style="width:<?php echo $width; ?>; margin:2px auto; padding:5px;">
					<div style="font-weight:bold"><?php echo JTEXT::_( 'Top 10' ) . ' ' . JTEXT::_( 'Visitor' ); ?></div>

					<div id="flashcontent1" style="font-weight:bold">
						<?php echo JTEXT::_( 'You must update the Flashplayer' ); ?>
					</div>
					<script type="text/javascript">
						/* <![CDATA[ */
						var swf		= '<?php echo $flashPath; ?>ampie/ampie.swf';
						var id		= 'ampie';
						var width	= '<?php echo $width; ?>';
						var height	= '<?php echo $height; ?>';
						var version	= '8';
						var bgcolor = '<?php echo $bgcolor; ?>';
						var so = new SWFObject( swf, id, width, height, version, bgcolor );
						so.addVariable( 'path', '<?php echo $flashPath; ?>ampie/' );
						so.addVariable( 'settings_file', '<?php echo $flashPath . 'charts/' . $chartType . '/ampie_settings.xml'; ?>' );
						so.addVariable( 'chart_data', escape( '<?php echo $results; ?>' ) );
						so.addVariable( 'additional_chart_settings', '<settings><base_color>#8F1108<\/base_color><\/settings>' );
						so.write( 'flashcontent1' );
						/* ]]> */
					</script>
				</div>
				<?php
			}else{ ?>
				<div><?php echo JTEXT::_( 'No data' ); ?></div>
				<?php
			}

			// keywords
			$rows = null;
			$query = 'SELECT keywords, COUNT(keywords) AS count_kw'
			. ' FROM #__jstats_keywords'
			. ' GROUP BY keywords'
			. ' ORDER BY count_kw DESC'
			//. ' LIMIT 10'
			;
			$JSDatabaseAccess->db->setQuery( $query );
			$rows = $JSDatabaseAccess->db->loadRowList();

			if( $rows ) {
				// now build the data for the chart
				$chartData	= array();
				$results	= '';
				// check amount of rows - now we limit them to 10, but can be done otherwise (see ampie_settings.xml)
				$rowSum = count( $rows );
				if( $rowSum < 10 ) {
					$amount = $rowSum;
				}else{
					$amount = 10;
				}

				for( $i = 0; $i < $amount; ++$i ) {
					$chartData[$i][0] = str_replace( '\'', '', $rows[$i][0] ); // [text]		-> title
					$chartData[$i][1] = $rows[$i][1]; // [number]	-> visits
				}

				foreach( $chartData as $cd ) {
					if( !empty( $cd[1] ) ) {
						$results .= $cd[0]  . ';' . $cd[1] . ';' .'\n'; // _smartSubstr
					}
				} ?>

				<div style="width:<?php echo $width; ?>; margin:2px auto; padding:5px;">
					<div style="font-weight:bold"><?php echo JTEXT::_( 'Top 10' ) . ' ' . JTEXT::_( 'Keywords' ); ?></div>

					<div id="flashcontent2" style="font-weight:bold">
						<?php echo JTEXT::_( 'You must update the Flashplayer' ); ?>
					</div>
					<script type="text/javascript">
						/* <![CDATA[ */
						var swf		= '<?php echo $flashPath; ?>ampie/ampie.swf';
						var id		= 'ampie';
						var width	= '<?php echo $width; ?>';
						var height	= '<?php echo $height; ?>';
						var version	= '8';
						var bgcolor = '<?php echo $bgcolor; ?>';
						var so = new SWFObject( swf, id, width, height, version, bgcolor );
						so.addVariable( 'path', '<?php echo $flashPath; ?>ampie/' );
						so.addVariable( 'settings_file', '<?php echo $flashPath . 'charts/' . $chartType . '/ampie_settings.xml'; ?>' );
						so.addVariable( 'chart_data', escape( '<?php echo $results; ?>' ) );
						so.addVariable( 'additional_chart_settings', '<settings><\/settings>' );
						so.write( 'flashcontent2' );
						/* ]]> */
					</script>
				</div>
				<?php
			}else{ ?>
				<div><?php echo JTEXT::_( 'No data' ); ?></div>
				<?php
			} ?>
		</div>
		<?php
		$JSTemplate = new js_JSTemplate();
		echo $JSTemplate->generateBeginingOfAdminForm();
		echo $JSTemplate->generateEndOfAdminForm();
	}
}