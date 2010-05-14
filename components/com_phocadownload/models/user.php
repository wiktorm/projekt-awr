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

class PhocaDownloadModelUser extends JModel
{
	var $_data_files 			= null;
	var $_total_files	 		= null;
	var $_pagination_files 		= null;
	var $_context_files			= 'com_phocadownload.phocadownloaduserfiles';
	

	function __construct() {
		parent::__construct();

		global $mainframe;
		// SubCategory
		$limit_files		= $mainframe->getUserStateFromRequest( $this->_context_files.'.list.limit', 'limit', 20, 'int' );
		$limitstart_files 	= JRequest::getVar('limitstart', 0, '', 'int');
		$limitstart_files 	= ($limit_files != 0 ? (floor($limitstart_files / $limit_files) * $limit_files) : 0);
		$this->setState($this->_context_files.'.list.limit', $limit_files);
		$this->setState($this->_context_files.'.list.limitstart', $limitstart_files);

	}
	
	function getDataFiles($userId) {
		if (empty($this->_data_files)) {
			$query = $this->_buildQueryFiles($userId);
			$this->_data_files = $this->_getList($query, $this->getState($this->_context_files.'.list.limitstart'), $this->getState($this->_context_files.'.list.limit'));
			
		}
		return $this->_data_files;
	}

	function getTotalFiles($userId) {
		if (empty($this->_total_files)) {
			$query = $this->_buildQueryFiles($userId);
			$this->_total_files = $this->_getListCount($query);
		}
		return $this->_total_files;
	}
	
	function getPaginationFiles($userId) {
		if (empty($this->_pagination_files)) {
			jimport('joomla.html.pagination');
			$this->_pagination_files = new JPagination( $this->getTotalFiles($userId),  $this->getState($this->_context_files.'.list.limitstart'), $this->getState($this->_context_files.'.list.limit') );
		}
		return $this->_pagination_files;
	}
	
	function _buildQueryFiles($userId) {
		$where		= $this->_buildContentWhereFiles($userId);
		$orderby	= $this->_buildContentOrderByFiles();
			
		$query = ' SELECT a.*, cc.title AS categorytitle, s.title AS sectiontitle, u.name AS editor, g.name AS groupname, us.id AS ownerid, us.username AS ownername '
			. ' FROM #__phocadownload AS a '
			. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = a.catid'
			. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = a.sectionid'
			. ' LEFT JOIN #__groups AS g ON g.id = a.access'
			. ' LEFT JOIN #__users AS u ON u.id = a.checked_out'
			. ' LEFT JOIN #__users AS us ON us.id = a.owner_id'
			. $where
			. $orderby;
		return $query;
	}

	
	function _buildContentOrderByFiles() {
		global $mainframe;
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_order',	'filter_order',	'a.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );

		if ($filter_order == 'a.ordering'){
			$orderby 	= ' ORDER BY sectiontitle, categorytitle, a.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , sectiontitle, categorytitle, a.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhereFiles($userId) {
		global $mainframe;
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_state','filter_state','',	'word' );
		$filter_catid		= $mainframe->getUserStateFromRequest( $this->_context_files.'.catid','catid',0,'int' );
		$filter_sectionid	= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_sectionid',	'filter_sectionid',	0,	'int' );
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_order','filter_order','a.ordering','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_order_Dir','filter_order_Dir_files',	'', 'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context_files.'.search', 'search', '', 'string' );
		$search				= JString::strtolower( $search );
		
		$where = array();
		
		$where[] = 'a.owner_id = '.(int)$userId;
		$where[] = 'a.owner_id > 0'; // Ignore -1

		if ($filter_catid > 0) {
			$where[] = 'a.catid = '.(int) $filter_catid;
		}
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
		if ( $filter_sectionid ) {
			$where[] = 'cc.section = '.(int)$filter_sectionid;
		}
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
	
	
	
	
	
