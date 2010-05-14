<?php
defined('_JEXEC') or die('Restricted access'); 
if ( $this->params->def( 'show_page_title', 1 ) ) {
	echo '<div class="componentheading'.$this->params->get( 'pageclass_sfx' ).'">'.$this->params->get('page_title').'</div>';
}
echo '<div id="phoca-dl-file-box">';
if (!empty($this->category[0])) {
	echo '<div class="pd-file">';
	if ($this->tmpl['display_up_icon'] == 1) {
		//echo '<div class="pdtop"><a title="'.JText::_('Category').'" href="'. JRoute::_('index.php?option=com_phocadownload&view=category&id='.$this->category[0]->id.':'.$this->category[0]->alias. $this->tmpl['limitstarturl'] . '&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'" >'.JHTML::_('image', 'components/com_phocadownload/assets/images/up.png', JText::_('Up')).  '</a></div>';

		echo '<div class="pdtop"><a title="'.JText::_('Category').'" href="'. JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($this->category[0]->id, $this->category[0]->alias, $this->category[0]->sectionid). $this->tmpl['limitstarturl']).'" >'.JHTML::_('image', 'components/com_phocadownload/assets/images/up.png', JText::_('Up')).  '</a></div>';
	
	}
} else {
	echo '<div class="pd-file"><div class="pdtop"></div>';
}



