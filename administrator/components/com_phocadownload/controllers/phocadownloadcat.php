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

class PhocaDownloadCpControllerPhocaDownloadcat extends PhocaDownloadCpController
{
	function __construct() {
		parent::__construct();
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'apply'  , 'save' );
		$this->registerTask( 'accesspublic', 'accessMenu');
		$this->registerTask( 'accessregistered', 'accessMenu');
		$this->registerTask( 'accessspecial', 'accessMenu');
	}
	
	function edit() {
		JRequest::setVar( 'view', 'phocadownloadcat' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar( 'hidemainmenu', 1 );

		parent::display();

		// Checkin the Phoca Download
		$model = $this->getModel( 'phocadownloadcat' );
		$model->checkout();
	}

	function save() {
		$post					= JRequest::get('post');
		$cid					= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['description']	= JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$section				= JRequest::getVar( 'sectionid', array(0), 'post', 'array' );
		$post['id'] 			= (int) $cid[0];
		$post['section'] 		= (int) $section[0];
		
		$uploadUserId	= JRequest::getVar( 'uploaduserid', array(0 => -2), 'post', 'array' );
		$post['uploaduserid'] = implode(',',$uploadUserId);
		$accessUserId	= JRequest::getVar( 'accessuserid', array(0 => 0), 'post', 'array' );
		$post['accessuserid'] = implode(',',$accessUserId);
		
		$model = $this->getModel( 'phocadownloadcat' );
		
		switch ( JRequest::getCmd('task') ) {
			case 'apply':
				$id	= $model->store($post);//you get id and you store the table data
				if ($id && $id > 0) {
					$msg = JText::_( 'Changes to Phoca Download Categories Saved' );
					//$id		= $model->store($post);
				} else {
					$msg = JText::_( 'Error Saving Phoca Download Categories' );
					$id		= $post['id'];
				}
				$this->setRedirect( 'index.php?option=com_phocadownload&controller=phocadownloadcat&task=edit&cid[]='. $id, $msg );
				break;

			case 'save':
			default:
				if ($model->store($post)) {
					$msg = JText::_( 'Phoca Download Categories Saved' );
				} else {
					$msg = JText::_( 'Error Saving Phoca Download Categories' );
				}
				$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloadcats', $msg );
				break;
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
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
		
		$model = $this->getModel( 'phocadownloadcat' );

		$model->accessmenu($cid[0],$access_id);
		$model->checkin();
		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect($link, $msg);
	}

	function remove()
	{
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

	/*	
		$cids = implode( ',', $cid );
		
		$query = 'SELECT c.id, c.name, c.title, COUNT( s.catid ) AS numcat'
		. ' FROM #__phocadownload_categories AS c'
		. ' LEFT JOIN #__phocadownload AS s ON s.catid = c.id'
		. ' WHERE c.id IN ( '.$cids.' )'
		. ' GROUP BY c.id'
		;
		
		$db->setQuery( $query );

		if (!($rows = $db->loadObjectList())) {
			JError::raiseError( 500, $db->stderr() );
			return false;
		}*/
		
		
		$model = $this->getModel( 'phocadownloadcat' );
		if(!$model->delete($cid))
		{
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
			$msg = JText::_( 'Error Deleting Phoca Download Categories' );
		}
		else {
			$msg = JText::_( 'Phoca Download Categories Deleted' );
		}

		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect( $link, $msg );
	}

	function publish()
	{
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('phocadownloadcat');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect($link);
	}

	function unpublish()
	{
		global $mainframe;

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('phocadownloadcat');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect($link);
	}

	function cancel()
	{
		$model = $this->getModel( 'phocadownloadcat' );
		$model->checkin();

		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect( $link );
	}

	function orderup()
	{
		$model = $this->getModel( 'phocadownloadcat' );
		$model->move(-1);

		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect( $link );
	}

	function orderdown()
	{
		$model = $this->getModel( 'phocadownloadcat' );
		$model->move(1);

		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect( $link );
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel( 'phocadownloadcat' );
		$model->saveorder($cid, $order);

		$msg = JText::_( 'New ordering saved' );
		$link = 'index.php?option=com_phocadownload&view=phocadownloadcats';
		$this->setRedirect( $link, $msg  );
	}
}
?>