	/*
	 * Add Image
	 */
/*	
	function storefile($data, $return, $edit = false) {
		
		if (!$edit) {
			//If this file doesn't exists don't save it
			if (!PhocaGalleryFile::existsFileOriginal($data['filename'])) {
				$this->setError('File not exists');
				return false;
			}
			
			$data['imgorigsize'] 	= PhocaGalleryFile::getFileSize($data['filename'], 0);
			
			//If there is no title and no alias, use filename as title and alias
			if (!isset($data['title']) || (isset($data['title']) && $data['title'] == '')) {
				$data['title'] = PhocaGalleryFile::getTitleFromFile($data['filename']);
			}

			if (!isset($data['alias']) || (isset($data['alias']) && $data['alias'] == '')) {
				$data['alias'] = PhocaGalleryFile::getTitleFromFile($data['filename']);
			}
			
			//clean alias name (no bad characters)
			$data['alias'] = PhocaGalleryText::getAliasName($data['alias']);
			
		} else {
			$data['alias'] = PhocaGalleryText::getAliasName($data['title']);
		}
		
		$row =& $this->getTable('phocadownload');
		
		
		if(isset($data['id']) && $data['id'] > 0) {
			if (!$row->load($data['id'])) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		
		// Bind the form fields to the Phoca gallery table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Create the timestamp for the date
		$row->date 				= gmdate('Y-m-d H:i:s');
		
		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = 'catid = ' . (int) $row->catid ;
			$row->ordering = $row->getNextOrder( $where );
		}

		// Make sure the Phoca gallery table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the Phoca gallery table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		if(!$edit) {
			//Create thumbnail small, medium, large	
			$returnFrontMessage = PhocaGalleryFileThumbnail::getOrCreateThumbnail($row->filename, $return, 1, 1, 1, 1);
			
			if ($returnFrontMessage == 'Success') {
				return true;
			} else {
				return false;
			}
		} else {
			if (isset($row->id)) {
				return $row->id;
			} else {
				return false;
			}
		}
	}
	*/
	
	function singleFileUpload(&$errUploadMsg, $file, $post) {
	
		global $mainframe;
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );
		jimport('joomla.client.helper');
		$user 				= &JFactory::getUser();
		$ftp 		=& JClientHelper::setCredentialsFromRequest('ftp');
		$path		= PhocaDownloadHelper::getPathSet();
		$folder		= JRequest::getVar( 'folder', '', '', 'path' );
		$format		= JRequest::getVar( 'format', 'html', '', 'cmd');
		$return		= JRequest::getVar( 'return-url', null, 'post', 'base64' );
		$viewBack	= JRequest::getVar( 'viewback', '', 'post', 'string' );
		//$catid 		= JRequest::getVar( 'catid', '', '', 'int'  );
		$paramsC 	= JComponentHelper::getParams('com_phocadownload') ;

	
		
		// USER RIGHT - UPLOAD - - - - - - - - - - -
		// 2, 2 means that user access will be ignored in function getUserRight for display Delete button
		$rightDisplayUpload	= 0;
		
		$catAccess	= PhocaDownloadHelper::getCategoryAccess((int)$post['catidfiles']);
	
		if (!empty($catAccess)) {
			$rightDisplayUpload = PhocaDownloadHelper::getUserRight('uploaduserid', $catAccess->uploaduserid, 2, 2, $user->get('id', 0), 0);
		}
		// - - - - - - - - - - - - - - - - - - - - - -	
		
		
		$post['sectionid'] = $this->getSection((int)$post['catidfiles']);
		if(!$post['sectionid']) {
			$errUploadMsg = JText::_('PHOCADOWNLOAD_WRONG_SECTION');	
			return false;
		}
		
