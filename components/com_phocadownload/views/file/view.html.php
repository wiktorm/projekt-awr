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
jimport( 'joomla.application.component.view');

class PhocaDownloadViewFile extends JView
{

	function display($tpl = null){		
		global $mainframe;
		
		jimport( 'joomla.filesystem.folder' ); 
		jimport( 'joomla.filesystem.file' );
		
		$params 		= &$mainframe->getParams();
		$tmpl			= array();
		$tmpl['user'] 	= &JFactory::getUser();
		$uri 			= &JFactory::getURI();
		$model			= &$this->getModel();
		$document		= &JFactory::getDocument();
		//$categoryId	= JRequest::getVar('catid', 0, '', 'int');
		$fileId			= JRequest::getVar('id', 0, '', 'int');
		$limitStart		= JRequest::getVar( 'start', 0, '', 'int');// we need it for category back link
		

	
		$tmpl['limitstart'] = $limitStart;
		if ($limitStart > 0 ) {
			$tmpl['limitstarturl'] = '&start='.$limitStart;
		} else {
			$tmpl['limitstarturl'] = '';
		}
		
		$category		= $model->getCategory($fileId, $params);
		$file			= $model->getDocument($fileId, $params, $tmpl['limitstarturl']);
		
		$css						= $params->get( 'theme', 'phocadownload-grey' );
		$tmpl['licenseboxheight']	= $params->get( 'license_box_height', 300 );
		$document->addStyleSheet(JURI::base(true).'/components/com_phocadownload/assets/'.$css.'.css');
	
		$js				= 'var enableDownloadButtonPD = 0;'
						 .'function enableDownloadPD() {'
						 .' if (enableDownloadButtonPD == 0) {'
						 .'   document.forms[\'phocadownloadform\'].elements[\'pdlicensesubmit\'].disabled=false;'
						 .'   enableDownloadButtonPD = 1;'
						 .' } else {'
						 .'   document.forms[\'phocadownloadform\'].elements[\'pdlicensesubmit\'].disabled=true;'
						 .'   enableDownloadButtonPD = 0;'
						 .' }'
						 .'}';
		$document->addScriptDeclaration($js);
		
		// PARAMS
		$tmpl['filename_or_name'] 		= $params->get( 'filename_or_name', 'filename' );
		$tmpl['display_up_icon'] 		= $params->get( 'display_up_icon', 1 );
		$tmpl['allowed_file_types']		= PhocaDownloadHelper::getSettings( 'allowed_file_types', '' );
		$tmpl['disallowed_file_types']	= PhocaDownloadHelper::getSettings( 'disallowed_file_types', '' );
		$tmpl['enable_user_statistics']	= PhocaDownloadHelper::getSettings( 'enable_user_statistics', 1 );
		$tmpl['display_file_comments'] 	= $params->get( 'display_file_comments', 0 );
		$tmpl['file_icon_size'] 		= $params->get( 'file_icon_size', 16 );
		$tmpl['display_file_view']		= $params->get('display_file_view', 0);
		$tmpl['download_metakey'] 		= $params->get( 'download_metakey', '' );
		$tmpl['download_metadesc'] 		= $params->get( 'download_metadesc', '' );
		$tmpl['display_downloads'] 		= $params->get( 'display_downloads', 0 );
		$tmpl['display_date_type'] 		= $params->get( 'display_date_type', 0 );
		$tmpl['displaynew']				= $params->get( 'display_new', 0 );
		$tmpl['displayhot']				= $params->get( 'display_hot', 0 );
		$tmpl['phoca_dwnld']			= PhocaDownloadHelper::renderPhocaDownload();
		$tmpl['download_external_link'] = $params->get( 'download_external_link', '_self' );
		$tmpl['send_mail_download'] 	= $params->get( 'send_mail_download', 0 );// not boolean but id of user
		//$tmpl['send_mail_upload'] 		= $params->get( 'send_mail_upload', 0 );
		
		// Meta data
		if (isset($file[0]) && $file[0]->metakey != '') {
			$mainframe->addMetaTag('keywords', $file[0]->metakey);
		} else if ($tmpl['download_metakey'] != '') {
			$mainframe->addMetaTag('keywords', $tmpl['download_metakey']);
		}
		if (isset($file[0]) && $file[0]->metadesc != '') {
			$mainframe->addMetaTag('description', $file[0]->metadesc);
		} else if ($tmpl['download_metadesc'] != '') {
			$mainframe->addMetaTag('description', $tmpl['download_metadesc']);
		}

		// DOWNLOAD
		// - - - - - - - - - - - - - - - 
		$download				= JRequest::getVar( 'download', array(0), '', 'array' );
		$licenseAgree			= JRequest::getVar( 'license_agree', '', 'post', 'string' );
		$downloadId		 		= (int) $download[0];

		if ($downloadId > 0) {
		
			if (isset($file[0]->id)) {
				$currentLink	= 'index.php?option=com_phocadownload&view=file&id='.$file[0]->id.':'.$file[0]->alias. $tmpl['limitstarturl'] . '&Itemid='. JRequest::getVar('Itemid', 0, '', 'int');
			} else {
				$currentLink	= 'index.php?option=com_phocadownload&view=sections&Itemid='. JRequest::getVar('Itemid', 0, '', 'int');
			}
		
			
			// Check Token
			$token	= JUtility::getToken();
			if (!JRequest::getInt( $token, 0, 'post' )) {
				//JError::raiseError(403, 'Request Forbidden');
				$mainframe->redirect(JRoute::_('index.php', false), JText::_("Form data is not valid"));
				exit;
			}
			
			// Check License Agreement
			if (empty($licenseAgree)) {
				$mainframe->redirect(JRoute::_($currentLink, false), JText::_("You must agree to listed terms"));
				exit;
			}
			
			$fileData	= $model->getDownload($downloadId, $currentLink);
			
			PhocaDownloadHelperFront::download($fileData, $downloadId, $currentLink, $tmpl);
			
		}
		// - - - - - - - - - - - - - - - 
		
		// CSS Image Path
		$imagePath		= PhocaDownloadHelper::getPathSet('icon');
		$cssImagePath	= str_replace ( '../', JURI::base(true).'/', $imagePath['orig_rel_ds']);
		
		$filePath		= PhocaDownloadHelper::getPathSet('file');
		
		// Breadcrumbs
		$pathway 		=& $mainframe->getPathway();
		if (!empty($category[0]->sectiontitle)) {
			$pathway->addItem($category[0]->sectiontitle, JRoute::_(PhocaDownloadHelperRoute::getSectionRoute($category[0]->sectionid, $category[0]->sectionalias)));
		}
		if (!empty($category[0]->sectiontitle) && !empty($category[0]->sectionid)) {
			$pathway->addItem($category[0]->title, JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($category[0]->id, $category[0]->alias, $category[0]->sectionid)));
		}
		if (!empty($file[0]->title)) {
			$pathway->addItem($file[0]->title);
		}

		$this->assignRef('tmpl',			$tmpl);
		
		$this->assignRef('category',		$category);
		$this->assignRef('file',			$file);
		$this->assignRef('params',			$params);
		$this->assignRef('cssimagepath',	$cssImagePath);
		$this->assignRef('absfilepath',		$filePath['orig_abs_ds']);
		$this->assignRef('request_url',		$uri->toString());
		parent::display($tpl);
		
	}
}
?>