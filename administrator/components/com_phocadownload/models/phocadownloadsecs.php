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

class PhocaDownloadCpModelPhocaDownloadsecs extends JModel
{
	var $_data 					= null;
	var $_total 				= null;
	var $_pagination 			= null;
	var $_context				= 'com_phocadownload.phocadownloadsec';

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
		if (empty($this->_data)){
			$query 		 = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}	
		return $this->_data;
	}

	function getTotal() {
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	function getPagination() {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	function _buildQuery() {
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = 'SELECT s.*, g.name AS groupname, u.name AS editor'
		. ' FROM #__phocadownload_sections AS s'
		. ' LEFT JOIN #__phocadownload AS a ON s.id = a.sectionid'
		. ' LEFT JOIN #__users AS u ON u.id = s.checked_out'
		. ' LEFT JOIN #__groups AS g ON g.id = s.access'
		. $where
		. ' GROUP BY s.id'
		. $orderby;
		
		return $query;
	}
	
	
	function _buildContentOrderBy() {		
		global $mainframe;
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	's.ordering','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );

		if ($filter_order == 's.ordering'){
			$orderby 	= ' ORDER BY  s.ordering '.$filter_order_Dir;
		} else if ($filter_order == 'groupname'){
			$orderby 	= ' ORDER BY g.groupname , s.ordering ' .$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order . ' ' . $filter_order_Dir .  ', s.ordering ';
		}
		return $orderby;
	}
	
	
	function _buildContentWhere() {
		global $mainframe;
		$filter_state  		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state', 'filter_state',  '', 'word' );
		$filter_order  		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',  'filter_order',  'a.ordering', 'cmd' );
		$filter_order_Dir 	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir', 'filter_order_Dir', '', 'word' );
		$search   			= $mainframe->getUserStateFromRequest( $this->_context.'.search', 'search', '', 'string' );
		$search				= JString::strtolower( $search );
		$where = array();

		if ($search) {
			$where[] = 'LOWER(s.title) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 's.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 's.published = 0';
			}
		}
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
}
?>