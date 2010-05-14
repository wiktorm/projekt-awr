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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class PhocaDownloadCpModelPhocaDownloadManager extends JModel
{
	function getState($property = null) {
		static $set;

		if (!$set) {
			$folder		= JRequest::getVar( 'folder', '', '', 'path' );
			$upload		= JRequest::getVar( 'upload', '', '', 'int' );
			$manager	= JRequest::getVar( 'manager', '', '', 'path' );
			
			$this->setState('folder', $folder);
			$this->setState('manager', $manager);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			
			$set = true;
		}
		return parent::getState($property);
	}

	function getFiles() {
		$list = $this->getList();
		return $list['files'];
	}

	function getFolders() {
		$list = $this->getList();
		return $list['folders'];
	}

	function getList() {
		static $list;

		//Params
		$params	= &JComponentHelper::getParams( 'com_phocadownload' );

		// Only process the list once per request
		if (is_array($list)) {
			return $list;
		}

		// Get current path from request
		$current = $this->getState('folder');

		// If undefined, set to empty
		if ($current == 'undefined') {
			$current = '';
		}
		
		// File Manager, Icon Manager
		$manager = $this->getState('manager');
		if ($manager == 'undefined') {
			$manager = '';
		}
		$path = phocadownloadHelper::getPathSet($manager);

		//$path = phocadownloadHelper::getPathSet();
		
		// Initialize variables
		if (strlen($current) > 0) {
			$orig_path = $path['orig_abs_ds'].$current;
		} else {
			$orig_path = $path['orig_abs_ds'];
		}
		$orig_path_server 	= str_replace(DS, '/', $path['orig_abs'] .'/');
		
		// Absolute Path defined by user
		$absolutePath	= PhocaDownloadHelper::getSettings('absolute_path', '');
		if ($absolutePath != '') {
			$orig_path_server 		= $absolutePath ;
		}
		
		$files 		= array ();
		$folders 	= array ();

		// Get the list of files and folders from the given folder
		$file_list 		= JFolder::files($orig_path);
		$folder_list 	= JFolder::folders($orig_path, '', false, false, array());
		
		// Iterate over the files if they exist
		//file - abc.img, file_no - folder/abc.img
		if ($file_list !== false) {
			foreach ($file_list as $file) {
				if (is_file($orig_path.DS.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {			
						$tmp 							= new JObject();
						$tmp->name 						= basename($file);
						$tmp->path_with_name 			= str_replace(DS, '/', JPath::clean($orig_path . DS . $file));
						$tmp->path_without_name_relative= $path['orig_rel_ds'] . str_replace($orig_path_server, '', $tmp->path_with_name);
						$tmp->path_with_name_relative_no= str_replace($orig_path_server, '', $tmp->path_with_name);	
						$files[] = $tmp;
						
				}	
			}
		}

		// Iterate over the folders if they exist
		if ($folder_list !== false) {
			foreach ($folder_list as $folder)
			{
				$tmp 							= new JObject();
				$tmp->name 						= basename($folder);
				$tmp->path_with_name 			= str_replace(DS, '/', JPath::clean($orig_path . DS . $folder));
				$tmp->path_without_name_relative= $path['orig_rel_ds'] . str_replace($orig_path_server, '', $tmp->path_with_name);
				$tmp->path_with_name_relative_no= str_replace($orig_path_server, '', $tmp->path_with_name);	

				$folders[] = $tmp;
			}
		}

		$list = array('folders' => $folders, 'files' => $files);
		return $list;
	}
}
?>