<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Download
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaDownloadCpModelPhocaDownloadcats extends JModel
{

	var $_data 					= null;
	var $_total 				= null;
	var $_pagination 			= null;
	var $_context				= 'com_phocadownload.phocadownloadcat';

	function __construct() {
		parent::__construct();		
		global $mainframe;
		// Get the pagination request variables
		$limit	= $mainframe->getUserStateFromRequest( $this->_context.'.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $this->_context.'.limitstart', 'limitstart',	0, 'int' );
		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	function getData() {
		if (empty($this->_data) ) {
			$query 		 = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	function getTotal()
	{
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	function _buildQuery() {
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy('cc.section');

		$query = ' SELECT cc.*, s.title AS sectiontitle, u.name AS editor, g.name AS groupname '
			. ' FROM #__phocadownload_categories AS cc '
			. ' LEFT JOIN #__users AS u ON u.id = cc.checked_out '
			. ' LEFT JOIN #__groups AS g ON g.id = cc.access '
			. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = cc.section '
			. $where
			. $orderby ;
		return $query;
	}
	
	function _buildContentOrderBy($cc_or_a) {		
		
		global $mainframe;
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order', 'filter_order','cc.ordering', 'cmd' );
		// Category tree works with id not with ordering
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );

		if ($filter_order == 'cc.ordering'){
			$orderby 	= ' ORDER BY sectiontitle, cc.ordering '.$filter_order_Dir;
		} else if ($filter_order == 'category'){
			$orderby 	= ' ORDER BY ' .$cc_or_a . ', cc.ordering ' .$filter_order_Dir;
		} else if ($filter_order == 'groupname'){
			$orderby 	= ' ORDER BY g.groupname , cc.ordering ' .$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order . ' ' . $filter_order_Dir .  ' ,sectiontitle, cc.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere() {
		global $mainframe;
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state',	'filter_state',	'',	'word' );
		$filter_sectionid	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_sectionid',	'filter_sectionid',	0,'int' );
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order','filter_order',	'cc.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context.'.search','search','','string' );
		$search				= JString::strtolower( $search );
		$where = array();

		if ($search) {
			$where[] = 'LOWER(cc.title) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'cc.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'cc.published = 0';
			}
		}
		
		if ( $filter_sectionid ) {
			$where[] = 'cc.section = '.(int)$filter_sectionid;
		}
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
}
?>