<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class PhocaDownloadModelSections extends JModel
{
	var $_section 			= null;
	var $_most_viewed_docs 	= null;
	var $_section_ordering	= null;
	var $_category_ordering	= null;

	function __construct() {
		parent::__construct();
	}

	function getSectionList($params) {
		$user	=& JFactory::getUser();
		$aid 	= $user->get('aid', 0);	
		
		if (empty($this->_section)) {	
			global $mainframe;
			$user 			= &JFactory::getUser();
			$aid 			= $user->get('aid', 0);			
			$query			= $this->_getSectionListQuery( $aid, $params );
			$this->_section = $this->_getList( $query );
			
			
			if (!empty($this->_section)) {
				foreach ($this->_section as $key => $value) {
					$query	= $this->_getCategoryListQuery( $value->id, $aid, $params );
					$this->_section[$key]->categories = $this->_getList( $query );
				}
			}
			
		}
		return $this->_section;
	}
	
	
	
	
	function _getSectionListQuery( $aid, $params ) {
		
		$display_sections = $params->get('display_sections', '');
		if ( $display_sections != '' ) {
			$section_ids_where = " AND s.id IN (".$display_sections.")";
		} else {
			$section_ids_where = '';
		}
		
		$hide_sections = $params->get('hide_sections', '');
		if ( $hide_sections != '' ) {
			$section_ids_not_where = " AND s.id NOT IN (".$hide_sections.")";
		} else {
			$section_ids_not_where = '';
		}

		$wheres[] = " s.published = 1";
		$wheres[] = " cc.published = 1";
		$wheres[] = " s.id = cc.section";
			

		if ($aid !== null) {
			$wheres[] = "s.access <= " . (int) $aid;
		}
		
		$sectionOrdering = $this->_getSectionOrdering();
		
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;
		$pQ			= $paramsC->get( 'enable_plugin_query', 0 );
		if ($pQ == 1) {
			// GWE MOD - to allow for access restrictions
			JPluginHelper::importPlugin("phoca");
			$dispatcher	   =& JDispatcher::getInstance();
			$joins = array();
			$results = $dispatcher->trigger('onGetSectionList', array (&$wheres, &$joins,  $params));
			// END GWE MOD
		}

		$query =  " SELECT s.id, s.title, s.alias, COUNT(cc.id) AS numcat, '' AS categories"
				. " FROM #__phocadownload_sections AS s, #__phocadownload_categories AS cc"
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. $section_ids_where
				. $section_ids_not_where
				. " GROUP BY s.id"
				. " ORDER BY s.".$sectionOrdering;
		return $query;
	}
	
	
	function _getCategoryListQuery( $sectionId, $aid, $params ) {
		
		$wheres[]	= " cc.section= ".(int)$sectionId;
		if ($aid !== null) {
			$wheres[] = "cc.access <= " . (int) $aid;
		}
		$wheres[] = " cc.published = 1";
		
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;
		$pQ			= $paramsC->get( 'enable_plugin_query', 0 );
		if ($pQ == 1) {
			// GWE MOD - to allow for access restrictions
			JPluginHelper::importPlugin("phoca");
			$dispatcher	   =& JDispatcher::getInstance();
			$joins = array();
			$results = $dispatcher->trigger('onGetCategoryList', array (&$wheres, &$joins, $sectionId, $params));
			// END GWE MOD
		}
		
		$categoryOrdering = $this->_getCategoryOrdering();
		
		$query = " SELECT  cc.id, cc.title, cc.alias, cc.access, cc.accessuserid, COUNT(c.id) AS numdoc"
				. " FROM #__phocadownload_categories AS cc"
				. " LEFT JOIN #__phocadownload AS c ON c.catid = cc.id AND c.published = 1  AND c.textonly = 0"
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. " GROUP BY cc.id"
				. " ORDER BY cc.".$categoryOrdering;
				
		return $query;
		
		
	}
	
	function getMostViewedDocsList($params) {
		$user	=& JFactory::getUser();
		$aid 	= $user->get('aid', 0);	
		
		if (empty($this->_most_viewed_docs)) {	
			global $mainframe;
			$user 						= &JFactory::getUser();
			$aid 						= $user->get('aid', 0);			
			$query						= $this->_getMostViewedDocsListQuery( $aid, $params );
			$this->_most_viewed_docs 	= $this->_getList( $query );
		}
		return $this->_most_viewed_docs;
	}
	
	function _getMostViewedDocsListQuery( $aid, $params ) {
		
		// PARAMS
		$most_viewed_docs_num = $params->get( 'most_viewed_docs_num', 5 );
		
		$display_sections = $params->get('display_sections', '');
		if ( $display_sections != '' ) {
			$section_ids_where = " AND s.id IN (".$display_sections.")";
		} else {
			$section_ids_where = '';
		}
		
		$hide_sections = $params->get('hide_sections', '');
		if ( $hide_sections != '' ) {
			$section_ids_not_where = " AND s.id NOT IN (".$hide_sections.")";
		} else {
			$section_ids_not_where = '';
		}
		
		$wheres[]	= " c.sectionid= s.id";
		$wheres[]	= " c.catid= cc.id";
		$wheres[]	= " c.published= 1";
		$wheres[]	= " c.approved= 1";
		$wheres[]	= " c.textonly= 0";
		if ($aid !== null) {
			$wheres[] = "c.access <= " . (int) $aid;
			$wheres[] = "s.access <= " . (int) $aid;
			$wheres[] = "cc.access <= " . (int) $aid;
		}
		
		// Active
		$jnow		=& JFactory::getDate();
		$now		= $jnow->toMySQL();
		$nullDate	= $this->_db->getNullDate();
		$wheres[] = ' ( c.publish_up = '.$this->_db->Quote($nullDate).' OR c.publish_up <= '.$this->_db->Quote($now).' )';
		$wheres[] = ' ( c.publish_down = '.$this->_db->Quote($nullDate).' OR c.publish_down >= '.$this->_db->Quote($now).' )';
		
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;
		$pQ			= $paramsC->get( 'enable_plugin_query', 0 );
		if ($pQ == 1) {
			// GWE MOD - to allow for access restrictions
			JPluginHelper::importPlugin("phoca");
			$dispatcher	   =& JDispatcher::getInstance();
			$joins = array();
			$results = $dispatcher->trigger('onGetMostViewedDocs', array (&$wheres, &$joins, 0, $params));	
			// END GWE MOD
		}
		
		$query = " SELECT c.id, c.title, c.alias, c.filename, c.date, c.hits, c.image_filename, s.title AS sectiontitle, s.id as sectionid, cc.id AS categoryid, cc.access as cataccess, cc.accessuserid as cataccessuserid, cc.title AS categorytitle, cc.alias AS categoryalias "
				." FROM #__phocadownload AS c, #__phocadownload_sections AS s, #__phocadownload_categories AS cc"
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. $section_ids_where
				. $section_ids_not_where
				. " ORDER BY c.hits DESC"
				. " LIMIT ".(int)$most_viewed_docs_num;
		return $query;
	}
	
	function _getSectionOrdering() {
		if (empty($this->_section_ordering)) {
	
			global $mainframe;
			$params						= &$mainframe->getParams();
			$ordering					= $params->get( 'section_ordering', 1 );
			$this->_section_ordering 	= PhocaDownloadHelperFront::getOrderingText($ordering);

		}
		return $this->_section_ordering;
	}
	
	function _getCategoryOrdering() {
		if (empty($this->_category_ordering)) {
	
			global $mainframe;
			$params						= &$mainframe->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->_category_ordering 	= PhocaDownloadHelperFront::getOrderingText($ordering);

		}
		return $this->_category_ordering;
	}
}
?>