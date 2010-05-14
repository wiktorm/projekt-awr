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
jimport( 'joomla.application.component.view' );

class PhocaDownloadCpViewPhocaDownloadsec extends JView
{
	function display($tpl = null) {
		global $mainframe;
		
		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}
		parent::display($tpl);
	}

	function _displayForm($tpl) {
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();
		$editor =& JFactory::getEditor();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );		
		JHTML::_('behavior.calendar');
		
		$phocadownload	=& $this->get('Data');
		
		$lists 	= array();		
		$isNew	= ($phocadownload->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'Phoca Download Sections' ), $phocadownload->title );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		// Edit or Create?
		if (!$isNew) {
			$model->checkout( $user->get('id') );
		} else {
			$phocadownload->published 		= 1;
			$phocadownload->order 			= 0;
			$phocadownload->access			= 0;
		}
		
		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__phocadownload_sections'
			. ' ORDER BY ordering';
		$lists['ordering'] 			= JHTML::_('list.specificordering',  $phocadownload, $phocadownload->id, $query, false );
		// build the html select list
		$lists['published'] 		= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $phocadownload->published );
		
		$active =  ( $phocadownload->image_position ? $phocadownload->image_position : 'left' );
		$lists['image_position'] 	= JHTML::_('list.positions',  'image_position', $active, NULL, 0, 0 );
		// Imagelist
		$lists['image'] 			= JHTML::_('list.images',  'image', $phocadownload->image );
		// build the html select list for the group access
		$lists['access'] 			= JHTML::_('list.accesslevel',  $phocadownload );		
		
		//clean component data
		jimport('joomla.filter.output');
		JFilterOutput::objectHTMLSafe( $phocadownload, ENT_QUOTES, 'description' );

		$this->assignRef('editor', $editor);
		$this->assignRef('lists', $lists);
		$this->assignRef('phocadownload', $phocadownload);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
		$this->_setToolbar($isNew);
	}
	
	function _setToolbar($isNew) {
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Phoca Download Sections' ).': <small><small>[ ' . $text.' ]</small></small>' , 'section');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		JToolBarHelper::help( 'screen.phocadownload', true );
	}
}
?>
