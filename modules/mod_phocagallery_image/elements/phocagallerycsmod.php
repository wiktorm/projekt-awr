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
defined('_JEXEC') or die('Restricted access');// no direct access
if (!JComponentHelper::isEnabled('com_phocagallery', true)) {
	return JError::raiseError(JText::_('Phoca Gallery Error'), JText::_('Phoca Gallery is not installed on your system'));
}
if (! class_exists('PhocaGalleryLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocagallery'.DS.'libraries'.DS.'loader.php');
}
phocagalleryimport('phocagallery.render.renderadmin');

class JElementPhocaGalleryCSMod extends JElement
{
	var	$_name = 'PhocaGalleryCSMod';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$class 		= ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );
		
		
		$db = &JFactory::getDBO();

	/*	$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocamenu_list AS a'
		. ' WHERE a.published = 1'
		. ' AND a.type = '.(int)$phocaList
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$lists = $db->loadObjectList();
		//array_unshift($lists, JHTML::_('select.option', '0', '- '.JText::_('All Lists').' -', 'value', 'text'));

		$icon 		= JHTML::_('image', '/administrator/components/com_phocamenu/assets/images/icon-16-warning.png', '');
		$warning 	= '<span class="error hasTip" title="'.JText::_( 'Warning' ).'::'.JText::_($warningText).'">'. $icon . '</span>';
		//return JHTML::_('select.genericlist',  $lists, ''.$control_name.'['.$name.']', $class, 'value', 'text', $value, $control_name.$name). '&nbsp;' .$warning;*/
		
		
		// build list of categories
		
		$query = 'SELECT a.title AS text, a.id AS value, a.parent_id as parentid'
		. ' FROM #__phocagallery_categories AS a'
	//	. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$categories = $db->loadObjectList();

		$tree = array();
		$text = '';
		$tree = PhocaGalleryRenderAdmin::CategoryTreeOption($categories, $tree, 0, $text, -1);
	//	$categoriesArray = JHTML::_( 'select.genericlist', $tree, 'filter_catid',  $javascript , 'value', 'text', $filter_catid );
		//-----------------------------------------------------------------------
		
		
		// Multiple
		$ctrl	= $control_name .'['. $name .']';
		$attribs	= ' ';
		if ($v = $node->attributes('size')) {
			$attribs	.= 'size="'.$v.'"';
		}
		if ($v = $node->attributes('class')) {
			$attribs	.= 'class="'.$v.'"';
		} else {
			$attribs	.= 'class="inputbox"';
		}
		if ($m = $node->attributes('multiple'))
		{
			$attribs	.= 'multiple="multiple"';
			$ctrl		.= '[]';
			//$value		= implode( '|', )
		}
		return JHTML::_('select.genericlist', $tree, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
	}
}
