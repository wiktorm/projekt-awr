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

class PhocaDownloadCpModelPhocaDownloadLinkSec extends JModel
{
	var $_data;
	
	function __construct() {
		parent::__construct();
	}

	function &getData() {
		if ($this->_loadData()) {
			
		} else {
			$this->_initData();
		}
		return $this->_data;
	}
	
	function _loadData() {
		if (empty($this->_data)) {		
			$query = 'SELECT s.title AS text, s.id AS value'
			. ' FROM #__phocadownload_sections AS s'
			. ' WHERE s.published = 1'
			. ' ORDER BY s.ordering';
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObjectList();
			return (boolean) $this->_data;
		}
		return true;
	}
	
	function _initData() {
		if (empty($this->_data)) {
			return (boolean) array();
		}
		return true;
	}	
}
?>