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
 
class phocaDownloadCpViewphocaDownloadLinkSec extends JView
{
	function display($tpl = null) {
		global $mainframe;
		$document	=& JFactory::getDocument();
		$uri		=& JFactory::getURI();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );
		
		$eName				= JRequest::getVar('e_name');
		$tmpl['ename']		= preg_replace( '#[^A-Z0-9\-\_\[\]]#i', '', $eName );
		$tmpl['backlink']	= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinks&amp;tmpl=component&amp;e_name='.$tmpl['ename'];
		
		$sections		= & $this->get( 'Data');
		array_unshift($sections, JHTML::_('select.option', '0', '- '.JText::_('Select Section').' -', 'value', 'text'));
		$lists['sectionid'] = JHTML::_( 'select.genericlist', $sections, 'sectionid', '' , 'value', 'text' );
		
		$this->assignRef('lists',	$lists);
		$this->assignRef('tmpl',	$tmpl);
		parent::display($tpl);
	}
}
?>