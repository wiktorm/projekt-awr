<?php
/**
* Forum Tab Class for handling the CB tab api
* @version $Id: cb.simpleboardtab.model.php 831 2010-01-26 11:04:24Z beat $
* @package Community Builder
* @subpackage plug_cbsimpleboardtab.php
* @author JoomlaJoe and Beat (Nick A. fixed Fireboard support)
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
* Thanks to LucaZone, www.lucazone.net for Fireboard adaptation suggestions
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class getForumModel extends cbTabHandler {
	
	/**
	 * Generate forum API
	 *
	 * @param moscomprofilerUser $user
	 * @param array              $params
	 * @return object
	 */
	function getConfig( $user ) {
		global $_CB_database;

		static $forum											=	null;

		if ( $forum === null ) {
			$forum												=	$this->getForumParams();
			if ( $forum !== null ) {
				if ( ! $forum->config ) {
					$query										=	'SELECT * FROM ' . $_CB_database->NameQuote( '#__' . $forum->prefix . '_config' );
					$_CB_database->setQuery( $query );
					$forum->config								=	$_CB_database->loadAssoc();
				} else {
					if ( $forum->component == 'com_fireboard' ) {
						global $fbConfig;
						$config									=&	$fbConfig;
					} elseif ( $forum->component != 'com_kunena' ) {
						global $sbConfig;
						$config									=&	$sbConfig;
					}
					
					include_once ( $forum->config );
					
					if ( $forum->component == 'com_kunena' ) {
						$config									=	get_object_vars( CKunenaConfig::getInstance() );
					}
					
					$forum->config								=	$config;
				}
				
				$forum->version									=	$this->getVersion( $forum );
			}
		}
		if ( $forum !== null && isset( $user->id ) ) {
			$forum->userdetails									=	$this->getUserDetails( $user, $forum );
		}
		return $forum;
	}
	
	/**
	 * Get forum version
	 *
	 * @param object $forum
	 * @return string
	 */
	function getVersion( $forum ) {
		global $_CB_database;
		static $version		=	null;

		if ( $version === null ) {
			if ( isset( $forum->config['version'] ) ) {
				$version										=	$forum->config['version'];
			} elseif ( ( $forum->component == 'com_fireboard' ) || ( $forum->component == 'com_kunena' ) ) {
				$query											=	'SELECT '		. $_CB_database->NameQuote( 'version' )
																.	"\n FROM "		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_version' )
																.	"\n ORDER BY "	. $_CB_database->NameQuote( 'id' ) . " DESC"
																;
				$_CB_database->setQuery( $query, 0, 1 );
				$version										=	$_CB_database->loadResult();
			} else {
				$version										=	'0.0.0';
			}
		}
		return $version;
	}
	
	/**
	 * Generate users details
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return object
	 */
	function getUserDetails( $user, $forum ) {
		global $_CB_database, $_CB_framework;
		
		static $usersdetailsCache								=	array();

		if ( ! isset( $usersdetailsCache[$user->id] ) ) {

			if ( ( isset ( $forum->config['showuserstats'] ) ? $forum->config['showuserstats'] : $forum->config['showstats'] ) || ( ! $forum->config['showranking'] && ! $forum->config['showkarma'] && ! ( isset ( $forum->config['poststats'] ) ? $forum->config['poststats'] : $forum->config['postStats'] ) ) ) {
				if ( ( ( $forum->component == 'com_fireboard' ) && ( $forum->version >= '1.0.3' ) ) || ( $forum->component == 'com_kunena' ) ) {
					$supportsDbRanks							=	', ' . $_CB_database->NameQuote( 'rank' );
				} else {
					$supportsDbRanks							=	null;
				}
				
				$userDetails									=	$this->getUserSettings( $user, $forum, $supportsDbRanks );
				
				if ( ( isset( $userDetails->posts ) ) && $userDetails->posts != 0 ) {
					if ( $forum->config['showranking'] ) {
						$uIsAdm									=	isModerator( $user->id );
						$uIsMod									=	$userDetails->moderator;
						$pathImage								=	$_CB_framework->getCfg( 'live_site' ) . '/components/' . $forum->component;
						
						if ( $supportsDbRanks ) {
							
							if ( $userDetails->rank != 0 ) {
								$where							=	$_CB_database->NameQuote( 'rank_id' ) . ' = ' . (int) $userDetails->rank;
							} else {
								$where							=	$_CB_database->NameQuote( 'rank_min' ) . ' <= ' . (int) $userDetails->posts
																.	"\n ORDER BY "	. $_CB_database->NameQuote( 'rank_min' ) . ' DESC';
							}
							
							$query								=	'SELECT '		. $_CB_database->NameQuote( 'rank_title' )
																.	', '			. $_CB_database->NameQuote( 'rank_image' )
																.	"\n FROM "		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_ranks' )
																.	"\n WHERE "		. $where;
							$_CB_database->setQuery( $query, 0, 1 );
							$userRank							=	null;
							$_CB_database->loadObject( $userRank );
							
				         	$pathImage							=	$pathImage . $this->params->get( 'TemplateRank', '/template/default/images/english' );
							$rText								=	$userRank->rank_title;
							$rImg								=	$pathImage . '/ranks/' . $userRank->rank_image;
						} else {
							$userDetails->rank					=	1;
							
							for ( $i = 1; $i <= 5; $i++ ) {
								$rankPrev						=	$forum->config['rank' . ( ( ( $i > 1 ) ? ( $i - 1 ) : $i ) )];
								$rank							=	$forum->config['rank' . $i];
								
								if ( $userDetails->posts <= $rank && ( $userDetails->posts > $rankPrev ) ) {
									$userDetails->rank			=	$i;
								} elseif ( ( $i == 5 ) && ( $userDetails->posts > $rank ) ) {
									$userDetails->rank			=	6;
								}
							}
							
							$rText								=	$forum->config['rank' . $userDetails->rank . 'txt'];
							$rImg								=	$pathImage . '/ranks/rank' . $userDetails->rank . '.gif';
						}
						
						if ( ( $userDetails->rank == 0 ) && $uIsMod ) {
							$rText								=	CBTxt::T( 'Moderator' );
							$rImg								=	$pathImage . '/ranks/rankmod.gif';
						}
						
						if ( ( $userDetails->rank == 0 ) && $uIsAdm ) {
							$rText								=	CBTxt::T( 'Administrator' );
							$rImg								=	$pathImage . '/ranks/rankadmin.gif';
						}
						
						if ( $forum->config['rankimages'] ) {
							$userDetails->msg_userrankimg		=	'<img src="' . htmlspecialchars( $rImg ) . '" alt="' . htmlspecialchars( $rText ) . '" border="0" />';
						}
						
						$userDetails->msg_userrank				=	$rText;
					}
				} else {
					$userDetails								=	false;
				}
			} else {
				$userDetails									=	false;
			}
			$usersdetailsCache[$user->id]						=	$userDetails;
		}
		
		return $usersdetailsCache[$user->id];
	}
	
	/**
	 * Generate users stats values for fields
	 *
	 * @param object $forum
	 * @param array  $params
	 * @return array
	 */
	function getUserStats( $forum ) {
		$stats												=	array();
		
		if ( isset ( $forum->config['showuserstats'] ) ? $forum->config['showuserstats'] : $forum->config['showstats'] ) {
			if ( $forum->config['showranking'] && ( $this->params->get( 'statRanking', 1 ) == 1 ) && ( $forum->userdetails !== false ) ) {
				$stats['forumrank']							=	$forum->userdetails->msg_userrank . ( $this->params->get('statRankingImg', 1 ) == 1 ? '<br />' . $forum->userdetails->msg_userrankimg : null );
			}
			if ( ( isset ( $forum->config['poststats'] ) ? $forum->config['poststats'] : $forum->config['postStats'] ) && ( ( $this->params->get( 'statPosts', 1 ) == 2 ) || ( ( $this->params->get( 'statPosts', 1 ) == 1 ) && ( $forum->userdetails !== false ) ) ) ) {
				$stats['forumposts']						=	( ( $forum->userdetails !== false ) ? $forum->userdetails->posts : 0 );
			}
		}
		
		if ( $forum->config['showkarma'] && ( $forum->userdetails !== false ) && ( ( $this->params->get( 'statKarma', 1 ) == 2 ) || ( ( $this->params->get( 'statKarma', 1 ) == 1 ) && ( $forum->userdetails->karma != 0 ) ) ) ) {
			$stats['forumkarma']							=	$forum->userdetails->karma;
		}
		
		return $stats;
	}
	
	/**
	 * Delete users specific subscription
	 *
	 * @param moscomprofilerUser $user
	 * @param int                $thread
	 * @param string             $prefix
	 */
	function _unsubThread( $user, $thread, $forum ) {
		global $_CB_database;
		
		$query												=	'DELETE FROM '	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_subscriptions' )
															.	"\n WHERE "		. $_CB_database->NameQuote( 'userid' )	. " = " . (int) $user->id
															.	"\n AND "		. $_CB_database->NameQuote( 'thread' )	. " = " . (int) $thread
															;
		$_CB_database->setQuery( $query );
		if ( ! $_CB_database->query() ) {
			trigger_error( 'CBForum-unsubThread SQL error' . $_CB_database->stderr( true ), E_USER_WARNING );
		}
	}
	
	/**
	 * Delete all users subscriptions
	 *
	 * @param moscomprofilerUser $user
	 * @param string             $prefix
	 */
	function _unsubAll( $user, $forum ) {
		global $_CB_database;
		
		$query												=	'DELETE FROM '	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_subscriptions' )
															.	"\n WHERE "		. $_CB_database->NameQuote( 'userid' )	. " = " . (int) $user->id
															;
		$_CB_database->setQuery( $query );
		if ( ! $_CB_database->query() ) {
			trigger_error( 'CBForum-unsubAll SQL error' . $_CB_database->stderr( true ), E_USER_WARNING );
		}
	}
	
	/**
	 * Delete users specific favorite
	 *
	 * @param moscomprofilerUser $user
	 * @param int                $thread
	 * @param string             $prefix
	 */
	function _unfavThread( $user, $thread, $forum ) {
		global $_CB_database;
		
		$query												=	'DELETE FROM '	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_favorites' )
															.	"\n WHERE "		. $_CB_database->NameQuote( 'userid' )	. " = " . (int) $user->id
															.	"\n AND "		. $_CB_database->NameQuote( 'thread' )	. " = " . (int) $thread
															;
		$_CB_database->setQuery( $query );
		if ( ! $_CB_database->query() ) {
			trigger_error( 'CBForum-unfavThread SQL error' . $_CB_database->stderr( true ), E_USER_WARNING );
		}
	}
	
	/**
	 * Delete all users favorites
	 *
	 * @param moscomprofilerUser $user
	 * @param string             $prefix
	 */
	function _unfavAll( $user, $forum ) {
		global $_CB_database;
		
		$query												=	'DELETE FROM '	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_favorites' )
															.	"\n WHERE "		. $_CB_database->NameQuote( 'userid' )	. " = " . (int) $user->id
															;
		$_CB_database->setQuery( $query );
		if ( ! $_CB_database->query() ) {
			trigger_error( 'CBForum-unfavAll SQL error' . $_CB_database->stderr( true ), E_USER_WARNING );
		}
	}
	
	/**
	 * Returns value of field
	 *
	 * @param moscomprofilerUser $user
	 * @param mixed              $field
	 * @param string             $output
	 * @param string             $reason
	 * @return mixed
	 */
	function getFieldValue( $user, $field, $output = 'html', $reason = 'profile' ) {
		$cbUser												=	CBuser::getInstance( $user->id );
		$format												=	$cbUser->getField( $field, null, $output, 'none', $reason );
		
		return $format;
	}
	
	/**
	 * Generates icon display of field
	 *
	 * @param mixed  $value
	 * @param string $title
	 * @param string $icon
	 * @param string $type
	 * @param string $mode
	 * @return mixed
	 */
	function getFieldIcon( $value, $title, $icon, $type = null, $mode = 'kunena' ) {
		global $_CB_framework;
		
		if ( $mode == 'kunena' ) {
			$iconPath										=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_kunena/template/default/images/english/icons/';
		} elseif ( $mode == 'cb' ) {
			$iconPath										=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/images/';
		} elseif ( $mode == 'images' ) {
			$iconPath										=	$_CB_framework->getCfg( 'live_site' ) . '/images/M_images/';
		}
		
		$value												=	explode( '|*|', $value );
		if ( count( $value ) < 2) {
			$value[1]										=	$value[0];
		}
		
		$format												=	'<img border="0" style="padding:2px;" src="' . $iconPath . $icon . '" title="' . htmlspecialchars( $title . $value[1] ) . '" alt="' . htmlspecialchars( $title . $value[1] ) . '" />';
		
		if ( $type == 'website' ) {
			$format											=	'<a href="' . htmlspecialchars( $value[0] ) . '" target="_blank">' . $format . '</a>';
		} elseif ( $type == 'icq' ) {
			$format											=	'<a href="http://www.icq.com/people/cmd.php?uin=' . htmlspecialchars( $value[0] ) . '&action=message" target="_blank">' . $format . '</a>';
		}
		
		return ( $value[0] ? $format : null );
	}
	
	/**
	 * Generates icon for online status
	 *
	 * @param moscomprofilerUser $user
	 * @param string             $mode
	 * @return mixed
	 */
	function getStatusIcon( $user, $mode = 'kunena' ) {
		$value												=	$this->getFieldValue( $user, 'onlinestatus', 'csv' );
		
		if ( $value == 'true' ) {
			$icon											=	'online';
			$status											=	CBTxt::T( 'ONLINE' );
		} else {
			$icon											=	'offline';
			$status											=	CBTxt::T( 'OFFLINE' );
		}
		
		$format												=	$this->getFieldIcon( $status, CBTxt::T( 'Online Status: ' ), $icon . '.gif', null, $mode );
		
		return $format;
	}
	
	/**
	 * Generates icon for profile link
	 *
	 * @param moscomprofilerUser $user
	 * @param string             $mode
	 * @return mixed
	 */
	function getProfileIcon( $user, $mode = 'kunena' ) {
		$icon												=	$this->getFieldIcon( $user->username, CBTxt::T( 'View Profile: ' ), 'profile.gif', null, $mode );
		$url												=	cbSef( 'index.php?option=com_comprofiler&amp;task=userProfile&amp;user=' . (int) $user->id . getCBprofileItemid( true ) );
		$format												=	'<a href="' . $url . '">' . $icon . '</a>';
		
		return $format;
	}
	
	/**
	 * Generates icon for PM link
	 *
	 * @param moscomprofilerUser $user
	 * @param string             $mode
	 * @return mixed
	 */
	function getPMIcon( $user, $mode = 'kunena' ) {
		global $_CB_PMS, $_CB_framework;
		
		$url												=	null;
		
		if ( $user->id ) {
			$resultArray									=	$_CB_PMS->getPMSlinks( $user->id, $_CB_framework->myId(), null, null, 1 );
			
			if ( count( $resultArray ) > 0 ) {
				foreach ( $resultArray as $res ) {
					if ( is_array( $res ) ) {
						$url								=	cbSef( $res['url'] );
					}
				}
			}
			
			if ( $url ) {
				$icon										=	$this->getFieldIcon( $user->username, CBTxt::T( 'Send Private Message: ' ), 'pm.gif', null, $mode );
				$format										=	'<a href="' . $url . '">' . $icon . '</a>';
			}
		}
		
		return ( $url ? $format : null );
	}
	
	/**
	 * Get viewable categories
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return array
	 */
	function getAllowedCategories( $user, $forum ) {
		global $_CB_framework, $_CB_database;
		
		$categories											=	null;
		
		if ( $_CB_framework->myId() != $user->id ) {
			$query											=	'SELECT ' 	. $_CB_database->NameQuote( 'allowed' )
															.	"\n FROM " 	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_sessions' )
															.	"\n WHERE "	. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $_CB_framework->myId()
															;
			$_CB_database->setQuery( $query, 0, 1 );
			$categories										=	$_CB_database->loadResult();
			if ( ! $categories ) {
				$query										=	'SELECT ' 	. $_CB_database->NameQuote( 'id' )
															.	"\n FROM " 	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_categories' )
															.	"\n WHERE "	. $_CB_database->NameQuote( 'published' )	. ' = 1'
															.	"\n AND "	. $_CB_database->NameQuote( 'pub_access' )	. ' = 0'
															;
				$_CB_database->setQuery( $query );
				$categories									=	implode( ',', $_CB_database->loadResultArray() );
			}
		}
		
		return ( $categories && ( strtolower( $categories) != 'na' ) ? $categories : null );
	}
	
	/**
	 * Count users total posts for paging
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return int
	 */
	function getUserPostTotal( $user, $forum ) {
		global $_CB_database;

		$cache												=	array();

		if ( ! isset( $cache[$user->id] ) ) {
			$categories										=	$this->getAllowedCategories( $user, $forum );
			$pagingParams									=	$this->_getPaging( array(), array( 'fposts_' ) );
			
			$query											=	'SELECT COUNT(*)'
															.	"\n FROM " 		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages' ) . ' AS a'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_categories' ) . ' AS b'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages' ) . ' AS c'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages_text' ) . ' AS d'
															.	"\n WHERE a."	. $_CB_database->NameQuote( 'catid' )	. ' = b.' . $_CB_database->NameQuote( 'id' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'thread' )	. ' = c.' . $_CB_database->NameQuote( 'id' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'id' )	. ' = d.' . $_CB_database->NameQuote( 'mesid' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'hold' )	. ' = 0'
															.	"\n AND b."		. $_CB_database->NameQuote( 'published' )	. ' = 1'
															.	"\n AND a."		. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $user->id
															.	( $categories != null ? "\n AND b." . $_CB_database->NameQuote( 'id' ) . " IN ( " . $categories . " )" : null )
															.	( $pagingParams['fposts_search'] ? "\n AND ( a." . $_CB_database->NameQuote( 'subject' ) . " LIKE '%" . cbEscapeSQLsearch( cbGetEscaped( $pagingParams['fposts_search'] ) ) . "%' OR d." . $_CB_database->NameQuote( 'message' ) . " LIKE '%" . cbEscapeSQLsearch( $pagingParams['fposts_search'] ) . "%' )" : null )
															;
			$_CB_database->setQuery( $query );
			$total											=	$_CB_database->loadResult();
			
			$cache[$user->id]								=	( $total && is_numeric( $total ) ? $total : null );
		}
		return $cache[$user->id];
	}
	
	/**
	 * Puts users posts into array
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return object
	 */
	function getUserPosts( $user, $forum ) {
		global $_CB_database;
		
		$categories											=	$this->getAllowedCategories( $user, $forum );
		$pagingParams										=	$this->_getPaging( array(), array( 'fposts_' ) );
		$postsNumber										=	$this->params->get( 'postsNumber', 10 );
		
		switch ( $pagingParams['fposts_sortby'] ) {
			case 'subjectASC':
				$order										=	'a.' . $_CB_database->NameQuote( 'subject' ) . ' ASC';
			break;
			case 'subjectDESC':
				$order										=	'a.' . $_CB_database->NameQuote( 'subject' ) . ' DESC';
			break;
			case 'categoryASC':
				$order										=	'b.' . $_CB_database->NameQuote( 'id' ) . ' ASC';
			break;
			case 'categoryDESC':
				$order										=	'b.' . $_CB_database->NameQuote( 'id' ) . ' DESC';
			break;
			case 'hitsASC':
				$order										=	'c.' . $_CB_database->NameQuote( 'hits' ) . ' ASC';
			break;
			case 'hitsDESC':
				$order										=	'c.' . $_CB_database->NameQuote( 'hits' ) . ' DESC';
			break;
			case 'dateASC':
				$order										=	'a.' . $_CB_database->NameQuote( 'time' ) . ' ASC';
			break;
			case 'dateDESC':
			default:
				$order										=	'a.' . $_CB_database->NameQuote( 'time' ) . ' DESC';
			break;
		}
		
		$query												=	'SELECT a.*'
															.	', b.'			. $_CB_database->NameQuote( 'id' ) . ' AS category'
															.	', b.'			. $_CB_database->NameQuote( 'name' ) . ' AS catname'
															.	', c.'			. $_CB_database->NameQuote( 'hits' ) . ' AS threadhits'
															.	"\n FROM " 		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages' ) . ' AS a'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_categories' ) . ' AS b'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages' ) . ' AS c'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages_text' ) . ' AS d'
															.	"\n WHERE a."	. $_CB_database->NameQuote( 'catid' )	. ' = b.' . $_CB_database->NameQuote( 'id' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'thread' )	. ' = c.' . $_CB_database->NameQuote( 'id' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'id' )	. ' = d.' . $_CB_database->NameQuote( 'mesid' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'hold' )	. ' = 0'
															.	"\n AND b."		. $_CB_database->NameQuote( 'published' )	. ' = 1'
															.	"\n AND a."		. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $user->id
															.	( $categories != null ? "\n AND b." . $_CB_database->NameQuote( 'id' ) . " IN ( " . $categories . " )" : null )
															.	( $pagingParams['fposts_search'] ? "\n AND ( a." . $_CB_database->NameQuote( 'subject' ) . " LIKE '%" . cbEscapeSQLsearch( cbGetEscaped( $pagingParams['fposts_search'] ) ) . "%' OR d." . $_CB_database->NameQuote( 'message' ) . " LIKE '%" . cbEscapeSQLsearch( $pagingParams['fposts_search'] ) . "%' )" : null )
															.	"\n ORDER BY "	. $order
															.	"\n LIMIT "		. (int) ( $pagingParams['fposts_limitstart'] ? $pagingParams['fposts_limitstart'] : 0 ) . ',' . (int) $postsNumber;
															;
		$_CB_database->setQuery( $query );
		$posts												=	$_CB_database->loadObjectList();
		
		return ( $posts ? $posts : null );
	}
	
	/**
	 * Count users total subscriptions for paging
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return int
	 */
	function getUserSubscriptionsTotal( $user, $forum ) {
		global $_CB_database;
		
		$query												=	'SELECT COUNT(*)'
															.	"\n FROM " 	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_subscriptions' )
															.	"\n WHERE "	. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $user->id
															;
		$_CB_database->setQuery( $query );
		$total												=	$_CB_database->loadResult();
		
		return ( $total && is_numeric( $total ) ? $total : null );
	}
	
	/**
	 * Puts users subscription posts into object
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return object
	 */
	function getUserSubscriptions( $user, $forum ) {
		global $_CB_database;
		
		$pagingParams										=	$this->_getPaging( array(), array( 'fsubs_' ) );
		$postsNumber										=	$this->params->get( 'postsNumber', 10 );
		
		switch ( $pagingParams['fsubs_sortby'] ) {
			case 'subjectASC':
				$order										=	'a.' . $_CB_database->NameQuote( 'subject' ) . ' ASC';
			break;
			case 'subjectDESC':
				$order										=	'a.' . $_CB_database->NameQuote( 'subject' ) . ' DESC';
			break;
			case 'categoryASC':
				$order										=	'b.' . $_CB_database->NameQuote( 'id' ) . ' ASC';
			break;
			case 'categoryDESC':
				$order										=	'b.' . $_CB_database->NameQuote( 'id' ) . ' DESC';
			break;
			case 'dateASC':
				$order										=	'a.' . $_CB_database->NameQuote( 'time' ) . ' ASC';
			break;
			case 'dateDESC':
			default:
				$order										=	'a.' . $_CB_database->NameQuote( 'time' ) . ' DESC';
			break;
		}

		$query												=	'SELECT a.*'
															.	', b.'			. $_CB_database->NameQuote( 'name' ) . ' AS catname'
															.	"\n FROM " 		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages' ) . ' AS a'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_categories' ) . ' AS b'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_subscriptions' ) . ' AS s'
															.	"\n WHERE a."	. $_CB_database->NameQuote( 'id' )	. ' = s.' . $_CB_database->NameQuote( 'thread' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'catid' )	. ' = b.' . $_CB_database->NameQuote( 'id' )
															.	"\n AND s."		. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $user->id
															.	"\n ORDER BY " . $order
															.	"\n LIMIT "		. (int) ( $pagingParams['fsubs_limitstart'] ? $pagingParams['fsubs_limitstart'] : 0 ) . ',' . (int) $postsNumber;
															;
		$_CB_database->setQuery( $query );
		$subs												=	$_CB_database->loadObjectList();
		
		return ( $subs ? $subs : null );
	}

	/**
	 * Count users total favorites for paging
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return int
	 */
	function getUserFavoritesTotal( $user, $forum ) {
		global $_CB_database;
		
		$query												=	'SELECT COUNT(*)'
															.	"\n FROM " 	. $_CB_database->NameQuote( '#__' . $forum->prefix . '_favorites' )
															.	"\n WHERE "	. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $user->id
															;
		$_CB_database->setQuery( $query );
		$total												=	$_CB_database->loadResult();
		
		return ( $total && is_numeric( $total ) ? $total : null );
	}
	
	/**
	 * Puts users subscription posts into object
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @return object
	 */
	function getUserFavorites( $user, $forum ) {
		global $_CB_database;
		
		$pagingParams										=	$this->_getPaging( array(), array( 'ffavs_' ) );
		$postsNumber										=	$this->params->get( 'postsNumber', 10 );
		
		switch ( $pagingParams['ffavs_sortby'] ) {
			case 'subjectASC':
				$order										=	'a.' . $_CB_database->NameQuote( 'subject' ) . ' ASC';
			break;
			case 'subjectDESC':
				$order										=	'a.' . $_CB_database->NameQuote( 'subject' ) . ' DESC';
			break;
			case 'categoryASC':
				$order										=	'b.' . $_CB_database->NameQuote( 'id' ) . ' ASC';
			break;
			case 'categoryDESC':
				$order										=	'b.' . $_CB_database->NameQuote( 'id' ) . ' DESC';
			break;
			case 'dateASC':
				$order										=	'a.' . $_CB_database->NameQuote( 'time' ) . ' ASC';
			break;
			case 'dateDESC':
			default:
				$order										=	'a.' . $_CB_database->NameQuote( 'time' ) . ' DESC';
			break;
		}

		$query												=	'SELECT a.*'
															.	', b.'			. $_CB_database->NameQuote( 'name' ) . ' AS catname'
															.	"\n FROM " 		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_messages' ) . ' AS a'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_categories' ) . ' AS b'
															.	', ' 			. $_CB_database->NameQuote( '#__' . $forum->prefix . '_favorites' ) . ' AS s'
															.	"\n WHERE a."	. $_CB_database->NameQuote( 'id' )	. ' = s.' . $_CB_database->NameQuote( 'thread' )
															.	"\n AND a."		. $_CB_database->NameQuote( 'catid' )	. ' = b.' . $_CB_database->NameQuote( 'id' )
															.	"\n AND s."		. $_CB_database->NameQuote( 'userid' )	. ' = ' . (int) $user->id
															.	"\n ORDER BY " . $order
															.	"\n LIMIT "		. (int) ( $pagingParams['ffavs_limitstart'] ? $pagingParams['ffavs_limitstart'] : 0 ) . ',' . (int) $postsNumber;
															;
		$_CB_database->setQuery( $query );
		$subs												=	$_CB_database->loadObjectList();
		
		return ( $subs ? $subs : null );
	}
	
	/**
	 * Builds and returns table titles with sorting
	 *
	 * @param array  $pagingParams
	 * @param string $sort
	 * @return object
	 */
	function getTableTitles( $pagingParams, $sort ) {
		global $_CB_framework;
		
		$sortImg										=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbsimpleboardtab/images/';
		$ascImg											=	'<img border="0" alt="ASC" src="' . $sortImg . 'asc.gif" />';
		$descImg										=	'<img border="0" alt="DESC" src="' . $sortImg . 'desc.gif" />';

		$title											=	new stdClass();
		$title->date									=	CBTxt::T( 'Date' ) . str_replace( '>' . CBTxt::T( 'Date' ) . '<', '>' . $descImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'dateDESC', CBTxt::T( 'Date' ), true ) ) . str_replace( '>' . CBTxt::T( 'Date' ) . '<', '>' . $ascImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'dateASC', CBTxt::T( 'Date' ) ) );
		$title->subject									=	CBTxt::T( 'Subject' ) . str_replace( '>' . CBTxt::T( 'Subject' ) . '<', '>' . $descImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'subjectDESC', CBTxt::T( 'Subject' ) ) ) . str_replace( '>' . CBTxt::T( 'Subject' ) . '<', '>' . $ascImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'subjectASC', CBTxt::T( 'Subject' ) ) );
		$title->category								=	CBTxt::T( 'Category' ) . str_replace( '>' . CBTxt::T( 'Category' ) . '<', '>' . $descImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'categoryDESC', CBTxt::T( 'Category' ) ) ) . str_replace( '>' . CBTxt::T( 'Category' ) . '<', '>' . $ascImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'categoryASC', CBTxt::T( 'Category' ) ) );
		$title->hits									=	CBTxt::T( 'Hits' ) . str_replace( '>' . CBTxt::T( 'Hits' ) . '<', '>' . $descImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'hitsDESC', CBTxt::T( 'Hits' ) ) ) . str_replace( '>' . CBTxt::T( 'Hits' ) . '<', '>' . $ascImg . '<', $this->_writeSortByLink( $pagingParams, $sort, 'hitsASC', CBTxt::T( 'Hits' ) ) );
	
		return $title;
	}
	
	/**
	 * Puts user forum settings into object
	 *
	 * @param moscomprofilerUser $user
	 * @param object             $forum
	 * @param mixed              $additional
	 * @return object
	 */
	function getUserSettings( $user, $forum, $additional = null ) {
		global $_CB_database;

		$cache												=	array();

		if ( ! isset( $cache[$user->id] ) ) {
			$query											=	'SELECT f.*'
															.	$additional
															.	"\n FROM "		. $_CB_database->NameQuote( '#__' . $forum->prefix . '_users' ) . 'AS f'
															.	', '			. $_CB_database->NameQuote( '#__users' ) . 'AS u'
															.	"\n WHERE f."	. $_CB_database->NameQuote( 'userid' )	. " = u." . $_CB_database->NameQuote( 'id' )
															.	"\n AND f."		. $_CB_database->NameQuote( 'userid' )	. " = " . (int) $user->id
															;
			$_CB_database->setQuery( $query, 0, 1 );
			$settings										=	null;
			$_CB_database->loadObject( $settings );
			
			$cache[$user->id]								=	( $settings ? $settings : null );
		}
		return $cache[$user->id];
	}
	
	/**
	 * Generates forum parameters based on forum type
	 *
	 * @param array $params
	 * @return object
	 */
	function getForumParams() {
		global $_CB_framework, $_CB_database;
		
		$forumType											=	(int) $this->params->get( 'forumType', 0 );
		$path												=	$_CB_framework->getCfg( 'absolute_path' );

		$forumParams										=	new stdClass();
		if ( in_array( $forumType, array( 0, 2 ) ) && file_exists( $path . '/administrator/components/com_joomlaboard/joomlaboard_config.php' ) ) {
			$forumParams->component							=	'com_joomlaboard';
			$forumParams->prefix							=	'sb';
			$forumParams->config							=	$path . '/administrator/components/com_joomlaboard/joomlaboard_config.php';
		} else if ( in_array( $forumType, array( 0, 3 ) ) && file_exists( $path . '/administrator/components/com_simpleboard/simpleboard_config.php' ) ) {
			$forumParams->component							=	'com_simpleboard';
			$forumParams->prefix							=	'sb';
			$forumParams->config							=	$path . '/administrator/components/com_simpleboard/simpleboard_config.php';
		} elseif ( in_array( $forumType, array( 0, 1 ) ) && file_exists( $path . '/administrator/components/com_fireboard/' ) ) {
			$forumParams->component							=	'com_fireboard';
			$forumParams->prefix							=	'fb';
			if ( file_exists( $path . '/administrator/components/com_fireboard/fireboard_config.php' ) ) {
				$forumParams->config						=	$path . '/administrator/components/com_fireboard/fireboard_config.php';
			} else {
				$forumParams->config						=	null;
			}
		} elseif ( in_array( $forumType, array( 0, 4 ) ) && file_exists( $path . '/administrator/components/com_kunena/' ) ) {
			$forumParams->component							=	'com_kunena';
			$forumParams->prefix							=	'fb';
			if ( file_exists( $path . '/components/com_kunena/lib/kunena.config.class.php' ) ) {
				$forumParams->config						=	$path . '/components/com_kunena/lib/kunena.config.class.php';
			} else {
				$forumParams->config						=	null;
			}
		} else {
			return null;
		}
		
		
		if ( $forumParams->component ) {
			$query											=	'SELECT '	. $_CB_database->NameQuote( 'id' )
															.	"\n FROM "	. $_CB_database->NameQuote( '#__menu' )
															.	"\n WHERE "	. $_CB_database->NameQuote( 'link' )	. " LIKE " . $_CB_database->Quote( '%' . $forumParams->component . '%' )
															;
			$_CB_database->setQuery( $query );
			$forumParams->itemid							=	'&amp;Itemid=' . $_CB_database->loadResult();
		}
		
		return $forumParams;
	}
	
	/**
	 * Function for the backend XML
	 *
	 * @param  string  $name          Name of the control
	 * @param  string  $value         Current value
	 * @param  string  $control_name  Name of the controlling array (if any)
	 * @return string                 HTML for the control data part or FALSE in case of error
	 */
	function loadExistingForums( $name, $value, $control_name ) {
		global $_CB_framework;
		
		$img_path											=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/images/';
		$installed											=	'<img src="' . $img_path . 'approve.png" alt="Installed" title="Installed" border="0" />';
		$uninstalled										=	'<img src="' . $img_path . 'reject.png" alt="Uninstalled" title="Uninstalled" border="0" />';
		$path												=	$_CB_framework->getCfg( 'absolute_path' );
		$forums												=	null;

		if ( file_exists( $path . '/administrator/components/com_joomlaboard/' ) ) {
			$forums											.=	'<div>' . $installed . ' Joomlaboard</div>';
		} else {
			$forums											.=	'<div>' . $uninstalled . ' Joomlaboard</div>';
		}
		
		if ( file_exists( $path . '/administrator/components/com_simpleboard/' ) ) {
			$forums											.=	'<div>' . $installed . ' Simpleboard</div>';
		} else {
			$forums											.=	'<div>' . $uninstalled . ' Simpleboard</div>';
		}
		
		if ( file_exists( $path . '/administrator/components/com_fireboard/' ) ) {
			$forums											.=	'<div>' . $installed . ' Fireboard</div>';
		} else {
			$forums											.=	'<div>' . $uninstalled . ' Fireboard</div>';
		}
		
		if ( file_exists( $path . '/administrator/components/com_kunena/' ) ) {
			$forums											.=	'<div>' . $installed . ' Kunena (It is advised to select Kunena manually as Kunena has additional options)</div>';
		} else {
			$forums											.=	'<div>' . $uninstalled . ' Kunena</div>';
		}
		
		return $forums;
	}
} //end of getForumModel