<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Download
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.filesystem.file' ); 
class phocadownloadCpViewPhocaDownloadUsers extends JView
{

	var $_context 	= 'com_phocadownload.phocadownloaduser';
	
	function display($tpl = null) {
		global $mainframe;
		$uri		=& JFactory::getURI();
		$document	=& JFactory::getDocument();
		$db		    =& JFactory::getDBO();
		$tmpl		= array();
		
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );

		//Filter		
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state',	'filter_state', '',	'word' );
		$filter_catid		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_catid',	'filter_catid',	0, 'int' );
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	'a.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context.'.search', 'search', '',	'string' );
		$search				= JString::strtolower( $search );

		// Get data from the model
		$items						= & $this->get( 'Data');
		$total						= & $this->get( 'Total');
		$tmpl['pagination'] 		= & $this->get( 'Pagination' );
	//	$tmpl['notapprovedfiles'] 	= & $this->get( 'NotApprovedFiles' );
		

	
		// state filter
		$lists['state']		= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] 	= $filter_order;

		// search filter
		$lists['search']	= $search;
		
		$this->assignRef('tmpl',		$tmpl);
		$this->assignRef('button',		$button);
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('request_url',	$uri->toString());
		
		parent::display($tpl);
		$this->_setToolbar();
	}
	
	function _setToolbar() {
		JToolBarHelper::title(   JText::_( 'PHOCADOWNLOAD_USERS' ), 'users' );
		//JToolBarHelper::publishList();
		//JToolBarHelper::unpublishList();
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Custom', '<a href="#" onclick="javascript:if(confirm(\''.JText::_('PHOCADOWNLOAD_WARNING_AUTHORIZE_ALL').'\')){submitbutton(\'approveall\');}" class="toolbar"><span class="icon-32-authorizeall" title="'.JText::_('PHOCADOWNLOAD_APPROVE_ALL').'" type="Custom"></span>'.JText::_('PHOCADOWNLOAD_APPROVE_ALL').'</a>');	
		JToolBarHelper::help( 'screen.phocadownload', true );
	}
}
?>