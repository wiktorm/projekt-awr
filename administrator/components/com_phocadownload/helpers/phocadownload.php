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
jimport('joomla.application.component.controller');
jimport( 'joomla.filesystem.folder' ); 
jimport( 'joomla.filesystem.file' );

class PhocaDownloadHelper
{	
	function filterCategory($query, $active = NULL, $frontend = NULL) {
		$db	= & JFactory::getDBO();

		$form = 'adminForm';
		if ($frontend == 1) {
			$form = 'phocadownloadfilesform';
		}
		
		$categories[] = JHTML::_('select.option', '0', '- '.JText::_('PHOCADOWNLOAD_SELECT_CATEGORY').' -');
		$db->setQuery($query);
		$categories = array_merge($categories, $db->loadObjectList());

		$category = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1" onchange="document.'.$form.'.submit( );"', 'value', 'text', $active);

		return $category;
	}
	
	function filterSection($query, $active = NULL, $frontend = NULL) {
		$db	= & JFactory::getDBO();
		$form = 'adminForm';
		if ($frontend == 1) {
			$form = 'phocadownloadfilesform';
		}
	
		$sections[] = JHTML::_('select.option', '0', '- '.JText::_('PHOCADOWNLOAD_SELECT_SECTION').' -');
		$db->setQuery( $query );
		$sections = array_merge($sections, $db->loadObjectList());

		$section = JHTML::_( 'select.genericlist', $sections, 'filter_sectionid',  'class="inputbox" size="1" onchange="document.'.$form.'.submit( );"' , 'value', 'text', $active );
		
		return $section;
	}
	
	function strTrimAll($input) {
		$output	= '';
	    $input	= trim($input);
	    for($i=0;$i<strlen($input);$i++) {
	        if(substr($input, $i, 1) != " ") {
	            $output .= trim(substr($input, $i, 1));
	        } else {
	            $output .= " ";
	        }
	    }
	    return $output;
	}
	
	function resetHits($redirect, $id)
	{
		global $mainframe;

		// Initialize variables
		$db	= & JFactory::getDBO();

		// Instantiate and load an article table
		$row = & JTable::getInstance('content');
		$row->Load($id);
		$row->hits = 0;
		$row->store();
		$row->checkin();

		$msg = JText::_('Successfully Reset Hit count');
		$mainframe->redirect('index.php?option=com_content&sectionid='.$redirect.'&task=edit&id='.$id, $msg);
	}
	
	function getTitleFromFilenameWithoutExt (&$filename) {
	
		$folder_array		= explode('/', $filename);//Explode the filename (folder and file name)
		$count_array		= count($folder_array);//Count this array
		$last_array_value 	= $count_array - 1;//The last array value is (Count array - 1)	
		
		$string = false;
		$string = preg_match( "/\./i", $folder_array[$last_array_value] );
		if ($string) {
			return PhocaDownloadHelper::removeExtension($folder_array[$last_array_value]);
		} else {
			return $folder_array[$last_array_value];
		}
	}
	
	function removeExtension($file_name) {
		return substr($file_name, 0, strrpos( $file_name, '.' ));
	}
	
