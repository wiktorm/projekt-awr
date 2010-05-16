<?php
/**
* Forum Tab Class for handling the CB tab api
* @version $Id: cb.simpleboardtab.tab.php 831 2010-01-26 11:04:24Z beat $
* @package Community Builder
* @subpackage plug_cbsimpleboardtab.php
* @author JoomlaJoe and Beat (Nick A. fixed Fireboard support)
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
* Thanks to LucaZone, www.lucazone.net for Fireboard adaptation suggestions
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class getForumTabTemplate {
	
	/**
	 * Generates HTML for stats in forum tab
	 *
	 * @param object  $template
	 * @param object  $forum
	 * @param object  $model
	 * @return mixed
	 */
	function ShowStats( $template, $forum, $model ) {
		$html					=	'<table width="50%" cellspacing="0" cellpadding="3" border="0">'
								.		'<tr class="sectiontableheader">'
								.			'<th colspan="2">' . CBTxt::T( 'Forum Statistics' ) . '</th>'
								.		'</tr>';
		
		if ( $template->showStats ) {
			if ( $template->showRank ) {
				$html			.=		'<tr class="sectiontableentry1">'
								.			'<td style="font-weight:bold;width:50%;">' . CBTxt::T( 'Forum Ranking' ) . '</td>'
								.			'<td>' . $forum->userdetails->msg_userrank . '<br />' . $forum->userdetails->msg_userrankimg . '</td>'
								.		'</tr>';
			}
			if ( $template->showPosts ) {
				$html			.=		'<tr class="sectiontableentry2">'
								.			'<td style="font-weight:bold;width:50%;">' . CBTxt::T( 'Total Posts' ) . '</td>'
								.			'<td>' . $forum->userdetails->posts . '</td>'
								.		'</tr>';
			}
		}
		
		if ( $template->showKarma ) {
			$html				.=		'<tr class="sectiontableentry1">'
								.			'<td style="font-weight:bold;width:50%;">' . CBTxt::T( 'Karma' ) . '</td>'
								.			'<td>' . $forum->userdetails->karma . '</td>'
								.		'</tr>';
		}
		
		$html					.=	'</table>';
		
		return $html;
	}
	
	/**
	 * Generates HTML for posts in forum tab
	 *
	 * @param object  $template
	 * @param object  $forum
	 * @param object  $model
	 * @return mixed
	 */
	function ShowPosts( $template, $forum, $model ) {
		$html					=	null;
		$oneOrTwo				=	1;
	
		if ( $template->posts ) {
			if ( $template->showSearch ) {
				$html			.=	'<div style="width:95%;text-align:right;">' . $template->searchForm . '</div><div style="clear:both;"></div><br />';
			}
			
			$html				.=	'<table width="100%" cellspacing="0" cellpadding="3" border="0">'
								.	'<thead>'
								.		'<tr class="sectiontableheader">'
								.			'<th colspan="4">' . $template->title . '</th>'
								.		'</tr>'
								.		'<tr class="sectiontableheader">'
								.			'<th width="20%">' . $template->titles->date . '</th>'
								.			'<th width="50%">' . $template->titles->subject . '</th>'
								.			'<th width="25%">' . $template->titles->category . '</th>'
								.			'<th width="5%">' . $template->titles->hits . '</th>'
								.		'</tr>'
								.	'</thead>'
								.	'<tbody>';
								
			foreach ( $template->posts AS $item ) {
				$postURL		=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=view&amp;catid=' . $item->catid . '&amp;id=' . $item->id ) . '#' . $item->id;
				$catURL			=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=' . ( $forum->component == 'com_kunena' ? 'showcat' : 'view' ) . '&amp;catid=' . $item->catid );
				
				$html			.=	'<tr class="sectiontableentry' . $oneOrTwo . '">'
								.		'<td>' . getFieldValue( 'date', date( 'Y-m-d, H:i:s', $item->time ) ) . '</td>'
								.		'<td><a href="' . $postURL . '">' . htmlspecialchars( stripslashes( $item->subject ) ) . '</a></td>'
								.		'<td><a href="' . $catURL . '">' . htmlspecialchars( stripslashes( $item->catname ) ) . '</a></td>'
								.		'<td>' . $item->threadhits . '</td>'
								.	'</tr>';
				$oneOrTwo		=	( $oneOrTwo == 1 ? 2 : 1 );
			}
			
			$html				.=	'</tbody>'
								.	'</table>';
								
			if ( $template->showPaging ) {
				$html			.=	'<br /><div style="width:95%;text-align:center;">' . $template->paging . '</div>';
			}
		} else {
			if ( $template->noResults ) {
				$html			.=	'<div style="width:95%;text-align:right;">' . $template->searchForm . '</div><div style="clear:both;"></div><div>' . CBTxt::T( 'No matching forum posts found.' ) . '</div>';
			} else {
				$html			.=	'<div>' . CBTxt::T( 'This user has no forum posts.' ) . '</div>';
			}
		}
		
		return $html;
	}
	
	/**
	 * Generates HTML for subscriptions in forum tab
	 *
	 * @param object  $template
	 * @param object  $forum
	 * @param object  $model
	 * @return mixed
	 */
	function ShowSubscriptions( $template, $forum, $model ) {
		$html					=	null;
		$oneOrTwo				=	1;
	
		if ( $template->subscriptions ) {
			$html				.=	'<br /><table width="100%" cellspacing="0" cellpadding="3" border="0">'
								.	'<thead>'
								.		'<tr class="sectiontableheader">'
								.			'<th colspan="4">' . CBTxt::T( 'Your Subscriptions' ) . '</th>'
								.		'</tr>'
								.		'<tr class="sectiontableheader">'
								.			'<th width="20%">' . $template->titles->date . '</th>'
								.			'<th width="45%">' . $template->titles->subject . '</th>'
								.			'<th width="25%">' . $template->titles->category . '</th>'
								.			'<th width="10%">' . CBTxt::T( 'Action' ) . '</th>'
								.		'</tr>'
								.	'</thead>'
								.	'<tbody>';
			
			foreach ( $template->subscriptions as $item ) {
				$unsubURL		=	cbSef( $template->unSubThreadURL . $item->thread );
				$postURL		=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=view&amp;catid='. $item->catid . '&amp;id=' . $item->id );
				$catURL			=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=' . ( $forum->component == 'com_kunena' ? 'showcat' : 'view' ) . '&amp;catid='. $item->catid );
					
				$html			.=	'<tr class="sectiontableentry' . $oneOrTwo . '">'
								.		'<td>' . getFieldValue( 'date', date( 'Y-m-d, H:i:s', $item->time ) ) . '</td>'
								.		'<td><a href="' . $postURL . '">' . htmlspecialchars( stripslashes( $item->subject ) ) . '</a></td>'
								.		'<td><a href="' . $catURL . '">' . htmlspecialchars( stripslashes( $item->catname ) ) . '</a></td>'
								.		'<td><a href="javascript:void(0);" onclick="javascript:if ( confirm(\'' . CBTxt::T( "Are you sure you want to unsubscribe from this forum subscription?" ) . '\') ) { location.href=\'' . $unsubURL . '\'; }">' . CBTxt::T( 'Unsubscribe' ) . '</a></td>'
								.	'</tr>';
				$oneOrTwo		=	( $oneOrTwo == 1 ? 2 : 1 );
			}
	
			$html				.=	'</tbody>'
								.	'</table>';
								
			if ( $template->showPaging ) {
				$html			.=	'<br /><div style="width:95%;text-align:center;">' . $template->paging . '</div>';
			}
								
			$html				.=	'<br /><div style="width:95%;text-align:center;"><input type="button" class="button" onclick="javascript:if ( confirm(\'' . CBTxt::T( "Are you sure you want to unsubscribe from all your forum subscriptions?" ) . '\') ) { location.href=\'' . $template->unSubAllURL . '\'; }" value="' . CBTxt::T( 'Unsubscribe All' ) . '" /></div>';		
		} else {
			$html				.=	'<br /><div>' . CBTxt::T( 'No subscriptions found for you.' ) . '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates HTML for favorites in forum tab
	 *
	 * @param object  $template
	 * @param object  $forum
	 * @param object  $model
	 * @return mixed
	 */
	function ShowFavorites( $template, $forum, $model ) {
		$html					=	null;
		$oneOrTwo				=	1;
	
		if ( $template->favorites ) {
			$html				.=	'<br /><table width="100%" cellspacing="0" cellpadding="3" border="0">'
								.	'<thead>'
								.		'<tr class="sectiontableheader">'
								.			'<th colspan="4">' . CBTxt::T( 'Your Favorites' ) . '</th>'
								.		'</tr>'
								.		'<tr class="sectiontableheader">'
								.			'<th width="20%">' . $template->titles->date . '</th>'
								.			'<th width="45%">' . $template->titles->subject . '</th>'
								.			'<th width="25%">' . $template->titles->category . '</th>'
								.			'<th width="10%">' . CBTxt::T( 'Action' ) . '</th>'
								.		'</tr>'
								.	'</thead>'
								.	'<tbody>';
			
			foreach ( $template->favorites as $item ) {
				$unsubURL		=	cbSef( $template->unFavThreadURL . $item->thread );
				$postURL		=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=view&amp;catid='. $item->catid . '&amp;id=' . $item->id );
				$catURL			=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=' . ( $forum->component == 'com_kunena' ? 'showcat' : 'view' ) . '&amp;catid='. $item->catid );
					
				$html			.=	'<tr class="sectiontableentry' . $oneOrTwo . '">'
								.		'<td>' . getFieldValue( 'date', date( 'Y-m-d, H:i:s', $item->time ) ) . '</td>'
								.		'<td><a href="' . $postURL . '">' . htmlspecialchars( stripslashes( $item->subject ) ) . '</a></td>'
								.		'<td><a href="' . $catURL . '">' . htmlspecialchars( stripslashes( $item->catname ) ) . '</a></td>'
								.		'<td><a href="javascript:void(0);" onclick="javascript:if ( confirm(\'' . CBTxt::T( "Are you sure you want to remove this favorite thread?" ) . '\') ) { location.href=\'' . $unsubURL . '\'; }">' . CBTxt::T( 'Remove' ) . '</a></td>'
								.	'</tr>';
				$oneOrTwo		=	( $oneOrTwo == 1 ? 2 : 1 );
			}
	
			$html				.=	'</tbody>'
								.	'</table>';
								
			if ( $template->showPaging ) {
				$html			.=	'<br /><div style="width:95%;text-align:center;">' . $template->paging . '</div>';
			}
								
			$html				.=	'<br /><div style="width:95%;text-align:center;"><input type="button" class="button" onclick="javascript:if ( confirm(\'' . CBTxt::T( "Are you sure you want to remove all your favorite threads?" ) . '\') ) { location.href=\'' . $template->unFavAllURL . '\'; }" value="' . CBTxt::T( 'Remove All' ) . '" /></div>';		
		} else {
			$html				.=	'<br /><div>' . CBTxt::T( 'No favorites found for you.' ) . '</div>';
		}
		
		return $html;
	}
} //end of getForumTabTemplate
?>