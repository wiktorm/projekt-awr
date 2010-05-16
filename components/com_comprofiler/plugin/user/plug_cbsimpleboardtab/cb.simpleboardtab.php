<?php
/**
* Forum Tab Class for handling the CB tab api
* @version $Id: cb.simpleboardtab.php 837 2010-01-26 15:04:03Z beat $
* @package Community Builder
* @subpackage plug_cbsimpleboardtab.php
* @author JoomlaJoe and Beat (Nick A. fixed Fireboard support)
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
* Thanks to LucaZone, www.lucazone.net for Fireboard adaptation suggestions
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS, $_CB_framework;
include_once( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/user/plug_cbsimpleboardtab/cb.simpleboardtab.model.php' );

$_PLUGINS->registerFunction( 'forumSideProfile', 'getDisplaySideBar', 'getForumTab' );
$_PLUGINS->registerFunction( 'kunenaIntegration', 'kunenaIntegration', 'getForumTab' );
$_PLUGINS->registerFunction( 'onBeforegetFieldRow', 'onBeforegetFieldRow', 'getForumTab' );
$_PLUGINS->registerUserFieldTypes( array( 	'forumstats'	=> 'CBfield_forumstats', 'forumsettings'	=> 'CBfield_forumsettings'	) );
$_PLUGINS->registerUserFieldParams();

class CBfield_forumsettings extends cbFieldHandler {
	
	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  moscomprofilerFields  $field
	 * @param  moscomprofilerUser    $user
	 * @param  string                $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string                $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches, 'list' for user-lists
	 * @param  int                   $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed                
	 */
	function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $_PLUGINS;

		static $forum								=	null;

		$value										=	null;
		$return										=	null;
		
		if ( is_object( $user ) ) {
			if ( $user->id ) {
				if ( ! isset( $forum ) ) {
					$params								=	array( &$user );
					$forum								=	$_PLUGINS->call( $this->getPluginId(), 'getConfig', 'getForumModel', $params );
				}
				
				if ( isset( $forum ) ) {
					$params								=	array( &$user, $forum );
					$userSettings						=	$_PLUGINS->call( $this->getPluginId(), 'getUserSettings', 'getForumModel', $params );
				}
			}

			if ( $field->name == 'forumsignature' ) {
				$value									=	isset( $userSettings ) ? $userSettings->signature : '';
			} elseif ( $field->name == 'forumorder' ) {
				$value									=	isset( $userSettings ) ? $userSettings->ordering : '0';
			} elseif ( $field->name == 'forumview' ) {
				$value									=	isset( $userSettings ) ? $userSettings->view : 'flat';
			}

			switch ( $output ) {
				case 'html':
				case 'rss':
					$return							=	$this->_formatFieldOutput( $field->name, $value, $output, false );
				break;
				case 'htmledit':
					if ( $field->name == 'forumsignature' ) {
						$field->cols				=	$field->params->get( 'fs_signature_cols', 0 );
						$field->rows				=	$field->params->get( 'fs_signature_rows', 0 );
						$return						=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'textarea', $value, 'class="inputbox cbforumsignature" cols="60" rows="4"' );
					} elseif ( $field->name == 'forumorder' ) {
						$choices					=	array();
						$choices[]					=	moscomprofilerHTML::makeOption( '0', _UE_FB_ORDERING_OLDEST );
						$choices[]					=	moscomprofilerHTML::makeOption( '1', _UE_FB_ORDERING_LATEST );
						$return						=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', ( $value === '' ? '0' : $value ), 'class="inputbox"', $choices );
					} elseif ( $field->name == 'forumview' ) {
						$choices					=	array();
						$choices[]					=	moscomprofilerHTML::makeOption( 'flat', _UE_FB_VIEWTYPE_FLAT );
						$choices[]					=	moscomprofilerHTML::makeOption( 'threaded', _UE_FB_VIEWTYPE_THREADED );
						$return						=	$this->_fieldEditToHtml( $field, $user, $reason, 'input', 'select', ( $value === '' ? 'flat' : $value ), 'class="inputbox"', $choices );
					}
				break;
				default:
					$return							=	$this->_formatFieldOutput( $field->name, $value, $output, true );
				break;
			}
		}		
		return $return;
	}
	
	/**
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  moscomprofilerFields  $field
	 * @param  moscomprofilerUser    $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array                 $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string                $reason    'edit' for save profile edit, 'register' for registration, 'search' for searches
	 */
	function prepareFieldDataSave( &$field, &$user, &$postdata, $reason ) {
		global $_CB_database, $_PLUGINS;

		static $forum								=	null;

		if ( is_object( $user ) && $user->id ) {
			if ( ! isset( $forum ) ) {
				$params								=	array( &$user );
				$forum								=	$_PLUGINS->call( $this->getPluginId(), 'getConfig', 'getForumModel', $params );
			}
			
			if ( isset( $forum ) ) {
				$params								=	array( &$user, $forum );
				$userSettings						=	$_PLUGINS->call( $this->getPluginId(), 'getUserSettings', 'getForumModel', $params );
				
				if ( isset( $userSettings ) ) {
					$new['signature']				=	cbGetParam( $postdata, 'forumsignature', null );
					$new['ordering']				=	cbGetParam( $postdata, 'forumorder', null );
					$new['view']					=	cbGetParam( $postdata, 'forumview', null );
					$setQueries						=	array();
					foreach ($new as $k => $v ) {
						if ( $v != $userSettings->$k ) {
							$setQueries[]			=	$_CB_database->NameQuote( $k ) . " = " . $_CB_database->Quote( $v );
						}
					}

					if ( count( $setQueries ) > 0 ) {
						$query						=	'UPDATE '	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_users' )
													.	"\n SET "	. implode( ', ', $setQueries )
													.	"\n WHERE "	. $_CB_database->NameQuote( 'userid' )	. " = " . (int) $user->id
													;
						$_CB_database->setQuery( $query );
						
						if ( ! $_CB_database->query() ) {
							trigger_error( 'Forum-updateUser SQL error' . $_CB_database->stderr( true ), E_USER_WARNING );
						}
					}
				}
			}
		}
	}
}

