<?php
/**
* @author Joomla-R-Us, http://joomla-r-us.com
* @email admin@joomla-r-us.com
* @version $Id: com_phocadownload.php, version 1.0
* @package Xmap
* @license GNU/GPL
* @description Xmap plugin for Phoca Download component
*/

defined( '_VALID_MOS' ) or defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/** Adds support for Phoca Download Sections to Xmap */
class xmap_com_phocadownload {
 
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

		$include_downloads = xmap_com_phocadownload::getParam($params,'include_downloads',1);
		$include_downloads = ( $include_downloads == 1
				  || ( $include_downloads == 2 && $xmap->view == 'xml') 
				  || ( $include_downloads == 3 && $xmap->view == 'html'));
		$params['include_downloads'] = $include_downloads;

		$priority = xmap_com_phocadownload::getParam($params,'cat_priority',$parent->priority);
		$changefreq = xmap_com_phocadownload::getParam($params,'cat_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['cat_priority'] = $priority;
		$params['cat_changefreq'] = $changefreq;

		$priority = xmap_com_phocadownload::getParam($params,'down_priority',$parent->priority);
		$changefreq = xmap_com_phocadownload::getParam($params,'down_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['down_priority'] = $priority;
		$params['down_changefreq'] = $changefreq;

		if ( ! file_exists($mosConfig_absolute_path . '/components/com_phocadownload/phocadownload.php') ) {
			return false;
		}
		
		// Traverse the Section/Category Hierarchy
		xmap_com_phocadownload::getSectionTree($xmap, $parent, $params);
		
		return true;
	}
	

	/** Phoca Download Sections */
	function getSectionTree( &$xmap, &$parent,&$params ) {
		if (defined('JPATH_SITE')) {
			$database = &JFactory::getDBO();
			$mosConfig_absolute_path = JPATH_SITE;
		} else {
			global $database,$mosConfig_absolute_path;
		}
		
		$query = 	"SELECT id, title, '0' as pid\n" .
		 		"FROM #__phocadownload_sections \n" .
		 		"WHERE PUBLISHED=1 \n" .
		 		"ORDER BY title";
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		$xmap->changeLevel(1);
		foreach($rows as $row) {
			$node = new stdclass;

			$node->id = $parent->id;
			$node->uid = $parent->uid.'s'.$row->id;
			$node->browserNav = $parent->browserNav;
		    	$node->name = stripslashes($row->title);
			$node->modified = intval(strtotime('now'));
			$node->priority = $params['cat_priority'];
			$node->changefreq = $params['cat_changefreq'];
			$node->link = str_replace('sections','section',$parent->link).'&amp;id='.$row->id;
			$node->pid = $row->pid;	// parent id
		    	if ($xmap->printNode($node) !== FALSE) {
				xmap_com_phocadownload::getCategoryTree( $xmap, $parent, $params, $row->id);
			}
	    	}
	    	$xmap->changeLevel(-1);
	}
		
	/** Phoca Download Categories */
	function getCategoryTree( &$xmap, &$parent,&$params, $sectionid=0, $p_id=0 ) {
		if (defined('JPATH_SITE')) {
			$database = &JFactory::getDBO();
			$mosConfig_absolute_path = JPATH_SITE;
		} else {
			global $database,$mosConfig_absolute_path;
		}
		
		$query  = 	"SELECT id, title, parent_id as pid \n" .
				"FROM #__phocadownload_categories \n" .
				"WHERE parent_id=" . $p_id . "\n" .
				"AND section=" . $sectionid . "\n" .
				"AND published=1\n" .
				"ORDER BY title";		 		 
		 
		$database->setQuery( $query );
		$rows = $database->loadObjectList();	
				 
		$xmap->changeLevel(1);
		foreach($rows as $row) {
			$node = new stdclass;
			$node->id = $parent->id;
			$node->uid = $parent->uid.'c'.$row->id;
			$node->browserNav = $parent->browserNav;
		    	$node->name = stripslashes($row->title);
			$node->modified = intval(strtotime('now'));
			$node->priority = $params['cat_priority'];
			$node->changefreq = $params['cat_changefreq'];
			$node->link = str_replace('sections','category',$parent->link).'&amp;id='.$row->id;
			
			$node->pid = $row->pid;									// parent id
		    	if ($xmap->printNode($node) !== FALSE) {		    		
				xmap_com_phocadownload::getCategoryTree( $xmap, $parent, $params, $sectionid, $row->id);
				if ( $params['include_downloads'] ) {
					xmap_com_phocadownload::getDownloads($xmap, $parent, $params, $sectionid, $row->id);
				}
			}
	    	}
		$xmap->changeLevel(-1);   
        }


	/** Downloads */
	function getDownloads( &$xmap, &$parent, &$params, $sectionid=0, $catid=0 ) {

		if (defined('JPATH_SITE')) {
			$database = &JFactory::getDBO();
			$mosConfig_absolute_path = JPATH_SITE;
		} else {
			global $database,$mosConfig_absolute_path;
		}
		$list = array();

		$query  = 	"SELECT id, title, date as mdate, '' as pid"
				."\n FROM #__phocadownload  "
				."\n WHERE published=1 "
				."\n AND sectionid = $sectionid "
				."\n AND catid=$catid "
				."\n ORDER BY title";

		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		$xmap->changeLevel(1);		
		foreach($rows as $row) {
			$node = new stdclass;

			$node->id = $parent->id;
			$node->uid = $parent->uid.'d'.$row->id;
			$node->browserNav = $parent->browserNav;
		    	$node->name = stripslashes($row->title);
			$node->modified = intval(strtotime($row->mdate));
			$node->priority = $params['down_priority'];
			$node->changefreq = $params['down_changefreq'];
			$node->link = str_replace('sections','category',$parent->link).'&amp;id='.$catid.'&amp;download='.$row->id;
			$node->pid = $row->pid;	 // parent id
			$xmap->printNode($node);
	    	}
		$xmap->changeLevel(-1);		
		return true;
	}

	function &getParam($arr, $name, $def) {
		if ( defined('JPATH_SITE') ) {
			$var = JArrayHelper::getValue( $arr, $name, $def, '' );
		} else {
			$var = xmap_com_phocadownload::getParam( $arr, $name, $def);
		}
		return $var;
	}
}