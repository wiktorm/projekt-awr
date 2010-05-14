<?php
defined('_JEXEC') or die('Restricted access'); 

if ( $this->params->def( 'show_page_title', 1 ) ) {
	echo '<div class="componentheading'.$this->params->get( 'pageclass_sfx' ).'">'. $this->params->get('page_title'). '</div>';
}

echo '<div id="phoca-dl-category-box">';
if (!empty($this->section[0])) {
	echo '<div class="pd-category">';
	if ($this->tmpl['display_up_icon'] == 1) {
		//echo '<div class="pdtop"><a title="'.JText::_('Section').'" href="'. JRoute::_('index.php?option=com_phocadownload&view=section&id='.$this->section[0]->id.':'.$this->section[0]->alias.'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'" >'.JHTML::_('image', 'components/com_phocadownload/assets/images/up.png', JText::_('Up')).  '</a></div>';
		echo '<div class="pdtop"><a title="'.JText::_('Section').'" href="'. JRoute::_(PhocaDownloadHelperRoute::getSectionRoute($this->section[0]->id, $this->section[0]->alias)).'" >'.JHTML::_('image', 'components/com_phocadownload/assets/images/up.png', JText::_('Up')).  '</a></div>';
	}
} else {
	echo '<div class="pd-category">'
		.'<div class="pdtop"></div>';
}