class CBfield_forumstats extends cbFieldHandler {
	
	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  moscomprofilerFields  $field
	 * @param  moscomprofilerUser    $user
	 * @param  string                $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string                $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches, 'list' for user-lists
	 * @param  int                   $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed                
	 */
	function getField( &$field, &$user, $output, $reason, $list_compare_types ) {
		global $ueConfig;
		
		static $stats								=	array();
		$value										=	null;

		if ( is_object( $user ) && $user->id ) {
			if ( ! isset( $stats[$user->id] ) ) {
				global $_PLUGINS;
				$params								=	array( &$user );
				$stats[$user->id]					=	$_PLUGINS->call( $this->getPluginId(), 'getDisplayFields', 'getForumTab', $params );
			}
			if ( isset( $stats[$user->id][$field->name] ) ) {
				$value								=	$stats[$user->id][$field->name];
			} else {
				$value								=	$ueConfig['emptyFieldsText'];
			}
			
		}

		switch ( $output ) {
			case 'html':
			case 'rss':
				return $this->_formatFieldOutput( $field->name, $value, $output, false );
			break;
			case 'htmledit':
				return null;
			break;
			default:
				return $this->_formatFieldOutput( $field->name, $value, $output, false );
			break;
		}
	}
}

class getForumTab extends getForumModel {
		
	/**
	* Constructor
	*/
	function getForumTab() {
		$this->cbTabHandler();
	}
	
	/**
	* Generates the info for the custom fields for the forum
	* @param  moscomprofilerUser  $user      the user being displayed
	* @return boolean                        either true, or false if ErrorMSG generated
	*/
	function getDisplayFields( $user ) {
		$forum										=	$this->getConfig( $user );
		
		if ( $this->params->get( 'statDisplay', 1 ) == 1 ) {
			if ( $forum && $forum->config ) {
				return $this->getUserStats( $forum );
			}
		}
		
		return null;
	}
	
