<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'statistics.common.html.php' );


/**
 *  This class hold HTML templates that are used by statistics pages
 *
 *  NOTICE: methods from class JoomlaStats_Engine will be moved here
 */
class js_JSStatisticsTpl extends js_JSStatisticsCommonTpl
{
	/** constructor initialize base class */
	function __construct() {
		parent::__construct();
	}

	/**
	 * this function return HTML template to page 'Page Hits'
	 * (case r06)
	 *
	 * old function name 'getPageHits();'
	 *
	 * @return string - html code
	 */
	function viewPageHitsPageTpl( $nbr_visited_pages, $sum_all_pages_impressions, $max_page_impressions, $result_arr, $summarized_info, $pagination ) {
		
		if (strlen($summarized_info['pages']) > 0) //($summarized_info['pages'] != '') not working for int!!!
			$summarized_info['pages'] = '&nbsp;'.$this->getStyleForSummarizedNumber( $summarized_info['pages'] ).'&nbsp;'.'&nbsp;';

		if (strlen($summarized_info['count']) > 0)
			$summarized_info['count'] = '&nbsp;'.$this->getStyleForSummarizedNumber( $summarized_info['count'] );
		
			
		$retval = '<table class="adminlist">' . "\n"
		. '<thead>' . "\n"
		. '<tr>'
		. '<th style="width: 3%;">#</th>'												// Order Nr.
		. '<th style="width: 5%;">' . JTEXT::_( 'Count' ) . '</th>'						// Count
		. '<th style="width: 20%;">' . JTEXT::_( 'Percent' ) . '</th>'					// Percent	
		. '<th style="width: 72%; text-align: left;">' . JTEXT::_( 'Page' ) . '</th>'	// Page name with url
		. '</tr>' . "\n"
		. '</thead>' . "\n"
		;
		
		if ( count($result_arr) > 0 ) {
			$k		= 0;
			$order_nbr = $pagination->limitstart;

			foreach( $result_arr as $result_row ) {
                $order_nbr++;

			    $retval .= ''
				. '<tr class="row' . $k . '">'
				. '<td align="right"><em>' . $order_nbr . '.</em></td>'
                . '<td style="text-align: center;" nowrap="nowrap">' . $result_row->page_impressions . '</td>'
        		. '<td align="left">' . $this->getPercentBarWithPercentNbr( $result_row->page_impressions, $max_page_impressions, $sum_all_pages_impressions ) . '</td>'
				. '<td nowrap="nowrap">'
					. '<a href="' . htmlentities( $result_row->page_url ) . '" target="_blank" title="'
					. htmlentities( $result_row->page_url ) . '">' . ( ($result_row->page_title!='') ? $result_row->page_title : $result_row->page_url ) . '</a>'
				. '</td>'
				. '</tr>'
				;

				$k = 1 - $k;
				
			}
		}
		else
		{
        	$retval .= '<tr><td colspan="4" style="text-align:center">'	. JTEXT::_( 'No data' )	. '</td></tr>';
        }

        //last row of table contain total values
		$retval .= ''
		. '<thead>' . "\n"
		. '<tr>' . "\n"
		. '<th>&nbsp;</th>'
		//'<th>Total:</th>'//@todo move text to translation files and replace previous line by this
		. '<th nowrap="nowrap">'.$sum_all_pages_impressions.$summarized_info['count'].'</th>'
		. '<th>&nbsp;</th>'
		. '<th style="text-align: left;">'
			. $nbr_visited_pages . $summarized_info['pages'] . '&nbsp;'
			. ( $nbr_visited_pages == 1 ? JTEXT::_( 'Page' ) : JTEXT::_( 'Pages' ) )
		. '</th>'
		. '</tr>' . "\n"
		. '</thead>' . "\n"
		. '<tfoot><tr><td colspan="4">'.$pagination->getListFooter().'</td></tr></tfoot>'
		. '</table>'
		;
		
		return $retval;
	}

	
	/**
	 * this function return HTML template to page 'Operating Systems'
	 * (case r07)
	 *
	 * old function name 'getSystems();'
	 *
	 * @return string - html code
	 */
	function viewSystemsPageTpl( $sum_all_system_visits, $max_system_visits, $ostype_name_arr, $result_arr ) {
		
		$totalsystems = count($result_arr);
	
					
		$retval = '<table class="adminlist">' . "\n";
		
		{// Header
			$ostype_name_str = JTEXT::sprintf('JoomlaStats group OS into %s sets', count($ostype_name_arr)) .': '. implode('; ', $ostype_name_arr);
			$retval .= ''
			. '<thead>' . "\n"
			. '<tr>'
			. '<th style="width: 1%;">#</th>'
			. '<th style="width: 1px;">' . JTEXT::_( 'Count' ) . '</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Percent' ) . '</th>'
			. '<th style="width: 100%">' . JTEXT::_( 'Operating Systems' ) .' ('. JTEXT::_( 'OS' ) .')'. '</th>'
			. '<th style="width: 1px; text-align: center;" title="'.$ostype_name_str.'">' . JTEXT::_( 'OS Type' ) . '</th>'
			. '</tr>' . "\n"
			. '</thead>' . "\n"
			;
		}

