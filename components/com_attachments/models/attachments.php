<?php
/**
 * Attachment list model definition
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Attachment List Model for all attachments belonging to one
 * content article (or other content-related entity)
 *
 * @package Attachments
 */
class AttachmentsModelAttachments extends JModel
{
	/**
	 * ID of parent of the list of attachments
	 */
	var $_parent_id = null;

	/**
	 * type of parent
	 */
	var $_parent_type = null;

	/**
	 * type of parent entity (each parent_type can support several)
	 */
	var $_parent_entity = null;

	/**
	 * Parent class object (an Attachments extension plugin object)
	 */
	var $_parent = null;

	/**
	 * Parent title
	 */
	var $_parent_title = null;

	/**
	 * Parent entity name
	 */
	var $_parent_entity_name = null;

	/**
	 * Whether some of the attachments should be visible to the user
	 */
	var $_some_visible = null;

	/**
	 * Whether some of the attachments should be modifiable to the user
	 */
	var $_some_modifiable = null;

	/**
	 * The desired sort order
	 */
	var $_sort_order;


	/**
	 * The list of attachments for the specified article/content entity
	 */
	var $_list = null;

	/**
	 * Number of attachments
	 *
	 * NOTE: After the list of attachments has been retrieved, if it is empty, this is set to zero.
	 *		 But _list remains null.   You can use this to check to see if the list has been loaded.
	 */
	var $_num_attachments = null;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}


	/**
	 * Set the parent id (and optionally the parent type)
	 *
	 * NOTE: If the $id is null, it will get both $id and $parent_id from JRequest
	 *
	 * @param int $id the id of the parent
	 * @param string $parent_type the parent type (defaults to 'com_content')
	 * @param string $parent_entity the parent entity (defaults to 'default')
	 */
	function setParentId($id=null, $parent_type='com_content', $parent_entity='default')
	{
	// Get the parent id and type
		if ( is_numeric($id) ) {
			$parent_id = intval($id);
			}
		else {
		// It was not an argument, so get parent id and type from the JRequest
		$parent_id	 = JRequest::getInt('article_id', null);

		// Deal with special case of editing from the front end
		if ( $parent_id == null ) {
		if ( JRequest::getCmd('view') == 'article' AND
			 JRequest::getCmd('task') == 'edit' ) {
			$parent_id = JRequest::getInt('id', null);
			}
		}

		// If article_id is not specified, get the general parent id/type
		if ( $parent_id == null ) {
		$parent_id = JRequest::getInt('parent_id', null);
		if ( $parent_id == null ) {
			$errmsg = JText::_('ERROR_NO_PARENT_ID_SPECIFIED') . ' (ERR 84)';
			JError::raiseError(500, $errmsg);
			}
		}
			}

	// Reset instance variables
		$this->_parent_id = $parent_id;
	$this->_parent_type = $parent_type;
	$this->_parent_entity = $parent_entity;

	$this->_parent = null;
	$this->_parent_class = null;
	$this->_parent_title = null;
	$this->_parent_entity_name = null;

	$this->_list = null;
	$this->_sort_order = null;
	$this->_some_visible = null;
	$this->_some_modifiable = null;
	$this->_num_attachments = null;
	}



	/**
	 * Get the parent id
	 *
	 * @return the parent id
	 */
	function getParentId()
	{
	if ( $this->_parent_id == null ) {
		$errmsg = JText::_('ERROR_NO_PARENT_ID_SPECIFIED') . ' (ERR 85)';
		JError::raiseError(500, $errmsg);
		}
	return $this->_parent_id;
	}


	/**
	 * Get the parent type
	 *
	 * @return the parent type
	 */
	function getParentType()
	{
	if ( $this->_parent_type == null ) {
		$errmsg = JText::_('ERROR_NO_PARENT_TYPE_SPECIFIED') . ' (ERR 86)';
		JError::raiseError(500, $errmsg);
		}
	return $this->_parent_type;
	}


	/**
	 * Get the parent entity
	 *
	 * @return the parent entity
	 */
	function getParentEntity()
	{
	if ( $this->_parent_entity == null ) {
		$errmsg = JText::_('ERROR_NO_PARENT_ENTITY_SPECIFIED') . ' (ERR 87)';
		JError::raiseError(500, $errmsg);
		}
	return $this->_parent_entity;
	}


	/**
	 * Get the parent class object
	 *
	 * @return the parent class object
	 */
	function getParentClass()
	{
	if ( $this->_parent_type == null ) {
		$errmsg = JText::_('ERROR_NO_PARENT_TYPE_SPECIFIED') . ' (ERR 88)';
		JError::raiseError(500, $errmsg);
		}

	if ( $this->_parent_class == null ) {

		// Get the parent handler
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($this->_parent_type) ) {
		$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 89)';
		JError::raiseError(500, $errmsg);
		}
		$this->_parent_class =& $apm->getAttachmentsPlugin($this->_parent_type);
		}

	return $this->_parent_class;
	}


	/**
	 * Get the title for the parent
	 *
	 * @return the title for the parent
	 */
	function getParentTitle()
	{
	// Get the title if we have not done it before
	if ( $this->_parent_title == null ) {
		$this->_parent_class = $this->getParentClass();

		// Make sure we have an article ID
		if ( $this->_parent_id == null ) {
		$errmsg = JText::_('ERROR_UNKNOWN_PARENT_ID') . ' (ERR 90)';
		JError::raiseError(500, $errmsg);
		}

		$this->_parent_title =
		$this->_parent_class->getTitle( $this->_parent_id, $this->_parent_entity );
		}

	return $this->_parent_title;
	}


	/**
	 * Get the EntityName for the parent
	 *
	 * @return the entity name for the parent
	 */
	function getParentEntityName()
	{
	// Get the parent entity name if we have not done it before
	if ( $this->_parent_entity_name == null ) {
		$this->_parent_class = $this->getParentClass();

		// Make sure we have an article ID
		if ( $this->_parent_id == null ) {
		$errmsg = JText::_('ERROR_NO_PARENT_ID_SPECIFIED') . ' (ERR 91)';
		JError::raiseError(500, $errmsg);
		}

		$this->_parent_entity_name = JText::_($this->_parent_class->getEntityName( $this->_parent_entity ));
		}

	return $this->_parent_entity_name;
	}


	/**
	 * Set the sort order (do this before doing getAttachmentsList)
	 *
	 * @param string $new_sort_order name of the new sort order
	 */
	function setSortOrder($new_sort_order)
	{
		if ( $new_sort_order == 'filename' )
			$order_by = "filename";
		else if ( $new_sort_order == 'file_size' )
			$order_by = "file_size";
		else if ( $new_sort_order == 'file_size_desc' )
			$order_by = "file_size DESC";
		else if ( $new_sort_order == 'description' )
			$order_by = "description";
		else if ( $new_sort_order == 'display_name' )
			$order_by = "display_name, filename";
		else if ( $new_sort_order == 'create_date' )
			$order_by = "create_date";
		else if ( $new_sort_order == 'create_date_desc' )
			$order_by = "create_date DESC";
		else if ( $new_sort_order == 'modification_date' )
			$order_by = "modification_date";
		else if ( $new_sort_order == 'modification_date_desc' )
			$order_by = "modification_date DESC";
		else if ( $new_sort_order == 'user_field_1' )
			$order_by = "user_field_1";
		else if ( $new_sort_order == 'user_field_2' )
			$order_by = "user_field_2";
		else if ( $new_sort_order == 'user_field_3' )
			$order_by = "user_field_3";
		else if ( $new_sort_order == 'id' )
			$order_by = "id";
		else
			$order_by = "filename";

	$this->_sort_order = $order_by;
	}



	/**
	 * Get or build the list of attachments
	 *
	 * @return the list of attachments for this parent
	 */
	function getAttachmentsList()
	{
	// Just return it if it has already been created
	if ( $this->_list != null ) {
		return $this->_list;
		}

	// Create the list

	// Get the parent id and type
	$parent_id	   = $this->getParentId();
	$parent_type   = $this->getParentType();
	$parent_entity = $this->getParentEntity();

	// Use parent entity corresponding to values saved in the attachments table
	$parent = $this->getParentClass();
	$parent_entity = $parent->getEntityName($parent_entity);

	// Define the list order
	if ( ! $this->_sort_order ) {
		$this->_sort_order = 'filename';
		}

	// Do the query
		$db =& JFactory::getDBO();
		$query	= "SELECT a.*, u.name as uploader_name FROM #__attachments AS a " .
		"LEFT JOIN #__users AS u ON u.id = a.uploader_id " .
		"WHERE a.parent_id='".(int)$parent_id."' AND a.published='1' " .
		"AND a.parent_type='$parent_type' AND a.parent_entity='$parent_entity' " .
		"ORDER BY " . $this->_sort_order;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

	$this->_some_visible = false;
	$this->_some_modifiable = false;

	// Install the list of attachments in this object
	$this->_num_attachments = count($rows);

	// Add permissions for each article in the list
		if ( $this->_num_attachments > 0 ) {
			$this->_list =& $rows;

		// Get the permissions for the attachments for this parent
		$user =& JFactory::getUser();

		// Add the permissions to each row
		$parent = $this->getParentClass();

		// Add permissions (returns a flag indicating if any attachments should be visible)
		$this->_some_visible = $parent->addPermissions($rows, $parent_id);

		// See if any of the attachments can be modified by this user
			foreach ( $rows as $row ) {
				if ( $row->user_may_edit ) {
			$this->_some_modifiable = true;
					break;
					}
				}
		}

	// Finally, return the list!
	return $this->_list;
	}


	/**
	 * Get the number of attachments
	 *
	 * @return the number of attachments for this parent
	 */
	function numAttachments()
	{
	return $this->_num_attachments;
	}


	/**
	 * Should some of the attachments be visible?
	 *
	 * @return true if there are attachments and some should be visible
	 */
	function someVisible()
	{
	// See if the attachments list has been loaded
	if ( $this->_list == null ) {

		// See if we have already loaded the attachements list
		if ( $this->_num_attachments === 0 ) {
		return false;
		}

		// Since the attachments have not been loaded, load them now
		$this->getAttachmentsList();
		}

	// Check for the special case in secure mode with "always list attachments" true
	// NOTE: This only affects displaying the attachments list, not downloading them!
	jimport('joomla.application.component.helper');
	$params =& JComponentHelper::getParams('com_attachments');
	$who_can_see = $params->get('who_can_see', 'logged_in');
	$secure = $params->get('secure', false);
	if ( $secure AND ($who_can_see == 'logged_in') ) {
		if ( $params->get('secure_list_attachments', false) ) {
		return true;
		}
		}

	return $this->_some_visible;
	}


	/**
	 * Should some of the attachments be modifiable?
	 *
	 * @return true if there are attachments and some should be modifiable
	 */
	function someModifiable()
	{
	// See if the attachments list has been loaded
	if ( $this->_list == null ) {

		// See if we have already loaded the attachements list
		if ( $this->_num_attachments === 0 ) {
		return false;
		}

		// Since the attachments have not been loaded, load them now
		$this->getAttachmentsList();
		}

	return $this->_some_modifiable;
	}

// Local Variables:
// tab-width: 4
// End:

}

?>