	/**
	* Generates the HTML to display the user profile tab
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return mixed                          either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab( $tab, $user, $ui ) {
		global $_CB_framework;
		
		cbimport( 'language.cbteamplugins' );
		$params										=	$this->params;
		$forum										=	$this->getConfig( $user );
		$return										=	null;
		
		if ( ! ( $forum && $forum->config ) ) {
			return CBTxt::T( 'The forum component is not installed.  Please contact your site administrator.' );
		}
		
		if ( ( $forum->component == 'com_fireboard' ) || ( $forum->component == 'com_kunena' ) ) {
			$base_url								=	$this->_getAbsURLwithParam( array(), 'userProfile', false );
			$fbunsub								=	cbGetParam( $_GET, 'fbunsubthread', null );
			$fbunfav								=	cbGetParam( $_GET, 'fbunfavthread', null );
			if ( $fbunsub ) {
				switch ( $fbunsub ) {
					case 'all':
						$this->_unsubAll( $user, $forum );
					break;
					default:
						$this->_unsubThread( $user, (int) $fbunsub, $forum );
					break;	
				}
			}
			if ( $fbunfav ) {
				switch ( $fbunfav ) {
					case 'all':
						$this->_unfavAll( $user, $forum );
					break;
					default:
						$this->_unfavThread( $user, (int) $fbunfav, $forum );
					break;	
				}
			}
		}
		
		//Load Templates
		include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbsimpleboardtab/view/cb.simpleboardtab.tab.php' );
		
		//Stats
		$template									=	null;
		$statDisplay								=	$params->get( 'statDisplay', 1 );
		if ( $statDisplay == 2 ) {
			$template->showStats					=	( isset ( $forum->config['showuserstats'] ) ? $forum->config['showuserstats'] : $forum->config['showstats'] );
			$template->showRank						=	( $forum->config['showranking'] && ( $params->get( 'statRanking', 1 ) == 1 ) && ( $forum->userdetails !== false ) );
			$template->showPosts					=	( ( isset ( $forum->config['poststats'] ) ? $forum->config['poststats'] : $forum->config['postStats'] ) && ( ( $params->get( 'statPosts', 1 ) == 2 ) || ( ( $params->get( 'statPosts', 1 ) == 1 ) && ( $forum->userdetails !== false ) ) ) );
			$template->showKarma					=	( $forum->config['showkarma'] && ( $forum->userdetails !== false ) && ( ( $params->get( 'statKarma', 1 ) == 2 ) || ( ( $params->get( 'statKarma', 1 ) == 1 ) && ( $forum->userdetails->karma != 0 ) ) ) );
			
			$return									.=	getForumTabTemplate::ShowStats( $template, $forum, $this );
		}
		
		//Paging - Global
		$postsNumber								=	$params->get( 'postsNumber', 10 );
		$pagingEnabled								=	$params->get( 'pagingEnabled', 0 );
		$searchEnabled								=	$params->get( 'searchEnabled', 0 );
		
		//Paging - Posts
		$pagingParams								=	$this->_getPaging( array(), array( 'fposts_' ) );
		$total										=	$this->getUserPostTotal( $user, $forum );
		
		if ( $pagingEnabled ) {
			if ( $pagingParams['fposts_limitstart'] === null ) {
				$pagingParams['fposts_limitstart']	=	0;
			}
			if ( $postsNumber > $total ) {
				$pagingParams['fposts_limitstart']	=	0;
			}
			if ( $searchEnabled ) {
				$searchForm							=	$this->_writeSearchBox( $pagingParams, 'fposts_', 'style="float:right;"', 'class="inputbox"' );
			} else {
				$pagingParams['fposts_search']		=	0;
			}
			$userHasPosts							=	( $total > 0 || ( $pagingParams['fposts_search'] && ( $forum->userdetails !== false ) && $forum->userdetails->posts > 0 ) );
		} else {
			$pagingParams['fposts_limitstart']		=	0;
			$pagingParams['fposts_search']			=	0;
		}

		//Posts
		$template									=	null;
		$template->posts							=	$this->getUserPosts( $user, $forum );
		
		if ( $pagingParams['fposts_search'] ) {
			$template->title						=	sprintf( CBTxt::T( 'Found %s Forum Posts' ), $total );
		} elseif ( $pagingEnabled ) {
			$template->title						=	sprintf( CBTxt::T( 'Forum Posts' ), $postsNumber );
		} else {
			$template->title						=	sprintf( CBTxt::T( 'Last %s Forum Posts' ), $postsNumber );
		}
		
		$template->showPaging						=	( $pagingEnabled && ( $postsNumber < $total ) );
		$template->showSearch						=	( $pagingEnabled && $searchEnabled );
		$template->noResults						=	( $pagingEnabled && $userHasPosts && $searchEnabled && $pagingParams['fposts_search'] );
		$template->paging							=	$this->_writePaging( $pagingParams, 'fposts_', $postsNumber, $total );
		$template->titles							=	$this->getTableTitles( $pagingParams, 'fposts_' );
		$template->searchForm						=	( $template->showSearch ? $searchForm : null );

		$return										.=	getForumTabTemplate::ShowPosts( $template, $forum, $this );

		//Paging - Subscriptions
		$pagingParams								=	$this->_getPaging( array(), array( 'fsubs_' ) );
		$total										=	$this->getUserSubscriptionsTotal( $user, $forum );
		
		if ( $pagingEnabled ) {
			if ( $pagingParams['fsubs_limitstart'] === null ) {
				$pagingParams['fsubs_limitstart']	=	0;
			}
			if ( $postsNumber > $total ) {
				$pagingParams['fsubs_limitstart']	=	0;
			}
		} else {
			$pagingParams['fsubs_limitstart']		=	0;
		}
		
		//Subscriptions
		$template									=	null;
		if ( ( $_CB_framework->myId() == $user->id ) && $forum->config['allowsubscriptions'] ) {
			$template->subscriptions				=	$this->getUserSubscriptions( $user, $forum );
			$template->titles						=	$this->getTableTitles( $pagingParams, 'fsubs_' );
		  	$template->showPaging					=	( $pagingEnabled && ( $postsNumber < $total ) );
			$template->paging						=	$this->_writePaging( $pagingParams, 'fsubs_', $postsNumber, $total );
		  	
			if ( ( $forum->component == 'com_fireboard' ) || ( $forum->component == 'com_kunena' ) ) {
				$template->unSubThreadURL			=	$base_url . '&amp;fbunsubthread=';
				$template->unSubAllURL				=	cbSef( $base_url . '&amp;fbunsubthread=all' );
			} else {
				$template->unSubThreadURL			=	'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=userprofile&amp;do=unsubscribe&amp;thread=';
				$template->unSubAllURL				=	cbSef( 'index.php?option=' . $forum->component . $forum->itemid . '&amp;func=userprofile&amp;do=update' );
			}
			
			$return									.=	getForumTabTemplate::ShowSubscriptions( $template, $forum, $this );
		}

		//Paging - Favorites
		$pagingParams								=	$this->_getPaging( array(), array( 'ffavs_' ) );
		$total										=	$this->getUserFavoritesTotal( $user, $forum );
		
		if ( $pagingEnabled ) {
			if ( $pagingParams['ffavs_limitstart'] === null ) {
				$pagingParams['ffavs_limitstart']	=	0;
			}
			if ( $postsNumber > $total ) {
				$pagingParams['ffavs_limitstart']	=	0;
			}
		} else {
			$pagingParams['ffavs_limitstart']		=	0;
		}
		
		//Favorites
		$template									=	null;
		if ( ( ( $forum->component == 'com_fireboard' ) || ( $forum->component == 'com_kunena' ) ) && ( $_CB_framework->myId() == $user->id ) && $forum->config['allowfavorites'] ) {
			$template->favorites					=	$this->getUserFavorites( $user, $forum );
			$template->titles						=	$this->getTableTitles( $pagingParams, 'ffavs_' );
		  	$template->showPaging					=	( $pagingEnabled && ( $postsNumber < $total ) );
			$template->paging						=	$this->_writePaging( $pagingParams, 'ffavs_', $postsNumber, $total );
			
			$template->unFavThreadURL				=	$base_url . '&amp;fbunfavthread=';
			$template->unFavAllURL					=	cbSef( $base_url . '&amp;fbunfavthread=all' );
			
			$return									.=	getForumTabTemplate::ShowFavorites( $template, $forum, $this );
		}

		return $return;
	}
	
	/**
	 * Replaces existing icons and information with fields data
	 *
	 * @param string $event
	 * @param object $config
	 * @param array  $params
	 */
	function kunenaIntegration( $event, &$config, &$params ) {
		global $_CB_framework;

		switch ( $event ) {
			case 'onStart':
				$_CB_framework->document->_outputToHeadCollectionStart();
				outputCbTemplate( 1 );
				$_CB_framework->outputCbJQuery( '' );		// make sure jQuery library is outputed
				break;

			case 'onEnd':
				echo $_CB_framework->getAllJsPageCodes();
				if ( cbGetParam( $_GET, 'no_html', 0 ) != 1 ) {
					echo $_CB_framework->document->_outputToHead();
				}
				break;

			case 'profileIntegration':
				return $this->getBeginnerMode( $params );
				break;

			default:
				;
			break;
		}
		return null;
	}

