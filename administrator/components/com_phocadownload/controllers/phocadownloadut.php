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

class PhocaDownloadCpControllerPhocaDownloadut extends PhocaDownloadCpController
{
	function __construct() {
		parent::__construct();
		$this->registerTask( 'add'  , 	'edit' );
	}

	function cancel() {
		$model = $this->getModel( 'phocadownload' );
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_phocadownload&view=phocadownloads' );
	}
	
	function reset() {
		
		$post					= JRequest::get('post');
		$cid					= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$idFile					= JRequest::getVar( 'idfile', 0, 'post', 'int' );

		$model = $this->getModel( 'phocadownloadut' );

		if ($model->reset($cid)) {
			$msg = JText::_( 'User Statistics successfully reset' );
		} else {
			$msg = JText::_( 'Error Reseting User Statistics' );
		}
		
		$link = 'index.php?option=com_phocadownload&view=phocadownloadut&cid[]='.(int)$idFile;
		$this->setRedirect($link, $msg);
	}
}
?>
