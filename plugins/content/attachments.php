<?php
/**
 * Attachments plugin for inserting attachments lists into content
 *
 * @package Attachments
 * @subpackage Main_plugin
 *
 * @copyright Copyright (C) 2007-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

$mainframe->registerEvent('onPrepareContent', 'addAttachments');
$mainframe->registerEvent('onAfterContentSave', 'fixAttachmentsParent');


/**
 * Fix the Javascript order problem that occurs occasionally
 */
function attachments_fixScripts()
{
	// Fix the document's list of scripts to remove modal.js and
	// then re-add it in order to force it to be after mootools.js
	//
	// NOTE: Not sure why this hack is necessary, but with some other
	//		 extensions, mootools.js seems to pop up after modal.js
	//		 which does not work since modal.js depends on mootools.js.

	$document =&  JFactory::getDocument();

	$modal_url = false;
	foreach ($document->_scripts as $url => $type) {
		if ( JString::strpos($url, '/media/system/js/modal.js') ) {
			$modal_url = $url;
			}
		}
	if ( $modal_url ) {
		unset($document->_scripts[$modal_url]);
		}
	$document->addScript( JURI::root(true).'/media/system/js/modal.js' );
}



/**
 * Return a list of attachments as HTML code.
 *
 * @param int $parent_id ID of the parent object
 * @param string $parent_entity type of the entity involved
 * @param bool $user_can_add true if the user can add attachments to this parent object
 * @param int $Itemid the menu item id for the display
 * @param string $from where the control should return to
 *
 * @return a list of attachments as HTML code
 */
function attachments_attachmentListHTML($parent_id, $parent_entity, $user_can_add, $Itemid, $from)
{
	global $option;

	$parent_type = $option;

	// Generate the HTML for the attachments for the specified parent
	$alist = '';
	$db =& JFactory::getDBO();
	$query = "SELECT count(*) FROM #__attachments "
		. "WHERE parent_id='".(int)$parent_id."' AND published='1' AND parent_type='$parent_type'";
	$db->setQuery($query);
	$total = $db->loadResult();

	if ( $total > 0 ) {

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Check the security status
		$attach_dir = JPATH_SITE.DS.$params->get('attachments_subdir', 'attachments');
		$secure = $params->get('secure', false);
		$hta_filename = $attach_dir.DS.'.htaccess';
		if ( ($secure AND !file_exists($hta_filename)) OR
			 (!$secure AND file_exists($hta_filename)) ) {
			require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');
			AttachmentsHelper::setup_upload_directory($attach_dir, $secure);
			}

		// Get the html for the attachments list
		require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.
					 'controllers'.DS.'attachments.php');
		$controller = new AttachmentsControllerAttachments();
		$alist = $controller->display($parent_id, $parent_type, $parent_entity,
									  null, true, true, false, $from);
		}

	return $alist;
}


/**
 * Return the HTML for the "Add Attachments" link
 *
 * @param int $parent_id ID of the parent object
 * @param string $parent_entity type of the entity involved
 * @param int $Itemid the menu item id for the display
 * @param string $from where the control should return to
 *
 * @return the HTML for the "Add Attachments" link
 */
function attachments_attachmentButtonsHTML($parent_id, $parent_entity, $Itemid, $from)
{
	global $option;

	$document =& JFactory::getDocument();

	JHTML::_('behavior.modal', 'a.modal-button');

	// Generate the HTML for a	button for the user to click to get to a form to add an attachment
	if ( $option == 'com_content' AND $parent_entity == 'default' ) {
		$url = "index.php?option=com_attachments&task=upload&article_id=$parent_id&tmpl=component";
		}
	else {
		$parent_type = $option;
		if ( $parent_entity != 'default' ) {
			$parent_type .= ':'.$parent_entity;
			}
		$url = "index.php?option=com_attachments&task=upload" .
			"&parent_id=$parent_id&parent_type=$parent_type&tmpl=component";
		}
	if ( $from ) {
		// Add a var to give a hint of where to return to
		// $url .= "&from=$from";
		$url .= "&from=closeme";
		}
	$url = JRoute::_($url);
	$icon_url = JURI::Base() . 'components/com_attachments/media/add_attachment.gif';

	$add_attachment_txt = JText::_('ADD_ATTACHMENT');
	$ahead = '<a class="modal-button" type="button" href="' . $url . '" ';
	$ahead .= "rel=\"{handler: 'iframe', size: {x: 900, y: 550}}\">";
	$links = "$ahead<img src=\"$icon_url\" alt=\"$add_attachment_txt\" /></a>";
	$links .= $ahead.$add_attachment_txt."</a>";
	return "\n<div class=\"addattach\">$links</div>\n";
}


