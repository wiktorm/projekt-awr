<?php
/**
* @version $Id: cb.authentication.php 873 2010-02-19 20:08:07Z beat $
* @package Community Builder
* @subpackage cb.authentication.php
* @author Beat
* @copyright (C) Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * CB High-level authentication class and methods:
 */
class CBAuthentication {
	/**
	 * Logins on host CMS using any allowed authentication methods
	 *
	 * @param  string          $username        The username
	 * @param  string|boolean  $password        Well, The password OR strictly boolean false for login without password
	 * @param  boolean         $rememberMe      If login should be remembered in a cookie to be sent back to user's browser
	 * @param  boolean         $message         If an alert message should be prepared on successful login
	 * @param  string          $return          IN & OUT: IN: return URL NOT SEFED for normal login completition (unless an event says different), OUT: redirection url (no htmlspecialchars) NOT SEFED
	 * @param  array           $messagesToUser  OUT: messages to display to user (html)
	 * @param  array           $alertmessages   OUT: messages to alert to user (text)
	 * @param  int             $loginType       0: username, 1: email, 2: username or email, 3: username, email or CMS authentication
	 */
	function login( $username, $password, $rememberMe, $message, &$return, &$messagesToUser, &$alertmessages, $loginType = 0 ) {
	    global $_CB_database, $_CB_framework, $ueConfig, $_PLUGINS;
	
		$returnURL										=	null;
	    if ( ( ! $username ) || ( ( ! $password ) && ( $password !== false ) ) ) {
			$resultError								=	_LOGIN_INCOMPLETE;
		} else {
			$_PLUGINS->loadPluginGroup('user');
			$_PLUGINS->trigger( 'onBeforeLogin', array( &$username, &$password ) );
			
			$resultError								=	null;
			$showSysMessage								=	true;
			$stopLogin									=	false;
			$loggedIn									=	false;
			
			if($_PLUGINS->is_errors()) {
				$resultError							=	$_PLUGINS->getErrorMSG();
			} else {
				$row									=	new moscomprofilerUser( $_CB_database );
				$foundUser								=	false;

				// Try login by CB authentication trigger:
				$_PLUGINS->trigger( 'onLoginAuthentication', array( &$username, &$password, &$row, $loginType, &$foundUser, &$stopLogin, &$resultError, &$messagesToUser, &$alertmessages, &$return ) );
	
				if ( ! $foundUser ) {
					if ( $loginType != 2 ) {
						// login by username:
						$foundUser						=	$row->loadByUsername( stripslashes( $username ) ) && ( ( $password === false ) || $row->verifyPassword( $password ) );
					}
					if ( ( ! $foundUser ) && ( $loginType >= 1 ) ) {
						// login by email:
						$foundUser						=	$row->loadByEmail( stripslashes( $username ) ) && ( ( $password === false ) || $row->verifyPassword( $password ) );
						if ( $foundUser ) {
							$username					=	$row->username;
						}
					}
					if ( ( ! $foundUser ) && ( $loginType > 2 ) ) {
						// If no result, try login by CMS authentication:
						if ( $_CB_framework->login( $username, $password, $rememberMe ) ) {
							$foundUser					=	$row->loadByUsername( stripslashes( $username ) );
							cbSplitSingleName( $row );
							$row->confirmed				=	1;
							$row->approved				=	1;
							$row->store();		// synchronizes with comprofiler table
							$loggedIn					=	true;
						}
					}
				}
				if ( $foundUser ) {
					$pluginResults = $_PLUGINS->trigger( 'onDuringLogin', array( &$row, 1, &$return ) );
					if ( is_array( $pluginResults ) && count( $pluginResults ) ) {
						foreach ( $pluginResults as $res ) {
							if ( is_array( $res ) ) {
								if ( isset( $res['messagesToUser'] ) ) {
									$messagesToUser[]	=	$res['messagesToUser'];
								}
								if ( isset( $res['alertMessage'] ) ) {
									$alertmessages[]	=	$res['alertMessage'];
								}
								if ( isset( $res['showSysMessage'] ) ) {
									$showSysMessage		=	$showSysMessage && $res['showSysMessage'];
								}
								if ( isset( $res['stopLogin'] ) ) {
									$stopLogin			=	$stopLogin || $res['stopLogin'];
								}
							}
						}
					}
					if($_PLUGINS->is_errors()) {
						$resultError					=	$_PLUGINS->getErrorMSG();
					}
					elseif ( $stopLogin ) {
						// login stopped: don't even check for errors...
					}
					elseif ($row->approved == 2){
						$resultError					=	_LOGIN_REJECTED;
					}
					elseif ($row->confirmed != 1){
						if ( $row->cbactivation == '' ) {
							$row->store();		// just in case the activation code was missing
						}
						$cbNotification = new cbNotification();
						$cbNotification->sendFromSystem($row->id,getLangDefinition(stripslashes($ueConfig['reg_pend_appr_sub'])),getLangDefinition(stripslashes($ueConfig['reg_pend_appr_msg'])));
						$resultError = _LOGIN_NOT_CONFIRMED;
					}
					elseif ($row->approved == 0){
						$resultError					=	_LOGIN_NOT_APPROVED;
					}
					elseif ($row->block == 1) {
						$resultError					=	_UE_LOGIN_BLOCKED;
					}
					elseif ($row->lastvisitDate == '0000-00-00 00:00:00') {
						if (isset($ueConfig['reg_first_visit_url']) and ($ueConfig['reg_first_visit_url'] != "")) {
							$return						=	$ueConfig['reg_first_visit_url'];
						} else {
							$return						=	null;	// by default return to homepage on first login.
						}
						$_PLUGINS->trigger( 'onBeforeFirstLogin', array( &$row, $username, $password, &$return ));
						if ($_PLUGINS->is_errors()) {
							$resultError				=	$_PLUGINS->getErrorMSG( "<br />" );
						}
					}
				} else {
					if ( $loginType < 2 ) {
						$resultError					=	_LOGIN_INCORRECT;
					} else {
						$resultError					=	_UE_INCORRECT_EMAIL_OR_PASSWORD;
					}
				}
			}
	
			if ( $resultError ) {
				if ( $showSysMessage ) {
					$alertmessages[]					=	$resultError;
				}
			} elseif ( ! $stopLogin ) {
				if ( ! $loggedIn ) {
					$_PLUGINS->trigger( 'onDoLoginNow', array( $username, $password, $rememberMe, &$row, &$loggedIn, &$resultError, &$messagesToUser, &$alertmessages, &$return ) );
				}
				if ( ! $loggedIn ) {
					$_CB_framework->login( $username, $password, $rememberMe );
					$loggedIn							=	true;
				}
				$_PLUGINS->trigger( 'onAfterLogin', array( &$row, $loggedIn ) );
				if ( $loggedIn && $message && $showSysMessage ) {
					$alertmessages[]					=	_LOGIN_SUCCESS;
				}
				if ( ! $loggedIn ) {
					$resultError						=	_LOGIN_INCORRECT;
				}
				// changing com_comprofiler to comprofiler is a quick-fix for SEF ON on return path...
				if ( $return && !( strpos( $return, 'comprofiler' /* 'com_comprofiler' */ ) && ( strpos( $return, 'login') || strpos( $return, 'logout') || strpos( $return, 'registers' ) || strpos( strtolower( $return ), 'lostpassword' ) ) ) ) {
				// checks for the presence of a return url
				// and ensures that this url is not the registration or login pages
					$returnURL							=	$return;
				} elseif ( ! $returnURL ) {
					$returnURL							=	'index.php';
				}
			}
		}
		$return											=	$returnURL;
		return $resultError;
	}
	/**
	 * Logouts on host CMS using any allowed authentication methods
	 * 
	 * @param  string  $return   IN&OUT: IN: suggested URL for redirect, OUT: needed URL for redirect (unsefed)
	 * @return string            null or HTML-clean error to display
	 */
	function logout( $return ) {
		global $_POST, $_CB_framework, $_CB_database, $_PLUGINS;

		$myId				=	(int) $_CB_framework->myId();

		if ( $myId ) {
			$myCbUser		=&	CBuser::getInstance( $myId );
			if ( $myCbUser !== null ) {
				$myUser		=&	$myCbUser->getUserData();

				$_PLUGINS->loadPluginGroup('user');
				$_PLUGINS->trigger( 'onBeforeLogout', array( $myUser ) );
				if($_PLUGINS->is_errors()) {
					return $_PLUGINS->getErrorMSG();
				}
				$loggedOut	=	false;
				$_PLUGINS->trigger( 'onDoLogoutNow', array( &$loggedOut, &$myUser, &$return ) );
				if ( ! $loggedOut ) {
					$_CB_framework->logout();
				}
				$_PLUGINS->trigger( 'onAfterLogout', array( $myUser, true ) );
			}
		}

		if ( ! ( ( cbStartOfStringMatch( $return, $_CB_framework->getCfg( 'live_site' ) ) || cbStartOfStringMatch( $return, 'index.php' ) ) ) ) {
			$return			=	null;
		} elseif ( strpos( $return, 'comprofiler' /* 'com_comprofiler' */ ) && ( strpos( $return, 'login') || strpos( $return, 'logout') || strpos( $return, 'registers' ) || strpos( strtolower( $return ), 'lostpassword' ) ) ) {
		// checks for the presence of a return url
		// and ensures that this url is not the registration or login pages
			$return			=	null;
		}

		return null;
	}
}

?>
