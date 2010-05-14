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
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaDownloadCpModelPhocaDownloadut extends JModel
{
	var $_id;
	var $_data = null;
	var $_total = null;
	var $_pagination = null;
	var $_context		= 'com_phocadownload.phocadownloadut';

	function __construct() {
		parent::__construct();		
		global $mainframe;
		
		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);	
		
		// Get the pagination request variables
		$limit	= $mainframe->getUserStateFromRequest( $this->_context.'.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $this->_context.'.limitstart', 'limitstart',	0, 'int' );
		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}
	
	function setId($id) {
		$this->_id		= $id;
		$this->_data	= null;
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
		$query = ' SELECT a.id, a.userid, a.fileid, d.filename AS filename, d.title AS filetitle, a.count, a.date, u.name AS uname, u.username AS username, 0 AS checked_out'
			. ' FROM #__phocadownload_user_stat AS a '
			. ' LEFT JOIN #__phocadownload AS d ON d.id = a.fileid '
			. ' LEFT JOIN #__users AS u ON u.id = a.userid '
			. $where
			. ' GROUP by a.id'
			. $orderby;

		return $query;
	}

	function _buildContentOrderBy(){
		global $mainframe;
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	'a.count','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'DESC',	'word' );

		if ($filter_order == 'a.ordering'){
			$orderby 	= ' ORDER BY  a.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , a.count, a.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere() {
		global $mainframe;

		
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state',	'filter_state',	'',	'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	'a.ordering','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context.'.search','search','',	'string' );
		$search				= JString::strtolower( $search );

		$where = array();
		
		$where[] = 'a.fileid ='.(int)$this->_id;

		if ($search) {
			$where[] = 'LOWER(d.title) LIKE '.$this->_db->Quote('%'.$search.'%')
					   .' OR LOWER(d.filename) LIKE '.$this->_db->Quote('%'.$search.'%')
					   .' OR LOWER(u.name) LIKE '.$this->_db->Quote('%'.$search.'%')
					   .' OR LOWER(u.username) LIKE '.$this->_db->Quote('%'.$search.'%');
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
	
	function reset($cid = array()) {		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$date = gmdate('Y-m-d H:i:s');
			//Delete it from DB
			$query = 'UPDATE '.$this->_db->nameQuote('#__phocadownload_user_stat')
					.' SET count = 0,'
					.' date = '.$this->_db->Quote($date)
					.' WHERE id IN ( '.$cids.' )';
					
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
}
?>