	function getExtension( $file_name ) {
		return strtolower( substr( strrchr( $file_name, "." ), 1 ) );
	}
	
	
	function getPathSet($item='') {
	
		if ($item == 'icon' || $item == 'iconspec1' || $item == 'iconspec2') {
			$path['orig_abs_ds'] 			= JPATH_ROOT . DS . 'images' . DS . 'phocadownload' . DS ;
			$path['orig_abs'] 				= JPATH_ROOT . DS . 'images' . DS . 'phocadownload' ;
			$path['orig_abs_user_upload'] 	= $path['orig_abs'] . DS . 'userupload' ;
			$path['orig_rel_ds'] 	= '../images/phocadownload/';
		} else {
			// File
			//$paramsC		= &JComponentHelper::getParams( 'com_phocadownload' );
			//$downloadFolder	= $paramsC->get( 'download_folder', 'phocadownload' );
			
			// Absolute path which can be outside public_html
			$absolutePath	= PhocaDownloadHelper::getSettings('absolute_path', '');
			if ($absolutePath != '') {
				$downloadFolder 		= str_replace('/', DS, JPath::clean($absolutePath));
				$path['orig_abs_ds'] 	= $absolutePath . DS ;
				$path['orig_abs'] 		= $absolutePath ;
				$path['orig_abs_user_upload'] 	= $path['orig_abs'] . DS . 'userupload' ;
				
				//$downloadFolderRel 	= str_replace(DS, '/', JPath::clean($downloadFolder));
				$path['orig_rel_ds'] 	= '';
			} else {
				$downloadFolder	= PhocaDownloadHelper::getSettings('download_folder', 'phocadownload' );

				$downloadFolder 		= str_replace('/', DS, JPath::clean($downloadFolder));
				$path['orig_abs_ds'] 	= JPATH_ROOT . DS . $downloadFolder . DS ;
				$path['orig_abs'] 		= JPATH_ROOT . DS . $downloadFolder ;
				$path['orig_abs_user_upload'] 	= $path['orig_abs'] . DS . 'userupload' ;
				
				$downloadFolderRel 	= str_replace(DS, '/', JPath::clean($downloadFolder));
				$path['orig_rel_ds'] 	= '../' . $downloadFolderRel .'/';
			}
		}
		return $path;
	}
	
	function getPhocaVersion()
	{
		$folder = JPATH_ADMINISTRATOR .DS. 'components'.DS.'com_phocadownload';
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$folder = JPATH_SITE .DS. 'components'.DS.'com_phocadownload';
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}

