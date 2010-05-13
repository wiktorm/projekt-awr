<?php
/**
 * Attachments search plugin
 *
 * @package Attachments
 * @subpackage Search_Plugin
 *
 * @copyright Copyright (C) 2007-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Register the plugin with the Joomla! system
 */

$mainframe->registerEvent( 'onSearch', 'plgSearchAttachments' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchAttachmentAreas' );


JPlugin::loadLanguage( 'plg_search_attachments', JPATH_ADMINISTRATOR);

/**
 * Return an array of search area names
 *
 * @return array An array of search areas
 */
function &plgSearchAttachmentAreas() {
	static $areas = array( 'attachments' => 'Attachments' );
	return $areas;
}


/**
 * Attachment Search method
 *
 * @return array An array of found items
 * @param string $text The search text
 * @param string $phrase Control the type of matches ('exact', 'all', 'any')
 * @param string $ordering A keyword indicating the desired order of the results ('oldest', 'newest', 'alpha')
 * @param string $areas Areas to limit the search
 */
function plgSearchAttachments( $text, $phrase='', $ordering='', $areas=null )
{
	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	// Exit if the search does not include attachments
	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchAttachmentAreas() ) )) {
			return array();
			}
		}

	// Make sure we have something to search for
	$text = JString::trim( $text );
	if ($text == '') {
		return array();
		}

	// load plugin params info
	$plugin =& JPluginHelper::getPlugin('search', 'attachments');
	$pluginParams = new JParameter( $plugin->params );
	$limit = $pluginParams->def( 'search_limit', 50 );

	// Get the component parameters
	jimport('joomla.application.component.helper');
	$attachParams =& JComponentHelper::getParams('com_attachments');
	$secure = $attachParams->get('secure', false);
	$user_field_1 = false;
	if ( JString::strlen($attachParams->get('user_field_1_name', '')) > 0 ) {
		$user_field_1 = true;
		$user_field_1_name = $attachParams->get('user_field_1_name');
		}
	$user_field_2 = false;
	if ( JString::strlen($attachParams->get('user_field_2_name', '')) > 0 ) {
		$user_field_2 = true;
		$user_field_2_name = $attachParams->get('user_field_2_name');
		}
	$user_field_3 = false;
	if ( JString::strlen($attachParams->get('user_field_3_name', '')) > 0 ) {
		$user_field_3 = true;
		$user_field_3_name = $attachParams->get('user_field_3_name');
		}

	$wheres = array();

	// Create the search query
	switch ($phrase)
	{
	case 'exact':
		$text	= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
		$user_fields_sql = '';
		if ( $user_field_1 )
			$user_fields_sql .= " OR (LOWER(a.user_field_1) LIKE $text)";
		if ( $user_field_2 )
			$user_fields_sql .= " OR (LOWER(a.user_field_2) LIKE $text)";
		if ( $user_field_3 )
			$user_fields_sql .= " OR (LOWER(a.user_field_3) LIKE $text)";

		$where	= "((LOWER(a.filename) LIKE $text)" .
			" OR (LOWER(a.display_name) LIKE $text)" .
			$user_fields_sql .
			" OR (LOWER(a.description) LIKE $text))";
		break;

	default:
		$words	= explode( ' ', $text );
		$wheres = array();
		foreach ($words as $word) {
			$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
			$wheres2	= array();
			$wheres2[]	= "LOWER(a.filename) LIKE $word";
			$wheres2[]	= "LOWER(a.display_name) LIKE $word";
			$wheres2[]	= "LOWER(a.url) LIKE $word";
			if ( $user_field_1 )
				$wheres2[] = "LOWER(a.user_field_1) LIKE $word";
			if ( $user_field_2 )
				$wheres2[] = "LOWER(a.user_field_2) LIKE $word";
			if ( $user_field_3 )
				$wheres2[] = "LOWER(a.user_field_3) LIKE $word";
			$wheres2[]	= "LOWER(a.description) LIKE $word";
			$wheres[]	= implode( ' OR ', $wheres2 );
			}
		$where	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
		break;
		}

	// Set up the sorting
	switch ( $ordering )
	{
	case 'oldest':
		$order = 'a.create_date ASC';
		break;

	case 'newest':
		$order = 'a.create_date DESC';
		break;

	case 'alpha':
	default:
		$order = 'a.filename DESC';
		}

	// Load the permissions functions
	$user =& JFactory::getUser();

	// Construct and execute the query
	$query = 'SELECT * FROM #__attachments AS a'
		. ' WHERE ('. $where .')'
		. ' AND a.published = 1'
		. ' ORDER BY '. $order;
	$db->setQuery( $query, 0, $limit );
	$rows = $db->loadObjectList();

	$count = count( $rows );

	// See if we are done
	$results = Array();
	if ( $count <= 0 ) {
		return $results;
		}

	// Prepare to get parent info
	JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
	$apm =& getAttachmentsPluginManager();

	// Add the result data to the results of the search
	$k = 0;
	for ( $i = 0; $i < $count; $i++ ) {

		$row =& $rows[$i];

		// Get the parent handler
		$parent_type = $row->parent_type;
		$parent_entity = $row->parent_entity;
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type, ignore it
			continue;
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Ignore the attachment if the user may not see the parent
		if ( ! $parent->userMayViewParent($row->parent_id, $parent_entity) ) {
			continue;
			}

		// Ignore the attachment if the parent is not published
		if ( ! $parent->isParentPublished($row->parent_id, $parent_entity) ) {
			continue;
			}

		// Do not add the attachment if the user may not access it
		if ( !$parent->userMayAccessAttachment($row)) {
			continue;
			}

		// Add the parent title
		$parent->loadLanguage();
		$row->parent_title = $parent->getTitle( $row->parent_id, $parent_entity );

		// Construct the download URL if necessary
		if ( $secure AND $row->uri_type == 'file' ) {
			$row->href =
				JRoute::_("index.php?option=com_attachments&task=download&id=".$row->id);
			}
		else {
			$row->href = $row->url;
			}
		if ( $row->display_name AND (JString::strlen($row->display_name) > 0) ) {
			$row->title = JString::str_ireplace('&#183;', '.', $row->display_name);
			}
		else {
			if ( $row->uri_type == 'file' ) {
				$row->title = $row->filename;
				}
			else {
				$row->title = $row->url;
				}
			}

		// Set the text to the string containing the search target
		if ( JString::strlen($row->display_name) > 0 ) {
			$text = $row->display_name .
				" (" . JText::_('FILENAME_COLON') . " " . $row->filename . ") ";
			}
		else {
			$text = JText::_('FILENAME_COLON') . " " . $row->filename;
			}

		if ( JString::strlen($row->description) > 0 ) {
			$text .= " | " . JText::_('DESCRIPTION_COLON') . $row->description;
			}

		if ( $user_field_1 AND (JString::strlen($row->user_field_1) > 0) ) {
			$text .= " | " . $user_field_1_name	 . ": " . $row->user_field_1;
			}
		if ( $user_field_2 AND (JString::strlen($row->user_field_2) > 0) ) {
			$text .= " | " . $user_field_2_name	 . ": " . $row->user_field_2;
			}
		if ( $user_field_3 AND (JString::strlen($row->user_field_3) > 0) ) {
			$text .= " | " . $user_field_3_name	 . ": " . $row->user_field_3;
			}
		$row->text = $text;
		$row->created = $row->create_date;
		$row->browsernav = 2;

		$entity_name = JText::_($parent->getEntityName($parent_entity));
		$parent_title = JText::_($parent->getTitle($row->parent_id, $parent_entity));

		$row->section = JText::sprintf('ATTACHED_TO_PARENT_S_TITLE_S',
									   $entity_name, $parent_title);

		$results[$k] = $row;
		$k++;
		}

	return $results;
}

// Local Variables:
// tab-width: 4
// End:

?>
