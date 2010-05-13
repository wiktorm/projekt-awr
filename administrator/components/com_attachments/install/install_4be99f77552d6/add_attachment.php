<?php
/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Add_Attachment_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

/**
 * Class for the button that allows you to add attachments from the editor
 *
 * @package Attachments
 */
class plgButtonAdd_attachment extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param &object &$subject The object to observe
	 * @param array  $config	An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgAdd_attachment(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Add Attachment button
	 *
	 * @return a button
	 */
	function onDisplay($name)
	{
		// Avoid displaying the button for anything except for registered parents
		global $option;

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($option) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return new JObject();
			}

		// Get the parent handler
		$parent_type = $option;
		$parent =& $apm->getAttachmentsPlugin($parent_type);
		$parent_entity = 'default';

		// Get the parent ID (id or first of cid array)
		//	   NOTE: $id=0 means no id (usually means creating a new entity)
		$cid = JRequest::getVar('cid', array(0), '', 'array');
		$id = 0;
		if ( count($cid) > 0 ) {
			$id = intval($cid[0]);
			}
		if ( $id == 0) {
			$nid = JRequest::getInt('id');
			if ( !is_null($nid) ) {
				$id = intval($nid);
				}
			}

		// Load the language file from the backend
		$lang =&  JFactory::getLanguage();
		$lang->load('plg_frontend_attachments', JPATH_ADMINISTRATOR);

		// Figure out where we are and construct the right link and set
		// up the style sheet (to get the visual for the button working)
		$doc =& JFactory::getDocument();

		// Add the regular css file
		require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/content/attachments.css' );
		AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/editors-xtd/add_attachment.css' );

		// Handle RTL styling (if necessary)
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/content/attachments_rtl.css' );
			AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/editors-xtd/add_attachment_rtl.css' );
			}

		// Load the language file from the frontend
		$lang->load('com_attachments', JPATH_SITE);

		// Create the button object
		$button = new JObject();
		$button->set('options', "{handler: 'iframe', size: {x: 900, y: 530}}");
		$link = $parent->getEntityAddUrl($id, $parent_entity, 'closeme');
		$link .= '&amp;editor=article';

		// Finalize the button info
		$button->set('modal', true);
		$button->set('class', 'modal');
		$button->set('text', JText::_('ADD_ATTACHMENT'));
		$button->set('name', 'add_attachment');
		$button->set('link', $link);
		$button->set('image', 'add_attachment.png');

		return $button;
	}
}

// Local Variables:
// tab-width: 4
// End:

?>
