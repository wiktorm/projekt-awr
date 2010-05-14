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

class PhocaDownloadCpControllerPhocaDownloadLic extends PhocaDownloadCpController
{
	function __construct() {
		parent::__construct();
		
		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'apply'  , 'save' );
	}
		
	function edit() {
		JRequest::setVar( 'view', 'phocadownloadlic' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar( 'hidemainmenu', 1 );

		parent::display();

		// Checkin the Phoca download
		$model = $this->getModel( 'phocadownloadlic' );
		$model->checkout();
	}
	
	function save() {
	
		$post					= JRequest::get('post');
		$cid					= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['description']	= JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post['id'] 			= (int) $cid[0];
		
		
		$model = $this->getModel( 'phocadownloadlic' );
		switch ( JRequest::getCmd('task') ) {
			case 'apply':
				$id	= $model->store($post);//you get id and you store the table data
				if ($id && $id > 0) {
					$msg = JText::_( 'Changes to Phoca Download License Saved' );
					//$id		= $model->store($post);
				} else {
					$msg = JText::_( 'Error Saving Phoca Download License' );
					$id		= $post['id'];
				}

				$this->setRedirect( 'index.php?option=com_phocadownload&controller=phocadownloadlic&task=edit&cid[]='. (int)$id, $msg );
				break;

			case 'save':
			default:
				if ($model->store($post)) {
					$msg = JText::_( 'Phoca Download License Saved' );
				} else {
					$msg = JText::_( 'Error Saving Phoca Download License' );
				}
				$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics', $msg );
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

		$model = $this->getModel( 'phocadownloadlic' );
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
			$msg = JText::_( 'Error Deleting Phoca Download License' );
		}
		else {
			$msg = JText::_( 'Phoca Download License Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics', $msg );
	}

	function publish() {
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('phocadownloadlic');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	}

	function unpublish() {
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('phocadownloadlic');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	}

	function cancel() {
		$model = $this->getModel( 'phocadownloadlic' );
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	}

	function orderup()
	{
		$model = $this->getModel( 'phocadownloadlic' );
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	}

	function orderdown()
	{
		$model = $this->getModel( 'phocadownloadlic' );
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel( 'phocadownloadlic' );
		$model->saveorder($cid, $order);

		$msg = JText::_( 'New ordering saved' );
		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadlics', $msg );
	}
}
?>
