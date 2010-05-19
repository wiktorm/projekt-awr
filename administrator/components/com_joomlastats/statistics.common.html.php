<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DS. 'template.html.php' );

/**
 *  This file contain HTML templates that are common for statistics pages
 */

/**
 *  This class hold HTML templates that are used by statistics pages
 *
 *  NOTICE: methods from class JoomlaStats_Engine will be moved here
 */
class js_JSStatisticsCommonTpl
{
	var $task; //@todo remove this member!!!
	//var _JSAdminImagePath - use getUrlPathToJSAdminImages() function to get path to admin images



	/** constructor */
	function __construct() {
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
	function js_JSStatisticsCommonTpl()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}	
	
	
	/**
	 * Retrurn url that to directory with JS admin images
	 *
	 * @return string		eg.: "http://127.0.0.1/joomla/administrator/components/com_joomlastats/images/"
	 */
	function getUrlPathToJSAdminImages() {
		return JURI::base(true) . '/components/com_joomlastats/images/';
	}

	// style 4 detail view
	function getStyleForDetailView( $aaaa ) {
		return '<span style="font-weight:normal; font-style:italic; color:#007BBD">'.$aaaa.'</span>';
	}
	
	//@depracated - use addSummStyleLine
	function getStyleForSummarizedNumber( $SummarizedNumber ) {
		return '&nbsp;<span style="font-weight:normal; font-style:italic;">[ '.$SummarizedNumber.' ]</span>';
	}

	/**
	 *  Add style to show summarized data 
	 *
	 *  @param bool $show_summarized       - true or false; Could be get from JSConf->show_summarized
	 *  @param any  $data                  - if $show_summarized == true; $data should contain number including summarized data;
	 *  @param any  $data_only_summarized  - if $show_summarized == false; this parameter is not considered; if $show_summarized == true; $data should contain only number of summarized data
	 *
	 *  eg.:
	 *     total 83, summarized 21, current 62
	 *        addSummStyleLine(true, 83, 21)  ->  "83 [21]"
	 *        addSummStyleLine(false, 83, 21)  ->  "83"
	 */
	function addSummStyleLine( $show_summarized, $data, $data_only_summarized ) {
		return $data . ( ($show_summarized==true) ? ('&nbsp;['.$data_only_summarized.']') : '' );
	}
		
	/**
	 *  Add style to show summarized data 
	 *
	 *  @param bool $show_summarized       - true or false; Could be get from JSConf->show_summarized
	 *  @param any  $data                  - if $show_summarized == true; $data should contain number including summarized data;
	 *  @param any  $data_only_summarized  - if $show_summarized == false; this parameter is not considered; if $show_summarized == true; $data should contain only number of summarized data
	 *
	 *  eg.:
	 *     total 83, summarized 21, current 62
	 *        addSummStyleLine(true, 83, 21)  ->  "83 [21]"
	 *        addSummStyleLine(false, 83, 21)  ->  "83"
	 */
	function addSummStyleTable( $show_summarized, $data, $data_only_summarized ) {
		if ( $show_summarized == false )
			return $data;
		
		$html = ''
		. '<table class="adminlist" cellspacing="0" width="100%"><tr>'
		. '<td style="width: 50%; text-align: right;">'.$data.'</td>'
		. '<td style="width: 50%; text-align: left;">['.$data_only_summarized.']</td>'
		. '</tr></table>'
		;
		return $html;
	}
	
	/**
	 * Displays a percentage bar
	 *
	 * @param integer $percent
	 * @param integer $maxpercent
	 * @return string
	 */
	function PercentBar( $percent, $maxpercent ) {
		$barmaxlength	= 180;
		$barlength		= (int) ( $percent / $maxpercent * $barmaxlength );
		if ($barlength == 0)
			$barlength = 1;//draw at least 1px bar-on
		$barrest		= ( $barmaxlength - $barlength );

		// draw the filled bar
		$retvar = '<img border="0" src="' . $this->getUrlPathToJSAdminImages() . 'bar-on.gif' . '" width="' . $barlength . '" height="7" alt="" />';
		
		// if there is non-filled bar to draw do so...
		if( $barrest > 0 ) {
			$retvar .= '<img border="0" src="' . $this->getUrlPathToJSAdminImages() . 'bar-off.gif' . '" width="' . $barrest . '" height="7" alt="" />';
		}

		return $retvar;
	}


