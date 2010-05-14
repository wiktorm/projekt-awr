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


class PhocaDownloadModelFile extends JModel
{
	var $_document 			= null;
	var $_category 			= null;
	var $_section			= null;
	var $_filename			= null;
	var $_directlink		= 0;

	function __construct() {
		parent::__construct();
	}

	function getDocument( $fileId, $params, $limitstartUrl) {
		$user	=& JFactory::getUser();
		$aid 	= $user->get('aid', 0);	
		
		if (empty($this->_document)) {	
			global $mainframe;
			$user 			= &JFactory::getUser();
			$aid 			= $user->get('aid', 0);			
			$query			= $this->_getDocumentQuery( $fileId, $aid, $params );
			
			$this->_document= $this->_getList( $query, 0 , 1 );
			
			// Don't display file if user has no access
			// - - - - - - - - - - - - - - - 
			if (empty($this->_document)) {
				return null;
			} 
			
			if (isset($this->_document[0]->access)) {
				if ($aid !== null) {
					if ($this->_document[0]->access > (int) $aid) {
					
							//$mainframe->redirect(JRoute::_('index.php?option=com_user&view=login', false), JText::_("Please login to download the file"));
							// Return URL
							$return	= 'index.php?option=com_phocadownload&view=file&id='.$this->_document[0]->id.':'.$this->_document[0]->alias. $limitstartUrl . '&Itemid='. JRequest::getVar('Itemid', 0, '', 'int');
							
							
							$returnUrl  	= 'index.php?option=com_user&view=login&return='.base64_encode($return);
							$mainframe->redirect(JRoute::_($returnUrl), JText::_("Please login to download the file"));
							exit;
					}
				} else {
					return null;
				}
			} else {
				return null;
			}
			// - - - - - - - - - - - - - - - -
		}
		return $this->_document;
	}
	
	function _getDocumentQuery( $fileId, $aid, $params ) {
		
		$categoryId	= 0;
		$category	= $this->getCategory($fileId, $params);
		if (isset($category[0]->id)) {
			$categoryId = $category[0]->id;
		}
		
		$wheres[]	= " c.catid= ".(int) $categoryId;
		$wheres[]	= " c.catid= cc.id";
		if ($aid !== null) {
		
			// Should be not displayed, only in case user will add direct url
			// IF unaccessible file = 1 then display unaccessible file for all
			// IF unaccessible file = 0 then display it only for them who have access to this file
			$wheres[] = '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND c.access <= ' . (int) $aid.') )';
			$wheres[] = '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND cc.access <= ' . (int) $aid.') )';
			//$wheres[] = "c.access <= " . (int) $aid;
			//$wheres[] = "cc.access <= " . (int) $aid;
		}
		$wheres[] = " c.published = 1";
		$wheres[] = " c.approved = 1";
		$wheres[] = " cc.published = 1";
		
		$wheres[] = " c.id = " . (int) $fileId;
		
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
			$results = $dispatcher->trigger('onGetDocument', array (&$wheres, &$joins, $fileId,  $params));		
			// END GWE MOD
		}
		
		$query = ' SELECT c.*, cc.id AS categoryid, cc.title AS categorytitle, cc.alias AS categoryalias, cc.access as cataccess, cc.accessuserid as cataccessuserid, lc.title AS licensetitle, lc.description AS licensetext, lc.id AS licenseid'
				.' FROM #__phocadownload AS c' 
				.' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = c.catid'
				.' LEFT JOIN #__phocadownload_licenses AS lc ON lc.id = c.confirm_license'
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				.' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY c.ordering';
				
		return $query;
	}
	
	function getCategory($fileId, $params) {
		
		if (empty($this->_category)) {	
			global $mainframe;
			$user 			= &JFactory::getUser();
			$aid 			= $user->get('aid', 0);			
			$query			= $this->_getCategoryQuery( $fileId, $aid, $params );
			$this->_category= $this->_getList( $query, 0, 1 );
		}
		return $this->_category;
	}
	
	function _getCategoryQuery( $fileId, $aid, $params ) {
		
		$wheres[]	= " c.id= ".(int)$fileId;
		if ($aid !== null) {
			$wheres[] = "cc.access <= " . (int) $aid;
		}
		$wheres[] = " cc.published = 1";
		
		// OSE Starts;
		/*if (JComponentHelper::isEnabled('com_osemsc', true)) {
            require_once (JPATH_ADMINISTRATOR . DS . "components" . DS . "com_osemsc" . DS . "warehouse" . DS . "api.php");
            $checkmsc = new OSEMSCAPI();
            $checkmsc->ACLCheck("phoca", "cat", $filename[0]->catid, true);
		}*/
		// OSE Modified Ends;
		
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;
		$pQ			= $paramsC->get( 'enable_plugin_query', 0 );
		if ($pQ == 1) {
			// GWE MOD - to allow for access restrictions
			JPluginHelper::importPlugin("phoca");
			$dispatcher	   =& JDispatcher::getInstance();
			$joins = array();
			$results = $dispatcher->trigger('onGetCategory', array (&$wheres, &$joins, $fileId,  $params));	
			// END GWE MOD
		}
		
		$query = " SELECT cc.id, cc.title, cc.alias, cc.image, cc.image_position, cc.description, cc.access as cataccess, cc.accessuserid as cataccessuserid, s.id AS sectionid, s.title AS sectiontitle, s.alias AS sectionalias"
				. " FROM #__phocadownload_categories AS cc"
				. " LEFT JOIN #__phocadownload AS c ON c.catid = cc.id"
				. " LEFT JOIN #__phocadownload_sections AS s ON s.id = cc.section"
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
		
		
		$query = ' SELECT c.catid, c.filename, c.directlink, c.access, c.metakey, c.metadesc, cc.access as cataccess, cc.accessuserid as cataccessuserid '
				.' FROM #__phocadownload AS c, #__phocadownload_categories AS cc '
				. ($pQ == 1 ? ((count($joins)>0?( " LEFT JOIN " .implode( " LEFT JOIN ", $joins )):"")):"") // GWE MOD
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY c.ordering";
				
		// - - - - - - - - - - - - - - - 
		// Should be not displayed but user can paste some direct link into browser
		// So don't download if user has no access
		$filename = $this->_getList( $query, 0, 1 );
		
		//OSE Modified Start;
		if (!empty($filename)) {
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
			
			if ($this->_directlink == 1) {
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
	
}
?>