if (!empty($this->category[0])) {
	// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
	// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
	$rightDisplay	= 0;
	if (!empty($this->category[0])) {
		$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $this->category[0]->cataccessuserid, $this->category[0]->cataccess, $this->tmpl['user']->get('aid', 0), $this->tmpl['user']->get('id', 0), 0);
	}
	// - - - - - - - - - - - - - - - - - - - - - -
	if ($rightDisplay == 1) {
		echo '<h3>'.$this->category[0]->title. '</h3>';

		// Description
		echo '<div class="contentpane'.$this->params->get( 'pageclass_sfx' ).'">';
		if ( (isset($this->tmpl['image']) && $this->tmpl['image'] !='') || (isset($this->category[0]->description) && $this->category[0]->description != '' && $this->category[0]->description != '<p>&#160;</p>')) {
			echo '<div class="contentdescription'.$this->params->get( 'pageclass_sfx' ).'">';
			if ( isset($this->tmpl['image']) ) {
				echo $this->tmpl['image'];
			}
			echo $this->category[0]->description
				.'</div><p>&nbsp;</p>';
		}
		echo '</div>';


		echo '<form action="'.$this->request_url.'" method="post" name="adminForm">';
		echo '<table width="100%">';	
		if (!empty($this->documentlist)) {	
			foreach ($this->documentlist as $valueDoc) {
				if ($valueDoc->textonly == 1) {
					
					echo '<tr><td colspan="5" class="textonly"><div class="description">';
					echo $valueDoc->description;
					echo '</div></td></tr>' . "\n";
				
				} else {

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
					
					
					// DETAILS
					$details = '';
					if ($valueDoc->title != '') {
						$details .= '<h4>'.$valueDoc->title.'</h4>';
					}
					if ($valueDoc->filename != '') {
						$details .= '<div>'.JText::_('File Name').': '.PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename ).'</div>';
					}
					if ($fileSize != '') {
						$details .= '<div>'.JText::_('File Size').': '.$fileSize.'</div>';
					}
					
					if ($valueDoc->version != '') {
						$details .= '<div>'.JText::_('Version').': '.$valueDoc->version.'</div>';
					}
					if ($valueDoc->license != '') {
						if ($valueDoc->license_url != '') {
							$details .= '<div>'.JText::_('License').': '.'<a href="'.$valueDoc->license_url.'" target="_blank">'.$valueDoc->license.'</a></div>';
						} else {
							$details .= '<div>'.JText::_('License').': '.$valueDoc->license.'</div>';
						}
					}
					if ($valueDoc->author != '') {
						if ($valueDoc->author_url != '') {
							$details .= '<div>'.JText::_('Author').': '.'<a href="'.$valueDoc->author_url.'" target="_blank">'.$valueDoc->author.'</a></div>';
						} else {
							$details .= '<div>'.JText::_('Author').': '.$valueDoc->author.'</div>';
						}
					}

					if ($valueDoc->author_email != '') {
						//$details .= '<div>'.JText::_('Email').': '. JHTML::_( 'email.cloak', $valueDoc->author_email).'</div>';
						$protectMail = str_replace('@', '['.JText::_('at').']', $valueDoc->author_email);
						$protectMail = str_replace('.', '['.JText::_('dot').']', $protectMail);
						$details .= '<div>'.JText::_('Email').': '. $protectMail.'</div>';
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
						$details .= '<div>'.JText::_('Date').': '.$fileDate.'</div>';
					}
					
					// DESCRIPTION
					$classMiddle 				= 'class="pdfile"';
					$descriptionOutputTop 		= '';
					$descriptionOutputBottom 	= '';
					$descriptionOutputOverlib	= '';
					
					if ($valueDoc->description != '' && $valueDoc->description != '<p>&#160;</p>' && $valueDoc->description != '<p>&nbsp;</p>' && $valueDoc->description != '<p></p>' && $valueDoc->description != '<br />') {
						
						if ($this->tmpl['display_description'] == 1 || $this->tmpl['display_description'] == 4) {
							$descriptionOutputTop 		= '<tr><td colspan="5">'.$valueDoc->description.'</td></tr>';
							$classMiddle 				= 'class="pdfile"';
						}
						if ($this->tmpl['display_description'] == 2 || $this->tmpl['display_description'] == 5) {	
							$descriptionOutputBottom 	= '<tr><td class="pdfile" colspan="5">'.$valueDoc->description.'</td></tr>';
							$classMiddle 				= '';
						}
						
						if ($this->tmpl['display_description'] == 3 || $this->tmpl['display_description'] == 4 ) {	
							$details .= '<div>'.JText::_('Description').':<br />'.$valueDoc->description.'</div>';
							$classMiddle 				= 'class="pdfile"';
						}
						
						if ( $this->tmpl['display_description'] == 5 ) {	
							$details .= '<div>'.JText::_('Description').':<br />'.$valueDoc->description.'</div>';
							$classMiddle 				= '';
						}
						
						if ( $this->tmpl['display_description'] == 6 ) {	
							$details .= '<div>'.JText::_('Description').':<br />'.$valueDoc->description.'</div>';
							$descriptionOutputTop 	= '<tr><td colspan="5">'.$details .'</td>';
							$classMiddle 				= 'class="pdfile"';
						}
						
						if ( $this->tmpl['display_description'] == 7 ) {	
							$details .= '<div>'.JText::_('Description').':<br />'.$valueDoc->description.'</div>';
							$descriptionOutputBottom 	= '<tr><td class="pdfile" colspan="5">'.$details .'</td></tr>';
							$classMiddle 				= '';
						}
						
					} else {
						
					}
					
					
					
					if ($this->tmpl['display_downloads'] == 1) {
						$details .= '<div>'.JText::_('Downloads').': '.$valueDoc->hits.' x</div>';
					}			
					
					// Title or name
					if ($this->tmpl['filename_or_name'] == 'title') {
						$displayNameHead = '';
						$displayName = $valueDoc->title;
					} else if ($this->tmpl['filename_or_name'] == 'filename'){
						$displayNameHead = '';
						$displayName = PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename );
					} else if ($this->tmpl['filename_or_name'] == 'filenametitle'){
						$displayNameHead = '<div><strong>'.$valueDoc->title.'</strong></div>';
						$displayName = PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename );
					}
					
					// Overlib
					$details		= PhocaDownloadHelper::strTrimAll($details);
					$overlib 	= "onmouseover=\"return overlib('".htmlspecialchars( addslashes('<div style="text-align:left;padding:5px">'.$details.'</div>') )."', CAPTION, '".JText::_('Details')."', BELOW, RIGHT, FGCOLOR, '".$this->ol['fgColor']."', BGCOLOR, '".$this->ol['bgColor']."', TEXTCOLOR, '".$this->ol['textColor']."', CAPCOLOR, '".$this->ol['capColor']."',CLOSECOLOR, '".$this->ol['closeColor']."', STICKY, MOUSEOFF);\"";
					$overlib .= " onmouseout=\"return nd();\"";
				
					// LINK
					// Confirm license - go to "file" view
					// or Display File View
					
					if ((int)$valueDoc->confirm_license > 0 || $this->tmpl['display_file_view'] == 1) {
							//$linkDownloadB = '<a href="'. JRoute::_('index.php?option=com_phocadownload&view=file&id='.$valueDoc->id.':'.$valueDoc->alias. $this->tmpl['limitstarturl'].'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'">';	// we need pagination to go back
						
							$linkDownloadB = '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getFileRoute($valueDoc->id, $valueDoc->catid,$valueDoc->alias, $valueDoc->categoryalias, $valueDoc->sectionid). $this->tmpl['limitstarturl']).'">';	// we need pagination to go back			
							$linkDownloadE ='</a>';
					} else {
						// EXTERNAL LINK
						if ($valueDoc->link_external != '') {
							
							$linkDownloadB = '<a href="'.$valueDoc->link_external.'" target="'.$this->tmpl['download_external_link'].'" >';
							$linkDownloadE ='</a>';
							
						} else {
						
							//$linkDownloadB = '<a href="'. JRoute::_('index.php?option=com_phocadownload&view=category&id='.$this->category[0]->id.':'.$this->category[0]->alias.'&download='.$valueDoc->id.':'.$valueDoc->alias.$this->tmpl['limitstarturl'].'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'">';
							$linkDownloadB = '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getFileRoute($valueDoc->id,$this->category[0]->id,$valueDoc->alias, $this->category[0]->alias, $valueDoc->sectionid, 'download').$this->tmpl['limitstarturl']).'">';
							$linkDownloadE ='</a>';
						
						}
					}
				
					// OUTPUT
					echo $descriptionOutputTop;			
					echo '<tr><td width="90%" '.$classMiddle.'>'.$displayNameHead. $imageFileNameThumbnail.'<div class="pd-document'.$this->tmpl['file_icon_size'].'" '.$imageFileName.'><div class="pd-float">';
					echo $linkDownloadB;
					echo $displayName;
					echo $linkDownloadE;
					if ($fileSize !='') {
						echo ' <small style="color:#ccc">('.$fileSize.')</small>';
					}
					echo '</div>';
					
					echo PhocaDownloadHelper::displayNewIcon($valueDoc->date, $this->tmpl['displaynew']);
					echo PhocaDownloadHelper::displayHotIcon($valueDoc->hits, $this->tmpl['displayhot']);
					
					//Specific icons
					if (isset($valueDoc->image_filename_spec1) && $valueDoc->image_filename_spec1 != '') {
						$iconPath	= PhocaDownloadHelper::getPathSet('icon');
						$iconPath	= str_replace ( '../', JURI::base(true).'/', $iconPath['orig_rel_ds']);
						echo '<div class="pd-float"><img src="'.$iconPath . $valueDoc->image_filename_spec1.'" alt="" /></div>';
					} 
					if (isset($valueDoc->image_filename_spec2) && $valueDoc->image_filename_spec2 != '') {
						$iconPath	= PhocaDownloadHelper::getPathSet('icon');
						$iconPath	= str_replace ( '../', JURI::base(true).'/', $iconPath['orig_rel_ds']);
						echo '<div class="pd-float"><img src="'.$iconPath . $valueDoc->image_filename_spec2.'" alt="" /></div>';
					} 
					
					echo '</div></td>' . "\n";
					
					// - - - - - -
					//Buttons
					// - - - - - - 
					$playerOutput 	= '';
					$previewOutput	= '';
					$detailOutput	= '';
					$downloadOutput	= '';
					
					// MEDIA PLAYER
					if ($this->tmpl['display_play'] == 1) {
						if (isset($valueDoc->filename_play) && $valueDoc->filename_play != '') {
							$fileExt = PhocaDownloadHelper::getExtension($valueDoc->filename_play);
							if ($fileExt == 'mp3' || $fileExt == 'mp4' || $fileExt == 'flv' ) {

								// Special height for music only
								$buttonPlOptions = $this->buttonpl->options;
								if ($fileExt == 'mp3') {
									$buttonPlOptions = $this->buttonpl->optionsmp3;
								}
					
								//$playLink = JRoute::_('index.php?option=com_phocadownload&view=play&id='.$valueDoc->id.':'.$valueDoc->alias.$this->tmpl['limitstarturl'].'&tmpl=component&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int'));
								$playLink = JRoute::_(PhocaDownloadHelperRoute::getFileRoute($valueDoc->id,$this->category[0]->id,$valueDoc->alias, $valueDoc->categoryalias,$valueDoc->sectionid, 'play').$this->tmpl['limitstarturl']);
								$playerOutput .= '<div class="pdplay'.$this->tmpl['button_style'].'"><div>';
								
								if ($this->tmpl['play_popup_window'] == 1) {
									$playerOutput .= '<a  href="'.$playLink.'" onclick="'. $buttonPlOptions.'" >'. JText::_('Play').'</a>';
								} else {	
									$playerOutput .= '<a class="modal-button" href="'.$playLink.'" rel="'. $buttonPlOptions.'" >'. JText::_('Play').'</a>';
								}
								$playerOutput .= '</div></div>';
							}
						}
					}
					
					// PREVIEW
					if ($this->tmpl['display_preview'] == 1) {
						if (isset($valueDoc->filename_preview) && $valueDoc->filename_preview != '') {
							$fileExt = PhocaDownloadHelper::getExtension($valueDoc->filename_preview);
							if ($fileExt == 'pdf' || $fileExt == 'jpeg' || $fileExt == 'jpg' || $fileExt == 'png' || $fileExt == 'gif') {
					
								$filePath	= PhocaDownloadHelper::getPathSet('file');
								$filePath	= str_replace ( '../', JURI::base(true).'/', $filePath['orig_rel_ds']);
								$previewLink = $filePath . $valueDoc->filename_preview;	
								$previewOutput	.= '<div class="pdpreview'.$this->tmpl['button_style'].'"><div>';
								
								if ($this->tmpl['preview_popup_window'] == 1) {
									$previewOutput .= '<a  href="'.$previewLink.'" onclick="'. $this->buttonpr->options.'" >'. JText::_('Preview').'</a>';
								} else {	
									if ($fileExt == 'pdf') {
										// Iframe - modal
										$previewOutput	.= '<a class="modal-button" href="'.$previewLink.'" rel="'. $this->buttonpr->options.'" >'. JText::_('Preview').'</a>';
									} else {
										// Image - modal
										$previewOutput	.= '<a class="modal-button" href="'.$previewLink.'" rel="'. $this->buttonpr->optionsimg.'" >'. JText::_('Preview').'</a>';
									}
								}
								$previewOutput	.= '</div></div>';
							}
						}
					}
					
					// DETAIL
					if ($this->tmpl['display_detail'] == 1) {
						$detailOutput	.= '<div class="pddetails'.$this->tmpl['button_style'].'"><div>';
						$detailOutput	.= '<a '.$overlib.' href="#">'. JText::_('Details').'</a>';
						$detailOutput	.= '</div></div>';
					}	
					
					// DOWNLOAD
					$downloadOutput .= '<div class="pddownload'.$this->tmpl['button_style'].'"><div>';
					$downloadOutput .= $linkDownloadB;
					$downloadOutput .= JText::_('Download');
					$downloadOutput .= $linkDownloadE;
					$downloadOutput .= '</div></div>';
					
					
					// Everytime we need 4 columns but button are not on the same columns - - - - - 
					$buttonColumns 	= 4;
					$buttonOutput	= '';
					if ($playerOutput != '') {
						$buttonOutput .= '<td '.$classMiddle.'>'.$playerOutput.'</td>';
						$buttonColumns--;
					}
					if ($previewOutput != '') {
						$buttonOutput .= '<td '.$classMiddle.'>'.$previewOutput.'</td>';
						$buttonColumns--;
					}
					if ($detailOutput != '') {
						$buttonOutput .= '<td '.$classMiddle.'>'.$detailOutput.'</td>';
						$buttonColumns--;
					}
					if ($downloadOutput != '') {
						$buttonOutput .= '<td '.$classMiddle.'>'.$downloadOutput.'</td>';
						$buttonColumns--;
					}
					
					for($i = 0;$i < $buttonColumns;$i++) {
						$buttonOutput = '<td '.$classMiddle.'> </td>'.$buttonOutput; 
					}
					// - - - - - 
					echo $buttonOutput;
					echo '</tr>' . "\n";
					echo $descriptionOutputBottom;
				}
			}
		}
		echo '</table>';
		

		if (count($this->documentlist)) {
			echo '<div class="pd-pagination"><center>';
			if ($this->params->get('show_pagination_limit')) {
				
				echo '<div style="margin:0 10px 0 10px;display:inline;">'
					.JText::_('Display Num') .'&nbsp;'
					.$this->tmpl['pagination']->getLimitBox()
					.'</div>';
			}
			
			if ($this->params->get('show_pagination')) {
			
				echo '<div style="margin:0 10px 0 10px;display:inline;" class="sectiontablefooter'.$this->params->get( 'pageclass_sfx' ).'" >'
					.$this->tmpl['pagination']->getPagesLinks()
					.'</div>'
				
					.'<div style="margin:0 10px 0 10px;display:inline;" class="pagecounter">'
					.$this->tmpl['pagination']->getPagesCounter()
					.'</div>';
			}
			echo '</center></div>';
		}
		echo '</form>';
		
		
		if (JComponentHelper::isEnabled('com_jcomments', true) && $this->tmpl['display_category_comments'] == 1) {
			include_once(JPATH_BASE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php');
			echo JComments::showComments($this->category[0]->id, 'com_phocadownload', JText::_('PHOCADOWNLOAD_CATEGORY') .' '. $this->category[0]->title);
		}
	} else {
		echo '<h3>'.JText::_('Category'). '</h3>';
		echo '<div class="pd-error">'.JText::_('PHOCADOWNLOAD_NO_RIGHTS_ACCESS_CATEGORY').'</div>';
	}
} else {
	echo '<h3>&nbsp;</h3>';
}
echo '</div></div>' . $this->tmpl['phoca_download'];
?>