		// Body
		if( $totalsystems > 0 ) {
			$k			= 0;
			$order_nbr	= 0;

			foreach( $result_arr as $row ) {
				$order_nbr++;
				
				$ostype_img_html = '<img src="'.$row->ostype_img_url.'" alt="'.$row->ostype_name.'" title="'.JTEXT::_( 'OS type' ).': '.$row->ostype_name.'" />';

				$retval .= '<tr class="row'.$k.'">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
			  	. '<td style="text-align: center;">' . $row->os_visits . '</td>'
			  	. '<td>' . $this->getPercentBarWithPercentNbr( $row->os_visits, $max_system_visits, $sum_all_system_visits ) . '</td>'
				. '<td nowrap="nowrap">'.$row->os_img_html.'&nbsp;&nbsp;'
				. ( $row->os_name ? $row->os_name : '<span style="color:#FF0000;">' . JTEXT::_( 'Unknown' ) . '</span>' )
				. '</td>'
				. '<td style="text-align: center;">'.$ostype_img_html.'</td>'
				. '</tr>' . "\n";

				$k = 1 - $k;
			}
		}
		

		{// TotalLine - Footer
			$retval .= ''
			. '<thead>'
			. '<tr>'
			. '<th>&nbsp;</th>'
			. '<th style="text-align: center;">' . $sum_all_system_visits . '</th>'
			. '<th>&nbsp;</th>'
			. '<th>'.$totalsystems.'&nbsp;'. ( ($totalsystems<=1) ? JTEXT::_( 'Operating System' ) : JTEXT::_( 'Operating Systems' ) ) . '</th>'
			. '<th>&nbsp;</th>'
			. '</tr>'
			. '</thead>'
			;
		}
		
		$retval .= '</table>' . "\n";