if (!empty($this->file[0])) {
	$valueDoc = $this->file[0];
	
	// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
	// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
	$rightDisplay	= 0;
	if (!empty($this->category[0])) {
		$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $valueDoc->cataccessuserid, $valueDoc->cataccess, $this->tmpl['user']->get('aid', 0), $this->tmpl['user']->get('id', 0), 0);
	}
	// - - - - - - - - - - - - - - - - - - - - - -
	if ($rightDisplay == 1) {
		
		// Title or name		
		if ($this->tmpl['filename_or_name'] == 'title') {
			$displayName = $valueDoc->title;
		} else if ($this->tmpl['filename_or_name'] == 'filename'){
			$displayName = PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename );
		} else if ($this->tmpl['filename_or_name'] == 'filenametitle'){
			$displayName = $valueDoc->title . ' - '.PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename );
		}

		echo '<h3>'.$displayName. '</h3>';
		
		
		if ((int)$this->tmpl['display_file_view'] == 1) {
		
			$details = '';
			if ($valueDoc->title != '') {
				$details .= '<h4>'.$valueDoc->title.'</h4>';
			}
			$details = '<table class="pd-file-details" border="0">';
			
			if ($valueDoc->filename != '') {
			
				// IMAGE FILENAME
				$imageFileName = '';
				$imageFileNameThumbnail = '';
				if ($valueDoc->image_filename !='') {
					$thumbnail = false;
					$thumbnail = preg_match("/phocathumbnail/i", $valueDoc->image_filename);
					if ($thumbnail) {
						$imageFileNameThumbnail = '<div style="margin-top:5px;margin-bottom:5px" ><img src="'.$this->cssimagepath.$valueDoc->image_filename.'" alt="" /></div>';
						$imageFileName 			= '';
					} else {
						$imageFileNameThumbnail = '';
						$imageFileName 			= 'style="background: url(\''.$this->cssimagepath.$valueDoc->image_filename.'\') 0 center no-repeat;"';
					}
				}
			
			
				$details .= '<tr><td><strong>'.JText::_('File Name').'</strong>:</td>';
				
				$details .= '<td class="pdfile">'. $imageFileNameThumbnail.'<div class="pd-document'.$this->tmpl['file_icon_size'].'" '.$imageFileName.'><div class="pd-float">';
				$details .= PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename );
				//if ($fileSize !='') {
				//	$details .= ' <small style="color:#ccc">('.$fileSize.')</small>';
				//}
				$details .= '</div>';
				
				$details .= PhocaDownloadHelper::displayNewIcon($valueDoc->date, $this->tmpl['displaynew']);
				$details .= PhocaDownloadHelper::displayHotIcon($valueDoc->hits, $this->tmpl['displayhot']);
				
				//Specific icons
				if (isset($valueDoc->image_filename_spec1) && $valueDoc->image_filename_spec1 != '') {
					$iconPath	= PhocaDownloadHelper::getPathSet('icon');
					$iconPath	= str_replace ( '../', JURI::base(true).'/', $iconPath['orig_rel_ds']);
					$details .= '<div class="pd-float"><img src="'.$iconPath . $valueDoc->image_filename_spec1.'" alt="" /></div>';
				} 
				if (isset($valueDoc->image_filename_spec2) && $valueDoc->image_filename_spec2 != '') {
					$iconPath	= PhocaDownloadHelper::getPathSet('icon');
					$iconPath	= str_replace ( '../', JURI::base(true).'/', $iconPath['orig_rel_ds']);
					$details .= '<div class="pd-float"><img src="'.$iconPath . $valueDoc->image_filename_spec2.'" alt="" /></div>';
				} 
				
				$details .= '</div></td></tr>' . "\n";

			}
			
			// FILESIZE
			if ($valueDoc->filename !='') {
				$absFile = str_replace('/', DS, JPath::clean($this->absfilepath . $valueDoc->filename));
				if (JFile::exists($absFile))
				{
					$fileSize = PhocaDownloadHelper::getFileSizeReadable(filesize($absFile));
				} else {
					$fileSize = '';
				}
			}
			
			if ($fileSize != '') {
				$details .= '<tr><td><strong>'.JText::_('File Size').'</strong>:</td><td>'.$fileSize.'</td></tr>';
			}
			
			if ($valueDoc->version != '') {
				$details .= '<tr><td><strong>'.JText::_('Version').'</strong>:</td><td>'.$valueDoc->version.'</td></tr>';
			}
			if ($valueDoc->license != '') {
				if ($valueDoc->license_url != '') {
					$details .= '<tr><td><strong>'.JText::_('License').'</strong>:</td><td>'.'<a href="'.$valueDoc->license_url.'" target="_blank">'.$valueDoc->license.'</a></td></tr>';
				} else {
					$details .= '<tr><td><strong>'.JText::_('License').'</strong>:</td><td>'.$valueDoc->license.'</td></tr>';
				}
			}
			if ($valueDoc->author != '') {
				if ($valueDoc->author_url != '') {
					$details .= '<tr><td><strong>'.JText::_('Author').'</strong>:</td><td>'.'<a href="'.$valueDoc->author_url.'" target="_blank">'.$valueDoc->author.'</a></td></tr>';
				} else {
					$details .= '<tr><td><strong>'.JText::_('Author').'</strong>:</td><td>'.$valueDoc->author.'</td></tr>';
				}
			}
			if ($valueDoc->author_email != '') {
				//$details .= '<tr><td><strong>'.JText::_('Email').'</strong>:</td><td>'. JHTML::_( 'email.cloak', $valueDoc->author_email).'</td></tr>';
				$protectMail = str_replace('@', '['.JText::_('at').']', $valueDoc->author_email);
				$protectMail = str_replace('.', '['.JText::_('dot').']', $protectMail);
				$details .= '<tr><td><strong>'.JText::_('Email').'</strong>:</td><td>'. $protectMail.'</td></tr>';
			}
			
			// FILEDATE
			$fileDate = '';
			if ((int)$this->tmpl['display_date_type'] > 0) {
				if ($valueDoc->filename !='') {
					$fileDate = PhocaDownloadHelper::getFileTime($valueDoc->filename, $this->tmpl['display_date_type']);
				}
			} else {
				$fileDate = JHTML::Date($valueDoc->date, "%d. %B %Y");
			}
			
			if ($fileDate != '') {
				$details .= '<tr><td><strong>'.JText::_('Date').'</strong>:</td><td>'.$fileDate.'</td></tr>';
			}
			
			if ($valueDoc->description != '' && $valueDoc->description != '<p>&#160;</p>' && $valueDoc->description != '<p>&nbsp;</p>' && $valueDoc->description != '<p></p>' && $valueDoc->description != '<br />') {
				$details .= '<tr><td colspan="2">'.$valueDoc->description.'</td></tr>';		
			}
		
			if ($this->tmpl['display_downloads'] == 1) {
				$details .= '<tr><td><strong>'.JText::_('Downloads').'</strong>:</td><td>'.$valueDoc->hits.' x</td></tr>';
			}
			$details .='</table><p>&nbsp;</p>';
			
			echo $details;
		
		}
		if ((int)$valueDoc->confirm_license > 0) {
			echo '<h4>'.JText::_('License Agreement').'</h4>';
			echo '<div id="phoca-dl-license" style="height:'.(int)$this->tmpl['licenseboxheight'].'px">'.$valueDoc->licensetext.'</div>';
			
			// External link
			if ($valueDoc->link_external != '') {	
				echo '<form action="" name="phocaDownloadForm" id="phocadownloadform" target="'.$this->tmpl['download_external_link'].'">';	
				echo '<input type="checkbox" name="license_agree" onclick="enableDownloadPD()" /> <span>'.JText::_('I agree to the terms listed above').'</span> ';
				echo '<input type="button" name="submit" onClick="location.href=\''.$valueDoc->link_external.'\';" id="pdlicensesubmit" value="'.JText::_('Download').'" />';
			} else {
				echo '<form action="'.$this->request_url.'" method="post" name="phocaDownloadForm" id="phocadownloadform">';
				echo '<input type="checkbox" name="license_agree" onclick="enableDownloadPD()" /> <span>'.JText::_('I agree to the terms listed above').'</span> ';
				echo '<input type="submit" name="submit" id="pdlicensesubmit" value="'.JText::_('Download').'" />';
				echo '<input type="hidden" name="download" value="'.$valueDoc->id.'" />';
				echo '<input type="hidden" name="'. JUtility::getToken().'" value="1" />';
			}
			echo '</form>';

			// For users who have disabled Javascript
			echo '<script type=\'text/javascript\'>document.forms[\'phocadownloadform\'].elements[\'pdlicensesubmit\'].disabled=true</script>';
		} else {
			// External link
			if ($valueDoc->link_external != '') {	
				echo '<form action="" name="phocaDownloadForm" id="phocadownloadform" target="'.$this->tmpl['download_external_link'].'">';
				echo '<input type="button" name="submit" onClick="location.href=\''.$valueDoc->link_external.'\';" id="pdlicensesubmit" value="'.JText::_('Download').'" />';
			} else {
				echo '<form action="'.$this->request_url.'" method="post" name="phocaDownloadForm" id="phocadownloadform">';
				echo '<input type="submit" name="submit" id="pdlicensesubmit" value="'.JText::_('Download').'" />';
				echo '<input type="hidden" name="license_agree" value="1" />';
				echo '<input type="hidden" name="download" value="'.$valueDoc->id.'" />';
				echo '<input type="hidden" name="'. JUtility::getToken().'" value="1" />';
			}
			echo '</form>';
		}
		
		if (JComponentHelper::isEnabled('com_jcomments', true) && $this->tmpl['display_file_comments'] == 1) {
			include_once(JPATH_BASE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php');
			echo JComments::showComments($valueDoc->id, 'com_phocadownload_files', JText::_('PHOCADOWNLOAD_FILE') .' '. $valueDoc->title);
		}
	
	} else {
		echo '<h3>'.JText::_('File') .'</h3>';
		echo '<div class="pd-error">'.JText::_('PHOCADOWNLOAD_NO_RIGHTS_ACCESS_CATEGORY').'</div>';
	}
} else {
	echo '<h3>&nbsp;</h3>';
}
echo '</div></div>'. $this->tmpl['phoca_dwnld'];
?>

