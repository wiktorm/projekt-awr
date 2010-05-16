<?php
/**
* Forum Tab Class for handling the CB tab api
* @version $Id: cb.simpleboardtab.sidebar.php 831 2010-01-26 11:04:24Z beat $
* @package Community Builder
* @subpackage plug_cbsimpleboardtab.php
* @author JoomlaJoe and Beat (Nick A. fixed Fireboard support)
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
* Thanks to LucaZone, www.lucazone.net for Fireboard adaptation suggestions
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class getForumSidebarTemplate {

	/**
	 * Generates HTML for expert kunena sidebar mode
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @param object             $model
	 * @return mixed
	 */
	function ShowExpert( $user, $forum, $model, $params ) {
		$html					=	'<div>'
								.		'<span class="view-username">' . $model->getFieldValue( $user, 'formatname', 'html', 'list' ) . '</span> '
								.		'<span class="msgusertype">(' . $user->usertype . ')</span>'
								.	'</div>'
								.	'<div>' . $model->getFieldValue( $user, 'avatar', 'html', 'list' ) . '</div>'
								.	'<div class="viewcover">' . $model->getFieldValue( $user, 'forumrank' ) . '</div>'
								.	'<div class="viewcover"><strong>' . CBTxt::T( 'Karma: ' ) . '</strong>' . $model->getFieldValue( $user, 'forumkarma' ) . '</div>'
								.	'<div class="viewcover"><strong>' . CBTxt::T( 'Posts: ' ) . '</strong>' . $model->getFieldValue( $user, 'forumposts' ) . '</div>'
								.	'<div>'
								.		$model->getStatusIcon( $user )
								.		$model->getPMIcon( $user )
								.		$model->getProfileIcon( $user )
								.	'</div>';
									
		return $html;
	}
}
?>