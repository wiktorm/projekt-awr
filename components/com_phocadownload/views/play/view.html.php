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

class PhocaDownloadViewPlay extends JView
{

	function display($tpl = null){		
		global $mainframe;
		
		$params 		= &$mainframe->getParams();
		$tmpl			= array();
		$tmpl['user'] 	= &JFactory::getUser();
		$uri 			= &JFactory::getURI();
		$model			= &$this->getModel();
		$document		= &JFactory::getDocument();
		$fileId			= JRequest::getVar('id', 0, '', 'int');
	//	$category		= $model->getCategory($fileId, $params);
		$file			= $model->getDocument($fileId, $params);
		
		$fileExt		= '';

		$filePath	= PhocaDownloadHelper::getPathSet('file');
		$filePath	= str_replace ( '../', JURI::base(true).'/', $filePath['orig_rel_ds']);
		if (isset($file[0]->filename_play) && $file[0]->filename_play != '') {
		
			$fileExt = PhocaDownloadHelper::getExtension($file[0]->filename_play);
			if ($fileExt == 'mp3' || $fileExt == 'mp4' || $fileExt == 'flv' ) {
				$tmpl['playfilewithpath']	= $filePath . $file[0]->filename_play;
				//$tmpl['playerpath']			= JURI::base().'components/com_phocadownload/assets/jwplayer/';
				$tmpl['playerpath']			= JURI::base().'components/com_phocadownload/assets/flowplayer/';				
				$tmpl['playerwidth']		= $params->get( 'player_width', 328 ); 
				$tmpl['playerheight']		= $params->get( 'player_height', 200 );
			} else {
				echo JText::_('No correct file for playing found');exit;
			}
		} else {
			echo JText::_('No file for playing found');exit;
		}
		
		$tmpl['filetype']	= '';
		if ($fileExt == 'mp3') {
			$tmpl['filetype'] 		= 'mp3';
			$tmpl['playerheight']	= $params->get( 'player_mp3_height', 30 );
		}
	
		$this->assignRef('file',			$file);
		$this->assignRef('tmpl',			$tmpl);
		$this->assignRef('params',			$params);
		$this->assignRef('request_url',		$uri->toString());
		parent::display($tpl);
	}
}
?>