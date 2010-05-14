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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementPhocaDownloadEmail extends JElement
{

	var	$_name = 'PhocaDownloadEmail';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		//get all super administrator
		$query = 'SELECT *' .
		' FROM #__users';
		$db->setQuery( $query );
		$users = $db->loadObjectList();
		array_unshift($users, JHTML::_('select.option', '0', JText::_('Nobody'), 'id', 'name'));

		
		return JHTML::_('select.genericlist',  $users, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'name', $value, $control_name.$name );
	}
}
?>