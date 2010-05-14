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
defined('_JEXEC') or die();
jimport('joomla.application.component.model');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class phocadownloadCpModelPhocaDownloadUsers extends JModel
{

	var $_data 			= null;
	var $_total 		= null;
	var $_pagination 	= null;
	var $_context		= 'com_phocadownload.phocadownloaduser';

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
		
		if (empty($this->_data)) {
			$query = $this->_buildQuery();
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
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		
		$query = ' SELECT a.*, fa.countfaid, fn.countfnid, 0 AS checked_out'
			. ' FROM #__users AS a'
			. ' LEFT JOIN #__phocadownload AS f ON f.owner_id = a.id '
			. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = f.catid '
			. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = f.sectionid '
			. ' LEFT JOIN #__groups AS g ON g.id = f.access '
			. ' LEFT JOIN #__users AS aa ON aa.id = f.checked_out '
			
			
			. ' LEFT JOIN (SELECT  fa.owner_id, fa.id, count(*) AS countfaid'
			. ' FROM #__phocadownload AS fa'
			. ' WHERE fa.approved = 1'
			. ' GROUP BY fa.owner_id) AS fa '
			. ' ON a.id = fa.owner_id'
			
			. ' LEFT JOIN (SELECT  fn.owner_id, fn.id, count(*) AS countfnid'
			. ' FROM #__phocadownload AS fn'
			. ' WHERE fn.approved = 0'
			. ' GROUP BY fn.owner_id) AS fn '
			. ' ON a.id = fn.owner_id'
			
			. $where
			. ' GROUP by a.id'
			. $orderby;
		return $query;
	}


	function _buildContentOrderBy() {
		global $mainframe;
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	'a.id', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );

		if ($filter_order == 'a.id'){
			$orderby 	= ' ORDER BY a.id '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', a.id';
		}
		
		return $orderby;
	}

	function _buildContentWhere() {
		global $mainframe;
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state',	'filter_state',	'',	'word' );
		//$filter_catid		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_catid',	'filter_catid',	0,	'int' );
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	'a.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context.'.search', 'search', '', 'string' );
		$search				= JString::strtolower( $search );

		$where = array();
		
		$where[] = 'a.id > 0';
		$where[] = '(fa.countfaid > 0 OR fn.countfnid > 0)';
		

		/*if ($filter_catid > 0) {
			$where[] = 'a.userid = '.(int) $filter_catid;
		}*/
		if ($search) {
			$where[] = 'LOWER(a.title) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'a.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'a.published = 0';
			}
		}
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
}
?>