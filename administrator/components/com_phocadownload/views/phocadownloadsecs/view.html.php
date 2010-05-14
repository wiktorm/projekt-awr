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
 
class phocaDownloadCpViewphocaDownloadsecs extends JView
{
	var $_context 	= 'com_phocagallery.phocadownloadsec';
	
	function display($tpl = null) {
		global $mainframe;
		$document	=& JFactory::getDocument();
		$uri		=& JFactory::getURI();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );

		//Filter
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state',	'filter_state', '',	'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	's.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context.'.search', 'search', '', 'string' );
		$search				= JString::strtolower( $search );
	
		// Get data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
	
		// build list of categories
		$javascript 		= 'onchange="document.adminForm.submit();"';
		// state filter
		$lists['state']		= JHTML::_('grid.state',  $filter_state );
		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] 	= $filter_order;
		// search filter
		$lists['search']	= $search;

		$ordering = ($lists['order'] == 's.ordering');//Ordering allowed ?
		
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('total',		$total);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('ordering', 	$ordering);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
		$this->_setToolbar();
	}
	
	function _setToolbar() {
		JToolBarHelper::title(   JText::_( 'Phoca Download Sections' ), 'section' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList(  JText::_( 'WARNWANTDELLISTEDITEMS' ), 'remove', 'delete');
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.phocadownload', true );
	}
}
?>