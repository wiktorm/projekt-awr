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

jimport( 'joomla.application.component.view');

class PhocaDownloadViewCategory extends JView
{

	function display($tpl = null)
	{		
		global $mainframe;
		
		jimport( 'joomla.filesystem.folder' ); 
		jimport( 'joomla.filesystem.file' );
		
		$params 			= &$mainframe->getParams();
		$tmpl				= array();
		$tmpl['user'] 		= &JFactory::getUser();
		$uri 				= &JFactory::getURI();
		$model				= &$this->getModel();
		$document			= &JFactory::getDocument();
		$categoryId			= JRequest::getVar('id', 0, '', 'int');
		$limitStart			= JRequest::getVar( 'limitstart', 0, '', 'int');
		$section			= $model->getSection($categoryId, $params);
		$category			= $model->getCategory($categoryId, $params);
		$documentList		= $model->getDocumentList($categoryId, $params);
		
		
		$tmpl['pagination']	= $model->getPagination($categoryId, $params);
		
		// Limit start
		if ($limitStart > 0 ) {
			$tmpl['limitstarturl'] =  '&start='.$limitStart;
		} else {
			$tmpl['limitstarturl'] = '';
		}
		
		$css			= $params->get( 'theme', 'phocadownload-grey' );
		$document->addStyleSheet(JURI::base(true).'/components/com_phocadownload/assets/'.$css.'.css');
		$document->addCustomTag('<script type="text/javascript" src="'.JURI::root().'includes/js/overlib_mini.js"></script>');
		
		switch($css) {
			case 'phocadownload-blue':
				$ol['fgColor']		= '#E5E5FF';
				$ol['bgColor']		= '#CCCCFF';
			break;
			
			case 'phocadownload-red':
				$ol['fgColor']		= '#E5E5FF';
				$ol['bgColor']		= '#FFB3C6';
			break;
			
			default:
				$ol['fgColor']		= '#f0f0f0';
				$ol['bgColor']		= '#D6D6D6';
			break;
		}
		// Overlib
		$ol['textColor']	= '#000000';
		$ol['capColor']		= '#000000';
		$ol['closeColor']	= '#000000';
		
		
		// PARAMS
		$tmpl['download_external_link'] = $params->get( 'download_external_link', '_self' );
		$tmpl['filename_or_name'] 		= $params->get( 'filename_or_name', 'filename' );
		$tmpl['display_downloads'] 		= $params->get( 'display_downloads', 0 );
		$tmpl['display_description'] 	= $params->get( 'display_description', 3 );
		$tmpl['display_detail'] 		= $params->get( 'display_detail', 1 );
		$tmpl['display_play'] 			= $params->get( 'display_play', 0 );
		$tmpl['playerwidth']			= $params->get( 'player_width', 328 ); 
		$tmpl['playerheight']			= $params->get( 'player_height', 200 );
		$tmpl['playermp3height']		= $params->get( 'player_mp3_height', 30 );
		$tmpl['previewwidth']			= $params->get( 'preview_width', 640 ); 
		$tmpl['previewheight']			= $params->get( 'preview_height', 480 );
		$tmpl['display_preview'] 		= $params->get( 'display_preview', 0 );
		$tmpl['play_popup_window'] 		= $params->get( 'play_popup_window', 0 );
		$tmpl['preview_popup_window'] 	= $params->get( 'preview_popup_window', 0 );
		$tmpl['file_icon_size'] 		= $params->get( 'file_icon_size', 16 );
		$tmpl['button_style'] 			= $params->get( 'button_style', '' );
		$tmpl['displaynew']				= $params->get( 'display_new', 0 );
		$tmpl['displayhot']				= $params->get( 'display_hot', 0 );
		$tmpl['display_up_icon'] 		= $params->get( 'display_up_icon', 1 );
		$tmpl['allowed_file_types']		= PhocaDownloadHelper::getSettings( 'allowed_file_types', '' );
		$tmpl['disallowed_file_types']	= PhocaDownloadHelper::getSettings( 'disallowed_file_types', '' );
		$tmpl['enable_user_statistics']	= PhocaDownloadHelper::getSettings( 'enable_user_statistics', 1 );
		$tmpl['display_category_comments']= $params->get( 'display_category_comments', 0 );
		$tmpl['display_date_type'] 		= $params->get( 'display_date_type', 0 );
		$tmpl['display_file_view']		= $params->get('display_file_view', 0);
		$tmpl['phoca_download']			= PhocaDownloadHelper::renderPhocaDownload();
		$tmpl['download_metakey'] 		= $params->get( 'download_metakey', '' );
		$tmpl['download_metadesc'] 		= $params->get( 'download_metadesc', '' );
		$tmpl['send_mail_download'] 	= $params->get( 'send_mail_download', 0 );// not boolean but id of user
		//$tmpl['send_mail_upload'] 		= $params->get( 'send_mail_upload', 0 );
		
		
		// Meta data
		if (isset($category[0]) && $category[0]->metakey != '') {
			$mainframe->addMetaTag('keywords', $category[0]->metakey);
		} else if ($tmpl['download_metakey'] != '') {
			$mainframe->addMetaTag('keywords', $tmpl['download_metakey']);
		}
		if (isset($category[0]) &&  $category[0]->metadesc != '') {
			$mainframe->addMetaTag('description', $category[0]->metadesc);
		} else if ($tmpl['download_metadesc'] != '') {
			$mainframe->addMetaTag('description', $tmpl['download_metadesc']);
		}

		// DOWNLOAD
		// - - - - - - - - - - - - - - - 
		$download	= JRequest::getVar( 'download', array(0), 'get', 'array' );
		$downloadId	= (int) $download[0];
		
		if ($downloadId > 0) {
			
			
			$currentLink	= 'index.php?option=com_phocadownload&view=category&id='.$category[0]->id.':'.$category[0]->alias.$tmpl['limitstarturl'] . '&Itemid='. JRequest::getVar('Itemid', 0, '', 'int');
			$fileData		= $model->getDownload($downloadId, $currentLink);
			
			PhocaDownloadHelperFront::download($fileData, $downloadId, $currentLink, $tmpl);
			
		}
		// - - - - - - - - - - - - - - - 
		
		JHTML::_('behavior.modal', 'a.modal-button');
		// PLAY - - - - - - - - - - - -
		$windowWidthPl 		= (int)$tmpl['playerwidth'] + 30;
		$windowHeightPl 	= (int)$tmpl['playerheight'] + 30;
		$windowHeightPlMP3 	= (int)$tmpl['playermp3height'] + 30;
		if ($tmpl['play_popup_window'] == 1) {
			$buttonPl = new JObject();
			$buttonPl->set('methodname', 'js-button');
			$buttonPl->set('options', "window.open(this.href,'win2','width=".$windowWidthPl.",height=".$windowHeightPl.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
			$buttonPl->set('optionsmp3', "window.open(this.href,'win2','width=".$windowWidthPl.",height=".$windowHeightPlMP3.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
		} else {
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
		// PREVIEW - - - - - - - - - - - -
		$windowWidthPr 	= (int)$tmpl['previewwidth'] + 20;
		$windowHeightPr = (int)$tmpl['previewheight'] + 20;
		if ($tmpl['preview_popup_window'] == 1) {
			$buttonPr = new JObject();
			$buttonPr->set('methodname', 'js-button');
			$buttonPr->set('options', "window.open(this.href,'win2','width=".$windowWidthPr.",height=".$windowHeightPr.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
		} else {
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
		
		// CSS Image Path
		$imagePath		= PhocaDownloadHelper::getPathSet('icon');
		$cssImagePath	= str_replace ( '../', JURI::base(true).'/', $imagePath['orig_rel_ds']);
		
		$filePath		= PhocaDownloadHelper::getPathSet('file');
		

		// Define image tag attributes
		if (!empty($category[0]->image)) {
			$attribs['align'] = '"'.$category[0]->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$tmpl['image'] = JHTML::_('image', 'images/stories/'.$category[0]->image, JText::_('Phoca Download'), $attribs);
		} else {
			$tmpl['image'] = '';
		}
		
		// Breadcrumbs
		$pathway 		=& $mainframe->getPathway();
		if (!empty($section[0]->title)) {
			$pathway->addItem($section[0]->title, JRoute::_(PhocaDownloadHelperRoute::getSectionRoute($section[0]->id, $section[0]->alias)));
		}
		if (!empty($category[0]->title)) {
			$pathway->addItem($category[0]->title);
		}

		$this->assignRef('tmpl',			$tmpl);
		$this->assignRef('section',			$section);
		$this->assignRef('category',		$category);
		$this->assignRef('documentlist',	$documentList);
		$this->assignRef('params',			$params);
		$this->assignRef('cssimagepath',	$cssImagePath);
		$this->assignRef('absfilepath',		$filePath['orig_abs_ds']);
		$this->assignRef('ol',				$ol);
		$this->assignRef('buttonpl',			$buttonPl);
		$this->assignRef('buttonpr',			$buttonPr);
		$this->assignRef('request_url',		$uri->toString());
		parent::display($tpl);
		
	}
}
?>