/**
 * The content plugin that inserts the attachments list into content items
 *
 * @param &object &$row the content object (eg, article) being displayed
 * @param &object &$params the parameters
 * @param int $page the 'page' number
 *
 * @return true if anything has been inserted into the content object
 */
function addAttachments( &$row, &$params, $page=0 )
{
	global $option;

	$parent_type = $option;

	// Get the article/parent handler
	JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
	$apm =& getAttachmentsPluginManager();
	if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
		// Exit quietly if there is no Attachments plugin to handle this parent_type
		return false;
		}
	$parent =& $apm->getAttachmentsPlugin($parent_type);

	// If this attachments plugin is disabled, skip it
	if ( ! $apm->attachmentsPluginEnabled($parent_type) ) {
		return false;
		}

	// Figure out the parent entity
	$parent_entity = $parent->determineParentEntity($row);
	if ( !$parent_entity ) {
		return false;
		}

	// Get the component parameters
	jimport('joomla.application.component.helper');
	$attachParams =& JComponentHelper::getParams('com_attachments');

	// Get the desired placement
	$attachments_placement = $attachParams->get('attachments_placement', 'end');
	if ( $attachments_placement == 'disabled_nofilter' ) {
		return false;
		}

	// Get some of the options
	$user =& JFactory::getUser();
	$logged_in = $user->get('username') <> '';
	$user_type = $user->get('usertype', false);
	$parent_id = null;
	if ( isset( $row->id ) AND $row->id > 0 ) {
		$parent_id = intval($row->id);
		}
	else {
		$parent_id = $parent->getParentId($row);
		}
	if ( $parent_id === false ) {
		return false;
		}

	// Load the language files from the backend
	$lang =&  JFactory::getLanguage();
	$lang->load('plg_frontend_attachments', JPATH_ADMINISTRATOR);

	// exit if we should not display attachments for this parent
	if ( $parent->attachmentsHiddenForParent($row, $parent_id, $parent_entity, $attachParams) ) {
		return false;
		}

	// See whether we can display the links to add attachments
	$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);
	$who_can_add = $attachParams->get('who_can_add');
	if ( $who_can_add == 'no_one' ) {
		$user_can_add = false;
		}

	// Determine where we are
	$from = JRequest::getCmd('view');
	$Itemid = JRequest::getInt( 'Itemid', 1);

	// Get the attachments tag, if present
	$attachments_tag = '';
	$attachments_tag_args = '';
	$match = false;
	if ( JString::strpos($row->text, '{attachments') ) {
		if ( preg_match('@{attachments([ ]*:*[^}]+)?}@', $row->text, $match) ) {
			$attachments_tag = true;
			}
		if ( isset($match[1]) AND $match[1] ) {
			$attachments_tag_args_raw = $match[1];
			$attachments_tag_args = ltrim($attachments_tag_args_raw, ' :');
			}
		if ( $attachments_tag ) {
			$attachments_tag = $match[0];
			}
		}

	// get viewing permission info
	$who_can_see = $attachParams->get('who_can_see', 'logged_in');
	$secure = $attachParams->get('secure', false);
	if ( $secure AND ($who_can_see == 'logged_in') ) {
		if ( $attachParams->get('secure_list_attachments', false) ) {
			$who_can_see = 'anyone';
			}
		}

	// Construct the attachment list (if appropriate)
	$html = '';
	$attachments_list = false;
	$add_attachement_btn = false;
	if ( ( $who_can_see == 'anyone' ) OR
		 ( ($who_can_see == 'logged_in') AND $logged_in ) ) {
		$attachments_list =
			attachments_attachmentListHTML($parent_id, $parent_entity, $user_can_add, $Itemid, $from);

		$html .= $attachments_list;
		}

	if ( $html OR $user_can_add ) {
		// Add the style sheet
		require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/content/attachments.css' );
		}

	// Construct the add-attachments button, if appropriate
	if ( $user_can_add ) {
		$add_attachments_btn =
			attachments_attachmentButtonsHTML($parent_id, $parent_entity, $Itemid, $from);
		$html .= $add_attachments_btn;
		}

	// Wrap both list and the Add Attachments button in another div
	if ( $html ) {
		$html = "<div class=\"attachmentsContainer\">\n" . $html . "\n</div>";
		}

	// Finally, add the attachments

	switch ( $attachments_placement ) {

	case 'beginning':
		// Put the attachments list at the beginning of the article/entity
		if ( $attachments_list OR $user_can_add ) {
			if ( $attachments_tag ) {
				$row->text = $html . $row->text;
				}
			else {
				$row->text = $html . JString::str_ireplace($attachments_tag, '', $row->text);
				}
			}
		break;

	case 'custom':
		// Insert the attachments at the desired location
		if ( $attachments_list OR $user_can_add ) {
			if ( $attachments_tag ) {
				$row->text = JString::str_ireplace($attachments_tag, $html, $row->text);
				}
			else {
				// If there is no tag, insert the attachments at the end
				$row->text .= $html;
				}
			}
		break;

	case 'disabled_filter':
		// Disable and strip out any attachments tags
		if ( $attachments_tag ) {
			$row->text = JString::str_ireplace($attachments_tag, '', $row->text);
			}
		break;

	default:
		// Add the attachments to the end of the article
		if ( $attachments_list OR $user_can_add ) {
			if ( $attachments_tag ) {
				$row->text = JString::str_ireplace($attachments_tag, '', $row->text) . $html;
				}
			else {
				$row->text .= $html;
				}
			}
		break;
		}

	// Correct the order of the Javascript files
	if ( $attachments_placement != 'disabled_filter' ) {
		attachments_fixScripts();
		}

	return true;
}



