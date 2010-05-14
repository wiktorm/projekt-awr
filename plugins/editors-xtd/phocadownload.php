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
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgButtonPhocaDownload extends JPlugin
{
	function plgButtonPhocaDownload(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onDisplay($name) {
		global $mainframe;

		$document = & JFactory::getDocument();
		$template = $mainframe->getTemplate();
		
		JHTML::stylesheet( 'phocadownload.css', 'plugins/editors-xtd/phocadownload/css/' );
		
		$link = 'index.php?option=com_phocadownload&amp;view=phocadownloadlinks&amp;tmpl=component&amp;e_name='.$name;

		JHTML::_('behavior.modal');

		$button = new JObject();
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('Phoca Download Link'));
		$button->set('name', 'phocadownload');
		$button->set('options', "{handler: 'iframe', size: {x: 600, y: 400}}");
		
		if (!$mainframe->isAdmin()) {
			$button = null;
		}
		return $button;
	}
}