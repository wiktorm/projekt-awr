<?php
/**
 * System plugin to display the existing attachments in the editor
 *
 * @package Attachments
 * @subpackage Show_Attachments_in_Editor_Plugin
 *
 * @copyright Copyright (C) 2009-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');


/**
 * Show Attachments in Editor system plugin
 *
 * @package Attachments
 */
class plgSystemShow_attachments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param &object &$subject The object to observe
	 * @param array  $config	An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgShow_attachments(&$subject, $config) 
	{
		parent::__construct($subject, $config);
	}


	/**
	 * Inserts the attachments list above the row of xtd-buttons
	 *
	 * @access	public
	 * @since	1.5
	 */
	function onAfterRender()
	{
		// Make sure this is content related
		$option= JRequest::getVar('option');
		if ( $option != 'com_content' ) {
			return;
			}

		// Get the article ID
		$article_id = JRequest::getVar('id');
		if (!$article_id)
		{
			$cid = JRequest::getVar( 'cid' , array() , '' , 'array' );
			@$article_id = $cid[0];
			$path = '..'.DS;
			}

		$task = JRequest::getCmd('task');
		$view = JRequest::getCmd('view');
		$layout = JRequest::getWord('layout');

		if ( $task =='edit' OR ( ($view == 'article') AND ( $layout=='form') ) ) {

			// Load the code from the attachments plugin to create the list
			require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');

			// Get the article/parent handler
			JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
			$apm =& getAttachmentsPluginManager();
			$parent =& $apm->getAttachmentsPlugin($option);
			$parent_entity = 'default';
			$user_can_add = $parent->userMayAddAttachment($article_id, $parent_entity);

			// Construct the attachment list
			$Itemid = JRequest::getInt( 'Itemid', 1);
			$from = 'editor';
			$attachments = AttachmentsHelper::attachmentListHTML($article_id, $parent_entity,
																 $user_can_add, $Itemid, $from, false, false);
			// $attachments = "<div>Attachments $article_id $parent_entity $user_can_add</div>";

			// Insert the attachments, if any
			// NOTE: Assume that anyone editing the article can see its attachments
			if ( $attachments ) {
				$body = JResponse::getBody();
				$body = str_replace('<div id="editor-xtd-buttons">',
									$attachments . '<div id="editor-xtd-buttons">', $body);
				JResponse::setBody($body);
				}
			}
		else {
			return;
			}
	}
}

// Local Variables:
// tab-width: 4
// End:

?>
