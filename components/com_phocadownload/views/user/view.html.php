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
jimport( 'joomla.html.pane' );


class PhocaDownloadViewUser extends JView
{
	var $_context_files			= 'com_phocadownload.phocadownloaduserfiles';

	function display($tpl = null) {
		
		global $mainframe;
		$document			= &JFactory::getDocument();
		$uri 				= &JFactory::getURI();
		$menus				= &JSite::getMenu();
		$menu				= $menus->getActive();
		$params				= &$mainframe->getParams();
		$user 				= &JFactory::getUser();
		$db					= & JFactory::getDBO();
	
		$tmpl['pi']		= 'components/com_phocadownload/assets/images/';
		$tmpl['pp']		= 'index.php?option=com_phocadownload&view=user&controller=user';
		$tmpl['pl']		= 'index.php?option=com_user&view=login&return='.base64_encode($tmpl['pp'].'&Itemid='. JRequest::getVar('Itemid', 0, '', 'int'));
		
		// Only registered users
		if ($user->aid == 0) {
			$mainframe->redirect(JRoute::_($tmpl['pl'], false), JText::_("ALERTNOTAUTH"));
			exit;
		}
		
		// CSS, JS
		$css = $params->get( 'theme', 'phocadownload-grey' );
		$document->addStyleSheet(JURI::base(true).'/components/com_phocadownload/assets/'.$css.'.css');
		
		
		
		
		// = = = = = = = = = = = 
		// PANE
		// = = = = = = = = = = =
		// - - - - - - - - - - 
		// ALL TABS
		// - - - - - - - - - -
		// UCP is disabled (security reasons)
		if ((int)$params->get( 'enable_user_cp', 0 ) == 0) {
			$mainframe->redirect(JURI::base(true), JText::_("PHOCADOWNLOAD_USER_UPLOAD_DISABLED"));
			exit;
		}
		
		$tmpl['tab'] 					= JRequest::getVar('tab', 0, '', 'string');
		$tmpl['maxuploadchar']			= $params->get( 'max_upload_char', 1000 );
		$tmpl['enableuseruploadapprove']= $params->get( 'enable_user_upload_approve', 0 );
		$tmpl['showpagetitle'] 			= $params->get( 'show_page_title', 1 );
		$tmpl['uploadmaxsize'] 			= $params->get( 'user_file_upload_size', 3145728 );
		$tmpl['uploadmaxsizeread']		= PhocaDownloadHelper::getFileSizeReadable($tmpl['uploadmaxsize']);
		$tmpl['userfilesmaxcount']		= $params->get( 'user_files_max_count', 5 );
		$tmpl['userfilesmaxsize']		= $params->get( 'user_files_max_size', 20971520 );
		$tmpl['iepx']					= '<div style="font-size:1px;height:1px;margin:0px;padding:0px;">&nbsp;</div>';
		$tmpl['send_mail_upload'] 		= $params->get( 'send_mail_upload', 0 );
	
	
	
		//Subcateogry
		//$tmpl['parentid']			= JRequest::getVar('parentcategoryid', 0, 'post', 'int');
		
		$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/comments.js');
		$document->addCustomTag(PhocaDownloadHelperFront::renderOnUploadJS());
		$document->addCustomTag(PhocaDownloadHelperFront::renderDescriptionUploadJS((int)$tmpl['maxuploadchar']));
		$tmpl['pdl']	= PhocaDownloadHelper::renderPhocaDownload();
		$document->addCustomTag(PhocaDownloadHelperFront::userTabOrdering());
		$model 			= $this->getModel('user');
		
		
		// Upload Form - - - - - - - - - - - - - - - 
		$ftp = !JClientHelper::hasCredentials('ftp');// Set FTP form
		$this->assignRef('session', JFactory::getSession());
		// END Upload Form - - - - - - - - - - - - - 
		
		$tmpl['displayupload'] = 1;
		
		
		
		// - - - - - - - - - -  
		// FORM
		// - - - - - - - - - -
		// No Controller because of returning back the values in case some form field is not OK
		
		// Set default for returning back
		$formData = new JObject();
		$formData->set('title', '');
		$formData->set('description','');
		$formData->set('author','');
		$formData->set('email','');
		$formData->set('license','');
		$formData->set('website','');
		$formData->set('version','');
		
		$tmpl['errorcatid'] 	= '';
		$tmpl['erroremail'] 	= '';
		$tmpl['errorwebsite'] 	= '';
		$tmpl['errorfile'] 		= '';
		
		$task 	= JRequest::getVar( 'task', '', 'post', 'string' );
		if($task == 'upload') {
			$post['title']			= JRequest::getVar( 'phocadownloaduploadtitle', '', 'post', 'string' );
			$post['description']	= JRequest::getVar( 'phocadownloaduploaddescription', '', 'post', 'string' );
			$post['catidfiles']		= JRequest::getVar( 'catidfiles', 0, 'post', 'int' );
			$post['description']	= substr($post['description'], 0, (int)$tmpl['maxuploadchar']);
			
			$post['approved']		= 0;
			$post['published']		= 1;
			$post['owner_id']		= $user->id;
			if ($tmpl['enableuseruploadapprove'] == 0) {
				$post['approved']	= 1;
			}
			$post['author']		= JRequest::getVar( 'phocadownloaduploadauthor', '', 'post', 'string' );
			$post['email']		= JRequest::getVar( 'phocadownloaduploademail', '', 'post', 'string' );
			$post['website']	= JRequest::getVar( 'phocadownloaduploadwebsite', '', 'post', 'string' );
			$post['license']	= JRequest::getVar( 'phocadownloaduploadlicense', '', 'post', 'string' );
			$post['version']	= JRequest::getVar( 'phocadownloaduploadversion', '', 'post', 'string' );
		
			if ($post['title'] != '')		{$formData->set('title', $post['title']);}
			if ($post['description'] != '')	{$formData->set('description', $post['description']);}
			if ($post['author'] != '')		{$formData->set('author', $post['author']);}
			if ($post['email'] != '')		{$formData->set('email', $post['email']);}
			if ($post['website'] != '')		{$formData->set('website', $post['website']);}
			if ($post['license'] != '')		{$formData->set('license', $post['license']);}
			if ($post['version'] != '')		{$formData->set('version', $post['version']);}
			
			
			// CHECK
			
			//catid
			$returnForm = 0;
			if ($post['catidfiles'] < 1) {
				$tmpl['errorcatid'] = JText::_('PHOCADOWNLOAD_PLEASE_SELECT_CATEGORY');
				$returnForm = 1;
			}
			jimport('joomla.mail.helper');
			if ($post['email'] != '' && !JMailHelper::isEmailAddress($post['email']) ) {
				$tmpl['erroremail'] = JText::_('PHOCADOWNLOAD_PLEASE_ENTER_VALID_EMAIL_ADDRESS');
				$returnForm = 1;
			}
			if ($post['website'] != '' && !PhocaDownloadHelper::isURLAddress($post['website']) ) {
				$tmpl['errorwebsite'] = JText::_('PHOCADOWNLOAD_PLEASE_ENTER_VALID_WEBSITE');
				$returnForm = 1;
			}
			
			// Upload		
			$errUploadMsg	= '';	
			$redirectUrl 	= '';
			$fileArray 		= JRequest::getVar( 'Filedata', '', 'files', 'array' );
			
			if(empty($fileArray) || (isset($fileArray['name']) && $fileArray['name'] == '')) {
			
				$tmpl['errorfile'] = JText::_('PHOCADOWNLOAD_PLEASE_ADD_FILE');
				$returnForm = 1;
			}
			
			if ($post['title'] == '') {
				$post['title']	= PhocaDownloadHelper::removeExtension($fileArray['name']);
			}
			$post['alias'] 	= PhocaDownloadHelper::getAliasName($post['title']);
			
			if ($returnForm == 0) {
				$errorUploadMsg = '';
				if($model->singleFileUpload($errorUploadMsg, $fileArray, $post)) {
				
					if ($tmpl['send_mail_upload'] > 0) {
						PhocaDownloadHelper::sendPhocaDownloadMail((int)$tmpl['send_mail_upload'], $post['title'], 2);
					}
					
					$Itemid		= JRequest::getVar( 'Itemid', 0, '', 'int');
					$limitStart	= JRequest::getVar( 'limitstart', 0, '', 'int');
					if ($limitStart > 0) {
						$limitStartUrl	= '&limitstart='.$limitStart;	
					} else {
						$limitStartUrl	= '';
					}
					$link = 'index.php?option=com_phocadownload&view=user&Itemid='. $Itemid . $limitStartUrl;
					$mainframe->redirect(JRoute::_($link, false), JText::_("PHOCADOWNLOAD_FILE_UPLOADED"));
					exit;
				} else {
					$tmpl['errorfile'] = JText::_('PHOCADOWNLOAD_FILE_NOT_UPLOADED');
					if ($errorUploadMsg != '') {
						$tmpl['errorfile'] .= '<br />' . $errorUploadMsg;
					}
				}

			}
		}
		
		
		// - - - - - - - - - - - 
		// FILES
		// - - - - - - - - - - -
		$tmpl['filesitems'] 		= $model->getDataFiles($user->id);
		$tmpl['filestotal'] 		= $model->getTotalFiles($user->id);
		$tmpl['filespagination'] 	= $model->getPaginationFiles($user->id);
			
		$filter_state_files		= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_state','filter_state', '','word');
		$filter_catid_files		= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_catid','filter_catid',0, 'int' );
		$catid_files			= $mainframe->getUserStateFromRequest( $this->_context_files. '.catid',	'catid', 0,	'int');
		$filter_sectionid_files	= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_sectionid',	'filter_sectionid',	0,	'int' );
		$filter_order_files		= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_order','filter_order','a.ordering', 'cmd' );
		$filter_order_Dir_files	= $mainframe->getUserStateFromRequest( $this->_context_files.'.filter_order_Dir','filter_order_Dir',	'',	'word' );
		$search_files			= $mainframe->getUserStateFromRequest( $this->_context_files.'.search', 'search', '', 'string' );
		$search_files			= JString::strtolower( $search_files );
		
		// build list of categories
		$javascript 	= 'class="inputbox" size="1" onchange="document.phocadownloadfilesform.submit();"';
		
		// get list of categories for dropdown filter	
		$whereC		= array();
		if ($filter_sectionid_files > 0) {
			$whereC[] = ' cc.section = '.$db->Quote($filter_sectionid_files);
		}
		//$whereC[]	= "(cc.uploaduserid LIKE '%-1%' OR cc.uploaduserid LIKE '%".(int)$user->id."%')";
		//$whereC[]	= "(cc.uploaduserid LIKE '%-1%' OR cc.uploaduserid LIKE '%,{".(int)$user->id."}' OR cc.uploaduserid LIKE '{".(int)$user->id."},%' OR cc.uploaduserid LIKE '%,{".(int)$user->id."},%' OR cc.uploaduserid ={".(int)$user->id."} )";
		$whereC[]	= "(cc.uploaduserid LIKE '%-1%' OR cc.uploaduserid LIKE '%,".(int)$user->id."' OR cc.uploaduserid LIKE '".(int)$user->id.",%' OR cc.uploaduserid LIKE '%,".(int)$user->id.",%' OR cc.uploaduserid =".(int)$user->id." )";
		$whereC 		= ( count( $whereC ) ? ' WHERE '. implode( ' AND ', $whereC ) : '' );
		
		// get list of categories for dropdown filter
		$query = 'SELECT cc.id AS value, cc.title AS text' .
				' FROM #__phocadownload_categories AS cc' .
				' LEFT JOIN #__phocadownload_sections AS s ON s.id = cc.section' .
				$whereC.
				' ORDER BY s.ordering, cc.ordering';

		$lists_files['catid'] = PhocaDownloadHelper::filterCategory($query, $catid_files, TRUE);
		
		$whereS		= array();
		//$whereS[]	= "(cc.uploaduserid LIKE '%-1%' OR cc.uploaduserid LIKE '%".(int)$user->id."%')";
		$whereS[]	= "(cc.uploaduserid LIKE '%-1%' OR cc.uploaduserid LIKE '%,".(int)$user->id."' OR cc.uploaduserid LIKE '".(int)$user->id.",%' OR cc.uploaduserid LIKE '%,".(int)$user->id.",%' OR cc.uploaduserid =".(int)$user->id." )";
		$whereS[]	= 's.published = 1';
		$whereS 		= ( count( $whereS ) ? ' WHERE '. implode( ' AND ', $whereS ) : '' );
		// sectionid
		$query = 'SELECT s.title AS text, s.id AS value'
		. ' FROM #__phocadownload_sections AS s'
		. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.section = s.id'
		. $whereS
		. ' GROUP BY s.id'
		. ' ORDER BY s.ordering';
		

		
		// state filter
	/*	$state_files[] 		= JHTML::_('select.option',  '', '- '. JText::_( 'Select State' ) .' -' );
		$state_files[] 		= JHTML::_('select.option',  'P', JText::_( 'Published' ) );
		$state_files[] 		= JHTML::_('select.option',  'U', JText::_( 'Unpublished') );
		$lists_image['state']	= JHTML::_('select.genericlist',   $state_files, 'filter_state', 'class="inputbox" size="1" onchange="document.phocadownloadfilesform.submit();"', 'value', 'text', $filter_state );*/
		
		$lists_files['sectionid'] = PhocaDownloadHelper::filterSection($query, $filter_sectionid_files, TRUE);
		
		// state filter
		$lists_files['state']	= JHTML::_('grid.state',  $filter_state_files );

		// table ordering
		$lists_files['order_Dir'] = $filter_order_Dir_files;
		$lists_files['order'] = $filter_order_files;

		// search filter
		$lists_files['search']= $search_files;
		
		$tmpl['catidfiles']			= $catid_files;

		$tmpl['filestab'] 			= 1;
		
		// Tabs
		$displayTabs	= 0;
		if ((int)$tmpl['filestab'] == 0) {
			$currentTab['files'] = -1;
		} else {
			$currentTab['files'] = $displayTabs;
			$displayTabs++;	
		}
	
		$tmpl['displaytabs']	= $displayTabs;
		$tmpl['currenttab']		= $currentTab;

		
		// ACTION
		$tmpl['action']	= $uri->toString();
		// SEF problem
		$isThereQM = false;
		$isThereQM = preg_match("/\?/i", $tmpl['action']);
		if ($isThereQM) {
			$amp = '&amp;';
		} else {
			$amp = '?';
		}
		$tmpl['actionamp']	=	$tmpl['action'] . $amp;
		$tmpl['istheretab'] = false;
		$tmpl['istheretab'] = preg_match("/tab=/i", $tmpl['action']);
		
		
		$tmpl['ps']	= '&tab='. $tmpl['currenttab']['files']
			. '&limitstart='.$tmpl['filespagination']->limitstart;

	
		// ASIGN
		$this->assignRef( 'listsfiles',		$lists_files);
		$this->assignRef( 'formdata',		$formData);
		$this->assignRef( 'tmpl', $tmpl);
		$this->assignRef( 'params', $params);
		$this->assignRef( 'session', JFactory::getSession());
		parent::display($tpl);
	}
}
?>
