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

class PhocaDownloadCpModelPhocaDownload extends JModel
{
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
			$user = &JFactory::getUser();
			// Check whether category access level allows access
			if ($this->_data->cat_access > $user->get('aid', 0)) {
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
				return;
			}
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
		if ($this->_id) {
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
	
	function store($data) {	
		$row =& $this->getTable();
		
		$data['filesize'] 	= PhocaDownloadHelper::getFileSize($data['filename'], 0);
		
		// Bind the form fields to the Phoca component table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Create the timestamp for the date
		/*
		if (!$row->date) {
			$row->date = gmdate('Y-m-d H:i:s');
		}
		if (!$row->publish_up) {
			$row->publish_up = gmdate('Y-m-d H:i:s');
		}*/
		$nullDate	= $this->_db->getNullDate();
		$config 	= &JFactory::getConfig();
		$tzoffset 	= $config->getValue('config.offset');
		$date 		= &JFactory::getDate($row->date, $tzoffset);
		$row->date 	= $date->toMySQL();

		// Append time if not added to publish date
		if (strlen(trim($row->publish_up)) <= 10) {
			$row->publish_up .= ' 00:00:00';
		}

		$date =& JFactory::getDate($row->publish_up, $tzoffset);
		$row->publish_up = $date->toMySQL();

		// Handle never unpublish date
		if (trim($row->publish_down) == JText::_('Never') || trim( $row->publish_down ) == '') {
			$row->publish_down = $nullDate;
		} else {
			if (strlen(trim( $row->publish_down )) <= 10) {
				$row->publish_down .= ' 00:00:00';
			}
			$date =& JFactory::getDate($row->publish_down, $tzoffset);
			$row->publish_down = $date->toMySQL();
		}
		
		

		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = 'catid = ' . (int) $row->catid ;
			$row->ordering = $row->getNextOrder( $where );
		}

		// Make sure the Phoca component table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the Phoca component table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		return $row->id;
	}

	function delete($cid = array()) {		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			//Delete it from DB
			$query = 'DELETE FROM #__phocadownload'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	function publish($cid = array(), $publish = 1) {
		$user 	=& JFactory::getUser();

		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__phocadownload'
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
	
	function approve($cid = array(), $approved = 1) {
		$user 	=& JFactory::getUser();

		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__phocadownload'
				. ' SET approved = '.(int) $approved
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

		if (!$row->move( $direction, ' catid = '.(int) $row->catid.' AND published >= 0 ' )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}


	function saveorder($cid = array(), $order) {
		$row =& $this->getTable();
		$groupings = array();

		// update ordering values
		for( $i=0; $i < count($cid); $i++ ) {
			$row->load( (int) $cid[$i] );
			// track categories
			$groupings[] = $row->catid;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('catid = '.(int) $group);
		}
		return true;
	}
	
	function _loadData() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT a.*, cc.title AS categorytitle, s.title AS sectiontitle,'.
					' cc.published AS cat_pub, cc.access AS cat_access'.
					' FROM #__phocadownload AS a' .
					' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = a.catid' .
					' LEFT JOIN #__phocadownload_sections AS s ON s.id = a.sectionid' .
					' WHERE a.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}
	
	function accessmenu($id, $access) {
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
	
	function _initData() {
		if (empty($this->_data)) {
		
			$createdate =& JFactory::getDate();
		
			$table = new stdClass();
			$table->id					= 0;
			$table->catid				= 0;
			$table->owner_id			= 0;
			$table->sectionid			= 0;
			$table->sid					= 0;
			$table->title				= null;
			$table->alias				= null;
			$table->filename        	= null;
			$table->filesize			= 0;
			$table->filename_play       = null;
			$table->filename_preview    = null;
			$table->version	        	= null;
			$table->author        		= null;
			$table->author_email        = null;
			$table->author_url       	= null;
			$table->license      		= null;
			$table->license_url        	= null;
			$table->image_filename      = null;
			$table->image_filename_spec1= null;
			$table->image_filename_spec2= null;
			$table->image_download      = null;
			$table->link_external      	= null;
			$table->description			= null;
			$table->version				= null;
			$table->directlink			= 0;
			$table->date				= null;
			$table->publish_up			= gmdate('Y-m-d H:i:s');
			$table->publish_down		= JText::_('Never');
			$table->hits				= 0;
			$table->textonly			= 0;
			$table->published			= 0;
			$table->checked_out			= 0;
			$table->checked_out_time	= 0;
			$table->ordering			= 0;
			$table->access				= 0;
			$table->confirm_license		= 0;
			$table->unaccessible_file	= 0;
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