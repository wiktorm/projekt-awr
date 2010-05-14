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

class PhocaDownloadCpModelPhocaDownloadsec extends JModel
{
	var $_id;
	var $_data;
	
	function __construct() {
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	function setId($id) {
		$this->_id		= $id;
		$this->_data	= null;
	}

	function &getData() {
		if ($this->_loadData()) {
			
		} else {
			$this->_initData();
		}
		return $this->_data;
	}
	
	function isCheckedOut( $uid=0 ) {
		if ($this->_loadData()) {
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}

	function checkin() {
		if ($this->_id) {
			$phocadownload = & $this->getTable();
			if(! $phocadownload->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	function checkout($uid = null) {
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$phocadownload = & $this->getTable();
			if(!$phocadownload->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}
	
	function store($data)
	{
		$row =& $this->getTable();
		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		if (!$row->date) {
			$row->date = gmdate('Y-m-d H:i:s');
		}
		
		// if new item, order last in appropriate group
		if (!$row->id) {
			$row->ordering = $row->getNextOrder( );
		}
		// Make sure the table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return $row->id;
	}
	
	function accessmenu($id, $access)
	{
		global $mainframe;
		$row =& $this->getTable();
		$row->load($id);
		$row->id = $id;
		$row->access = $access;
		if ( !$row->check() ) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		if ( !$row->store() ) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
	}

	function delete($cid = array()) {
		JRequest::checkToken() or jexit( 'Invalid Token' );
		global $mainframe;
		$db =& JFactory::getDBO();
		
		$result = false;
	
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			
			// FIRST - if there are categories ---------------------------------------------------	
			$query = 'SELECT s.id, s.title, COUNT(c.id) AS numcat'
			. ' FROM #__phocadownload_sections AS s'
			. ' LEFT JOIN #__phocadownload_categories AS c ON c.section=s.id'
			. ' WHERE s.id IN ( '.$cids.' )'
			. ' GROUP BY s.id'
			;
			$db->setQuery( $query );
				
			if (!($rows2 = $db->loadObjectList())) {
				JError::raiseError( 500, $db->stderr('Load Data Problem') );
				return false;
			}

			// Add new CID without sections which have categories (we don't delete sections with categories)
			$err_cat = array();
			$cid 	 = array();

			foreach ($rows2 as $row) {
				if ($row->numcat == 0) {
					$cid[] = (int) $row->id;
				} else {
					$err_cat[] = $row->title;
				}
			}
			// End categories ----------------------------------------------------------------------
			if (count( $cid )) {
				$cids = implode( ',', $cid );
				$query = 'DELETE FROM #__phocadownload_sections'
				. ' WHERE id IN ( '.$cids.' )'
				;
				
				$db->setQuery( $query );
				if (!$db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
			
			
			// There are some images in the category - don't delete it
			$msg = '';
			if (count( $err_cat )) {
				$cids_cat = implode( ", ", $err_cat );
				
				$msg .= JText::_( 'WARNNOTREMOVEDRECORDS PHOCA DOWNLOAD SECTION') . ': ' .$cids_cat;

				$link = 'index.php?option=com_phocadownload&view=phocadownloadsecs';
				$mainframe->redirect($link, $msg);
			}
		}
		return true;
	}

	function publish($cid = array(), $publish = 1){
		$user 	=& JFactory::getUser();

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__phocadownload_sections'
				. ' SET published = '.(int) $publish
				. ' WHERE id IN ( '.$cids.' )'
				. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	function move($direction) {
		$row =& $this->getTable();
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		if (!$row->move( $direction, ' published >= 0 ' )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}
	
	function saveorder($cid = array(), $order) {
		$row 	=& $this->getTable();
		$total	= count( $cid );

		// update ordering values
		for( $i=0; $i < $total; $i++ )
		{
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError(500, $db->getErrorMsg() );
				}
			}
		}
		$row->reorder( );
	}
	
	function _loadData() {
		if (empty($this->_data)) {		
			$query = 'SELECT p.* '.	
					' FROM #__phocadownload_sections AS p' .
					' WHERE p.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}
	
	function _initData() {
		if (empty($this->_data)) {
			$table = new stdClass();
			$table->id					= 0;
			$table->title				= null;
			$table->name				= null;
			$table->alias				= null;
			$table->image				= null;
			$table->scope     			= null;
			$table->image_position		= null;
			$table->description			= null;
			$table->published			= 0;
			$table->checked_out			= 0;
			$table->checked_out_time	= 0;
			$table->editor				= null;
			$table->ordering			= 0;
			$table->access				= 0;
			$table->date				= 0;
			$table->count				= 0;
			$table->params				= null;
			$table->metakey				= null;
			$table->metadesc			= null;
			$this->_data				= $table;
			return (boolean) $this->_data;
		}
		return true;
	}	
}
?>