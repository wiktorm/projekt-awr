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
class PhocaDownloadCpControllerPhocaDownload extends PhocaDownloadCpController
{
	function __construct() {
		parent::__construct();
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'apply'  , 'save' );
		$this->registerTask( 'install'  , 'install' );
		$this->registerTask( 'upgrade'  , 'upgrade' );
		$this->registerTask( 'text'  , 'text' );
		$this->registerTask( 'accesspublic', 'accessMenu');
		$this->registerTask( 'accessregistered', 'accessMenu');
		$this->registerTask( 'accessspecial', 'accessMenu');
		$this->registerTask( 'approve', 'approve');
		$this->registerTask( 'disapprove', 'disapprove');
	}

	function install() {
		$msg = JText::_( 'Phoca Download successfully installed' );
		$link = 'index.php?option=com_phocadownload&view=phocadownloads';
		$this->setRedirect($link, $msg);
	}
	
	function upgrade() {
		$msg = JText::_( 'Phoca Download successfully upgraded' );
		$link = 'index.php?option=com_phocadownload&view=phocadownloads';
		$this->setRedirect($link, $msg);
	}
		
	function edit() {
		JRequest::setVar( 'view', 'phocadownload' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar( 'hidemainmenu', 1 );

		parent::display();

		// Checkin the Phoca download
		$model = $this->getModel( 'phocadownload' );
		$model->checkout();
	}
	
	function text()
	{
		// Only Text (description - no file)
		JRequest::setVar( 'view', 'phocadownload' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'text', 1 );
		
		parent::display();
	}
	
	function save() {
	
		$post					= JRequest::get('post');
		$cid					= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['description']	= JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$section				= JRequest::getVar( 'sectionid', array(0), 'post', 'array' );
		$catid					= JRequest::getVar( 'catid', array(0), 'post', 'array' );
		$textonlyid				= JRequest::getVar( 'textonly', array(0), 'post', 'array' );
		$post['id'] 			= (int) $cid[0];
		$post['section'] 		= (int) $section[0];
		$post['catid'] 			= (int) $catid[0];
		$post['textonly']		= (int) $textonlyid[0];
		
		if (!empty($post['directlink'])) {
			$post['directlink'] = 1;
		} else {
			$post['directlink'] = 0;
		}
		
		if (!empty($post['unaccessible_file'])) {
			$post['unaccessible_file'] = 1;
		} else {
			$post['unaccessible_file'] = 0;
		}
		
		$model = $this->getModel( 'phocadownload' );
		switch ( JRequest::getCmd('task') ) {
			case 'apply':
				$id	= $model->store($post);//you get id and you store the table data
				if ($id && $id > 0) {
					$msg = JText::_( 'Changes to Phoca Download File Saved' );
					//$id		= $model->store($post);
				} else {
					$msg = JText::_( 'Error Saving Phoca File Download' );
					$id		= $post['id'];
				}
				
				if ($post['textonly'] == 1) {
					$this->setRedirect( 'index.php?option=com_phocadownload&controller=phocadownload&task=text&cid[]='. $id, $msg );
				} else {
					$this->setRedirect( 'index.php?option=com_phocadownload&controller=phocadownload&task=edit&cid[]='. $id, $msg );
				}
				break;

			case 'save':
			default:
				if ($model->store($post)) {
					$msg = JText::_( 'Phoca Download File Saved' );
				} else {
					$msg = JText::_( 'Error Saving Phoca Download' );
				}
				$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads', $msg );
				break;
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
	}
	

	function remove() {
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel( 'phocadownload' );
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
			$msg = JText::_( 'Error Deleting Phoca Download File' );
		}
		else {
			$msg = JText::_( 'Phoca Download Deleted File' );
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads', $msg );
	}

	function publish()
	{
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('phocadownload');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}

	function unpublish()
	{
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('phocadownload');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}
	
	function approve() {
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to approve' ) );
		}

		$model = $this->getModel('phocadownload');
		if(!$model->approve($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}

	function disapprove() {
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to disapprove' ) );
		}

		$model = $this->getModel('phocadownload');
		if(!$model->approve($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}

	function cancel()
	{
		$model = $this->getModel( 'phocadownload' );
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}

	function orderup()
	{
		$model = $this->getModel( 'phocadownload' );
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}

	function orderdown()
	{
		$model = $this->getModel( 'phocadownload' );
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel( 'phocadownload' );
		$model->saveorder($cid, $order);

		$msg = JText::_( 'New ordering saved' );
		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads', $msg );
	}
	
	function accessMenu()
	{
		$post			= JRequest::get('post');
		$cid			= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$access			= $post['task'];
		
		switch ($access)
		{
			case 'accessregistered':
			$access_id= 1;
			break;

			case 'accessspecial':
			$access_id= 2;
			break;
			
			case 'accesspublic':
			default:
			$access_id= 0;
			break;
		}
		
		$model = $this->getModel( 'phocadownload' );

		$model->accessmenu($cid[0],$access_id);
		$model->checkin();
		$link = 'index.php?option=com_phocadownload&view=phocadownloads';
		$this->setRedirect($link, $msg);
	}
}
?>
