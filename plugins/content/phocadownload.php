<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @plugin Phoca Plugin
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
if (!JComponentHelper::isEnabled('com_phocadownload', true)) {
	return JError::raiseError(JText::_('Phoca Download Error'), JText::_('Phoca Download is not installed on your system'));
}
require_once( JPATH_ROOT.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'route.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownload.php' );

class plgContentPhocaDownload extends JPlugin
{	
	function plgContentPhocaDownload( &$subject, $params ) {
        parent::__construct( $subject, $params  );
    }

	function onPrepareContent( &$article, &$params, $limitstart = null ) {
		

		$document		= &JFactory::getDocument();
		$db 			= &JFactory::getDBO();		
		$plugin 		= &JPluginHelper::getPlugin('content', 'phocadownload');
	 	$pluginP 		= new JParameter( $plugin->params );
		$iSize			= $pluginP->get('icon_size', 32);
		
		// PARAMS - direct from Phoca Component Global configuration
		$component 		= 'com_phocadownload';
		$table 			=& JTable::getInstance('component');
		$table->loadByOption( $component );
		$paramsC	 	= new JParameter( $table->params );
		
		// Start Plugin
		$regex_one		= '/({phocadownload\s*)(.*?)(})/si';
		$regex_all		= '/{phocadownload\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$article->text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		$document->addStyleSheet(JURI::base(true).'/plugins/content/phocadownload/css/phocadownload.css');
		
		
		$view				= '';
		$id					= '';
		$text				= '';
		$target 			= '';
		$playerwidth		= $paramsC->get( 'player_width', 328 );
		$playerheight		= $paramsC->get( 'player_height', 200 );
		$previewwidth		= $paramsC->get( 'preview_width', 640 ); 
		$previewheight		= $paramsC->get( 'preview_height', 480 );				
		$playerheightmp3	= $paramsC->get( 'player_mp3_height', 30 );
		$url				= '';
		$youtubewidth		= 448;
		$youtubeheight		= 336;
		$fileView			= $paramsC->get( 'display_file_view', 0 );
		
		$previewWindow 		= $paramsC->get( 'preview_popup_window', 0 );
		$playWindow 		= $paramsC->get( 'play_popup_window', 0 );
		
		// Start if count_matches
		if ($count_matches != 0) {
			
			// Start CSS
			for($i = 0; $i < $count_matches; $i++) {
				
				
				
											
				
				// Get plugin parameters
				$phocadownload	= $matches[0][$i][0];
				preg_match($regex_one,$phocadownload,$phocadownload_parts);
				$parts			= explode("|", $phocadownload_parts[2]);
				$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");

				
				foreach($parts as $key => $value) {
					$values = explode("=", $value, 2);
					
					foreach ($values_replace as $key2 => $values2) {
						$values = preg_replace($values2, '', $values);
					}
					
					// Get plugin parameters from article
						 if($values[0]=='view')				{$view				= $values[1];}
					else if($values[0]=='id')				{$id				= $values[1];}
					else if($values[0]=='text')				{$text				= $values[1];}
					else if($values[0]=='target')			{$target			= $values[1];}
					else if($values[0]=='playerwidth')		{$playerwidth		= (int)$values[1];}
					else if($values[0]=='playerheight')		{$playerheight		= (int)$values[1];}
					else if($values[0]=='playerheightmp3')	{$playerheightmp3	= (int)$values[1];}
					
					else if($values[0]=='previewwidth')		{$previewwidth		= (int)$values[1];}
					else if($values[0]=='previewheight')	{$previewheight		= (int)$values[1];}
					
					else if($values[0]=='youtubewidth')		{$youtubewidth		= (int)$values[1];}
					else if($values[0]=='youtubeheight')	{$youtubeheight		= (int)$values[1];}
					
					else if($values[0]=='previewwindow')	{$previewWindow		= (int)$values[1];}
					else if($values[0]=='playwindow')		{$playWindow		= (int)$values[1];}
					
					else if($values[0]=='url')				{$url				= $values[1];}
					
				}
				
				switch($target) {
					case 'b':
						$targetOutput = 'target="_blank" ';
					break;
					case 't':
						$targetOutput = 'target="_top" ';
					break;
					case 'p':
						$targetOutput = 'target="_parent" ';
					break;
					case 's':
						$targetOutput = 'target="_self" ';
					break;
					default:
						$targetOutput = '';
					break;
				}
				
				$output = '';
				//Itemid
				$menu 		=& JSite::getMenu();
				$itemSection= $menu->getItems('link', 'index.php?option=com_phocadownload&view=sections');
				if(isset($itemSection[0])) {
					$itemId = $itemSection[0]->id;
				} else {
					$itemId = JRequest::getVar('Itemid', 1, 'get', 'int');
				}
				
				switch($view) {
					
					// - - - - - - - - - - - - - - - -
					// SECTIONS
					// - - - - - - - - - - - - - - - -
					case 'sections':						
						if ($text !='') {
							$textOutput = $text;
						} else {
							$textOutput = JText::_('Download Sections');
						}
						
						$link = PhocaDownloadHelperRoute::getSectionsRoute();
						
						$output .= '<div class="phocadownloadsections'.(int)$iSize.'"><a href="'. JRoute::_($link).'" '.$targetOutput.'>'. $textOutput.'</a></div>';
					break;
					
					// - - - - - - - - - - - - - - - -
					// SECTION
					// - - - - - - - - - - - - - - - -
					case 'section':
						if ((int)$id > 0) {
							$query = 'SELECT a.id, a.title, a.alias,'
							. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
							. ' FROM #__phocadownload_sections AS a'
							. ' WHERE a.id = '.(int)$id;
							
							$db->setQuery($query);
							$item = $db->loadObject();
							
							if (isset($item->id) && isset($item->slug)) {
								
								if ($text !='') {
									$textOutput = $text;
								} else if (isset($item->title) && $item->title != '') {
									$textOutput = $item->title;
								} else {
									$textOutput = JText::_('Download Section');
								}
								$link = PhocaDownloadHelperRoute::getSectionRoute($item->id, $item->alias);
								// 'index.php?option=com_phocadownload&view=section&id='.$item->slug.'&Itemid='. $itemId
								
								$output .= '<div class="phocadownloadsection'.(int)$iSize.'"><a href="'. JRoute::_($link).'" '.$targetOutput.'>'. $textOutput.'</a></div>';
							}
						}
					break;
					
					// - - - - - - - - - - - - - - - -
					// CATEGORY
					// - - - - - - - - - - - - - - - -
					case 'category':
						if ((int)$id > 0) {
							$query = 'SELECT a.id, a.title, a.alias, s.id as sectionid,'
							. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
							. ' FROM #__phocadownload_categories AS a'
							. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = a.section'
							. ' WHERE a.id = '.(int)$id;
							
							$db->setQuery($query);
							$item = $db->loadObject();
							
							if (isset($item->id) && isset($item->slug)) {
								
								if ($text !='') {
									$textOutput = $text;
								} else if (isset($item->title) && $item->title != '') {
									$textOutput = $item->title;
								} else {
									$textOutput = JText::_('Download Category');
								}
								$link = PhocaDownloadHelperRoute::getCategoryRoute($item->id, $item->alias, $item->sectionid);
								//'index.php?option=com_phocadownload&view=category&id='.$item->slug.'&Itemid='. $itemId
								$output .= '<div class="phocadownloadcategory'.(int)$iSize.'"><a href="'. JRoute::_($link).'" '.$targetOutput.'>'. $textOutput.'</a></div>';
							}
				
						}
					break;
					
					// - - - - - - - - - - - - - - - -
					// FILE
					// - - - - - - - - - - - - - - - -
					case 'file':
					case 'fileplay':
					case 'fileplaylink':
					case 'filepreviewlink':
						if ((int)$id > 0) {
							$query = 'SELECT a.id, a.title, a.alias, a.filename_play, a.filename_preview, a.link_external, c.id as catid, a.confirm_license, c.title as cattitle, c.alias as catalias, s.id as sectionid,'
							. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'
							. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as catslug'
							. ' FROM #__phocadownload AS a'
							. ' LEFT JOIN #__phocadownload_categories AS c ON a.catid = c.id'
							. ' LEFT JOIN #__phocadownload_sections AS s ON c.section = s.id'
							. ' WHERE a.id = '.(int)$id;
							
							$db->setQuery($query);
							$item = $db->loadObject();
							
							if (isset($item->id) && isset($item->slug) && isset($item->catid) && isset($item->catslug)) {
								
								if ($text !='') {
									$textOutput = $text;
								} else if (isset($item->title) && $item->title != '') {
									$textOutput = $item->title;
								} else {
									if ($view == 'fileplay') {
										$textOutput = JText::_('Play File');
									} else {
										$textOutput = JText::_('Download File');
									}
								}
							
								// - - - - - 
								// PLAY
								// - - - - - 
								if ($view == 'fileplay') {
									$play		= 1;
									$fileExt	= '';
									$filePath	= PhocaDownloadHelper::getPathSet('file');
									$filePath	= str_replace ( '../', JURI::base(true).'/', $filePath['orig_rel_ds']);
									if (isset($item->filename_play) && $item->filename_play != '') {
										$fileExt = PhocaDownloadHelper::getExtension($item->filename_play);
										if ($fileExt == 'mp3' || $fileExt == 'mp4' || $fileExt == 'flv' ) {
											$tmpl['playfilewithpath']	= $filePath . $item->filename_play;
											$tmpl['playerpath']			= JURI::base().'components/com_phocadownload/assets/flowplayer/';	
										} else {
											$output .= JText::_('No correct file for playing found');
											$play = 0;
										}
									} else {
										$output .= JText::_('No file for playing found');
										$play = 0;
									}
								
									if ($play == 1) {
										
										//Correct MP3
										$tmpl['filetype']		= '';
										if ($fileExt == 'mp3') {
											$tmpl['filetype'] 	= 'mp3';
											$playerheight		= $playerheightmp3;
										}
										$versionFLP 	= '3.1.5';
										$versionFLPJS 	= '3.1.4';
									
										//Flow Player
										$document = &JFactory::getDocument();
										$document->addScript($tmpl['playerpath'].'flowplayer-'.$versionFLPJS.'.min.js');
									
										$output .= '<div style="text-align:center;margin: 10px auto">'. "\n"
												  .'<div style="margin: 0 auto;text-align:center; width:'. $playerwidth.'px"><a href="'. $tmpl['playfilewithpath'].'"  style="display:block;width:'. $playerwidth.'px;height:'. $playerheight.'px" id="pdplayer'.$i.'"></a>'. "\n";
												  
										if ($tmpl['filetype'] == 'mp3') {
											$output .= '<script type="text/javascript">'. "\n"
											.'window.addEvent("domready", function() {'. "\n"
											
											
											.'flowplayer("pdplayer'.$i.'", "'.$tmpl['playerpath'].'flowplayer-'.$versionFLP.'.swf",'
											.'{ ' . "\n"
											.' clip: { '. "\n"
											.'		url: \''.$tmpl['playfilewithpath'].'\','. "\n"
											.'		autoPlay: false'  . "\n"
										//	.'		autoBuffering: true' . "\n"
											.'	}, '. "\n"
											.'	plugins: { '. "\n"
											.'		controls: { ' . "\n"
											.'			fullscreen: false, '. "\n"
											.'			height: '. $playerheight . "\n"
											.'		} ' . "\n"
											.'	} '. "\n"
											.'} '. "\n"
											.');'. "\n"
											
											.'});'
											.'</script>'. "\n";
										} else {
											
											$output .= '<script type="text/javascript">'. "\n"
											.'window.addEvent("domready", function() {'. "\n"
										
											.'flowplayer("pdplayer'.$i.'", "'. $tmpl['playerpath'].'flowplayer-'.$versionFLP.'.swf",'. "\n"
											.'{ ' . "\n"
											.' clip: { '. "\n"
											.'		url: \''.$tmpl['playfilewithpath'].'\','. "\n"
											.'		autoPlay: false,'  . "\n"
											.'		autoBuffering: true' . "\n"
											.'	}, '. "\n"
											.'} '. "\n"
											.');'. "\n"
											
											.'});'
											.'</script>'. "\n";											
										}

										$output .= '</div></div>'. "\n";
									}
								
								} else if ($view == 'fileplaylink') { 
								
									// PLAY - - - - - - - - - - - -
									$windowWidthPl 				= (int)$playerwidth + 30;
									$windowHeightPl 			= (int)$playerheight + 30;
									$windowHeightPlMP3 			= (int)$playerheightmp3 + 30;
									//$playWindow 	= $paramsC->get( 'play_popup_window', 0 );
									if ($playWindow == 1) {
										$buttonPl = new JObject();
										$buttonPl->set('methodname', 'js-button');
										$buttonPl->set('options', "window.open(this.href,'win2','width=".$windowWidthPl.",height=".$windowHeightPl.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
										$buttonPl->set('optionsmp3', "window.open(this.href,'win2','width=".$windowWidthPl.",height=".$windowHeightPlMP3.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
									} else {
										JHTML::_('behavior.modal', 'a.modal-button');
										$document->addCustomTag( "<style type=\"text/css\"> \n"  
									." #sbox-window.phocadownloadplaywindow   {background-color:#fff;padding:2px} \n"
									." #sbox-overlay.phocadownloadplayoverlay  {background-color:#000;} \n"			
									." </style> \n");
										$buttonPl = new JObject();
										$buttonPl->set('name', 'image');
										$buttonPl->set('modal', true);
										$buttonPl->set('methodname', 'modal-button');
										$buttonPl->set('options', "{handler: 'iframe', size: {x: ".$windowWidthPl.", y: ".$windowHeightPl."}, overlayOpacity: 0.7, classWindow: 'phocadownloadplaywindow', classOverlay: 'phocadownloadplayoverlay'}");
										$buttonPl->set('optionsmp3', "{handler: 'iframe', size: {x: ".$windowWidthPl.", y: ".$windowHeightPlMP3."}, overlayOpacity: 0.7, classWindow: 'phocadownloadplaywindow', classOverlay: 'phocadownloadplayoverlay'}");
									}
									// - - - - - - - - - - - - - - -

									$fileExt	= '';
									$filePath	= PhocaDownloadHelper::getPathSet('file');
									$filePath	= str_replace ( '../', JURI::base(true).'/', $filePath['orig_rel_ds']);
									if (isset($item->filename_play) && $item->filename_play != '') {
										$fileExt = PhocaDownloadHelper::getExtension($item->filename_play);
										if ($fileExt == 'mp3' || $fileExt == 'mp4' || $fileExt == 'flv' ) {
											// Special height for music only
											$buttonPlOptions = $buttonPl->options;
											if ($fileExt == 'mp3') {
												$buttonPlOptions = $buttonPl->optionsmp3;
											}
											if ($text == '') {
												$text = JText::_('Play');
											}
											$playLink = JRoute::_(PhocaDownloadHelperRoute::getFileRoute($item->id,$item->catid,$item->alias, $item->catalias,$item->sectionid, 'play'));
											$output .= '<div class="phocadownloadplay'.(int)$iSize.'">';
											
											if ($playWindow == 1) {
												$output .= '<a  href="'.$playLink.'" onclick="'. $buttonPlOptions.'" >'. $text.'</a>';
											} else {	
												$output .= '<a class="modal-button" href="'.$playLink.'" rel="'. $buttonPlOptions.'" >'. $text.'</a>';
											}
											$output .= '</div>';
										}
									} else {
										$output .= JText::_('No file for playing found');
									}
									
								
								
								
								} else if ($view == 'filepreviewlink') {
								
								
									if (isset($item->filename_preview) && $item->filename_preview != '') {
										$fileExt 	= PhocaDownloadHelper::getExtension($item->filename_preview);
										if ($fileExt == 'pdf' || $fileExt == 'jpeg' || $fileExt == 'jpg' || $fileExt == 'png' || $fileExt == 'gif') {
								
											$filePath	= PhocaDownloadHelper::getPathSet('file');
											$filePath	= str_replace ( '../', JURI::base(true).'/', $filePath['orig_rel_ds']);
											$previewLink = $filePath . $item->filename_preview;
											//$previewWindow 	= $paramsC->get( 'preview_popup_window', 0 );
											
											// PREVIEW - - - - - - - - - - - -
											$windowWidthPr 	= (int)$previewwidth + 20;
											$windowHeightPr = (int)$previewheight + 20;
											if ($previewWindow == 1) {
												$buttonPr = new JObject();
												$buttonPr->set('methodname', 'js-button');
												$buttonPr->set('options', "window.open(this.href,'win2','width=".$windowWidthPr.",height=".$windowHeightPr.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
											} else {
												JHTML::_('behavior.modal', 'a.modal-button');
												$document->addCustomTag( "<style type=\"text/css\"> \n"  
											." #sbox-window.phocadownloadpreviewwindow   {background-color:#fff;padding:2px} \n"
											." #sbox-overlay.phocadownloadpreviewoverlay  {background-color:#000;} \n"			
											." </style> \n");
												$buttonPr = new JObject();
												$buttonPr->set('name', 'image');
												$buttonPr->set('modal', true);
												$buttonPr->set('methodname', 'modal-button');
												$buttonPr->set('options', "{handler: 'iframe', size: {x: ".$windowWidthPr.", y: ".$windowHeightPr."}, overlayOpacity: 0.7, classWindow: 'phocadownloadpreviewwindow', classOverlay: 'phocadownloadpreviewoverlay'}");
												$buttonPr->set('optionsimg', "{handler: 'image', size: {x: 200, y: 150}, overlayOpacity: 0.7, classWindow: 'phocadownloadpreviewwindow', classOverlay: 'phocadownloadpreviewoverlay'}");
											}
											// - - - - - - - - - - - - - - -
											
											
																						
											if ($text == '') {
												$text = JText::_('Preview');
											}
											
											$output .= '<div class="phocadownloadpreview'.(int)$iSize.'">';
											if ($previewWindow == 1) {
												$output .= '<a  href="'.$previewLink.'" onclick="'. $buttonPr->options.'" >'. $text.'</a>';
											} else {	
												if ($fileExt == 'pdf') {
													// Iframe - modal
													$output	.= '<a class="modal-button" href="'.$previewLink.'" rel="'. $buttonPr->options.'" >'. JText::_('Preview').'</a>';
												} else {
													// Image - modal
													$output	.= '<a class="modal-button" href="'.$previewLink.'" rel="'. $buttonPr->optionsimg.'" >'. JText::_('Preview').'</a>';
												}
											}
											$output	.= '</div>';
										}
									} else {
										$output .= JText::_('No file for previewing found');
									}
								
								} else {
									if ((isset($item->confirm_license) && $item->confirm_license > 0) || $fileView == 1) {
										$link = PhocaDownloadHelperRoute::getFileRoute($item->id,$item->catid,$item->alias, $item->catalias,$item->sectionid, 'file');
										//'index.php?option=com_phocadownload&view=file&id='.$item->slug.'&Itemid='.$itemId
										$output .= '<div class="phocadownloadfile'.(int)$iSize.'"><a href="'. JRoute::_($link).'" '.$targetOutput.'>'. $textOutput.'</a></div>';	
									} else {
										if ($item->link_external != '') {
											$link = $item->link_external;
										} else {
											$link = PhocaDownloadHelperRoute::getFileRoute($item->id,$item->catid,$item->alias,$item->catalias,$item->sectionid, 'download');
										}
										//$link = PhocaDownloadHelperRoute::getCategoryRoute($item->catid,$item->catalias,$item->sectionid);
											
										//'index.php?option=com_phocadownload&view=category&id='. $item->catslug. '&download='. $item->slug. '&Itemid=' . $itemId
										$output .= '<div class="phocadownloadfile'.(int)$iSize.'"><a href="'. JRoute::_($link).'" '.$targetOutput.'>'. $textOutput.'</a></div>';
									}
								}
							}
				
						}
					break;
					
					// - - - - - - - - - - - - - - - -
					// YOUTUBE
					// - - - - - - - - - - - - - - - -
					case 'youtube':
						
						if ($url != '' && PhocaDownloadHelper::isURLAddress($url) ) {

							$codeArray 	= explode('=', $url);
							$code 		= str_replace($codeArray[0].'=', '', $url);

							$output .= '<object height="'.(int)$youtubeheight.'" width="'.(int)$youtubewidth.'">'
							.'<param name="movie" value="http://www.youtube.com/v/'.$code.'"></param>'
							.'<param name="allowFullScreen" value="true"></param>'
							.'<param name="allowscriptaccess" value="always"></param>'
							.'<embed src="http://www.youtube.com/v/'.$code.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" height="'.(int)$youtubeheight.'" width="'.(int)$youtubewidth.'"></embed></object>';
							
						
						} else {
							$output .= JText::_('Wrong Youtube URL');
						}
					break;

					
				}
				$article->text = preg_replace($regex_all, $output, $article->text, 1);
			}
		}// end if count_matches
		return true;
	}
}
?>