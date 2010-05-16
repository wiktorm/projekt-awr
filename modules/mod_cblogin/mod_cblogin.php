<?php
/**
* Community builder Login Module 1.2 RC 3
* $Id: mod_cblogin.php 864 2010-02-10 01:43:27Z beat $
*
* @version 1.2
* @package Community Builder 1.2 extensions
* @copyright (C) 2004-2010 Beat & JoomlaJoe & parts 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*
* Credits to: Jeffrey Randall for initial implementation of avatar, and
* to Antony Ventouris for the PMS integration (he also added the cool animated image)
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CB framework
 * @global CBframework $_CB_framework
 */
global $_CB_framework, $_CB_database, $ueConfig, $mainframe, $_SERVER;
if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
	if ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) {
		echo 'CB not installed';
		return;
	}
	include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
} else {
	if ( ! file_exists( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' ) ) {
		echo 'CB not installed';
		return;
	}
	include_once( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' );
}
cbimport( 'cb.database' );
cbimport( 'language.front' );

$absolute_path		=	$_CB_framework->getCfg( 'absolute_path' );
$cblogin_live_site	=	$_CB_framework->getCfg( 'live_site' );

$len_live_site		=	strlen($cblogin_live_site);		// do not remove: used further down as well

$isHttps			=	(isset($_SERVER['HTTPS']) && ( !empty( $_SERVER['HTTPS'] ) ) && ($_SERVER['HTTPS'] != 'off') );
$return		=	'http' . ( $isHttps ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'];
if (!empty ($_SERVER['PHP_SELF']) && ! empty ($_SERVER['REQUEST_URI'])) {
	$return	.=	$_SERVER['REQUEST_URI'];	// Apache
} else {
	$return	.=	$_SERVER['SCRIPT_NAME'];	// IIS
	if (isset($_SERVER['QUERY_STRING']) && ! empty($_SERVER['QUERY_STRING'])) {
		$return	.=	'?' . $_SERVER['QUERY_STRING'];
	}
}
$return	=	preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', preg_replace('/eval\((.*)\)/', '', htmlspecialchars( urldecode( $return ) ) ) );

$return = cbUnHtmlspecialchars( $return );
// avoid unauthorized page acces at very first login after registration confirmation
if (preg_match( '/index.php\?option=com_comprofiler&task=confirm&confirmCode=|index.php\?option=com_comprofiler&task=login/', $return)) $return = "index.php";

// $params is defined by include: ignore this warning:
if (is_callable(array($params,"get"))) {				// Mambo 4.5.0 compatibility
	$message_login 	= $params->get( 'login_message', 0 );
	$message_logout = $params->get( 'logout_message', 0 );
	$pretext 		= $params->get( 'pretext' );
	$posttext 		= $params->get( 'posttext' );
	$logoutpretext 	= $params->get( 'logoutpretext' );
	$logoutposttext = $params->get( 'logoutposttext' );
	$login 			= $params->get( 'login', $return );
	$logout 		= $params->get( 'logout', "index.php" );
	if ( $logout == '#' ) {
		$logout		= $return;
	}
	$name 			= $params->get( 'name', 0 );
	$greeting 		= $params->get( 'greeting', 1 );
	$class_sfx		= $params->get( 'moduleclass_sfx', "");
	$horizontal		= $params->get( 'horizontal', 0);
	$show_avatar	= $params->get( 'show_avatar', 0);
	$avatar_position = $params->get( 'avatar_position', "default");
	$text_show_profile = $params->get( 'text_show_profile', "");
	$text_edit_profile = $params->get( 'text_edit_profile', "");
	$pms_type		= $params->get( 'pms_type', 0);
	$show_pms		= $params->get( 'show_pms', 0);
	$remember_enabled = $params->get( 'remember_enabled', 1);
	$https_post		= $params->get( 'https_post', 0);
	$showPendingConnections = $params->get( 'show_connection_notifications', 0);
	$show_newaccount = $params->get( 'show_newaccount', 1 );
	$show_lostpass 	= $params->get( 'show_lostpass', 1 );
	$name_lenght 	= $params->get( 'name_lenght', "14" );
	$pass_lenght 	= $params->get( 'pass_lenght', "14" );
	$compact 		= $params->get( 'compact', 0 );
	$cb_plugins		= $params->get( 'cb_plugins', 0 );
	$show_username_pass_icons	=	$params->get( 'show_username_pass_icons', 0 );
	$show_buttons_icons			=	$params->get( 'show_buttons_icons', 0 );
	$show_remind_register_icons	=	$params->get( 'show_remind_register_icons', 0 );
} else {
	$message_login 	= 0;
	$message_logout = 0;
	$pretext 		= "";
	$posttext 		= "";
	$logoutpretext	= "";
	$logoutposttext	= "";
	$login 			= $return;
	$logout 		= "index.php";
	$name 			= 0;
	$greeting 		= 1;
	$class_sfx		= "";
	$horizontal		= 0;
	$show_avatar	= 0;
	$avatar_position = "default";
	$text_show_profile = "";
	$text_edit_profile = "";
	$pms_type		= 0;
	$show_pms		= 0;
	$remember_enabled = 1;
	$https_post		= 0;
	$showPendingConnections = 0;
	$show_newaccount = 1;
	$show_lostpass 	= 1;
	$name_lenght 	= "10";
	$pass_lenght 	= "10";
	$compact		= 0;
	$cb_plugins		= true;
	$show_username_pass_icons	=	0;
}

switch ( checkJversion() ) {
 	case 0:
	// Mambo 4.5 & Joomla 1.0:
		$urlImgPath = $cblogin_live_site."/modules/mod_cblogin/";
 		break;
 	case -1:
	// Mambo 4.6.x:
		$urlImgPath = $cblogin_live_site."/modules/mod_cblogin/";
		break;
 	case 1:
 	default:
	// Joomla 1.5+
		$urlImgPath = $cblogin_live_site."/modules/mod_cblogin/mod_cblogin/";
		$lang =& JFactory::getLanguage();
		$lang->load("mod_login");		// might not be needed, unsure
 		break;
}

if ( $_CB_framework->myId() ) {
	if ($name) {
		if ($name == 2) {
			$query = "SELECT firstname FROM #__comprofiler WHERE id = ". (int) $_CB_framework->myId();
		} else {
			$query = "SELECT name FROM #__users WHERE id = ". (int) $_CB_framework->myId();
		}
		$_CB_database->setQuery( $query );
		// some joomla versions (1.5.1, 1.0.11+ do htmlspecialchars in database!):
		$name = htmlspecialchars( cbUnHtmlspecialchars( $_CB_database->loadResult() ) );
	} else {
		$name = htmlspecialchars( cbUnHtmlspecialchars( $_CB_framework->myUsername() ) );
	}

	$logout = cbSef( htmlspecialchars( $logout ) );

	$andItemid = getCBprofileItemid( true, false );

	// wondering if this was even neeeded: echo '<div style="width:100%; padding:0px; border-width:0px; margin:0px;">'."\n";

	$logoutPost = cbSef("index.php?option=com_comprofiler&amp;task=logout");
	echo '<form action="'.$logoutPost.'" method="post" id="mod_login_logoutform'.$class_sfx.'" style="margin:0px;">'."\n";

	// Add Authenticated Pre text
	if ( $logoutpretext ) {
		if ( defined( $logoutpretext ) ) {
			echo constant( $logoutpretext );
		} else {
			echo $logoutpretext;
		}
		echo "\n";
	}


	$avatarDisplayed = false;
	if ($show_avatar == 0) {
		if ($greeting) echo '<span id="mod_login_greeting'.$class_sfx.'">'.sprintf( _UE_HI_NAME, $name ).'</span>'.($horizontal ? "&nbsp;" : "");
	} else {
		
		if (($avatar_position=="default") and ($greeting)) echo '<span id="mod_login_greeting'.$class_sfx.'">'.sprintf( _UE_HI_NAME, $name ).'</span>';

		$cbUser		=&	CBuser::getInstance( $_CB_framework->myId() );
		$oValue		=	$cbUser->avatarFilePath( $show_avatar );
		if ($oValue) {
			if ($avatar_position=="default") echo '<div style="text-align:center; margin-left:auto; margin-right:auto;">';
			else echo '<div style="float:'.$avatar_position.'; margin: 3px 0px 4px 0px; ">';
			echo '<a href="'.cbSef("index.php?option=com_comprofiler&amp;task=userProfile".$andItemid).'" class="mod_login'.$class_sfx.'">';		
			echo '<img src="' . htmlspecialchars( $oValue ) . '" style="margin: 0px 1px 3px 1px; border-width:0px;" alt="'.$name
				 . '" title="'. _UE_MENU_VIEWMYPROFILE_DESC . '" class="mod_login'.$class_sfx.'" id="mod_login_avatar'.$class_sfx.'" />';
			echo "</a></div>\n";
			$avatarDisplayed = true;
		}
	}
	
	if ( trim( $text_show_profile ) ) {
		if ( ! ( ( $compact && ( $show_remind_register_icons == 2 ) ) || $horizontal ) ) {
			if ($avatar_position=="default") {
				echo '<div style="text-align:center; margin-left:auto; margin-right:auto;">';
			} else {
				echo '<div style="float:'.$avatar_position.'; margin: 3px 0px 4px 0px; ">';
			}
		}
		echo '<a href="'.cbSef("index.php?option=com_comprofiler&amp;task=userProfile".$andItemid).'" class="mod_login'.$class_sfx.'">';		
		echo '<span title="' . _UE_MENU_VIEWMYPROFILE_DESC . '" class="mod_login_showprofile'.$class_sfx.'">';
		if ( $show_remind_register_icons >= 1 ) {
			echo '<img src="' . $urlImgPath . 'username.png" alt="' . _UE_MENU_VIEWMYPROFILE_DESC . '" width="25px" height="20px" style="border-width:0px;cursor:pointer;" /> ';
		}
		if ( $show_remind_register_icons <= 1 ) {
			if ( defined( $text_show_profile ) ) {
				echo constant( $text_show_profile );
			} else {
				echo $text_show_profile;
			}
		}
		echo '</span>';
		echo '</a>';
		if ( ! ( ( $compact && ( $show_remind_register_icons == 2 ) ) || $horizontal ) ) {
			echo "</div>\n";
		}
	}

	if ( trim( $text_edit_profile ) ) {
		if ( ! ( ( $compact && ( $show_remind_register_icons == 2 ) ) || $horizontal ) ) {
			if ($avatar_position=="default") {
				echo '<div style="text-align:center; margin-left:auto; margin-right:auto;">';
			} else {
				echo '<div style="float:'.$avatar_position.'; margin: 3px 0px 4px 0px; ">';
			}
		}
		echo '<a href="'.cbSef("index.php?option=com_comprofiler&amp;task=userDetails".$andItemid).'" class="mod_login'.$class_sfx.'">';		
		echo '<span title="' . _UE_EDIT_TITLE . '" class="mod_login_editprofile'.$class_sfx.'">';
		if ( $show_remind_register_icons >= 1 ) {
			echo '<img src="' . $urlImgPath . 'edit_profile.png" alt="' . _UE_EDIT_TITLE . '" width="25px" height="20px" style="border-width:0px;cursor:pointer;" /> ';
		}
		if ( $show_remind_register_icons <= 1 ) {
			if ( defined( $text_edit_profile ) ) {
				echo constant( $text_edit_profile );
			} else {
				echo $text_edit_profile;
			}
		}
		echo '</span>';
		echo '</a>';
		if ( ! ( ( $compact && ( $show_remind_register_icons == 2 ) ) || $horizontal ) ) {
			echo "</div>\n";
		}
	}

	if ( !$horizontal ) {
		$preDiv = '<div style="text-align:center; margin:0px auto;"> '
				. '<div style="margin:auto; align:center; width:100%;"> '
				. '<div style="display:table; margin:auto; align:center;';
		$postDiv = "</div></div></div>\n";
	}
	
	if ( $show_avatar and ($avatar_position!="default") and ($greeting) ) {
		if ($avatarDisplayed && ( ! $horizontal ) ) {
			echo $preDiv . '" id="mod_login_greeting'.$class_sfx.'">';
			echo '<br />';
			echo sprintf( _UE_HI_NAME, '<br />'.$name );
			echo $postDiv;
		} else {
			echo '<span id="mod_login_greeting'.$class_sfx.'">'.sprintf( _UE_HI_NAME, $name ).'</span>';
		}
	}
	
	$pms = 0;
	if($show_pms != 0) {
		$pms = $pms_type;		// RC2 quick fix
		if($pms != 0)
		{
			switch ($pms) {
				case 1:
					$pmsnameprefix = "";
					$query_pms_count = "SELECT count(id) FROM #__".$pmsnameprefix."pms WHERE username=" . $_CB_database->Quote( $_CB_framework->myUsername() ) . " AND readstate=0";
					$_CB_database->setQuery( $query_pms_count );
					$total_pms = $_CB_database->loadResult();
		
					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."pms%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix."pms&amp;page=index".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 2:
					$pmsnameprefix = "my";
					$query_pms_count = "SELECT count(id) FROM #__".$pmsnameprefix."pms WHERE username=" . $_CB_database->Quote( $_CB_framework->myUsername() ) . " AND readstate=0";
					$_CB_database->setQuery( $query_pms_count );
					$total_pms = $_CB_database->loadResult();
		
					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."pms%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix."pms&amp;task=inbox".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 3:
					$query_pms_count="SELECT count(u.id) FROM #__uddeim AS u WHERE u.totrash=0 AND u.toread=0 AND u.toid=" . (int) $_CB_framework->myId();
					$_CB_database->setQuery($query_pms_count);
					$total_pms = $_CB_database->loadResult();	

					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_uddeim%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_uddeim&amp;task=inbox".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 4:		// PMS Enhanced by Stefan:
					$pmsnameprefix = "";
					$query_pms_count = "SELECT count(id) FROM #__".$pmsnameprefix."pms WHERE username=" . $_CB_database->Quote( $_CB_framework->myUsername() ) . " AND readstate=0 AND inbox=1";
					$_CB_database->setQuery( $query_pms_count );
					$total_pms = $_CB_database->loadResult();
		
					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."pms%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix."pms&amp;page=inbox".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 5:		// Clexus:
					$pmsnameprefix = "my";
					$query_pms_count = "SELECT count(id) FROM #__".$pmsnameprefix."pms WHERE userid='" . (int) $_CB_framework->myId() . "' AND readstate=0";
					$_CB_database->setQuery( $query_pms_count );
					$total_pms = $_CB_database->loadResult();
		
					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."pms%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix."pms&amp;task=inbox".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 6:		// PMS Enhanced 2.x by Stefan:
					$pmsnameprefix = "";
					$query_pms_count = "SELECT count(id) FROM #__".$pmsnameprefix."pms WHERE recip_id=" . (int) $_CB_framework->myId() . " AND readstate%2=0 AND inbox=1";
					$_CB_database->setQuery( $query_pms_count );
					$total_pms = $_CB_database->loadResult();
		
					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."pms%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix."pms&amp;page=inbox".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 7:
					$pmsnameprefix="missus";
                    $query_pms_count = "SELECT COUNT(*) FROM #__missus AS m JOIN #__missus_receipt AS r WHERE m.id=r.id AND r.receptorid='" . (int) $_CB_framework->myId() . "' AND r.rptr_rstate=0 AND r.rptr_tstate=0 AND r.rptr_dstate=0 AND m.is_draft=0";
                    $_CB_database->setQuery( $query_pms_count );
                    $total_pms = $_CB_database->loadResult();
                    
                    $query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."%'";
                    $_CB_database->setQuery( $query_pms_link );
                    $pms_link_id = $_CB_database->loadResult();
                    $pms_link = "index.php?option=com_".$pmsnameprefix."&amp;func=showinbox".($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
                    break;
                case 8:
                	$pmsnameprefix="jim";
                	$query_pms_count = "SELECT COUNT(id) FROM #__jim WHERE username=" . $_CB_database->Quote( $_CB_framework->myUsername() ) . " AND readstate=0";
                    $_CB_database->setQuery( $query_pms_count );
                    $total_pms = intval($_CB_database->loadResult());

					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix.($pms_link_id ? "&amp;Itemid=".$pms_link_id : "");
					break;
				case 9:
					$pmsnameprefix="primezilla";
					$query_pms_count = "SELECT COUNT(*) FROM #__primezilla_inbox WHERE userid=" . (int) $_CB_framework->myId() . " AND flag_read=0 AND flag_deleted=0";
					$_CB_database->setQuery( $query_pms_count );
					$total_pms = intval($_CB_database->loadResult());
					
					$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_".$pmsnameprefix."%'";
					$_CB_database->setQuery( $query_pms_link );
					$pms_link_id = $_CB_database->loadResult();
					$pms_link = "index.php?option=com_".$pmsnameprefix.($pms_link_id ? "&Itemid=".$pms_link_id : "");
					break;
				case 10:	// JAM (Joomla Advanced Message), J1.5 only:
					// Amount unread messages:
					$query	= 'SELECT COUNT(id)'
					. ' FROM `#__jam_receivers`'
					. ' WHERE rid = ' . (int) $_CB_framework->myId() . ' AND inbox = 1 AND state = 0';
					$_CB_database->setQuery( $query );
					$total_pms		=	$_CB_database->loadResult();

					// JAM url:
					if ( checkJversion() == 1 ) {
						$menu			=&	JSite::getMenu();
						$item			=	$menu->getItems( 'link', 'index.php?option=com_jam&view=inbox', true);
					} else {
						$item			=	false;
					}
					if ( $item ) {
						$pms_link		=	'index.php?Itemid=' . $item->id;
					} else {
						$pms_link		=	'index.php?option=com_jam&view=inbox';
					}
					break;
				/* Test-code for SMF PMS integration: to be validated with SMF team before integration !
				case xxx:
					global $user_info;
					$total_pms = $user_info['unread_messages'];
					$pms_link = ???
				*/
				default:
					break;
			}

			$pmsMsg = "";
			if (($total_pms) > 0 ) {
				$pmsMsg .= '<a href="'.cbSef("$pms_link").'" class="mod_login'.$class_sfx.'" id="mod_login_pmsimg'.$class_sfx.'">';
				$pmsMsg .= '<img border="0" src="'.$urlImgPath.'mail.gif" width="14" height="15" alt="NEW" class="mod_login'.$class_sfx.'" id="mod_login_messagesimg'.$class_sfx.'" /></a><br />'."\n";
				$pmsMsg .= '<a href="'.cbSef("$pms_link").'" class="mod_login'.$class_sfx.'" id="mod_login_pmsa'.$class_sfx.'">';
				$pmsMsg .= '<span id="mod_login_messagestext'.$class_sfx.'">'._UE_PM_MESSAGES_HAVE." ".$total_pms."&nbsp;".($total_pms == 1 ? _UE_PM_NEW_MESSAGE : _UE_PM_NEW_MESSAGES)."</span></a>\n";
			} else {
				if($show_pms >= 2 ) {
					$pmsMsg .= '<a href="'.cbSef("$pms_link").'" class="mod_login'.$class_sfx.'" id="mod_login_no_pms'.$class_sfx.'">';
					$pmsMsg .= '<span id="mod_login_nomessagestext'.$class_sfx.'">'._UE_PM_NO_MESSAGES."</span></a>\n";
				}
			}
			if ($pmsMsg) {
				if ( !$horizontal ) echo $preDiv.' margin-top:0.7em;" id="mod_login_pms'.$class_sfx.'">';
				echo $pmsMsg;
				if ( !$horizontal ) echo $postDiv;
			}
		}
	}

	if($showPendingConnections) {
		if(isset($ueConfig['allowConnections']) && $ueConfig['allowConnections']) {
			// $query = "SELECT count(*) FROM #__comprofiler_members WHERE pending=1 AND memberid=". (int) $_CB_framework->myId();
			$query = "SELECT COUNT(*)"
			. "\n FROM #__comprofiler_members AS m"
			. "\n LEFT JOIN #__comprofiler AS c ON m.referenceid=c.id"
			. "\n LEFT JOIN #__users AS u ON m.referenceid=u.id"
			. "\n WHERE m.memberid=" . (int) $_CB_framework->myId() . " AND m.pending=1"
			. "\n AND c.approved=1 AND c.confirmed=1 AND c.banned=0 AND u.block=0"
			;
			if(!$_CB_database->setQuery($query)) print $_CB_database->getErrorMsg();
			$totalpendingconnections = $_CB_database->loadResult();
			if($totalpendingconnections > 0) {
				if ( !$horizontal ) echo '<div style="margin:0.7em 0px 0px 0px; align:center; text-align:center;" id="mod_login_connections'.$class_sfx.'">';
				echo "<span id='mod_login_pendingConn".$class_sfx."'>";
				echo "<a href='".cbSef("index.php?option=com_comprofiler&amp;task=manageConnections".$andItemid)."' class='mod_login".$class_sfx."' id='mod_login_connectimg".$class_sfx."'>";
				echo '<img border="0" src="'.$urlImgPath.'users.gif" width="21" height="15" alt="NEW" class="mod_login'.$class_sfx.'" id="mod_login_connections_img'.$class_sfx.'" />';
				echo "</a> ";
				echo "<a href='".cbSef("index.php?option=com_comprofiler&amp;task=manageConnections".$andItemid)."' class='mod_login".$class_sfx."' id='mod_login_connect".$class_sfx."'>";
				echo _UE_PM_MESSAGES_HAVE." ".$totalpendingconnections."&nbsp;"._UE_CONNECTIONREQUIREACTION."</a></span>";
				if ( !$horizontal ) echo "</div>";
			}
		}
	}

	if (!$horizontal) {
		if ((!$avatarDisplayed) or ($avatar_position!="default") or ($pms)) $topMargin = "1.4em";
		else $topMargin = "2px";
		echo '<div style="text-align:center; margin:auto; margin: '.$topMargin.' 0px 2px 0px;">';
	}

	if ( $cb_plugins ) {
		include_once( $absolute_path . "/administrator/components/com_comprofiler/plugin.class.php");
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup('user');
		$pluginsResults	=	$_PLUGINS->trigger( 'onAfterLogoutForm', array( $name_lenght, $pass_lenght, $horizontal, $class_sfx, &$params ) );
		if ( implode( $pluginsResults ) != '' ) {
			$divHtml	=	( $horizontal ? '<span class="mod_logout_plugin'.$class_sfx.'">' : '<div class="mod_logout_plugin'.$class_sfx.'">' );
			echo $divHtml . implode( '</div>' . $divHtml, $pluginsResults ) . '</div>';
		}
	}

	// Logout button/icon:
	switch ( $show_buttons_icons ) {
		case 2:
			$buttonStyle	=	' style="width:25px;height:20px;border-width:0px;margin:0px;cursor:pointer;vertical-align:top;background-image:url(' . $urlImgPath . 'logout.png);background-position:0 0;background-repeat:no-repeat;"'
							.	' title="' . _UE_BUTTON_LOGOUT . '"';
			$buttonValue	=	'';
			break;
		case 1:
			$buttonStyle	=	' style="min-height:20px;padding-left:30px;cursor:pointer;background-image:url(' . $urlImgPath . 'logout.png);background-position:0 0;background-repeat:no-repeat;width:auto;"';
			$buttonValue	=	_UE_BUTTON_LOGOUT;
			break;
		case 0:
		default:
			$buttonStyle	=	'';
			$buttonValue	=	_UE_BUTTON_LOGOUT;
			break;
	}
	echo '<input type="submit" name="Submit" class="button'.$class_sfx.'" value="' . $buttonValue . '"' . $buttonStyle . ' />';

	echo "\n".'<input type="hidden" name="op2" value="logout" />'."\n";
	echo '<input type="hidden" name="lang" value="' . $_CB_framework->getCfg( 'lang' ) . '" />'."\n";
	echo '<input type="hidden" name="return" value="B:' . base64_encode( $logout ) . '" />'."\n";
	echo '<input type="hidden" name="message" value="' . htmlspecialchars( $message_logout ) . '" />'."\n";
	echo cbGetSpoofInputTag( 'logout' );
	// this is left for backwards compatibility only, to be removed after CB 1.2:
	if ( is_callable("josSpoofValue")) {
		$validate = josSpoofValue( 1 );
		echo "<input type=\"hidden\" name=\"" .  $validate . "\" value=\"1\" />\n";
	}
	if ( !$horizontal ) echo "</div>";
	echo "</form>";		// wondering if this was even neeeded: </div>";

	// Add Authenticated Post text
	if ( $logoutposttext ) {
		if ( defined( $logoutposttext ) ) {
			echo constant( $logoutposttext );
		} else {
			echo $logoutposttext;
		}
		echo "\n";
	}





} else {	// Login Form :





	switch ( isset( $ueConfig['login_type'] ) ? $ueConfig['login_type'] : 0 ) {
		case 2:
			$userNameText	=	_UE_EMAIL;
			break;
		case 1:
			// NEXT 3 LINES: CB 1.2 RC 2 + CB 1.2 specific : remove after !
			if ( ! defined( '_UE_USERNAME_OR_EMAIL' ) ) {
				DEFINE('_UE_USERNAME_OR_EMAIL','Username or email');
			}
			$userNameText	=	_UE_USERNAME_OR_EMAIL;
			break;
		case 0:
		default:
			$userNameText	=	_UE_USERNAME;
			break;
	}

	// redirect to site url (so cookies are recognized correctly after login):
	if (strncasecmp($cblogin_live_site, "http://www.", 11)==0 // && strncasecmp($cblogin_live_site, "http://", 7)==0
		&& strncasecmp( substr($cblogin_live_site, 11), substr($login, 7), $len_live_site - 11 ) == 0 ) {
			// the login return string matches the live site without 'www.' in it:
			// add www subdomain as live_site has it.
			$login = "http://www." . substr($login, 7);
	} elseif (strncasecmp($cblogin_live_site, "https://www.", 12)==0 // && strncasecmp($cblogin_live_site, "https://", 8)==0
		&& strncasecmp( substr($cblogin_live_site, 12), substr($login, 8), $len_live_site - 12 ) == 0 ) {
			$login = "https://www." . substr($login, 8);	// same for https

/* However, we can't remove www in joomla 1.0.13+, because cookies would fail on domain test to allow for login:

	} elseif (strncasecmp($cblogin_live_site, "http://", 7)==0 && strncasecmp($cblogin_live_site, "http://www.", 11)==0
		&& strncasecmp( substr($cblogin_live_site, 7), substr($login, 11), $len_live_site - 7 ) == 0 ) {
			$login = "http://" . substr($login, 11);
	} elseif (strncasecmp($cblogin_live_site, "https://", 8)==0 && strncasecmp($cblogin_live_site, "https://www.", 12)==0
		&& strncasecmp( substr($cblogin_live_site, 8), substr($login, 12), $len_live_site - 8 ) == 0 ) {
			$login = "https://" . substr($login, 12);
*/
	}

	$login = cbSef( $login );

	if ( $https_post > 1 /* && ! $isHttps */ ) {
		if ((strncmp($login, "http:", 5)!=0) && (strncmp($login, "https:", 6)!=0)) {
			$login = $cblogin_live_site . '/' . $login;
		}
		$login = str_replace("http://","https://",$login);
	}

	$loginPost = cbSef("index.php?option=com_comprofiler&amp;task=login");
	if ( $https_post /* && ! $isHttps */ ) {
		if ( ( substr($loginPost, 0, 5) != "http:" ) && ( substr($loginPost, 0, 6) != "https:" ) ) {
			$loginPost = $cblogin_live_site."/".$loginPost;
		}
		$loginPost = str_replace("http://","https://",$loginPost);
	}
	// now we need to make sure that the cookie in return of this post is sent to the most generic domain, in case multiple domains exist:
	// if the current page ($return) is without www, then login should also be without www, even if live_site has www:
	if (strncasecmp($loginPost, "http://www.", 11)==0 // && strncasecmp($cblogin_live_site, "http://", 7)==0
		&& strncasecmp( substr($loginPost, 11), substr($return, 7), $len_live_site - 11 ) == 0 ) {
			// the login return string matches the live site without 'www.' in it:
			// add www subdomain as live_site has it.
			$loginPost = "http://" . substr($loginPost, 11);
	} elseif (strncasecmp($loginPost, "https://www.", 12)==0 // && strncasecmp($cblogin_live_site, "https://", 8)==0
		&& strncasecmp( substr($loginPost, 12), substr($return, 8), $len_live_site - 12 ) == 0 ) {
			$loginPost = "https://" . substr($loginPost, 12);	// same for https
	}

	echo '<form action="'.$loginPost.'" method="post" id="mod_loginform'.$class_sfx.'" ';
/*
	if ($compact) {
		echo "onsubmit=\""
			. "return ( ( this.elements['mod_login_username" . $class_sfx . "'].value != '"._USERNAME."')"
			. "&& ( this.elements['mod_login_password" . $class_sfx . "'].value != 'paswww') )"
			."\" ";
	}
*/
	echo 'style="margin:0px;">'."\n";
	if ( $pretext ) {
		if ( defined( $pretext ) ) {
			echo constant( $pretext );
		} else {
			echo $pretext;
		}
		echo "\n";
	}
	if (!$horizontal) {
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="mod_login'.$class_sfx.'">'."\n"
		. "<tr><td>";
	}

	$bgstyleUser			=	'';
	$bgstylePass			=	'';
	if ( $compact ) {
		if ( $show_username_pass_icons >= 1 ) {
			$bgstyleUser	.=	' style="background-image:url(' . $urlImgPath . 'username.png); background-repeat: no-repeat; background-position: 0px 0px; padding-left: 30px; min-height: 18px;width:auto;" ';
			$bgstylePass	.=	' style="background-image:url(' . $urlImgPath . 'password.png); background-repeat: no-repeat; background-position: 0px 0px; padding-left: 30px; min-height: 18px;width:auto;" ';
		}
		if ( $show_username_pass_icons <= 1 ) {
			$bgstyleUser	.=	" alt=\"" . htmlspecialchars( $userNameText ) . "\" value=\"" . htmlspecialchars( $userNameText ) . "\" "
							.	"onfocus=\"if (this.value=='" . addslashes( $userNameText ) . "') this.value=''\" onblur=\"if(this.value=='') { this.value='" . addslashes( $userNameText ) . "'; return false; }\""
							;
			$bgstylePass	.=	" alt=\""._UE_PASS."\" value=\"paswww\" onfocus=\"if (this.value=='paswww') this.value=''\" onblur=\"if(this.value=='') { this.value='paswww'; return false; }\""
							;
		}
	} else {
		if ( $show_username_pass_icons == 2 ) {
			$bgstyleUser	.=	' style="vertical-align:top;" ';
			$bgstylePass	.=	' style="vertical-align:top;" ';
		}
		$txtusername		=	'<label for="mod_login_username'.$class_sfx.'">'
							.	( $show_username_pass_icons >= 1 ? '<img src="' . $urlImgPath . 'username.png" width="25" height="20" alt="' . $userNameText . '" /> ' : '' )
							.	( $show_username_pass_icons <= 1 ? $userNameText : '' )
							.	'</label>'
							;
		$txtpassword		=	'<label for="mod_login_password'.$class_sfx.'">'
							.	( $show_username_pass_icons >= 1 ? '<img src="' . $urlImgPath . 'password.png" width="25" height="20" alt="' . _UE_PASS . '" /> ' : '' )
							.	( $show_username_pass_icons <= 1 ? _UE_PASS : '' )
							.	'</label>'
							;

		echo '<span id="mod_login_usernametext'.$class_sfx.'">'.$txtusername.'</span>';
	}
	if ( $compact || ( $show_username_pass_icons == 2 ) ) {
		$bgstyleUser		.=	' title="' . $userNameText . '"';
		$bgstylePass		.=	' title="' . _UE_PASS . '"';
	}
	if ($horizontal) {
		echo "&nbsp;\n";
	} elseif ( ( ! $compact ) && ( $show_username_pass_icons != 2 ) ) {
		echo "<br />\n";
	}
	echo '<input type="text" name="username" id="mod_login_username'.$class_sfx.'" class="inputbox'.$class_sfx.'" size="'.$name_lenght.'"' . $bgstyleUser . ' />';
	if ($horizontal) {
		echo "&nbsp;\n";
	} else {				// if (!$compact) {
		echo "<br />\n";
	}
	if (!$compact) {
		echo '<span id="mod_login_passwordtext'.$class_sfx.'">'.$txtpassword.'</span>';
	}
	if ($horizontal) {
		echo "&nbsp;";
	} elseif ( ( ! $compact ) && ( $show_username_pass_icons != 2 ) ) {
		echo "<br />";
	}
	echo '<span><input type="password" name="passwd" id="mod_login_password'.$class_sfx.'" class="inputbox'.$class_sfx.'" size="'.$pass_lenght.'"' . $bgstylePass . ' /></span>';
	if ($horizontal) {
		echo "&nbsp;\n";
	} else {
		echo "<br />\n";
	}

	echo '<input type="hidden" name="op2" value="login" />'."\n";
	echo '<input type="hidden" name="lang" value="' . $_CB_framework->getCfg( 'lang' ) . '" />' . "\n";
	echo '<input type="hidden" name="force_session" value="1" />'."\n";		// makes sure to create joomla 1.0.11+12 session/bugfix
	echo '<input type="hidden" name="return" value="B:' . base64_encode( $login ) . '" />'."\n";
	echo '<input type="hidden" name="message" value="' . htmlspecialchars( $message_login ) . '" />'."\n";
	$loginFrom		=	( defined( '_UE_LOGIN_FROM' ) ? constant( '_UE_LOGIN_FROM' ) : 'loginmodule' );
	echo '<input type="hidden" name="loginfrom" value="' . htmlspecialchars( $loginFrom ) . '" />'."\n";
	echo cbGetSpoofInputTag( 'login' );
	// this is left for backwards compatibility only, to be removed after CB 1.2:
	if ( is_callable("josSpoofValue")) {
		$validate = josSpoofValue( 1 );
		echo "<input type=\"hidden\" name=\"" .  $validate . "\" value=\"1\" />\n";
	}

	switch ($remember_enabled) {
		case 1:
			echo '<input type="checkbox" name="remember" id="mod_login_remember'.$class_sfx.'"' . ( $class_sfx ? ' class="inputbox'.$class_sfx.'"' : '' ) . ' value="yes" /> '
				.'<span id="mod_login_remembermetext'.$class_sfx.'"><label for="mod_login_remember'.$class_sfx.'">'._UE_REMEMBER_ME."</label></span>";
			if ($horizontal) echo "&nbsp;\n"; else echo "<br />\n";
		break;
		case 2:
			echo '<input type="hidden" name="remember" value="yes" />';
		break;
		case 3:
			echo '<input type="checkbox" name="remember" id="mod_login_remember'.$class_sfx.'"' . ( $class_sfx ? ' class="inputbox'.$class_sfx.'"' : '' ) . ' value="yes" checked="checked" /> '
				.'<span id="mod_login_remembermetext'.$class_sfx.'"><label for="mod_login_remember'.$class_sfx.'">'._UE_REMEMBER_ME."</label></span>";
			if ($horizontal) echo "&nbsp;\n"; else echo "<br />\n";
		break;
		default:
		break;
	}

	$pluginDisplays		=	array();
	if ( $cb_plugins ) {
		include_once( $absolute_path . "/administrator/components/com_comprofiler/plugin.class.php");
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup('user');
		$pluginsResults	=	$_PLUGINS->trigger( 'onAfterLoginForm', array( $name_lenght, $pass_lenght, $horizontal, $class_sfx, &$params ) );
		if ( count( $pluginsResults ) > 0 ) {
			foreach ( $pluginsResults as $pR ) {
				if ( is_array( $pR ) ) {
					foreach ($pR as $pK => $pV ) {
						$pluginDisplays[$pK][]			=	$pV;
					}
				} elseif ( $pR != '' ) {
					$pluginDisplays['beforeButton'][]	=	$pR;
				}
			}
			
		}
		foreach ( $pluginDisplays as $pK => $pV ) {
			$divHtml				=	( $horizontal ? '<span' : '<div' ) . ' class="mod_login_plugin'.$class_sfx.' mod_login_plugin_' . $pK . '">';
			$sldivHtml				=	( $horizontal ? '</span>' : '</div>' );
			$pluginDisplays[$pK]	=	$divHtml . implode( $sldivHtml . $divHtml, $pV ) . $sldivHtml;
		}
		
		if ( isset( $pluginDisplays['beforeButton'] ) ) {
			echo $pluginDisplays['beforeButton'];
		}
	}

	// Login button/icon:
	switch ( $show_buttons_icons ) {
		case 2:
			$buttonStyle	=	' style="width:25px;height:20px;border-width:0px;margin:0px;cursor:pointer;vertical-align:top;background-image:url(' . $urlImgPath . 'login.png);background-position:0 0;background-repeat:no-repeat;"'
							.	' title="' . _UE_BUTTON_LOGIN . '"';
			$buttonValue	=	'';
			break;
		case 1:
			$buttonStyle	=	' style="min-height:20px;padding-left:30px;cursor:pointer;background-image:url(' . $urlImgPath . 'login.png);background-position:0 0;background-repeat:no-repeat;width:auto;"';
			$buttonValue	=	_UE_BUTTON_LOGIN;
			break;
		case 0:
		default:
			$buttonStyle	=	'';
			$buttonValue	=	_UE_BUTTON_LOGIN;
			break;
	}
	echo '<input type="submit" name="Submit" class="button'.$class_sfx.'" value="' . $buttonValue . '"' . $buttonStyle . ' />';

	if ($horizontal || ( $show_remind_register_icons == 2 ) ) {
		echo "&nbsp;&nbsp;\n";
	} else {
		echo "</td></tr>\n<tr><td>";
	}

	if ( isset( $pluginDisplays['afterButton'] ) ) {
		echo $pluginDisplays['afterButton'];
	}

	if ($show_lostpass) {
		$loginPost = cbSef("index.php?option=com_comprofiler&amp;task=lostPassword");
		if ( $https_post /* && ! $isHttps */ ) {
			if ( ( substr($loginPost, 0, 5) != "http:" ) && ( substr($loginPost, 0, 6) != "https:" ) ) {
				$loginPost = $cblogin_live_site."/".$loginPost;
			}
			$loginPost = str_replace("http://","https://",$loginPost);
		}
		echo '<a href="'.$loginPost.'" class="mod_login'.$class_sfx.'">';

		if ( $show_remind_register_icons >= 1 ) {
			echo '<img src="' . $urlImgPath . 'forgot.png" alt="' . _UE_USERNAME_PASSWORD_REMINDER . '" title="' . _UE_USERNAME_PASSWORD_REMINDER . '" width="25px" height="20px" style="border-width:0px;cursor:pointer;" /> ';
		}
		if ( $show_remind_register_icons <= 1 ) {
			if ($compact) {
				echo _UE_LOST_USERNAME_PASSWORD;	
			} else {
				echo ( ( checkJversion() == -1 ) ? _UE_USERNAME_PASSWORD_REMINDER : _UE_LOST_USERNAME_PASSWORD );
			}
		}
		echo '</a>';

		if ( $show_remind_register_icons == 2 ) {
			echo "&nbsp;\n"; 
		} elseif ($horizontal) {
			if ($compact) {
				echo "&nbsp;|";
			} else {
				echo "&nbsp;\n"; 
			}
		} else echo "</td></tr>\n";
	}

	// CB config may override the system configuration setting
	$registration_enabled	=	$_CB_framework->getCfg( 'allowUserRegistration' );
	if ( ! $registration_enabled ) {
		if ( isset($ueConfig['reg_admin_allowcbregistration']) && $ueConfig['reg_admin_allowcbregistration'] == '1' ) {
			$registration_enabled = true;
		}
	}
	if ($registration_enabled && $show_newaccount) {
		if ($horizontal || ( $show_remind_register_icons == 2 ) ) {
			echo '&nbsp;<span id="mod_login_noaccount'.$class_sfx.'">';
		} else {
			echo "<tr><td>";
		}
		if ( ( ! $compact ) && ( $show_remind_register_icons == 0 ) ) {
			echo _UE_NO_ACCOUNT . " ";
		}
		$loginPost = cbSef("index.php?option=com_comprofiler&amp;task=registers");
		if ( $https_post /* && ! $isHttps */ ) {
			if ( ( substr($loginPost, 0, 5) != "http:" ) && ( substr($loginPost, 0, 6) != "https:" ) ) {
				$loginPost = $cblogin_live_site."/".$loginPost;
			}
			$loginPost = str_replace("http://","https://",$loginPost);
		}
		echo '<a href="'.$loginPost.'" class="mod_login'.$class_sfx.'">';
		if ( $show_remind_register_icons >= 1 ) {
			echo '<img src="' . $urlImgPath . 'register.png" alt="' . _UE_REGISTER . '" title="' . _UE_REGISTER . '" width="25px" height="20px" style="border-width:0px;cursor:pointer;" /> ';
		}
		if ( $show_remind_register_icons <= 1 ) {
			echo ( ( ( checkJversion() == -1 ) && ! $compact ) ? _UE_CREATE_ACCOUNT : _UE_REGISTER );
		}
		echo '</a>';
		if ($horizontal || ( $show_remind_register_icons == 2 ) ) {
			echo "</span>\n";
		}
		if ( ! $horizontal ) {
			echo "</td></tr>\n";
		}
	}
	if (!$horizontal) {
		echo "</table>";
	}
	echo "</form>";

	if ( isset( $pluginDisplays['almostEnd'] ) ) {
		echo $pluginDisplays['almostEnd'];
	}

	if ( $posttext ) {
		if ( defined( $posttext ) ) {
			echo constant( $posttext );
		} else {
			echo $posttext;
		}
		echo "\n";
	}
}
?>
