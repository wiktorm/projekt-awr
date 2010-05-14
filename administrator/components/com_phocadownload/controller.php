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

jimport('joomla.application.component.controller');

// Submenu view
$view	= JRequest::getVar( 'view', '', '', 'string', JREQUEST_ALLOWRAW );
 if ($view == 'phocadownloadsecs') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs', true );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats');
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
} else if ($view == 'phocadownloadcats') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats', true );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
}  else if ($view == 'phocadownloadset') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset', true );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
} else if ($view == 'phocadownloadstat') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat', true );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
} else if ($view == 'phocadownloadinfo') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo',true );
} else if ($view == 'phocadownloads') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads', true);
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
} else if ($view == 'phocadownloadlics') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics',true );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
} else if ($view == 'phocadownloadusers') {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload');
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers', true );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
} else {
	JSubMenuHelper::addEntry(JText::_('Control Panel'), 'index.php?option=com_phocadownload', true);
	JSubMenuHelper::addEntry(JText::_('Files'), 'index.php?option=com_phocadownload&view=phocadownloads');
	JSubMenuHelper::addEntry(JText::_('Sections'), 'index.php?option=com_phocadownload&view=phocadownloadsecs' );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_phocadownload&view=phocadownloadcats' );
	JSubMenuHelper::addEntry(JText::_('Licenses'), 'index.php?option=com_phocadownload&view=phocadownloadlics' );
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_phocadownload&view=phocadownloadset' );
	JSubMenuHelper::addEntry(JText::_('Statistics'), 'index.php?option=com_phocadownload&view=phocadownloadstat' );
	JSubMenuHelper::addEntry(JText::_('PHOCADOWNLOAD_USERS'), 'index.php?option=com_phocadownload&view=phocadownloadusers' );
	JSubMenuHelper::addEntry(JText::_('Info'), 'index.php?option=com_phocadownload&view=phocadownloadinfo' );
}

class phocadownloadCpController extends JController {
	function display() {
		parent::display();
	}
}
?>
