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

class PhocaDownloadCpModelPhocaDownloadset extends JModel
{

	var $_data  = null;
	var $_id	= null;

	function __construct() {
		parent::__construct();
	}

	function getData() {
		if (empty($this->_data)) {
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query);
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
	
	function store($dataArray, &$errorMsg)
	{	
		$row =& $this->getTable();
		if (!empty($dataArray)) {
			foreach ($dataArray as $key => $value) {
				$data['id'] 	= $key;
				$data['value']	= $value;
				
				// Bind the form fields to the Phoca Component table
				if (!$row->bind($data)) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}

				// Make sure the Phoca Component table is valid
				if (!$row->check()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}

				// Store the Phoca Component table to the database
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				
				if($key == 1) {
					if(isset($value) && $value != '' && $value != 'phocadownload') {
					
						$valueF = JPATH_ROOT . DS .  $value;
						if (!JFolder::exists( $valueF)) {
							if (JFolder::create( $valueF, 0755 )) {
								@JFile::write($valueF.DS."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
								JFolder::create( $valueF.DS.'userupload', 0755 );
								@JFile::write($valueF.DS.'userupload'.DS."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
								$errorMsg	= 'PHOCADOWNLOAD_FOLDER_CREATED';
							} else {
								$errorMsg	= 'PHOCADOWNLOAD_FOLDER_CREATE_ERROR';
							}
						}
					}
				
				}
			}
		}
		return true;
	}	

	function _buildQuery() {
		$query = ' SELECT st.*'
			. ' FROM #__phocadownload_settings AS st'
			. ' ORDER BY st.id';
		return $query;
	}	
}
?>