	/** this function format percentages from double to string
	 *
	 *  Examples:
	 *     getFormatedPercentages(0.543054) -> '54.30' (not '54.3')
	 *
	 *  @param $percent like 0.4350363
	 *  return formated string '43.50' (not '43.5')
	 */
	function getFormatedPercentages($percent) {
		//$per_cent_format_token = '%01.0f';
		$per_cent_format_token = '%01.1f';
		//$per_cent_format_token = '%01.2f';
												
		return sprintf($per_cent_format_token, $percent * 100);
	}
		
	/**
	 * Displays a percentage bar
	 *
	 * @param integer $percent
	 * @param integer $maxpercent
	 * @return string
	 */
	function getPercentBarWithPercentNbr( $value, $max_value, $sum_all_values ) {
		
		//$percent = round( ( ( $row->os_visits / $sum_all_system_visits ) *100 ), 2 );
		//$totalmaxpercent	= round( ( ( $max_system_visits / $sum_all_system_visits ) *100 ), 2 );
		
		$PercentBar = $this->PercentBar( $value, $max_value );
		$PercentNbr = $this->getFormatedPercentages( $value / $sum_all_values );
		
		/** in IE 6.0, 7.0 style not working so use html tag cellspacing="0" */
		$retvar = '
			<table style="width: 100%; border-width: 0px; border-collapse: collapse; border-spacing: 0px;" cellspacing="0">
			<tr>
				<td style="height: auto; padding: 0px; border-width: 0px; text-align: left; white-space: nowrap;">'.$PercentBar.'</td>
				<td style="height: auto; padding: 0px; padding-left: 7px; border-width: 0px; text-align: right;">'.$PercentNbr.'%</td>
			</tr>
			</table>
		';
		
		return $retvar;
	}		
	
	/**
	 * writes the header of JoomlaStats with all actions as links
	 *
	 * @param array $MenuArrIdAndText
	 * @return string
	 *
	 * @todo mic: has to be reworked, because of the changes with translations to JTEXT
	 */
	function getJSStatisticsMenuTpl( $MenuArrIdAndText ) {
		$n = 0;

		$html =
		'<table width="100%" border="0" cellpadding="2" cellspacing="0">' . "\n"
		. '<tr><td width="10">&nbsp;</td>'; //empty column - leave a little whitespace on the left

		foreach( $MenuArrIdAndText as $id => $description ) {

			if ($id == 'r18')
				continue; //easy hack, should be removed

			$n++;
			if( strlen( $id ) == 3 ) {
				// we hit a menu item (not an empty line for example)
				if( ( $n != 1 ) && ( ( $n - 1 ) % 6 == 0 ) ) {
					// We just started a new line and we have some items left, so start a new line
					$html .= '<tr><td width="10">&nbsp;</td>';	// start with same whitespace on the left
				}

				// $html .= "<a href=\"index2.php?option=com_joomlastats&task=$id&d=".$this->d."&m=".$this->m."&y=".$this->y."\">$description</a>";
				$html .= '<td style="text-align:left">'
				. '<a href="javascript: if(document.adminForm.limitstart) document.adminForm.limitstart.value=0; submitbutton(\'' .$id. '\')" title="' . $description . '">' . $description . '</a>'
				. '</td>';

				if( $n % 6 == 0 ) {
					$html .= '<td>&nbsp;</td></tr>' . "\n";
				}
			}
		}

		if( $n % 6 != 0 ) {
			// if we didn't just finish the row than do it now.
			// mic: leaving that here results in XHTML.error because 1 tr is too much
			//$html .= '</tr>' . "\n";
		}

		$html .= '</table>' . "\n";

		return $html;
	}