/**
 * Set the parent_id for all attachments that were added to this
 * content before it was saved the first time.
 *
 * This method is called right after the content is saved.
 *
 * @param &object &$article A JTableContent object
 * @param bool $isNew If the content is newly created
 *
 * @return	void
 *
 * NOTE: Currently this only supports attachment parents being articles since
 *		 this will only be invoked when articles are saved.
 */
function fixAttachmentsParent( &$article, $isNew )
{
	global $option, $mainframe;

	if ( !$isNew )
		// If the article is not new, this step is not needed
		return true;

	// Get the attachments associated with this newly created object
	// NOTE: We assume that all attachments that have parent_id=null
	//		 and are created by the current user are for this article.
	$user =& JFactory::getUser();
	$user_id = $user->get('id');
	$db =& JFactory::getDBO();
	$query = "SELECT * FROM #__attachments "
		. "WHERE uploader_id='$user_id' AND parent_id IS NULL";
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	$parent_type = $option;

	if ( count($rows) == 0 )
		return true;

	// Change the attachment to the new article!
	JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'tables');
	$atrow =& JTable::getInstance('attachments', 'Table');

	require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');

	foreach ($rows as $row) {

		// Change the filename/URL as necessary
		$error_msg = AttachmentsHelper::switch_parent($row, null, $article->id);
		if ( $error_msg != '' ) {
			$errmsg = JText::_($error_msg) . ' (ERR 200)';
			JError::raiseError(500, $errmsg);
			}

		// Update the parent info
		$atrow->load($row->id);
		$atrow->parent_id = $article->id;
		$atrow->parent_type = $parent_type;
		$atrow->filename_sys = $row->filename_sys;
		$atrow->url = $row->url;

		if ( !$atrow->store() ) {
			$errmsg = $row->getError() . ' (ERR 201)';
			JError::raiseError(500, $errmsg);
			}
		}

	return true;
}


// Local Variables:
// tab-width: 4
// End:

?>
