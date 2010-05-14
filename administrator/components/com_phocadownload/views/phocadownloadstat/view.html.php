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
 
class PhocaDownloadCpViewPhocaDownloadstat extends JView
{
	var $_context 	= 'com_phocagallery.phocadownloadstat';
	
	function display($tpl = null) {
		global $mainframe;
		$uri		=& JFactory::getURI();
		$document	=& JFactory::getDocument();
		$db		    =& JFactory::getDBO();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );
		$params = JComponentHelper::getParams('com_phocadownload') ;

		//Filter
		$sectionid			= JRequest::getVar( 'sectionid', -1, '', 'int' );
		$option				= JRequest::getCmd( 'option' );
		
		$filter_state		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_state',	'filter_state',	'',	'word' );
		$filter_catid		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_catid',	'filter_catid',	0,	'int' );
		$catid				= $mainframe->getUserStateFromRequest( $this->_context.'.catid',	'catid', 0,	'int');
		$filter_sectionid	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_sectionid',	'filter_sectionid',	-1,	'int');
		$filter_order		= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order',	'filter_order',	'a.hits','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $this->_context.'.filter_order_Dir',	'filter_order_Dir',	'DESC',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $this->_context.'.search','search', '','string' );
		$search				= JString::strtolower( $search );

		// Get data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$maxHit		= & $this->get( 'MaxHit' );
		
		
		// build list of categories
		$javascript 	= 'class="inputbox" size="1" onchange="submitform( );"';
		
		// get list of categories for dropdown filter	
		$filter = '';
		if ($filter_sectionid > 0) {
			$filter = ' WHERE cc.section = '.$db->Quote($filter_sectionid);
		}

		// get list of categories for dropdown filter
		$query = 'SELECT cc.id AS value, cc.title AS text' .
				' FROM #__phocadownload_categories AS cc' .
				' INNER JOIN #__phocadownload_sections AS s ON s.id = cc.section' .
				$filter .
				' ORDER BY s.ordering, cc.ordering';

		$lists['catid'] = PhocaDownloadHelper::filterCategory($query, $catid);
		
		// sectionid
		$query = 'SELECT s.title AS text, s.id AS value'
		. ' FROM #__phocadownload_sections AS s'
		. ' WHERE s.published = 1'
		. ' ORDER BY s.ordering';
		
		$lists['sectionid'] = PhocaDownloadHelper::filterSection($query, $filter_sectionid);
		
		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;
		
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('maxhit',		$maxHit[0]->maxhit);
		$this->assignRef('request_url',	$uri->toString());
		
		parent::display($tpl);
		$this->_setToolbar();
	}
	
	function _setToolbar() {
		JToolBarHelper::title(   JText::_( 'Phoca Download Statistics' ), 'statistics' );
		JToolBarHelper::cancel( 'cancel', 'Close' );
		JToolBarHelper::help( 'screen.phocadownload', true );
	}
}
?>