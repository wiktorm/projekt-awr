<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.client.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class PhocaDownloadCpControllerPhocaDownloadupload extends PhocaDownloadCpController
{
	function __construct()
	{
		parent::__construct();
		// Register Extra tasks
		$this->registerTask( 'upload' , 'upload');
		$this->registerTask( 'createfolder' , 'createfolder');
	}

	
	function createfolder()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		//$path	= PhocaDownloadHelper::getPathSet();
		$folderNew		= JRequest::getCmd( 'foldername', '');
		$folderCheck	= JRequest::getVar( 'foldername', null, '', 'string', JREQUEST_ALLOWRAW);
		$parent			= JRequest::getVar( 'folderbase', '', '', 'path' );
		$viewBack		= JRequest::getVar( 'viewback', '', '', '' );
		
		$link = '';
		switch ($viewBack) {
			case 'phocadownloadmanager:file':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=file&tmpl=component&folder='.$parent;
				$path	= PhocaDownloadHelper::getPathSet();// we use viewback to get right path
			break;
			case 'phocadownloadmanager:fileplay':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=fileplay&tmpl=component&folder='.$parent;
				$path	= PhocaDownloadHelper::getPathSet();// we use viewback to get right path
			break;
			case 'phocadownloadmanager:filepreview':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=filepreview&tmpl=component&folder='.$parent;
				$path	= PhocaDownloadHelper::getPathSet();// we use viewback to get right path
			break;
			
			case 'phocadownloadmanager:icon':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=icon&tmpl=component&folder='.$parent;
				$path	= PhocaDownloadHelper::getPathSet('icon');// we use viewback to get right path
			break;
			
			case 'phocadownloadmanager:iconspec1':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=iconspec1&tmpl=component&folder='.$parent;
				$path	= PhocaDownloadHelper::getPathSet('icon');// we use viewback to get right path
			break;
			
			case 'phocadownloadmanager:iconspec2':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=iconspec2&tmpl=component&folder='.$parent;
				$path	= PhocaDownloadHelper::getPathSet('icon');// we use viewback to get right path
			break;
			
			default:
				$mainframe->redirect('index.php?option=com_phocadownload', 'Controller U Error');
			break;
		
		}

		JRequest::setVar('folder', $parent);

		if (($folderCheck !== null) && ($folderNew !== $folderCheck)) {
			$mainframe->redirect($link, JText::_('WARNDIRNAME'));
		}

		if (strlen($folderNew) > 0) {
			$path = JPath::clean($path['orig_abs_ds'].DS.$parent.DS.$folderNew);
			if (!is_dir($path) && !is_file($path))
			{
				JFolder::create($path);
				JFile::write($path.DS."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
				
				$mainframe->redirect($link, JText::_('Folder Created'));
			} else {
				$mainframe->redirect($link, JText::_('Folder exists'));
			}
			//JRequest::setVar('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}
		
		$mainframe->redirect($link);
	}
	
	
	
	function upload()
	{
		global $mainframe;
	
		// Check for request forgeries
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		// Set FTP credentials, if given
		$ftp 		=& JClientHelper::setCredentialsFromRequest('ftp');
	
		$file 		= JRequest::getVar( 'Filedata', '', 'files', 'array' );
		$folder		= JRequest::getVar( 'folder', '', '', 'path' );
		$format		= JRequest::getVar( 'format', 'html', '', 'cmd');
		$return		= JRequest::getVar( 'return-url', null, 'post', 'base64' );
		$viewBack	= JRequest::getVar( 'viewback', '', '', '' );
		$err		= null;
		
		$manager	= '';
		switch ($viewBack) {
			case 'phocadownloadmanager:file':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=file&tmpl=component&folder='.$folder;
				$path	= PhocaDownloadHelper::getPathSet();// we use viewback to get right path
				$manager = 'file';
			break;
			case 'phocadownloadmanager:fileplay':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=fileplay&tmpl=component&folder='.$folder;
				$path	= PhocaDownloadHelper::getPathSet();// we use viewback to get right path
				$manager = 'fileplay';
			break;
			case 'phocadownloadmanager:filepreview':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=filepreview&tmpl=component&folder='.$folder;
				$path	= PhocaDownloadHelper::getPathSet();// we use viewback to get right path
				$manager = 'filepreview';
			break;
			
			case 'phocadownloadmanager:icon':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=icon&tmpl=component&folder='.$folder;
				$path	= PhocaDownloadHelper::getPathSet('icon');// we use viewback to get right path
				$manager = 'icon';
			break;
			
			case 'phocadownloadmanager:iconspec1':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=iconspec1&tmpl=component&folder='.$folder;
				$path	= PhocaDownloadHelper::getPathSet('icon');// we use viewback to get right path
				$manager = 'iconspec1';
			break;
			
			case 'phocadownloadmanager:iconspec2':
				$link	= 'index.php?option=com_phocadownload&view=phocadownloadmanager&manager=iconspec2&tmpl=component&folder='.$folder;
				$path	= PhocaDownloadHelper::getPathSet('icon');// we use viewback to get right path
				$manager = 'iconspec2';
			break;
			
			default:
				$mainframe->redirect('index.php?option=com_phocadownload', 'Controller U Error');
			break;
		
		}
		

		// Make the filename safe
		if (isset($file['name'])) {
			$file['name']	= JFile::makeSafe($file['name']);
		}
		
		
		// All HTTP header will be overwritten with js message
		if (isset($file['name'])) {
			$filepath = JPath::clean($path['orig_abs_ds'].$folder.DS.strtolower($file['name']));
			if (!PhocaDownloadHelperUpload::canUpload( $file, $err, $manager )) {
				
				if ($format == 'json') {					
					switch ($err) {
						case 'WARNFILETOOLARGE':
							header('HTTP/1.0 413 Request Entity Too Large');
							jexit('Error. The File Is Too Large!');
						break;
						
						default:
							header('HTTP/1.0 415 Unsupported Media Type');
							jexit('Error. Unsupported Media Type!');
						break;
					}	
				} else {
					JError::raiseNotice(100, JText::_($err));
					// REDIRECT
					if ($return) {
						$mainframe->redirect(base64_decode($return).'&folder='.$folder);
					}
					return;
				}
			}

			if (JFile::exists($filepath)) {
				if ($format == 'json') {
					header('HTTP/1.0 409 Conflict');
					jexit('Error. File already exists');
				} else {
					JError::raiseNotice(100, JText::_('Error. File already exists'));
					// REDIRECT
					if ($return) {
						$mainframe->redirect(base64_decode($return).'&folder='.$folder);
					}
					return;
				}
			}

			if (!JFile::upload($file['tmp_name'], $filepath)) {
				if ($format == 'json') {
					header('HTTP/1.0 406 Not Acceptable');
					jexit('Error. Unable to upload file');
				} else {
					JError::raiseWarning(100, JText::_('Error. Unable to upload file'));
					// REDIRECT
					if ($return) {
						$mainframe->redirect(base64_decode($return).'&folder='.$folder);
					}
					return;
				}
			} else {
				if ($format == 'json') {
					header('HTTP/1.0 400');// With 400 error will be not displayed (?? - ok)
					jexit('Upload complete');
				} else {
					$mainframe->enqueueMessage(JText::_('Phoca Download, Upload complete'));
					// REDIRECT
					if ($return) {
						$mainframe->redirect(base64_decode($return).'&folder='.$folder);
					}
					return;
				}
			}
		} else {
			$msg = JTEXT::_('WARNFILETYPE');
			if ($format == 'json') {
					header('HTTP/1.0 415 Unsupported Media Type');
					jexit('Error. Unable to upload file');
				} else {
				if ($return) {
					$mainframe->redirect(base64_decode($return).'&folder='.$folder, $msg);
				} else {
					$mainframe->redirect($link, $msg);
				}
			}
		}
	}
}
