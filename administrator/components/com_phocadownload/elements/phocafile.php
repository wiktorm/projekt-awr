<?php
/**
* @version		$Id: newsfeed.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Renders a Phoca Download Category selection element
 *
 * @package 	PhocaDownload
 * @subpackage	Parameter
 * @since		1.5
 */

class JElementPhocaFile extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'PhocaFiles';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT f.id, f.title, c.title as category, s.title as section '
		 				.' FROM #__phocadownload as f, #__phocadownload_categories as c, #__phocadownload_sections as s '
		 				.' WHERE f.published = 1 '
		 				.' AND f.catid = c.id '
		 				.' AND c.section = s.id '
		 				.' AND c.published = 1 '
		 				.' AND s.published = 1 '
		 				.' GROUP BY f.id '
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );

		$n = count( $options );
		for ($i = 0; $i < $n; $i++)
		{
			$options[$i]->text =  $options[$i]->section . ' - ' . $options[$i]->category . ' - ' . $options[$i]->title;
		}

		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select File').' -', 'id', 'text'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}
