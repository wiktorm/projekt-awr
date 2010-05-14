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
defined( '_JEXEC' ) or die();
jimport( 'joomla.client.helper' );
jimport( 'joomla.application.component.view' );

class PhocaDownloadCpViewPhocaDownloadManager extends JView
{
	function display($tpl = null) {
		
		global $mainframe;
		$paramsC 	= &JComponentHelper::getParams( 'com_phocadownload' );
		$document	= &JFactory::getDocument();
		$document->addStyleSheet('../administrator/components/com_phocadownload/assets/phocadownload.css');
		$document->addStyleSheet('../administrator/templates/system/css/system.css');
		
		// Do not allow cache
		JResponse::allowCache(false);
		
		// File Manager, Icon Manager
		$manager 	= JRequest::getVar( 'manager', '', '', 'path' );
		$path 		= PhocadownloadHelper::getPathSet($manager);
		$this->assignRef('files', $this->get('files'));
		$this->assignRef('folders', $this->get('folders'));
		$this->assignRef('state', $this->get('state'));
		
	
		// Upload Form ------------------------------------
		JHTML::_('behavior.mootools');
		//$document->addScript('components/com_phocadownload/assets/upload/mediamanager.js');
		$document->addStyleSheet('components/com_phocadownload/assets/upload/mediamanager.css');

		// Set FTP form
		$ftp = !JClientHelper::hasCredentials('ftp');
		
		// Set flash uploader if ftp password and login exists (will be not problems)
		$state			= $this->get('state');
		
		$refreshSite 	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager='.$state->manager.'&tmpl=component&folder='.$state->folder;
		
		if (!$ftp) {
			if ((int)PhocaDownloadHelper::getSettings( 'enable_flash', 1 ) == 1) {
				PhocaDownloadHelperUpload::uploader('file-upload', array('onAllComplete' => 'function(){ window.location.href="'.$refreshSite.'"; }'));
			}
		}
		
		// SETTINGS - Upload size
		$upload_maxsize = (int) PhocaDownloadHelper::getSettings( 'upload_maxsize', 3000000 );	
		// END Upload Form ------------------------------------
		
		$this->assignRef('session', JFactory::getSession());
		$this->assignRef('uploadmaxsize', $upload_maxsize);
		$this->assignRef('manager', $manager);
		$this->assign('require_ftp', $ftp);

		parent::display($tpl);
		echo JHTML::_('behavior.keepalive');
	}

	function setFolder($index = 0) {
		if (isset($this->folders[$index])) {
			$this->_tmp_folder = &$this->folders[$index];
		} else {
			$this->_tmp_folder = new JObject;
		}
	}

	function setFile($index = 0) {
		if (isset($this->files[$index])) {
			$this->_tmp_file = &$this->files[$index];
		} else {
			$this->_tmp_file = new JObject;
		}
	}
}