		$xml_items = '';
		if (count($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
					foreach($data as $key => $value) {
						$xml_items[$key] = $value;
					}
				}
			}
		}
		
		if (isset($xml_items['version']) && $xml_items['version'] != '' ) {
			return $xml_items['version'];
		} else {
			return '';
		}
	}
	
	function getFileSize($filename, $readable = 1) {
		
		$path			= &PhocaDownloadHelper::getPathSet();
		$fileNameAbs	= JPath::clean($path['orig_abs'] . DS . $filename);
		
		if ($readable == 1) {
			return PhocaDownloadHelper::getFileSizeReadable(filesize($fileNameAbs));
		} else {
			return filesize($fileNameAbs);
		}
	}
	
	function getFileTime($filename, $function, $format = "%d. %B %Y") {
		
		$path			= &PhocaDownloadHelper::getPathSet();
		$fileNameAbs	= JPath::clean($path['orig_abs'] . DS . $filename);
		if (JFile::exists($fileNameAbs)) {
			switch($function) {
				case 2:
					$fileTime = filectime($fileNameAbs);
				break;
				case 3:
					$fileTime = fileatime($fileNameAbs);
				break;
				case 1:
				default:
					$fileTime = filemtime($fileNameAbs);
				break;
			}
			
			$fileTime = JHTML::Date($fileTime, $format);
		} else {
			$fileTime = '';
		}
		return $fileTime;
	}

	
	function getFileSizeReadable ($size, $retstring = null)//http://aidanlister.com/repos/v/function.size_readable.php
	{
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        if ($retstring === null) { $retstring = '%01.2f %s'; }
        $lastsizestring = end($sizes);
        foreach ($sizes as $sizestring) {
                if ($size < 1024) { break; }
                if ($sizestring != $lastsizestring) { $size /= 1024; }
        }
        if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } // Bytes aren't normally fractional
        return sprintf($retstring, $size, $sizestring);
	}
	
	function getTitleFromFilenameWithExt (&$filename) {
		$folder_array		= explode('/', $filename);//Explode the filename (folder and file name)
		$count_array		= count($folder_array);//Count this array
		$last_array_value 	= $count_array - 1;//The last array value is (Count array - 1)	
		
		return $folder_array[$last_array_value];
	}

	
	function getMimeType($extension, $params) {
		
		$regex_one		= '/({\s*)(.*?)(})/si';
		$regex_all		= '/{\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$params,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		$returnMime = '';
		
		for($i = 0; $i < $count_matches; $i++) {
			
			$phocaDownload	= $matches[0][$i][0];
			preg_match($regex_one,$phocaDownload,$phocaDownloadParts);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");
			$values = explode("=", $phocaDownloadParts[2], 2);	
			
			foreach ($values_replace as $key2 => $values2) {
				$values = preg_replace($values2, '', $values);
			}

			// Return mime if extension call it
			if ($extension == $values[0]) {
				$returnMime = $values[1];
			}
		}

		if ($returnMime != '') {
			return $returnMime;
		} else {
			return "PhocaErrorNoMimeFound";
		}
	}
	
	function getMimeTypeString($params) {
		
		$regex_one		= '/({\s*)(.*?)(})/si';
		$regex_all		= '/{\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$params,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		$extString 	= '';
		$mimeString	= '';
		
		for($i = 0; $i < $count_matches; $i++) {
			
			$phocaDownload	= $matches[0][$i][0];
			preg_match($regex_one,$phocaDownload,$phocaDownloadParts);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");
			$values = explode("=", $phocaDownloadParts[2], 2);	
			
			foreach ($values_replace as $key2 => $values2) {
				$values = preg_replace($values2, '', $values);
			}
				
			// Create strings
			$extString .= $values[0];
			$mimeString .= $values[1];
			
			$j = $i + 1;
			if ($j < $count_matches) {
				$extString .=',';
				$mimeString .=',';
			}
		}
		
		$string 		= array();
		$string['mime']	= $mimeString;
		$string['ext']	= $extString;
		
		return $string;
	}
	
	function getSettings($title = '', $default = '') {
	
		$db		=& JFactory::getDBO();
		$wheres = array();
		
		if ($title == '') {
			$select	= 'st.*';
			$where	= '';
		} else {
			$select		= 'st.value';
			$wheres[]	= 'st.title =\''.$title.'\'';
			
			$where = " WHERE " . implode( " AND ", $wheres );
		}
		
		
		$query = ' SELECT '.$select
			. ' FROM #__phocadownload_settings AS st'
			. $where
			. ' ORDER BY st.id';
			
		$db->setQuery($query);
		$settings = $db->loadObjectList();
		
		
		// All ITEMS
		if ($title == '') {
			return $settings;
		} else {
			// ONLY ONE ITEM
			if (empty($settings)) {
				return $default;
			} else {
				if (isset($settings[0]->value)) {
					return($settings[0]->value);
				} else {
					return '';
				}
			}
		}
	}
	
	function getSettingsValues($params) {
		
		$regex_one		= '/({\s*)(.*?)(})/si';
		$regex_all		= '/{\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$params,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		$values = array();
		
		for($i = 0; $i < $count_matches; $i++) {
			
			$phocaDownload	= $matches[0][$i][0];
			preg_match($regex_one,$phocaDownload,$phocaDownloadParts);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");
			$values = explode("=", $phocaDownloadParts[2], 2);	
			
			foreach ($values_replace as $key2 => $values2) {
				$values = preg_replace($values2, '', $values);
			}
			
			// Create strings
			$returnValues[$i]['id']	= $values[0];
			$returnValues[$i]['value']	= $values[1];
		}

		return $returnValues;
	}
	
	
	function getTextareaSettings($id, $title, $value, $class = 'text_area', $rows = 8, $cols = 50, $style = 'width:300px' ) {
		
		return '<textarea class="'.$class.'" name="phocaset['.$id.']" id="phocaset['.$id.']" rows="'.$rows.'" cols="'.$cols.'" style="'.$style.'" title="'.JText::_( $title . ' DESC' ).'" />'.$value.'</textarea>';
	}
	
	function getTextareaEditorSettings($id, $title, $value, $class = 'text_area', $rows = 20, $cols = 60, $width = 750, $height = 300 ) {
		
		//return '<textarea class="'.$class.'" name="phocaset['.$id.']" id="phocaset['.$id.']" rows="'.$rows.'" cols="'.$cols.'" style="'.$style.'" title="'.JText::_( $title . ' DESC' ).'" />'.$value.'</textarea>';
		$editor =& JFactory::getEditor();
		return $editor->display( 'phocaset['.$id.']',  $value, $width, $height, $cols, $rows, array('pagebreak', 'readmore') ) ;
	}
	
	function getTextSettings($id, $title, $value, $class = 'text_area', $size = 50, $maxlength = 255, $style = 'width:300px' ) {
		
		return '<input class="'.$class.'" type="text" name="phocaset['.$id.']" id="phocaset['.$id.']" value="'.$value.'" size="'.$size.'" maxlength="'.$maxlength.'" style="'.$style.'" title="'.JText::_( $title . ' DESC' ).'" />';
	}
	
	function getSelectSettings($id, $title, $value, $values, $class = 'inputbox', $size = 50, $maxlength = 255, $style = 'width:300px' ) {
		
		$valuesArray = PhocaDownloadHelper::getSettingsValues($values);
		
		$select = '<select name="phocaset['.$id.']" id="phocaset['.$id.']" class="'.$class.'">'. "\n";
		foreach ($valuesArray as $valueOption) {
			if ($value == $valueOption['id']) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			
			$select .= '<option value="'.$valueOption['id'].'" '.$selected.'>'.JText::_($valueOption['value']).'</option>' . "\n";
		}
		$select .= '</select>'. "\n";

		return $select;					
	}
	
	function displayNewIcon ($date, $time = 0) {
		
		if ($time == 0) {
			return '';
		}
		
		$dateAdded 	= strtotime($date, time());
		$dateToday 	= time();
		$dateExists = $dateToday - $dateAdded;
		$dateNew	= $time * 24 * 60 * 60;
		
		if ($dateExists < $dateNew) {
			return '&nbsp;'. JHTML::_('image.site', 'icon-new.png', 'components/com_phocadownload/assets/images/', '','','new');
		} else {
			return '';
		}
	
	}
	
	function displayHotIcon ($hits, $requiredHits = 0) {
		
		if ($requiredHits == 0) {
			return '';
		}
		
		if ($requiredHits <= $hits) {
			return '&nbsp;'. JHTML::_('image.site', 'icon-hot.png', 'components/com_phocadownload/assets/images/', '','','hot');
		} else {
			return '';
		}
	
	}
	
	/**
	 * Method to display multiple select box
	 * @param string $name Name (id, name parameters)
	 * @param array $active Array of items which will be selected
	 * @param int $nouser Select no user
	 * @param string $javascript Add javascript to the select box
	 * @param string $order Ordering of items
	 * @param int $reg Only registered users
	 * @return array of id
	 */
	
	function usersList( $name, $active, $nouser = 0, $javascript = NULL, $order = 'name', $reg = 1 ) {
		
		$activeArray = $active;
		if ($active != '') {
			$activeArray = explode(',',$active);
		}
		
		$db		= &JFactory::getDBO();
		$and	= '';
		if ( $reg ) {
			// does not include registered users in the list
			$and = ' AND gid > 18';
		}

		$query = 'SELECT id AS value, name AS text'
		. ' FROM #__users'
		. ' WHERE block = 0'
		. $and
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query );
		if ( $nouser ) {
			
			// Access rights (default open for all)
			// Upload and Delete rights (default closed for all)
			switch ($name) {
				/*case 'accessuserid[]':
					$idInput1 	= -1;
					$idText1	= JText::_( 'All Registered Users' );
					$idInput2 	= -2;
					$idText2	= JText::_( 'Nobody' );
				break;*/
				
				default:
					$idInput1 	= -2;
					$idText1	= JText::_( 'Nobody' );
					$idInput2 	= -1;
					$idText2	= JText::_( 'All Registered Users' );
				break;
			}
			
			$users[] = JHTML::_('select.option',  $idInput1, '- '. $idText1 .' -' );
			$users[] = JHTML::_('select.option',  $idInput2, '- '. $idText2 .' -' );
			
			$users = array_merge( $users, $db->loadObjectList() );
		} else {
			$users = $db->loadObjectList();
		}

		$users = JHTML::_('select.genericlist',   $users, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray );

		return $users;
	}
	
	function approved( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' ) {
		$img 	= $row->approved ? $imgY : $imgX;
		$task 	= $row->approved ? 'disapprove' : 'approve';
		$alt 	= $row->approved ? JText::_( 'PHOCADOWNLOAD_APPROVED' ) : JText::_( 'PHOCADOWNLOAD_NOT_APPROVED' );
		$action = $row->approved ? JText::_( 'PHOCADOWNLOAD_DISAPPROVE_ITEM' ) : JText::_( 'PHOCADOWNLOAD_APPROVE_ITEM' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $prefix.$task .'\')" title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}
	
	function getAliasName($name) {
		$name = JFilterOutput::stringURLSafe($name);
		if(trim(str_replace('-','',$name)) == '') {
			$datenow	= &JFactory::getDate();
			$name 		= $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}
		return $name;
	}
	function isURLAddress($url) {
		return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}

	function getCategoryAccess($id) {
		
		$output = array();
		$db 	= &JFactory::getDBO();
		$query 	= 'SELECT c.access, c.uploaduserid' .
				' FROM #__phocadownload_categories AS c' .
				' WHERE c.id = '. (int) $id;
		$db->setQuery($query, 0, 1);
		$output = $db->loadObject();
		return $output;
	}
	
		function renderPhocaDownload() {
			return ''; //base64_decode('PGRpdiBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyBjb2xvcjojY2NjOyI+UG93ZXJlZCBieSA8YSBocmVmPSJodHRwOi8vd3d3LnBob2NhLmN6L3Bob2NhZG93bmxvYWQiIHN0eWxlPSJ0ZXh0LWRlY29yYXRpb246IG5vbmU7IiB0YXJnZXQ9Il9ibGFuayIgdGl0bGU9IlBob2NhIERvd25sb2FkIj5QaG9jYSBEb3dubG9hZDwvYT48L2Rpdj4=');
	}
	
	/**
	 * Method to check if the user have access to category
	 * Display or hide the not accessible categories - subcat folder will be not displayed
	 * Check whether category access level allows access
	 *
	 * E.g.: Should the link to Subcategory or to Parentcategory be displayed
	 * E.g.: Should the delete button displayed, should be the upload button displayed
	 *
	 * @param string $params rightType: accessuserid, uploaduserid, deleteuserid - access, upload, delete right
	 * @param int $params rightUsers - All selected users which should have the "rightType" right
	 * @param int $params rightGroup - All selected Groups of users(public, registered or special ) which should have the "rT" right
	 * @param int $params userAID - Specific group of user who display the category in front (public, special, registerd)
	 * @param int $params userId - Specific id of user who display the category in front (1,2,3,...)
	 * @param int $params Additional param - e.g. $display_access_category (Should be unaccessed category displayed)
	 * @return boolean 1 or 0
	 */
	
	function getUserRight($rightType = 'accessuserid', $rightUsers, $rightGroup = 0, $userAID = 0, $userId = 0 , $additionalParam = 0 ) {	
		
		$rightUsersIdArray = array();
		if (!empty($rightUsers)) {
			$rightUsersIdArray = explode( ',', trim( $rightUsers ) );
		} else {
			$rightUsersIdArray = array();
		}

		$rightDisplay = 1;
		if ($additionalParam == 0) { // We want not to display unaccessable categories ($display_access_category)
			if ($rightGroup != 0) {
			
				if ($rightGroup > $userAID) {
					$rightDisplay  = 0;
				} else { // Access level only for one registered user
					if (!empty($rightUsersIdArray)) {
						// Check if the user is contained in selected array
						$userIsContained = 0;
						foreach ($rightUsersIdArray as $key => $value) {
							if ($userId == $value) {
								$userIsContained = 1;// check if the user id is selected in multiple box
								break;// don't search again
							}
					
							// for access (-1 not selected - all registered, 0 all users)
							if ($value == -1) {
								$userIsContained = 1;// in multiple select box is selected - All registered users
								break;// don't search again
							}
						}

						if ($userIsContained == 0) {
							$rightDisplay = 0;
						}
					} else {
						
						// Access rights (default open for all)
						// Upload and Delete rights (default closed for all)
						switch ($rightType) {
							case 'accessuserid':
								$rightDisplay = 1;
							break;
							
							default:
								$rightDisplay = 0;
							break;
						}
					}
				}	
			}
		}
		return $rightDisplay;
	}
	
	function getUserFileInfo($file, $userId) {		
		
		$db 				=& JFactory::getDBO();
		$allFile['size']	= 0;
		$allFile['count']	= 0;
		$query = 'SELECT SUM(a.filesize) AS sumfiles, COUNT(a.id) AS countfiles'
				.' FROM #__phocadownload AS a'
			    .' WHERE a.owner_id = '.(int)$userId;
		$db->setQuery($query, 0, 1);
		$fileData = $db->loadObject();
		
		if(isset($fileData->sumfiles) && (int)$fileData->sumfiles > 0) {
			$allFile['size'] = (int)$allFile['size'] + (int)$fileData->sumfiles;
		}
		
		if (isset($file['size'])) {
				$allFile['size'] = (int)$allFile['size'] + (int)$file['size'];
				$allFile['count'] = (int)$fileData->countfiles + 1;
		}
		
		return $allFile;
	}
	
	/*
	 * param method 1 = download, 2 = upload
	 */
	function sendPhocaDownloadMail ( $id, $fileName, $method = 1 ) {
		global $mainframe;
		$db 		= JFactory::getDBO();
		$sitename 	= $mainframe->getCfg( 'sitename' );
		$mailfrom 	= $mainframe->getCfg( 'mailfrom' );
		$fromname	= $sitename;
		$date		= JHTML::_('date',  gmdate('Y-m-d H:i:s'), JText::_( 'DATE_FORMAT_LC2' ));
		$user 		= &JFactory::getUser();
		$params 	= &$mainframe->getParams();
		
		if (isset($user->name) && $user->name != '') {
			$name = $user->name;
		} else {
			$name = JText::_('Anonymous');
		}
		if (isset($user->username) && $user->username != '') {
			$userName = ' ('.$user->username.')';
		} else {
			$userName = '';
		}
		
		if ($method == 1) {
			$subject 		= $sitename. ' - ' . JText::_( 'File downloaded' );
			$title 			= JText::_( 'File downloaded' );
			$messageText 	= JText::_( 'File') . ' "' .$fileName . '" '.JText::_('was downloaded by'). ' '.$name . $userName.'.';
		} else {
			$subject 		= $sitename. ' - ' . JText::_( 'File uploaded' );
			$title 			= JText::_( 'New File uploaded' );
			$messageText 	= JText::_( 'File') . ' "' .$fileName . '" '.JText::_('was uploaded by'). ' '.$name . $userName.'.';
		}
		
		//get all super administrator
		$query = 'SELECT name, email, sendEmail' .
		' FROM #__users' .
		' WHERE id = '.(int)$id;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		
		if (isset($rows[0]->email)) {
			$email 	= $rows[0]->email;
		}

		
		$message = $title . "\n\n"
		. JText::_( 'Website' ) . ': '. $sitename . "\n"
		. JText::_( 'Date' ) . ': '. $date . "\n"
		. 'IP: ' . $_SERVER["REMOTE_ADDR"]. "\n\n"
		. JText::_( 'Message' ) . ': '."\n"
		. "\n\n"
		. $messageText
		. "\n\n"
		. JText::_( 'Regards' ) .", \n"
		. $sitename ."\n";
					
		$subject = html_entity_decode($subject, ENT_QUOTES);
		$message = html_entity_decode($message, ENT_QUOTES);
		
		JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);	
		return true;
	}
}
?>