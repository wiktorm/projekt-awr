<?php 
/**
* @author Aleksandar Bogdanovic, http://www.banitech.com
* @email albog@banitech.com
* @version $Id: com_phocagallery.php
* @package Xmap
* @license GNU/GPL
* @description Xmap plugin for Phoca Gallery component
*/
defined('_JEXEC') or die ('Restricted Access');

class xmap_com_phocagallery {

	function &getTree ( &$xmap, &$parent, &$params ) {
		$link_query = parse_url( $parent->link );
                parse_str( html_entity_decode($link_query['query']), $link_vars );
                $catid = JArrayHelper::getValue($link_vars,'id',0);

		
		$priority = JArrayHelper::getValue($params,'cat_priority',$parent->priority,'');
		$changefreq = JArrayHelper::getValue($params,'cat_changefreq',$parent->changefreq,'');
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['cat_priority'] = $priority;
		$params['cat_changefreq'] = $changefreq;

		$priority = JArrayHelper::getValue($params,'file_priority',$parent->priority,'');
		$changefreq = JArrayHelper::getValue($params,'file_changefreq',$parent->changefreq,'');
		if ($priority  == '-1')
			$priority = $parent->priority;

		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['file_priority'] = $priority;
		$params['file_changefreq'] = $changefreq;

		xmap_com_phocagallery::getPhocaCategoriesTree($xmap, $parent, $params, $catid);
	}
	
	function getPhocaCategoriesTree(&$xmap, &$parent, &$params, &$catid) {

		$cats = xmap_com_phocagallery::getDBCategories($catid);

		$xmap->changeLevel(1);
		foreach($cats as $row) {
			$node = new stdclass;
			$node->id   = $parent->id;
			$node->uid  = $parent->id.'c'.$row->id;
			$node->pid  = $row->parent_id;
			$node->name = $row->title;
			$node->priority   = $params['cat_priority'];		
			$node->changefreq = $params['cat_changefreq'];
			$node->link = 'index.php?option=com_phocagallery&amp;view=category&amp;id='.$row->id.':'.$row->alias;
			$node->tree = array();
			
			if( ($xmap->printNode($node) !== FALSE) && $params['expand_categories'] ) {
				xmap_com_phocagallery::getPhocaCategoriesTree($xmap, $parent, $params, $row->id);
			}
		}
		$xmap->changeLevel(-1);
	}
	function getDBCategories($catid){
		$db = &JFactory::getDBO();
		$query = 'SELECT id, title, name, alias, parent_id FROM #__phocagallery_categories WHERE parent_id = '.$catid.' AND published=1 ORDER BY ordering';
		$db->setQuery($query);
		$lists = $db->loadObjectList();
		return $lists;
	}

}

?>