	/**
	 * Generates the HTML to display the user forum sidebar
	 *
	 * @param string $component
	 * @param mixed  $additional
	 * @param int    $userid
	 * @param mixed  $params
	 * @return mixed
	 */
	function getDisplaySideBar( $component, $additional, $userid, $params ) {
		global $_CB_framework;
		
		$cbUser										=&	CBuser::getInstance( $userid );
		
		if ( $cbUser === null ) { 
			$cbUser									=&	CBuser::getInstance( null );
		}
		
		$user										=	$cbUser->getUserData();
		$forum										=	$this->getConfig( $user );
		$return										=	null;
		
		if ( $forum && ( $forum->component == 'com_kunena' ) ) {
			$mode									=	(int) $this->params->get( 'sidebarMode', 0 );
			$config									=	$params['config'];
			$msg_params								=	$params['msg_params'];
			$userprofile							=	$params['userprofile'];
			$messageobject							=	$msg_params['messageobject'];
			
			if ( $mode === 1 ) { //Beginner
				$username							=	$this->getFieldValue( $user, (int) $this->params->get( 'sidebarBeginnerName', null ), 'html', 'list' );
				
				if ( $config->changename && ( $messageobject->name != $msg_params['username'] ) ) {
					$msg_params['username']			=	$messageobject->name;
				} elseif ( $username ) {
					$msg_params['username']			=	$username;
				}
			} elseif ( $mode === 2 ) { //Advanced
				if ( $user->id && $userprofile->userid ) {
					$format							=	$this->params->get( 'sidebarAdvancedExists', null );
				} elseif ( ! $user->id && $userprofile->userid ) {
					$format							=	$this->params->get( 'sidebarAdvancedDeleted', null );
				} elseif ( ! $user->id && ! $userprofile->userid ) {
					$format							=	$this->params->get( 'sidebarAdvancedPublic', null );
				}
				
				if ( $format ) {
					$return							=	$cbUser->replaceUserVars( $format );
				}
			} elseif ( $mode === 3 ) { //Expert
				include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbsimpleboardtab/view/cb.simpleboardtab.sidebar.php' );
				
				$return								=	getForumSidebarTemplate::ShowExpert( $user, $forum, $this, $params );
			}
		}
		
		return $return;
	}
	