		$userFolder = substr(md5($user->username),0, 10);
		if ($rightDisplayUpload == 1) {
		
			// Check the size of all images by users
			$maxUserUploadSize 	= (int)$paramsC->get( 'user_files_max_size', 20971520 );
			$maxUserUploadCount	= (int)$paramsC->get( 'user_files_max_count', 5 );
			$allFile	= PhocaDownloadHelper:: getUserFileInfo($file, $user->id);


			if ($maxUserUploadSize > 0 && (int) $allFile['size'] > $maxUserUploadSize) {
				$errUploadMsg = JText::_('PHOCADOWNLOAD_WARNUSERFILESTOOLARGE');	
				return false;
			}
			
			if ((int) $allFile['count'] > $maxUserUploadCount) {
				$errUploadMsg = JText::_('PHOCADOWNLOAD_WARNUSERFILESTOOMUCH');	
				return false;
			}

			// Make the filename safe
			if (isset($file['name'])) {
				$file['name']	= JFile::makeSafe($file['name']);
			}
			
			if (isset($file['name'])) {
				$filepath 				= JPath::clean($path['orig_abs_user_upload']. DS. $userFolder . DS.$file['name']);
				$filepathUserFolder 	= JPath::clean($path['orig_abs_user_upload']. DS. $userFolder);
				if (!PhocaDownloadHelperUpload::canUpload( $file, $errUploadMsg, 'file', 1 )) {
				
					if ($errUploadMsg == 'PHOCADOWNLOAD_WARNFILETOOLARGE') {
						$errUploadMsg 	= JText::_($errUploadMsg) . ' ('.PhocaDownloadHelper::getFileSizeReadable($file['size']).')';
					} else {
						$errUploadMsg 	= JText::_($errUploadMsg);
					}
					return false;
				}

				if (JFile::exists($filepath)) {
					$errUploadMsg = JText::_("PHOCADOWNLOAD_FILE_ALREADY_EXISTS");
					return false;
				}

				if (!JFile::upload($file['tmp_name'], $filepath)) {
					$errUploadMsg = JText::_("PHOCADOWNLOAD_UNABLE_TO_UPLOAD_FILE");
					return false;
				} else {
					// Saving file name into database with relative path
					if (!JFile::exists($filepathUserFolder . DS ."index.html")) {
						@JFile::write($filepathUserFolder . DS ."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
					}
					$file['name']	=  'userupload/'.$userFolder.'/' . $file['name'];
					$succeeded 		= false;
					if ($this->_save($post, $file['name'], $errUploadMsg)) {
						return true;
					} else {
						return false;
					}
				}
			} else {				
				$errUploadMsg = JText::_("PHOCADOWNLOAD_WARNFILETYPE");	
				$redirectUrl = $return;				
				return false;
			}
		} else {			
			$errUploadMsg = JText::_("PHOCADOWNLOAD_NOT_AUTHORISED_TO_UPLOAD");			
			
			return false;
		}
		return false;
		
		
	}
	
	function _save($data, $filename, &$errSaveMsg) {

		$row =& $this->getTable('phocadownload');

		$data['filesize'] 	= PhocaDownloadHelper::getFileSize($filename, 0);
		
		// Bind the form fields to the Phoca gallery table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Create the timestamp for the date
		//$row->date 			= gmdate('Y-m-d H:i:s');
		//$row->publish_up	= gmdate('Y-m-d H:i:s');
		$jnow		=& JFactory::getDate();
		if (isset($jnow->_date)) {
			$jnow->_date = (int)$jnow->_date - 2; // to not display pending because of 1 second
		}
		$now				= $jnow->toMySQL();
		$row->date 			= $now;
		$row->publish_up	= $now;
		$row->publish_down	= null;
		$row->filename	= $filename;
		$row->catid		= $data['catidfiles'];

		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = 'catid = ' . (int) $row->catid ;
			$row->ordering = $row->getNextOrder( $where );
		}

		// Make sure the Phoca gallery table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the Phoca gallery table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}
			
	function getSection($catid) {

		$query = 'SELECT c.section'
			. ' FROM #__phocadownload_categories AS c'
			. ' WHERE c.id = '.(int)$catid;
		
		$this->_db->setQuery( $query );
		$sectionId = $this->_db->loadObject();
	
		if (isset($sectionId->section)) {
			return $sectionId->section;
		}
		return false;
	}
}
?>