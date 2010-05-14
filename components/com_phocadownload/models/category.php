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


class PhocaDownloadModelCategory extends JModel
{
	var $_document 			= null;
	var $_category 			= null;
	var $_section			= null;
	var $_filename			= null;
	var $_directlink		= 0;
	var $_file_ordering		= null;
	var $_pagination		= null;
	var $_total				= null;

	function __construct() {
		
		global $mainframe;
		parent::__construct();
		
		$config = JFactory::getConfig();		
		
		$paramsC 			= JComponentHelper::getParams('com_phocadownload') ;
		$defaultPagination	= $paramsC->get( 'default_pagination', '20' );
		
		// Get the pagination request variables
		$this->setState('limit', $mainframe->getUserStateFromRequest('com_phocadownload.limit', 'limit', $defaultPagination, 'int'));
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

		// Get the filter request variables
		$this->setState('filter_order', JRequest::getCmd('filter_order', 'ordering'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
		
	}
	
	function getPagination($categoryId, $params) {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new PhocaPagination( $this->getTotal($categoryId, $params), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	function getTotal($categoryId, $params) {
		if (empty($this->_total)) {
			$user	=& JFactory::getUser();
			$aid 	= $user->get('aid', 0);	
			$query = $this->_getDocumentListQuery($categoryId, $aid, $params);
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	function getDocumentList($categoryId, $params) {
		$user	=& JFactory::getUser();
		$aid 	= $user->get('aid', 0);	
		
		if (empty($this->_document)) {	
			global $mainframe;
			$user 			= &JFactory::getUser();
			$aid 			= $user->get('aid', 0);			
			$query			= $this->_getDocumentListQuery( $categoryId, $aid, $params );
			$this->_document= $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_document;
	}
	
	function _getDocumentListQuery( $categoryId, $aid, $params ) {
		
		$wheres[]	= ' c.catid= '.(int)$categoryId;
		$wheres[]	= ' c.catid= cc.id';
		if ($aid !== null) {
		
			// IF unaccessible file = 1 then display unaccessible file for all
			// IF unaccessible file = 0 then display it only for them who have access to this file
			$wheres[] = '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND c.access <= ' . (int) $aid.') )';
			$wheres[] = '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND cc.access <= ' . (int) $aid.') )';
			//$wheres[] = 'c.access <= ' . (int) $aid;
			//$wheres[] = 'cc.access <= ' . (int) $aid;
		}
		$wheres[] = ' c.published = 1';
		$wheres[] = " c.approved = 1";
		$wheres[] = ' cc.published = 1';
		
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
			$results = $dispatcher->trigger('onGetDocumentList', array (&$wheres, &$joins,$categoryId , $params));	
			// END GWE MOD
		}
		
		$fileOrdering = $this->_getFileOrdering();
		
		$query = ' SELECT c.*, cc.id AS categoryid, cc.title AS categorytitle, cc.alias AS categoryalias, cc.access as cataccess, cc.accessuserid as cataccessuserid '
				.' FROM #__phocadownload AS c, #__phocadownload_categories AS cc'
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. ' WHERE ' . implode( ' AND ', $wheres )
				. ' ORDER BY c.'.$fileOrdering;
				
		return $query;
	}
	
	function getCategory($categoryId, $params) {
		$user	=& JFactory::getUser();
		$aid 	= $user->get('aid', 0);	
		//$wheres[] = " cc.published = 1 ";	
		if (empty($this->_category)) {	
			global $mainframe;
			$user 			= &JFactory::getUser();
			$aid 			= $user->get('aid', 0);			
			$query			= $this->_getCategoryQuery( $categoryId, $aid, $params );
			$this->_category= $this->_getList( $query, 0, 1 );
		}
		return $this->_category;
	}
	
	function _getCategoryQuery( $categoryId, $aid, $params ) {
		
		$wheres[]	= " cc.id= ".(int)$categoryId;
		if ($aid !== null) {
			$wheres[] = "cc.access <= " . (int) $aid;
		}
		$wheres[] = " cc.published = 1";
		
		// OSE Starts;
		/*
			if (JComponentHelper::isEnabled('com_osemsc', true)) {
				require_once (JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "warehouse" . DS . "api.php");
				$checkmsc = new OSEMSCAPI();
				$allow_access = $checkmsc->ACLCheck("phoca", "cat", $categoryId, false);
				if ($allow_access==false) {
					$wheres[] = "cc.id NOT IN ({$categoryId})";	
				}
			}
		*/
		// OSE Modified Ends;
		
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;
		$pQ			= $paramsC->get( 'enable_plugin_query', 0 );
		if ($pQ == 1) {
			// GWE MOD - to allow for access restrictions
			JPluginHelper::importPlugin("phoca");
			$dispatcher	   =& JDispatcher::getInstance();
			$joins = array();
			$results = $dispatcher->trigger('onGetCategory', array (&$wheres, &$joins,$categoryId , $params));	
			// END GWE MOD
		}
		
		$query = " SELECT cc.id, cc.title, cc.alias, cc.image, cc.access as cataccess, cc.accessuserid as cataccessuserid, cc.image_position, cc.description, cc.metakey, cc.metadesc"
				. " FROM #__phocadownload_categories AS cc"
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY cc.ordering";
		return $query;
	}
	
	function getSection($categoryId, $params) {
		$user	=& JFactory::getUser();
		$aid 	= $user->get('aid', 0);	
		if (empty($this->_category)) {	
			global $mainframe;
			$user 			= &JFactory::getUser();
			$aid 			= $user->get('aid', 0);			
			$query			= $this->_getSectionQuery( $categoryId, $aid, $params );
			$this->_section= $this->_getList( $query, 0, 1 );
		}
		return $this->_section;
	}
	
	function _getSectionQuery( $categoryId, $aid, $params ) {
		
		$wheres[]	= " cc.id= ".(int)$categoryId;
		$wheres[]	= " cc.section=s.id";
		if ($aid !== null) {
			$wheres[] = "cc.access <= " . (int) $aid;
			$wheres[] = "s.access <= " . (int) $aid;
		}
		$wheres[] = " cc.published = 1";
		
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;
		$pQ			= $paramsC->get( 'enable_plugin_query', 0 );
		if ($pQ == 1) {
			// GWE MOD - to allow for access restrictions
			JPluginHelper::importPlugin("phoca");
			$dispatcher	   =& JDispatcher::getInstance();
			$joins = array();
			$results = $dispatcher->trigger('onGetSectionCategory', array (&$wheres, &$joins,$categoryId , $params));	
			// END GWE MOD
		}
		
		$query = " SELECT s.id, s.title, s.alias"
				. " FROM #__phocadownload_sections AS s"
				. " ,#__phocadownload_categories AS cc"
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY cc.ordering";
		return $query;
	}
	
	
	/*
	 * Last download function - the file will be checked before download
	 */
	function getDownload($id, $return) {
		
		global $mainframe;
		$user		=& JFactory::getUser();
		$aid 		= $user->get('aid', 0);
		$returnUrl  = 'index.php?option=com_user&view=login&return='.base64_encode($return);
       

		$outcome	= array();
		
		$wheres[]	= " c.id= ".(int)$id;
		
		// Get the file information and then check if it is possible for user download (so we can leave a message)
		//if ($aid !== null) {
		//	$wheres[] = "c.access <= " . (int) $aid;
		//}
		$wheres[] = " c.published 	= 1";
		$wheres[] = " c.approved 	= 1";
		$wheres[] = " c.catid		= cc.id";
		
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
			$results = $dispatcher->trigger('onGetDownload', array (&$wheres, &$joins,$id,  $paramsC));	
			// END GWE MOD
		}
		
		
		/*$query = " SELECT c.filename, c.directlink, c.access"
				." FROM #__phocadownload AS c"
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY c.ordering";*/
		
		
        $query = ' SELECT c.catid, c.filename, c.directlink, c.access, c.confirm_license, c.metakey, c.metadesc, cc.access as cataccess, cc.accessuserid as cataccessuserid '
				.' FROM #__phocadownload AS c, #__phocadownload_categories AS cc '
				. ($pQ == 1 ? ((count($joins)>0?( ' LEFT JOIN ' .implode( ' LEFT JOIN ', $joins )):'')):'') // GWE MOD
				. ' WHERE ' . implode( ' AND ', $wheres )
				. ' ORDER BY c.ordering';
        $filename = $this->_getList($query, 0, 1);
        
		//OSE Modified Start;
		if (!empty($filename)){
			if (JComponentHelper::isEnabled('com_osemsc', true)) {
				require_once (JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "warehouse" . DS . "api.php");
				$checkmsc = new OSEMSCAPI();
				$checkmsc->ACLCheck("phoca", "cat", $filename[0]->catid, true);
			}
        }
        //OSE Modified End;
				
		
		// - - - - - - - - - - - - - - -
		// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
		// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
		$rightDisplay	= 0;
		if (!empty($filename[0])) {
			$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $filename[0]->cataccessuserid, $filename[0]->cataccess, $user->get('aid', 0), $user->get('id', 0), 0);
		}
		// - - - - - - - - - - - - - - - - - - - - - -
		if ($rightDisplay == 0) {
			$mainframe->redirect(JRoute::_($returnUrl), JText::_("PHOCADOWNLOAD_NO_RIGHTS_ACCESS_CATEGORY_FILE"));
			exit;
		}
		
		
		if (empty($filename)) {
			$outcome['file'] 		= "PhocaErrorNoDBResult";
			$outcome['directlink']	= 0;
			return $outcome;
		} 
		
		if (isset($filename[0]->access)) {
			if ($aid !== null) {
				if ($filename[0]->access > (int) $aid) {
						//$mainframe->redirect(JRoute::_('index.php?option=com_user&view=login', false), JText::_("Please login to download the file"));
						$mainframe->redirect(JRoute::_($returnUrl), JText::_("Please login to download the file"));
						exit;
				}
			} else {
				$outcome['file'] 		= "PhocaErrorAidProblem";
				$outcome['directlink']	= 0;
				return $outcome;
			}
		} else {
			$outcome['file'] 		= "PhocaErrorNoDBResult";
			$outcome['directlink']	= 0;
			return $outcome;
		}
		// - - - - - - - - - - - - - - - -
		
		
		$this->_filename 	= $filename[0]->filename;
		$this->_directlink 	= $filename[0]->directlink;
		$filePath			= PhocaDownloadHelper::getPathSet('file');
		
		if ($this->_filename !='') {
			
			// Important - you cannot use direct link if you have selected absolute path
			// Absolute Path defined by user
			$absolutePath	= PhocaDownloadHelper::getSettings('absolute_path', '');
			if ($absolutePath != '') {
				$this->_directlink = 0;
			}
			
			if ($this->_directlink == 1 ) {
				$relFile = JURI::base(true).'/'.PhocaDownloadHelper::getSettings('download_folder', 'phocadownload' ).'/'.$this->_filename;
				$outcome['file'] 		= $relFile;
				$outcome['directlink']	= $this->_directlink;
				return $outcome;
			} else {
				$absFile = str_replace('/', DS, JPath::clean($filePath['orig_abs_ds'] . $this->_filename));
			}
		
			if (JFile::exists($absFile)) {
				$outcome['file'] 		= $absFile;
				$outcome['directlink']	= $this->_directlink;
				return $outcome;
			} else {
			
				$outcome['file'] 		= "PhocaErrorNoAbsFile";
				$outcome['directlink']	= 0;
				return $outcome;
			}
		} else {
		
				$outcome['file'] 		= "PhocaErrorNoDBFile";
				$outcome['directlink']	= 0;
				return $outcome;
		}
		
	}
	
	function _getFileOrdering() {
		if (empty($this->_file_ordering)) {
	
			global $mainframe;
			$params						= &$mainframe->getParams();
			$ordering					= $params->get( 'file_ordering', 1 );
			$this->_file_ordering 		= PhocaDownloadHelperFront::getOrderingText($ordering);

		}
		return $this->_file_ordering;
	}
}
?>