	/**
	 * Replaces existing icons and information with fields data
	 *
	 * @param array  $params
	 */
	function getBeginnerMode( &$params ) {
		$cbUser										=&	CBuser::getInstance( $params['userid'] );
		
		if ( $cbUser === null ) { 
			$cbUser									=&	CBuser::getInstance( null );
		}
		
		$user										=	$cbUser->getUserData();
		$forum										=	$this->getConfig( $user );
		
		if ( $forum && ( $forum->component == 'com_kunena' ) ) {
			$mode									=	(int) $this->params->get( 'sidebarMode', 0 );
			$userinfo								=	$params['userinfo'];
			
			if ( $mode === 1 ) { //Beginner
				$fields								=	array();
				$fields[]							=	$this->getFieldValue( $user, (int) $this->params->get( 'sidebarBeginnerAvatar', null ), 'html', 'list' );
				for ( $i = 1; $i <= 11; $i++ ) {
					$fields[]						=	trim( $this->getFieldValue( $user, (int) $this->params->get( 'sidebarBeginner' . $i, null ), 'csv' ) );
				}
				
				if ( $fields[0] ) {
					$userinfo->avatar				=	$fields[0];
				}
				
				if ( $fields[1] ) {
					$userinfo->personalText			=	$fields[1];
				}
				
				if ( $fields[2] ) {
					$userinfo->birthdate			=	$fields[2];
				}
				
				if ( $fields[3] ) {
					$userinfo->location				=	$fields[3];
				}
				
				if ( $fields[4] ) {
					if ( $fields[4] == '_UE_MALE' ) {
						$gender						=	1;
					} elseif ( $fields[4] == '_UE_FEMALE' ) {
						$gender						=	2;
					} else {
						$gender						=	null;
					}
					
					$userinfo->gender				=	$gender;
				}
				
				if ( $fields[5] ) {
					$userinfo->ICQ					=	$fields[5];
				}
				
				if ( $fields[6] ) {
					$userinfo->AIM					=	$fields[6];
				}
				
				if ( $fields[7] ) {
					$userinfo->YIM					=	$fields[7];
				}
				
				if ( $fields[8] ) {
					$userinfo->MSN					=	$fields[8];
				}
				
				if ( $fields[9] ) {
					$userinfo->SKYPE				=	$fields[9];
				}
				
				if ( $fields[10] ) {
					$userinfo->GTALK				=	$fields[10];
				}
				
				if ( $fields[11] ) {
					$value							=	explode( '|*|', $fields[11] );
					
					if ( count( $value ) < 2) {
						$value[1]					=	$value[0];
					}
					
					$userinfo->websitename			=	$value[1];
					$userinfo->websiteurl			=	$value[0];
				}
			}
		}
	}
} //end of getForumTab
?>