	/**
	 * This function return Statistics Page Header HTML
	 *
	 * @param unknown_type $FilterSearch
	 * @param unknown_type $FilterDate
	 * @param unknown_type $vid
	 * @param unknown_type $moreinfo
	 * @param unknown_type $FilterDomain
	 * @param unknown_type $task				mic: removed: switched to this->task
	 * @param unknown_type $ReportTitle
	 * @param unknown_type $StatisticsMenu
	 * @param iso date $LastSummarizationDate
	 * @return string
	 *
	 */
	function getJSStatisticsHeaderHtmlCodeTpl( $FilterSearch, $FilterDate, $vid, $moreinfo, $FilterDomain, $ReportTitle, $StatisticsMenu, $LastSummarizationDate, $include_summarized ) {
		global $mainframe;

		$JSTemplate = new js_JSTemplate();

		$HtmlStatisticsMenu = $this->getJSStatisticsMenuTpl( $StatisticsMenu );
		$html				= '';

		$html .= $JSTemplate->generateBeginingOfAdminForm( $this->task )
		. '<!-- hidden value for display stats -->' . "\n"
		. '<input type="hidden" name="vid" value="' . $vid . '" />' . "\n"
		. '<input type="hidden" name="moreinfo" value="' . $moreinfo . '" />' . "\n"
		. $FilterSearch->getHtmlSearchFilterHiddenCode()
		. $FilterDomain->getHtmlDomainFilterHiddenCode()
		;

		$filters_html = $FilterDate->getHtmlDateFilterCode();
		if ($FilterSearch->show_search_filter == true)
			$filters_html .= '<br /><br />' . $FilterSearch->getHtmlSearchFilterVisibleCode();
		if ($FilterDomain->show_domain_filter == true)
			$filters_html .= '<br /><br />' . $FilterDomain->getHtmlDomainFilterVisibleCode();

		
		$html .= '<table border="0" align="center" cellspacing="0" width="100%">' . "\n"
	  	. '<tr><td>
			<table class="adminlist" border="0" cellspacing="0" width="100%">' . "\n"
			. '<!-- 1st row: Logo + date selection -->
			<tr>' . "\n"
				. '<td width="100%" class="sectionname" style="vertical-align: bottom;">'
				. JTEXT::_( 'Translation by Author' )
				. '</td>
				<td nowrap="nowrap">
					' . JTEXT::_( 'Filter' ) . '<br/>
					' . $filters_html . '
				</td>
			</tr>' . "\n"
			. '<!-- 3rd row: menu -->' . "\n"
			. '<tr>' . "\n"
				. '<td colspan="2">' . $HtmlStatisticsMenu . '</td>
			</tr>' . "\n"
			. '<!-- 3rd row end -->' . "\n"
		. '</table>' . "\n"
	  	. '</td></tr>'
	  	. '<tr><td>'
		;

		$LastSummarization_html = '';

		if( $include_summarized ) {
			$LastSummarization_html = JTEXT::_( 'Including summarized data until' ) . ' ' . $LastSummarizationDate;
		}else{
			$LastSummarization_html = ( $LastSummarizationDate ? JTEXT::_( 'Last summarize' )
			. '&nbsp;(<span style="color:red">' . JTEXT::_( 'Data not visible' ) . '</span>)&nbsp;'
			. ' [ '. $LastSummarizationDate . ' ] ' : '' );
		}

		$LastSummarization_html .= ( $LastSummarization_html ? ' ' : '' );

		// new mic: output as div
		$html .= '<div id="infoLine" class="infoLine" style="float:left; width:100%; margin:2px auto 2px auto; padding 2px; border-top: 2px solid #9BCFA7; border-bottom: 2px solid #9BCFA7; background-color: #EFFFF9; text-align:center;">'
		. '<div style="width:50%; float:left; text-align:right">'.$ReportTitle.'</div>'
		. '<div style="width:50%; float:left; text-align:right; color:#007BBD">' . $LastSummarization_html . '</div>'
		. '</div>' . "\n"
		. '<div style="clear:both"></div>';

		return $html;
	}

	/**
	 * This function return Statistics Page Footer HTML code
	 *
	 * @return string
	 */
	function getJSStatisticsFooterHtmlCodeTpl() {
		$JSTemplate = new js_JSTemplate();

		$html = '
		</td>
		</tr>' . "\n"
		. '</table>' . "\n"
		;

		$html .= $JSTemplate->generateEndOfAdminForm();
		//$html .= '</div><!-- needed by j1.0.15 -->';//can not be used for lists! (in FF mosPageNav generate footer that is left justified instead of center)

		return $html;
	}
}
