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
 
class phocaDownloadCpViewphocaDownloadLinks extends JView
{
	function display($tpl = null) {
		global $mainframe;
		$document	=& JFactory::getDocument();
		$uri		=& JFactory::getURI();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );
		
		$eName	= JRequest::getVar('e_name');
		$eName	= preg_replace( '#[^A-Z0-9\-\_\[\]]#i', '', $eName );
		
		$tmpl['linksections']	= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinksecs&amp;tmpl=component&amp;e_name='.$eName;
		$tmpl['linksection']	= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinksec&amp;tmpl=component&amp;e_name='.$eName;
		$tmpl['linkcategory']	= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinkcat&amp;tmpl=component&amp;e_name='.$eName;
		$tmpl['linkfile']		= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinkfile&amp;tmpl=component&amp;e_name='.$eName;
		$tmpl['linkytb']		= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinkytb&amp;tmpl=component&amp;e_name='.$eName;
		
		$this->assignRef('tmpl',	$tmpl);
		parent::display($tpl);
	}
}
?>