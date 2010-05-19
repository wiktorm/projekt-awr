<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}


require_once( dirname( __FILE__ ) .DS. 'statistics.common.html.php' );
require_once( dirname( __FILE__ ) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );




/**
 *	This class generate statistics and show them in joomla back end (administrator panel)
 *
 *	NOTICE: methods from class JoomlaStats_Engine will be moved here
 *
 *	NOTICE: This class should contain only argument less functions that are called by task/action
 */
class js_JSStatisticsCommon
{
	/** hold JoomlaStats configuration */
	var $JSConf = null;


	var $MenuArrIdAndText = array();


	function __construct( $JSConf ) {

		$this->JSConf = $JSConf;


		//initialize itself
		$this->getJSStatisticsMenu();
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
	function js_JSStatisticsCommon()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	
	
	/**
	 * build the menu items
	 *
	 * @param array $MenuArrIdAndText
	 * @since 2.3.x (mic): building the text with JTEXT
	 */
	function getJSStatisticsMenu() {

		$this->MenuArrIdAndText['r01'] = JTEXT::_( 'Summary Year' );
		$this->MenuArrIdAndText['r02'] = JTEXT::_( 'Summary Month' );
		$this->MenuArrIdAndText['r03'] = JTEXT::_( 'Visits' );
		$this->MenuArrIdAndText['r05'] = JTEXT::_( 'Visitors by country' );
		$this->MenuArrIdAndText['r06'] = JTEXT::_( 'Page Hits' );
		$this->MenuArrIdAndText['r07'] = JTEXT::_( 'Systems' );
		$this->MenuArrIdAndText['r08'] = JTEXT::_( 'Browsers' );
		$this->MenuArrIdAndText['r09'] = JTEXT::_( 'Bots by domain' );
		$this->MenuArrIdAndText['r10'] = JTEXT::_( 'Bots' );
		$this->MenuArrIdAndText['r11'] = JTEXT::_( 'Not identified visitors' );
		$this->MenuArrIdAndText['r12'] = JTEXT::_( 'Unknown bots/spiders' );
		$this->MenuArrIdAndText['r14'] = JTEXT::_( 'Search Engines' );
		$this->MenuArrIdAndText['r15'] = JTEXT::_( 'Keywords' );
		$this->MenuArrIdAndText['r16'] = JTEXT::_( 'Referrers by domain' );
		$this->MenuArrIdAndText['r17'] = JTEXT::_( 'Referrers by page' );
		//$this->MenuArrIdAndText['rNotUsed'] = JTEXT::_( 'Resolutions' );
	}

	/**
	 * collecting and pass thru several datas for building the html.header (incl. <form> tag)
	 *
	 * @param string $FilterSearch
	 * @param string $FilterDate
	 * @param integer $vid
	 * @param string $moreinfo
	 * @param string $DatabaseSizeHtmlCode
	 * @param string $FilterDomain
	 * @return string
	 */
	function getJSStatisticsHeaderHtmlCode( $FilterSearch, $FilterDate, $vid, $moreinfo, $FilterDomain ) {

		$include_summarized = $this->JSConf->include_summarized;
		$JSDbSOV = new js_JSDbSOV();
		$LastSummarizationDate = false;
		$JSDbSOV->getJSLastSummarizationDate($LastSummarizationDate);
		
			
		$task = JRequest::getVar( 'task', 'js_view_statistics_default' ); // mic: changed to J.1.5-style

		//title to pages that are not in menu
		$this->MenuArrIdAndText['r18'] = JTEXT::_( 'Detail visit information' );


		$JSStatisticsCommonTpl = new js_JSStatisticsCommonTpl();
		$JSStatisticsCommonTpl->task = $task; // new mic

		$html = $JSStatisticsCommonTpl->getJSStatisticsHeaderHtmlCodeTpl($FilterSearch, $FilterDate, $vid, $moreinfo, $FilterDomain, $this->MenuArrIdAndText[$task], $this->MenuArrIdAndText, $LastSummarizationDate, $include_summarized);

		return $html;
	}

	/**
	 * builds the footer (also with the final </form> tag)
	 *
	 * @return string
	 */
	function getJSStatisticsFooterHtmlCode() {
		$JSStatisticsCommonTpl = new js_JSStatisticsCommonTpl();

		$html = $JSStatisticsCommonTpl->getJSStatisticsFooterHtmlCodeTpl();

		return $html;
	}
}
