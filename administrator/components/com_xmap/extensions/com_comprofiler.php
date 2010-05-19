<?php
/**
* @author Joomla-R-Us, http://joomla-r-us.com
* @email admin@joomla-r-us.com
* @version $Id: com_comprofiler.php, version 1.1
* @package Xmap
* @license GNU/GPL
* @description   Xmap plugin for Community Builder (User Lists)
*
* @Acknowledgement:
*
*   Parts of the code based on :
*
*       Community Builder 1.1 UserLists Integrator for SEF Service Map
*                                    by
*                              Radslaw Kubera
*
*       http://extensions.joomla.org/extensions/extension-specific/sef-service-map-extensions/6650
*/

defined( '_VALID_MOS' ) or defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/** Adds support for Community Builder User Lists to Xmap */
class xmap_com_comprofiler {
 
	/*
	* This function is called before a menu item is printed. We use it to set the
	* proper uniqueid for the item
	*/
	function prepareMenuItem(&$node) {
           return true;
	}

	/** Get the content tree for this kind of content */
	function getTree( &$xmap, &$parent, &$params ) {
	
		if (defined('JPATH_SITE')) {
			$mosConfig_absolute_path = JPATH_SITE;
		} else {
			global $mosConfig_absolute_path;
		}

		$show_plinks = xmap_com_comprofiler::getParam($params,'show_plinks',1);

		$priority = xmap_com_comprofiler::getParam($params,'user_priority',$parent->priority);
		$changefreq = xmap_com_comprofiler::getParam($params,'user_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['user_priority'] = $priority;
		$params['user_changefreq'] = $changefreq;


		if ( ! file_exists($mosConfig_absolute_path . '/components/com_comprofiler/comprofiler.php') ) {
			return false;
		}
		
		include JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_comprofiler'.DS.'ue_config.php';

		if ($ueConfig['allow_profilelink']==1) {
		
			// Traverse the User Lists
			xmap_com_comprofiler::getUserListTree($xmap, $parent, $params, $ueConfig['name_format'], $show_plinks);
			
			return true;
			
		} else {
			return false;
		
		}
	
	
		
	}
	

	/** CB User Lists */
	function getUserListTree( &$xmap, &$parent,&$params , $format, $show_plinks) {
	

		if (defined('JPATH_SITE')) {
			$database = &JFactory::getDBO();
			$mosConfig_absolute_path = JPATH_SITE;
		} else {
			global $database,$mosConfig_absolute_path;
		}
		global $my;
		$uid	= (int) $my->id;
		
		
		//find default list
		$useraccessgroupSQL	= " AND useraccessgroupid IN (".implode(',',xmap_com_comprofiler::xmap_com_comprofiler_getChildGIDS(xmap_com_comprofiler::xmap_com_comprofiler_userGID($uid,$database),$database)).")";
		$query = "SELECT * FROM #__comprofiler_lists WHERE published=1 " . $useraccessgroupSQL . " ORDER BY ordering";
		$database->setQuery($query);
		$lists = $database->loadObjectList();

		$xmap->changeLevel(1);
		foreach($lists as $list) {
			if (xmap_com_comprofiler::xmap_com_comprofiler_allowAccess( $database, $list->useraccessgroupid,'RECURSE', xmap_com_comprofiler::xmap_com_comprofiler_userGID($uid,$database))) {
				$node = new stdclass;
				$node->id = $parent->id;
				$node->uid = $parent->uid.'l'.$list->listid;
				$node->browserNav = $parent->browserNav;
				$node->name = stripslashes($list->title);

				$node->modified = intval(strtotime('now'));
				$node->priority = $params['user_priority'];
				$node->changefreq = $params['user_changefreq'];
				$node->link = $parent->link.'&amp;id='.$list->listid;
				$node->pid = 1;	// parent id
				if ($xmap->printNode($node) !== FALSE ) {
					if ( $show_plinks == 1 
				  		|| ( $show_plinks == 2 && $xmap->view == 'xml')
				  		|| ( $show_plinks == 3 && $xmap->view == 'html')) {					
						xmap_com_comprofiler::getUserList( $xmap, $parent, $params, $list->listid, 0, $list, $format);
					}
				}
			}

	    	}
	    	$xmap->changeLevel(-1);
	    	
	}
		
	/** CB User List (users)  */
	function getUserList( &$xmap, &$parent,&$params, $sectionid=0, $p_id=0, $list, $format ) {

	
		if (defined('JPATH_SITE')) {
			$database = &JFactory::getDBO();
			$mosConfig_absolute_path = JPATH_SITE;
		} else {
			global $database,$mosConfig_absolute_path;
		}
		

		$usergids = explode(",",$list->usergroupids);
		foreach( $usergids AS $usergid ) {
			$allusergids[]		=	$usergid;
			if ($usergid==29 || $usergid==30) {
				$groupchildren	=	array();
				$version = com_comprofiler_bot_checkJversion();
				if ($version==0) $groupchildren	= com_comprofiler_bot_get_group_children_version_0($database, $usergid, 'ARO','RECURSE' );
				else com_comprofiler_bot_get_group_children_version_1($database, $usergid, 'ARO','RECURSE' );
				$allusergids	=	array_merge($allusergids,$groupchildren);
			}
		}
		$usergids = implode( ",", $allusergids );
				
		$queryFrom = "FROM #__users u, #__comprofiler ue WHERE u.id = ue.id AND u.block = 0 AND ue.approved = 1 AND ue.banned = 0 AND ue.confirmed = 1 AND u.gid IN (".$usergids.")";

		$query = "SELECT *, '' AS 'NA' " . $queryFrom . " order by  " . $list->sortfields;
		$database->setQuery($query);
		$users = $database->loadObjectList();
		
		$xmap->changeLevel(1);
		foreach($users as $user) {

			$node = new stdclass;
			$node->id = $parent->id;
			$node->uid = $parent->uid.'u'.$user->id;
			$node->browserNav = $parent->browserNav;

			switch ($format) {
				case 2: $node->name = stripslashes($user->name.' ('.$user->username.')'); break;
				case 3: $node->name = stripslashes($user->username); break;
				case 4: $node->name = stripslashes($user->username.' ('.$user->name.')'); break;
				case 1:
				default: $node->name = stripslashes($user->name); break;
			}
			
			$lastupdate = strtotime($user->lastupdatedate);
			$registered = strtotime($user->registerDate);
			if ($registered>$lastupdate) $node->modified=$user->registerDate; else $node->modified=$user->lastupdatedate;
			
			
			
			$node->priority = $params['user_priority'];
			$node->changefreq = $params['user_changefreq'];

			$link="index.php?option=com_comprofiler&amp;task=userProfile&amp;user=".$user->id."&amp;Itemid=" . $parent->id;			
			$node->link = $link;
			
			$node->pid = 0;		// parent id
		    	$xmap->printNode($node);
	    	}		
		
		$xmap->changeLevel(-1);


        }


	function &getParam($arr, $name, $def) {
		if ( defined('JPATH_SITE') ) {
			$var = JArrayHelper::getValue( $arr, $name, $def, '' );
		} else {
			$var = xmap_com_comprofiler::getParam( $arr, $name, $def);
		}
		return $var;
	}
	

	function xmap_com_comprofiler_getChildGIDS( $gid,$_CB_database ) {
		static $gidsArry			=	array();	// cache
		$gid		=	(int) $gid;

		if ( ! isset( $gidsArry[$gid] ) ) {
			if ( xmap_com_comprofiler::xmap_com_comprofiler_checkJversion() <= 0 ) {
			$query	=	"SELECT g1.group_id, g1.name"
				."\n FROM #__core_acl_aro_groups g1"
				."\n LEFT JOIN #__core_acl_aro_groups g2 ON g2.lft >= g1.lft"
				."\n WHERE g2.group_id =" . (int) $gid
				."\n ORDER BY g1.name";
			} else {
			$query	=	"SELECT g1.id AS group_id, g1.name"
				."\n FROM #__core_acl_aro_groups g1"
				."\n LEFT JOIN #__core_acl_aro_groups g2 ON g2.lft >= g1.lft"
				."\n WHERE g2.id =" . (int) $gid
				."\n ORDER BY g1.name";
			}
			$standardlist		=	array( -2 );
			if( $gid > 0) {
				$standardlist[]	=	-1;
			}
		$_CB_database->setQuery( $query );
			$gidsArry[$gid]		=	$_CB_database->loadResultArray();
			if ( ! is_array( $gidsArry[$gid] ) ) {
			$gidsArry[$gid]	=	array();
		}
			$gidsArry[$gid]		=	array_merge( $gidsArry[$gid], $standardlist );
		}
		return $gidsArry[$gid];
	}
	

	function xmap_com_comprofiler_userGID( $oID,$_CB_database ){

		static $uidArry			=	array();	// cache

		$oID					=	(int) $oID;
		if ( ! isset( $uidArry[$oID] ) ) {
			if( $oID > 0 ) {
				$query			=	"SELECT gid FROM #__users WHERE id = ".(int) $oID;
				$_CB_database->setQuery( $query );
				$uidArry[$oID]	=	$_CB_database->loadResult();
			}
			else {
				$uidArry[$oID]	=	0;
			}
		}
		return $uidArry[$oID];
	}
	
	function xmap_com_comprofiler_checkJversion() {
		global $_VERSION;

		static $version	=	null;

		if ( $version !== null ) {
			return $version;
		}

		$version = 1;
		if ( @$_VERSION->PRODUCT == "Mambo" ) {
			if ( strncasecmp( $_VERSION->RELEASE, "4.6", 3 ) < 0 ) {
				$version = 0;
			} else {
				$version = -1;
			}
		} elseif ( @$_VERSION->PRODUCT == "Elxis" ) {
			$version	 = 0;
		} elseif ( (@$_VERSION->PRODUCT == "Joomla!") || (@$_VERSION->PRODUCT == "Accessible Joomla!") ) {
			if (strncasecmp($_VERSION->RELEASE, "1.0", 3)) {
				$version = 1;
			} else {
				$version = 0;
			}
		}
		return $version;
	}	


	function xmap_com_comprofiler_allowAccess( $_CB_database, $accessgroupid, $recurse, $usersgroupid) {

		if ($accessgroupid == -2 || ($accessgroupid == -1 && $usersgroupid > 0)) {
			//grant public access or access to all registered users
			return true;
		}
		else {
			//need to do more checking based on more restrictions
			if( $usersgroupid == $accessgroupid ) {
				//direct match
				return true;
			}
			else {
				if ($recurse=='RECURSE') {
					$groupchildren=array();
					$groupchildren=com_comprofiler_bot_getParentGIDS($_CB_database,$accessgroupid);
					if ( is_array( $groupchildren ) && count( $groupchildren ) > 0) {
						if ( in_array($usersgroupid, $groupchildren) ) {
							//match
							return true;
						}
					}
				}
			}

			//deny access
			return false;
		}
	}
}