		return $retval;
	}

			
	/**
	 * This function return HTML code to page 'Not identified visitors' 
	 * (case r11)
	 *
	 * @param array $rows
	 * @param array $pagination
	 * @return string
	 */
	function viewNotIdentifiedVisitorsPageTpl( $rows, $pagination ) {

		$retval = ''
		. "\n"
		. '<table class="adminlist">'
		. '<thead>'
		. '<tr>'
		. '<th style="width: 1%;">#</th>'
		. '<th align="left" width="10%">' . JTEXT::_( 'Time' ) . '</th>'
		. '<th align="left" width="5%">' . JTEXT::_( 'Code' ) . '</th>'
		. '<th align="left" width="10%">' . JTEXT::_( 'Country/Domain' ) . '</th>'
		. '<th align="left" width="75%">' . JTEXT::_( 'UserAgent' ) . '</th>'
		. '</tr>'
		. '</thead>'
		;

		if ( $rows ) {
			$k = 0;
			$order_nbr	= 0;
		    foreach( $rows as $row ) {
				$order_nbr++;
				$time =& JFactory::getDate($row->visit_date.' '.$row->visit_time);
				//$time->setOffset($time_zone_offset);//no we are in local time!
				$time_str = $time->toFormat();

                $retval .= ''
				. '<tr class="row' . $k . '">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td nowrap="nowrap">' . $time_str. '</td>'
				. '<td nowrap="nowrap">' . $row->tld . '</td>'
				. '<td nowrap="nowrap">' . $row->fullname . '</td>'
				. '<td nowrap="nowrap">' . $row->useragent . '</td>'
				. '</tr>'
				;
                $k = 1 - $k;
            }
        } else {
        	$retval .= '<tr><td colspan="5" style="text-align:center">' . JTEXT::_( 'No data' ) . '</td></tr>';
    	}

		$retval .= ''
		. '<tfoot><tr><td colspan="5">'.$pagination->getListFooter().'</td></tr></tfoot>'
        . '</table>' . "\n";

		return $retval;
	}

	
	/**
	 *  this function return HTML table with 'Search Engines' and 'Keywords'
	 *  (case r14 and r15)
	 *
	 *  old function name 'getSearches();'
	 *
	 *  @return html page
	 */
	function viewSearchEnginesAndKeywordsTpl( $isKeywords, $rows, $pagination, $total, $max_value, $sum_all_values ) {
		
		$retval = ''
		. "\n"
		. '<table class="adminlist">'
		. '<thead>'
		. '<tr>'
			. '<th style="width: 1%;">#</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Count' ) . '</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Percent' ) . '</th>'
			. '<th style="width: 100%;">' . (($isKeywords) ? JTEXT::_( 'Search Keyphrases' ) : JTEXT::_( 'Search Engines' )) . '</th>'
		. '</tr>'
		. '</thead>'
		. "\n"
		;
		
		if ( count($rows) > 0 ) {
			$k = 0;
			$order_nbr = $pagination->limitstart;
			for ($i=$order_nbr; ($i<count($rows) && $i<($pagination->limitstart+$pagination->limit)); $i++) {
				$row = $rows[$i];
				$order_nbr++;
				
				$retval .= ''
				. '<tr class="row' . $k . '">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td style="text-align: center;">' . $row->count . '</td>'
				. '<td align="left">' . $this->getPercentBarWithPercentNbr( $row->count, $max_value, $sum_all_values ) . '</td>'
				. '<td nowrap="nowrap">'
				. ( ($isKeywords) ?
					wordwrap( $row->keywords, 100, '<br />' )
				:
					(
					'<a href="javascript:document.adminForm.dom.value=\''. $row->searcher_name. '\';'
						. 'document.adminForm.limitstart.value=0;'
						. 'submitbutton(\'r15\');"'
						. ' title="' . JTEXT::_( 'View search items' ) . '">'
							. $row->searcher_name
						. '</a>'
					)
				)
				. '</td>'
				. '</tr>'
				. "\n"
				;
				
				$k = 1 - $k;
			}
		} else {
			$retval .= '<tr><td colspan="4" style="text-align:center">'. JTEXT::_( 'No data' ) . '</td></tr>';
		}
		
		// TotalLine
		$retval .= ''
		. '<thead>'
		. '<tr>'
		. '<th>&nbsp;</th>'
		. '<th style="text-align: center;">' . $sum_all_values . '</th>'
        . '<th>&nbsp;</th>'
        . '<th nowrap="nowrap" style="text-align: left;">'
        	. $total . '&nbsp;'
        	. ( ($isKeywords) ?
        		( ($total) == 1 ? JTEXT::_( 'Keyword' ) : JTEXT::_( 'Keywords' ) )
        	:
        		( ($total) == 1 ? JTEXT::_( 'Search engine entry' ) : JTEXT::_( 'Different search engine entries' ) )
        	)
        . '</th>'
        . '</tr>'
		. '</thead>'
		. '<tfoot><tr><td colspan="4">'.$pagination->getListFooter().'</td></tr></tfoot>'
		. '</table>'
		. "\n"
		;
		
		return $retval;
	}

	
	/**
	 *  this function return HTML table with 'Search Engines' and 'Keywords'
	 *  (case r16 and r17)
	 *
	 *  old function name 'getSearches();'
	 *
	 *  @return html page
	 */
	function viewReferrersTpl( $byPage, $rows, $pagination, $total, $max_value, $sum_all_values ) {
		
		$retval = ''
		. "\n"
		. '<table class="adminlist">'
		. '<thead>'
		. '<tr>'
			. '<th style="width: 1%;">#</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Count' ) . '</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Percent' ) . '</th>'
			. '<th style="width: 100%;">' . (($byPage) ? JTEXT::_( 'Referrer page' ) : JTEXT::_( 'Referrer domain' )) . '</th>'
		. '</tr>'
		. '</thead>'
		. "\n"
		;
		
		if ( count($rows) > 0 ) {
			$k = 0;
			$order_nbr = $pagination->limitstart;
			for ($i=$order_nbr; ($i<count($rows) && $i<($pagination->limitstart+$pagination->limit)); $i++) {
				$row = $rows[$i];
				$order_nbr++;
				
				$retval .= ''
				. '<tr class="row' . $k . '">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td style="text-align: center;">' . $row->counter . '</td>'
				. '<td align="left">' . $this->getPercentBarWithPercentNbr( $row->counter, $max_value, $sum_all_values ) . '</td>'
				. '<td nowrap="nowrap">'
				. ( ($byPage) ?
					(
            			'<a href="'. $row->referrer .'" target="_blank" title="'
            			. JTEXT::_( 'Opens URL in new window' ) . '">' . $row->referrer . '</a>'
					)
				:
					(
                	'<a href="javascript:document.adminForm.dom.value=\''
            			. $row->domain . '\'; document.adminForm.limitstart.value=0; submitbutton(\'r17\');"'
            			. ' title="' . JTEXT::_( 'Click to view referring page' ) . '"'
            			. '>' . $row->domain . '</a>'
					)
				)
				. '</td>'
				. '</tr>'
				. "\n"
				;
				
				$k = 1 - $k;
			}
		} else {
			$retval .= '<tr><td colspan="4" style="text-align:center">'. JTEXT::_( 'No data' ) . '</td></tr>';
		}
		
		// TotalLine
		$retval .= ''
		. '<thead>'
		. '<tr>'
		. '<th>&nbsp;</th>'
		. '<th style="text-align: center;">' . $sum_all_values . '</th>'
        . '<th>&nbsp;</th>'
        . '<th nowrap="nowrap" style="text-align: left;">'
		. ( ( $total == 0) ? 
			( JTEXT::_('No referring domains') )
			: 
       		( $total . '&nbsp;' . (($total == 1) ? JTEXT::_( 'Referring domain' ) : JTEXT::_( 'Referring domains' )))
		  )
        . '</th>'
        . '</tr>'
		. '</thead>'
		. '<tfoot><tr><td colspan="4">'.$pagination->getListFooter().'</td></tr></tfoot>'
        . '</table>' . "\n"
		;
		
		return $retval;
	}

	/**
	 *  this function return HTML table with 'Bots by domain'
	 *  (case r09)
	 *
	 *  old function name 'getBots();'
	 *
	 *  @return html page
	 */
	function viewBotsByDomainTpl( $rows, $pagination, $total, $max_value, $sum_all_values ) {
		
		$retval = ''
		. "\n"
		. '<table class="adminlist">'
		. '<thead>'
		. '<tr>'
			. '<th style="width: 1%;">#</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Count' ) . '</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Percent' ) . '</th>'
			. '<th style="width: 100%;">' . JTEXT::_( 'Bot/Spider' ) . '</th>'
		. '</tr>'
		. '</thead>'
		. "\n"
		;
		
		if ( count($rows) > 0 ) {
			$k = 0;
			$order_nbr = $pagination->limitstart;
			for ($i=$order_nbr; ($i<count($rows) && $i<($pagination->limitstart+$pagination->limit)); $i++) {
				$row = $rows[$i];
				$order_nbr++;
				
				$retval .= ''
				. '<tr class="row' . $k . '">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td style="text-align: center;">' . $row->numbers . '</td>'
				. '<td align="left">' . $this->getPercentBarWithPercentNbr( $row->numbers, $max_value, $sum_all_values ) . '</td>'
				. '<td nowrap="nowrap">'
                	. '<a title="' . JTEXT::_( 'Details' )
                	. '" href="javascript:document.adminForm.dom.value=\''
                	. rawurlencode( $row->browser ) . '\';submitbutton(\'r10\');">'
                	. $row->browser
                	. '</a>'
				. '</td>'
				. '</tr>'
				. "\n"
				;
				
				$k = 1 - $k;
			}
		} else {
			$retval .= '<tr><td colspan="4" style="text-align:center">'. JTEXT::_( 'No data' ) . '</td></tr>';
		}
		
		// TotalLine
		$retval .= ''
		. '<thead>'
		. '<tr>'
		. '<th>&nbsp;</th>'
		. '<th style="text-align: center;">' . $sum_all_values . '</th>'
        . '<th>&nbsp;</th>'
        . '<th nowrap="nowrap">'
		. ( ( $total == 0) ? 
			( JTEXT::_('No Bots') )
			: 
       		( $total . '&nbsp;' . (($total == 1) ? JTEXT::_( 'Bot' ) : JTEXT::_( 'Different Bots' )))
		  )
        . '</th>'
        . '</tr>'
		. '</thead>'
		. '<tfoot><tr><td colspan="4">'.$pagination->getListFooter().'</td></tr></tfoot>'
		. '</table>'
		. "\n"
		;
		
		return $retval;
	}
	

	/**
	 *  this function return HTML table with 'Bots'
	 *  (case r10)
	 *
	 *  old function name 'getBots();'
	 *
	 *  @return html page
	 */
	function viewBotsTpl( $rows, $pagination, $total ) {
		
		$retval = ''
		. "\n"
		. '<table class="adminlist">'
		. '<thead>'
		. '<tr>'
			. '<th style="width: 1%;">#</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'TLD' ) . '</th>'
			. '<th style="width: 1px; text-align: center;">' . JTEXT::_( 'Country/Domain' ) . '</th>'
			. '<th style="width: 1px;">' . JTEXT::_( 'Pages' ) . '</th>'
			. '<th style="width: 1px;">' . JTEXT::_( 'Time' ) . '</th>'
			. '<th style="width: 100%;">' . JTEXT::_( 'Bot/Robot/Crawler/Spider name' ) . '</th>'
		. '</tr>'
		. '</thead>'
		. "\n"
		;
		
		if ( count($rows) > 0 ) {
			$k = 0;
			$order_nbr = $pagination->limitstart;
			for ($i=$order_nbr; ($i<count($rows) && $i<($pagination->limitstart+$pagination->limit)); $i++) {
				$row = $rows[$i];
				$order_nbr++;
				
				$time =& JFactory::getDate($row->visit_date.' '.$row->visit_time);
				//$time->setOffset($time_zone_offset);//no we are in local time zone
				$time_str = $time->toFormat();

				$retval .= ''
				. '<tr class="row' . $k . '"' . ( ($row->pages_nbr === null) ? (' style="color:#666666" title="' . JTEXT::_( 'Data already purged' ) . '"' ) : ('')) .'>'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td style="text-align: center;">' . $row->tld . '</td>'
				. '<td align="left">' . JTEXT::_( $row->tld ) . '</td>'
				;

				// PAGES column
				if ($row->pages_nbr <= 0) {
	                $retval .= '<td style="text-align: center;">***</td>'; //*** placeholder for archived/purged items
				} else {
	                $retval .= ''
					. '<td style="text-align: right;" nowrap="nowrap" title="' .JTEXT::_( 'Click for additional details' ). '">'
					. '<a href="javascript:document.adminForm.moreinfo.value=\'' . $row->visit_id . '\';submitbutton(\'r18\');">'
					. $row->pages_nbr
					. '&nbsp;&nbsp;'
			        . '<img src="'. $this->getUrlPathToJSAdminImages() .'pathinfo.png" border="0" alt="' . JTEXT::_( 'Path info' ) . '" />'
					. '</a>'
	                . '</td>'
					;
				}

				$retval .= ''
				. '<td nowrap="nowrap">' . $time_str . '</td>'
				. '<td nowrap="nowrap">'
					. $row->browser . ( $row->useragent ? ' (' . substr( $row->useragent, 0, 70 ) . ')' : '' )
				. '</td>'
				. '</tr>'
				. "\n"
				;
				
				$k = 1 - $k;
			}
		} else {
			$retval .= '<tr><td colspan="6" style="text-align:center">'. JTEXT::_( 'No data' ) . '</td></tr>';
		}

		// TotalLine //@todo missing TotalLine!
		$retval .= ''
		. '<tfoot><tr><td colspan="6">'.$pagination->getListFooter().'</td></tr></tfoot>'
		. '</table>'
		. "\n"
		;
		
		return $retval;
	}

	/**
	 *  @deprecated
	 *  @return html page
	 */
	function viewSummaryMonthAndSummaryYearHeadTpl( $isMonth ) {
		
		$retval = '<table class="adminlist">' . "\n"
		. '<thead>' . "\n"
		. '<tr>'
		. '<th nowrap="nowrap">' . (($isMonth==true) ? JTEXT::_( 'Day' ) : JTEXT::_( 'Month' )) . '</th>'
		. '<th colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of unique visitors' ) .'">' . JTEXT::_( 'Unique visitors' ) . '</th>'
		. '<th colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of visitors' ) .'">' . JTEXT::_( 'Visitors' ) . '</th>'
		. '<th nowrap="nowrap" title="' . JTEXT::_( 'Number of visitors' ) . ' / ' . JTEXT::_( 'Number of unique visitors' ) . '">' . JTEXT::_( 'Visits average' ) . '</th>'
		. '<th colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of visited pages' ) .'">' . JTEXT::_( 'Page impressions' ) . '</th>'
		. '<th nowrap="nowrap">' . JTEXT::_( 'Referrers' ) . '</th>'
		. '<th colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of unique bots/spiders' ) .'">' . JTEXT::_( 'Unique bots/spiders' ) .'</th>'
		. '<th colspan="2" nowrap="nowrap" title="' . JTEXT::_( 'Number of bots/spiders' ) .'">' . JTEXT::_( 'Bots/spiders' ) .'</th>'
		. '<th nowrap="nowrap" title="' . JTEXT::_( 'Number of unique not identified visitors' ) .'">' . JTEXT::_( 'Unique NIV' ) .'</th>'
		. '<th nowrap="nowrap" title="' . JTEXT::_( 'Number of not identified visitors' ) .'">' . JTEXT::_( 'NIV' ) .'</th>'
		. '<th nowrap="nowrap">' . JTEXT::_( 'Unique sum' ) . '</th>'
		. '<th nowrap="nowrap">' . JTEXT::_( 'Sum' ) . '</th>'
		. '</tr>'
		. '</thead>' . "\n";
		
		return $retval;
	}
	
	/**
	 *  this function return HTML table with 'Summary month' and 'Summary year'
	 *  (case r14 and r15)
	 *
	 *  old function name 'getSearches();'
	 *
	 *  @return html page
	 */
	function viewSummaryMonthAndSummaryYearTpl( $isMonth, $show_summarized, $rows, $total ) {
		require_once( dirname(__FILE__) .DS. 'template.html.php' );
		
		$JSTemplate = new js_JSTemplate();
		
		
		$retval = '';



		
		//Total line (last line, sum line)
		$visits_average = '0.0';
		if( ( $total->tuv != 0 ) && ( $total->tv != 0 ) ) {
			$format_token = '%01.2f';
			$visits_average = sprintf($format_token, ( $total->tv / $total->tuv ));
		}
			
		$retval .= ''
		. '<thead>' . "\n"
		. '<tr>'
		. '<th align="center">' . ( ($isMonth) ? $JSTemplate->monthToString($total->month_or_year, true) : $total->month_or_year ) . '</th>' // Day or Month
		. '<th colspan="2" align="right">' . $this->addSummStyleLine( $show_summarized, $total->tuv, $total->tuvpurge ) . '</th>' // Unique visitors
		. '<th colspan="2" align="right">' . $this->addSummStyleLine( $show_summarized, $total->tv, $total->tvpurge ) . '</th>' // Number of visits
		. '<th align="center">'.$visits_average.'</th>' // Visits average
		. '<th colspan="2" align="right">' . $this->addSummStyleLine( $show_summarized, $total->tp, $total->tppurge ) . '</th>' // Pages
		. '<th align="center">' . $total->tr . '</th>' // Referrers
		. '<th colspan="2" align="right">' . $this->addSummStyleLine( $show_summarized, $total->tub, $total->tubpurge ) . '</th>' // Unique bots
		. '<th colspan="2" align="right">' . $this->addSummStyleLine( $show_summarized, $total->tb, $total->tbpurge ) . '</th>' // Number of bots
		. '<th>' . $this->addSummStyleLine( $show_summarized, $total->tuniv, $total->tunivpurge ) . '</th>'
		. '<th>' . $this->addSummStyleLine( $show_summarized, $total->tniv,  $total->tnivpurge ) . '</th>'
		. '<th>' . $this->addSummStyleLine( $show_summarized, $total->tusum, $total->tusumpurge ) . '</th>'
		. '<th>' . $this->addSummStyleLine( $show_summarized, $total->tsum,  $total->tsumpurge ) . '</th>'
		. '</tr>' . "\n"
		. '</thead>' . "\n"
		. '</table>' . "\n";
		
		return $retval;
	}	

	/**
	 * This function return HTML code to page 'Resolutions'
	 * (case r)
	 *
	 *  ############################################################
	 *        THIS FUNCTION NOT WORKING AND IT IS NOT USED!
	 *  ############################################################
	 *
	 * @param array $rows
	 * @param array $pagination
	 * @return string
	 * @since 2.3.x
	 */
	function viewResolutionsTpl( $rows, $pagination, $summary ) {

		$retval = '<table class="adminlist" cellspacing="0" width="100%">' . "\n"
		. '<tr>'
		. '<th width="10%">' . JTEXT::_( 'Count' ).'</th>'
		. '<th width="45%">' . JTEXT::_( 'Percent' ).'</th>'
		. '<th align="left" width="45%">' . JTEXT::_( 'Resolutions' ).'</th>'
		. '</tr>';

		if( $summary['number'] != 0 ) {
        	$k = 0;

			if( count( $rows ) > 0 ) {
            	foreach( $rows as $row ) {
        			$retval .= '<tr class="row' . $k . '">'
					. '<td nowrap="nowrap">' . $row->numbers . '</td>'
	        		. '<td align="left">' . $this->getPercentBarWithPercentNbr( $row->numbers, $max_value, $sum_all_values ) . '</td>'
					. '<td nowrap="nowrap">&nbsp;'
					. ( $row->screen ? $row->screen : JTEXT::_( 'Unknown' ) )
					. '</td>'
					. '</tr>' . "\n";

					$k = 1 - $k;
				}
			}
		}

		// Summary Bar
		$retval .= '<tr><th align="center">&nbsp;' . $summary['number'] . '</th>'
		. '<th>&nbsp;</th>'
		. '<th align="left">' . $summary['screens'] . '&nbsp;';

		if( $summary['screens'] != 0 ) {
			$retval .= ( $summary['screens'] == 1 ? JTEXT::_( 'Resolution' ) : JTEXT::_( 'Resolutions' ) );
		}else{
			$retval .= JTEXT::_( 'Resolution' );
		}

		$retval .= '</th></tr>' . "\n"
		//. '<tfoot><tr><td colspan="4">'.$pagination->getListFooter().'</td></tr></tfoot>'
		. '</table>' . "\n"
		;

		return $retval;
	}




	/**
	 *  This function return HTML content (texts + tables) with 'Details about Visit' (date, time, user etc.)
	 *  (case r18)
	 *
	 *  old function name 'moreVisitInfo();'
	 *  old task case r03a
	 *
	 *
	 *  @return html page
	 */
	function viewDetailVisitInformationTpl( $VisitObj, $VisitorObj, $impressions_sum_all, $impressions_result_arr, $path_result_arr ) {
 		$retval  = '';	
		$retval .= '<br/><br/>';
		$retval .= $this->viewSubGeneralDetailVisitInformationTpl( $VisitObj, $VisitorObj );
		$retval .= '<br/><br/><br/>';
		$retval .= $this->viewSubImpressionsDetailVisitInformationTpl( $impressions_sum_all, $impressions_result_arr );
		$retval .= '<br/><br/><br/>';
		$retval .= $this->viewSubPathDetailVisitInformationTpl( $path_result_arr );
		$retval .= '<br/><br/><br/>';

		//$retval .= '<div style="text-align:center">[&nbsp;<a href="javascript:submitbutton(\'r03\');">' . JTEXT::_( 'Back' ) . '</a>&nbsp;]</div>'; //sometimes we need to back to r10!

		return $retval;
	}


	/**
	 *  Part of (case 18) "Detail Visit Information"
	 *
	 *  @private
	 */
	function viewSubImpressionsDetailVisitInformationTpl( $impressions_sum_all, $result_arr ) {
 		$retval  = '';
 		$retval .= '<div style="text-align: center; font-weight: bold; font-size: larger;">' . JTEXT::_( 'Visited pages' ) . '</div>';
					
		$retval .= '<div style="text-align: center;">';
		$retval .= '<table class="adminlist" style="width: 90%;" align="center">' . "\n";
		
		{// Header
			$retval .= ''
			. '<thead>' . "\n"
			. '<tr>'
			. '<th style="width: 1%;">#</th>'
			. '<th style="width: 1px;" title="'.JTEXT::_( 'Number of impressions' ).'">' . JTEXT::_( 'IMP.' ) . '</th>'
			. '<th style="width: 100%; text-align: left;">' . JTEXT::_( 'Page' ) . '</th>'
			. '</tr>' . "\n"
			. '</thead>' . "\n"
			;
		}

		// Body
		if( count($result_arr) > 0 ) {
			$k			= 0;
			$order_nbr	= 0;

			foreach( $result_arr as $row ) {
				$order_nbr++;
				
				$retval .= '<tr class="row'.$k.'">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
			  	. '<td style="text-align: right;">' . $row->impresions . '</td>'
				. '<td>'
					. '<a href="' . htmlentities($row->page) . '" target="_blank"' . 'title="' . JTEXT::_( 'Click opens new window' ) . '">'
						. ( $row->page_title == '' ? $row->page : $row->page_title )
					. '</a>'
				. '</td>'
				. '</tr>' . "\n";

				$k = 1 - $k;
			}
		}
		

		{// TotalLine - Footer
			$retval .= ''
			. '<thead>'
			. '<tr>'
			. '<th>&nbsp;</th>'
			. '<th style="text-align: center;">' . $impressions_sum_all . '</th>'
			. '<th>&nbsp;</th>'
			. '</tr>'
			. '</thead>'
			;
		}
		
		$retval .= '</table>' . "\n";
		$retval .= '</div>';

		return $retval;
	}


	/**
	 *  Part of (case 18) "Detail Visit Information"
	 *
	 *  @private
	 */
	function viewSubPathDetailVisitInformationTpl( $result_arr ) {
 		$retval  = '';
 		$retval .= '<div style="text-align: center; font-weight: bold; font-size: larger;">' . JTEXT::_( 'Path info' ) . '</div>';
					
		$retval .= '<div style="text-align: center;">';
		$retval .= '<table class="adminlist" style="width: 90%;" align="center">' . "\n";
		
		{// Header
			$retval .= ''
			. '<thead>' . "\n"
			. '<tr>'
			. '<th style="width: 1%;" title="'.JTEXT::_( 'Pages are ordered in visit order' ).'">'.JTEXT::_( 'Order' ).'</th>'
			. '<th style="width: 100%; text-align: left;" title="'.JTEXT::_( 'Pages are ordered in visit order' ).'">' . JTEXT::_( 'Page' ) . '</th>'
			. '</tr>' . "\n"
			. '</thead>' . "\n"
			;
		}

		// Body
		if( count($result_arr) > 0 ) {
			$k			= 0;
			$order_nbr	= 0;

			foreach( $result_arr as $row ) {
				$order_nbr++;
				
				$retval .= '<tr class="row'.$k.'">'
			  	. '<td style="text-align: right;"><em>'.$order_nbr.'.</em></td>'
				. '<td>'
					. '<a href="' . htmlentities($row->page) . '" target="_blank"' . 'title="' . JTEXT::_( 'Click opens new window' ) . '">'
						. ( $row->page_title == '' ? $row->page : $row->page_title )
					. '</a>'
				. '</td>'
				. '</tr>' . "\n";

				$k = 1 - $k;
			}
		}
		

		{// TotalLine - Footer
			$retval .= ''
			. '<thead>'
			. '<tr>'
			. '<th>'.count($result_arr).'</th>'
			. '<th>&nbsp;</th>'
			. '</tr>'
			. '</thead>'
			;
		}
		
		$retval .= '</table>' . "\n";
		$retval .= '</div>';

		return $retval;
	}


	/**
	 *  Part of (case 18) "Detail Visit Information"
	 *
	 *  @private
	 */
	function viewSubGeneralDetailVisitInformationTpl( $VisitObj, $VisitorObj ) {
 		$retval  = '';
 		$retval .= '<div style="font-weight: bold; font-size: larger;">' . JTEXT::_( 'Visitor details' ) . '</div>';
 		//$retval .= '<div style="text-align: center; font-weight: bold; font-size: larger;">' . JTEXT::_( 'Path info' ) . '</div>';
					
		//$retval .= '<div style="text-align: center;">';
		$retval .= '<table class="adminlist" style="width: 20px;">' . "\n";
		
		{// Header
			$retval .= ''
			. '<thead>' . "\n"
			. '<tr>'
			. '<th>#</th>'
			. '<th>'.JTEXT::_( 'Visitor' ).'</th>'
			. '<th>'.JTEXT::_( 'Value' ).'</th>'
			. '</tr>' . "\n"
			. '</thead>' . "\n"
			;
		}

		$visitor_type_name = JTEXT::_('Not identified'); //_JS_DB_IPADD__TYPE_NOT_IDENTIFIED_VISITOR
		if ($VisitorObj->type == _JS_DB_IPADD__TYPE_REGULAR_VISITOR)
			$visitor_type_name = JTEXT::_('Regular');
		else if ($VisitorObj->type == _JS_DB_IPADD__TYPE_BOT_VISITOR)
			$visitor_type_name = JTEXT::_('Bot');


		// Body
		$retval .= ''
		. '<tr class="row0">'
			. '<td style="text-align: right;"><em>1.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'Visit date' ).':</td>'
			. '<td nowrap="nowrap">'.$VisitObj->visit_date.'&nbsp;'.$VisitObj->visit_time.'</td>'
		. '</tr>' . "\n"
		. '<tr class="row1">'
			. '<td style="text-align: right;"><em>2.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'Username' ).':</td>'
			. '<td nowrap="nowrap">'.( ($VisitObj->joomla_userid > 0) ? $VisitObj->joomla_username : JTEXT::_( 'Not logged in' ) ).'</td>'
		. '</tr>' . "\n"
		. '<tr class="row0">'
			. '<td style="text-align: right;"><em>3.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'IP' ).':</td>'
			. '<td nowrap="nowrap">'.$VisitorObj->visitor_ip.'</td>'
		. '</tr>' . "\n"
		. '<tr class="row1">'
			. '<td style="text-align: right;"><em>4.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'NS-Lookup' ).':</td>'
			. '<td nowrap="nowrap">'.$VisitorObj->visitor_nslookup.'</td>'
		. '</tr>' . "\n"
		. '<tr class="row0">'
			. '<td style="text-align: right;"><em>5.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'System' ).':</td>'
			. '<td nowrap="nowrap">'.$VisitorObj->system.'</td>'
		. '</tr>' . "\n"
		. '<tr class="row1">'
			. '<td style="text-align: right;"><em>6.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'Browser' ).':</td>'
			. '<td nowrap="nowrap">'.$VisitorObj->browser.'</td>'
		. '</tr>' . "\n"
		. '<tr class="row0">'
			. '<td style="text-align: right;"><em>7.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'Visitor type' ).':</td>'
			. '<td nowrap="nowrap">'.$visitor_type_name.'</td>'
		. '</tr>' . "\n"
		. '<tr class="row1">'
			. '<td style="text-align: right;"><em>8.</em></td>'
			. '<td style="padding-right: 20px;" nowrap="nowrap">'.JTEXT::_( 'Country' ).':</td>'
			. '<td nowrap="nowrap">'.$VisitorObj->tld_name.'</td>'
		. '</tr>' . "\n"
		;

		$retval .= '</table>' . "\n";
		//$retval .= '</div>';

		return $retval;
	}
}
