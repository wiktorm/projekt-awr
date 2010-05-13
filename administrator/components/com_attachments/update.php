<?php
/**
 * Attachments component
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

global $mainframe;
$mainframe->isAdmin() or die('Must be admin to execute!');

/**
 * A class for update functions
 *
 * @package Attachments
 */
class AttachmentsUpdate
{
	/**
	 * Add icon filenames for all attachments missing an icon
	 */
	function add_icon_filenames()
	{
		require_once(JPATH_COMPONENT_SITE.DS.'file_types.php');

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id, filename, file_type, icon_filename FROM #__attachments "
			. "WHERE file_type IS NULL";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_FILE_TYPE_FIELDS_NEED_UPDATING');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Update the icon file_types all the attachments (that do not have one already)
		$row =& JTable::getInstance('Attachments', 'Table');
		$numUpdated = 0;
		foreach ($IDs as $id) {

			$row->load($id);

			// Only update those attachment records that don't already have an icon_filename
			if ( JString::strlen( $row->icon_filename ) == 0 ) {
				$new_icon_filename = AttachmentsFileTypes::icon_filename($row->filename,
																		 $row->file_type);
				if ( JString::strlen( $new_icon_filename) > 0 ) {
					$row->icon_filename = $new_icon_filename;
					if (!$row->store()) {
						$errmsg = JText::sprintf('ERROR_ADDING_ICON_FILENAME_FOR_ATTACHMENT_S', $row->filename) .
							' ' . $row->getError() . ' (ERR 0)';
						JError::raiseError(500, $errsmg);
						}
					$numUpdated++;
					}
				}
			}

		return JText::sprintf( 'ADDED_ICON_FILENAMES_TO_N_ATTACHMENTS', $numUpdated );
	}

	/**
	 * Update dates for all attachments with null dates
	 */
	function update_null_dates()
	{
		global $mainframe;

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT * FROM #__attachments";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return 0;
			}

		// Update the dates for all the attachments
		$numUpdated = 0;
		foreach ($rows as $row) {

			// Update the new create and update dates if they are null
			$updated = false;
			$create_date = $row->create_date;
			if ( is_null($create_date) OR $create_date == ''  ) {
				jimport( 'joomla.utilities.date' );
				$cdate = new JDate(filemtime($row->filename_sys), $mainframe->getCfg('offset'));
				$create_date = $cdate->toMySQL();
				$updated = true;
				}
			$mod_date = $row->modification_date;
			if ( is_null($mod_date) OR $mod_date == '' ) {
				jimport( 'joomla.utilities.date' );
				$mdate = new JDate(filemtime($row->filename_sys), $mainframe->getCfg('offset'));
				$mod_date = $mdate->toMySQL();
				$updated = true;
				}

			// Update the record
			if ( $updated ) {
				$query = "UPDATE #__attachments set modification_date='{$mod_date}', " .
					"create_date='{$create_date}' WHERE id='".(int)$row->id."'";
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = JText::sprintf('ERROR_UPDATING_NULL_DATE_FOR_ATTACHMENT_FILE_S',
											 $row->filename) . " ";
					echo $errmsg . $db->stderr();
					}
				$numUpdated++;
				}
			}

		return $numUpdated;
	}

	/**
	 * Update the attachments table to the current release
	 *
	 * This function is called during installation to update the attachments
	 * table. This function adds/renames fields and applies other changes to
	 * the attachments table to make it work with this release.
	 */
	function update_attachments_table()
	{
		// NOTE: It should be harmless to run this function multiple times

		$db =& JFactory::getDBO();
		$indent = "&nbsp;&nbsp;&nbsp;";

		// Make sure the table is unicode compatible
		$query = "ALTER TABLE #__attachments "
			. "CONVERT TO CHARACTER SET `utf8` COLLATE `utf8_general_ci`";
		$db->setQuery($query);
		if (!$db->query()) {
			$errmsg = JText::_('ERROR_CHANGING_TABLE_TO_UTF8_UNICODE') . ' ' . $db->stderr() . ' (ERR 1)';
			JError::raiseError( 500, $errmsg );
			return false;
			}
		echo $indent .
			JText::_('CONVERTED_ATTACHMENTS_TABLE_TO_UTF8_WITH_COLLATION_UTF8_GENERAL_CI') .
			"<br />";
		echo $indent .
			JText::_('YOU_MAY_CHANGE_TO_ANOTHER_COLLATION_WITH_MYSQL') . "<br />&nbsp;<br />";

		// Get the existing field names
		$query = "explain #__attachments";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$fields = array();
		$types = array();
		$nullable = array();
		$default = array();
		foreach ($rows as $row) {
			$fields[] = $row->Field;
			$types[$row->Field] = $row->Type;
			$nullable[$row->Field] = $row->Null;
			$default[$row->Field] = $row->Default;
			}

		// Rename fields as necessary
		$num_renamed_fields = 0;
		$rename_fields = Array();
		$rename_fields['article_id'] =
			Array( 'new_name' => 'parent_id',
				   'col_def' => "INT(11) UNSIGNED DEFAULT NULL" );
		$rename_fields['display_filename'] =
			Array( 'new_name' => 'display_name',
				   'col_def' => "VARCHAR(80) NOT NULL DEFAULT ''" );

		// Fix the article ID (NB: LEAVE FIRST ONE AS `article_id` on the next line!)
		foreach ( $rename_fields as $old_name => $arr ) {
			if ( in_array( $old_name, $fields ) ) {
				// Change the field name
				$query = "ALTER TABLE #__attachments CHANGE `{$old_name}` `{$arr['new_name']}` "
					. $arr['col_def'];
				echo $indent . JText::sprintf('RENAMING_COLUMN_S_TO_S', $old_name, $arr['new_name']) . "<br />";
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = JText::sprintf('ERROR_RENAMING_FIELD_S_TO_S',
											 $old_name, $arr['new_name'] ) . ' ' . $db->stderr() . ' (ERR 2)';
					JError::raiseError(500, $errmsg);
					}
				$num_renamed_fields++;
				}
			}

		// Get the existing field names again (with renamed fields)
		if ( $num_renamed_fields > 0 ) {
			$query = "explain #__attachments";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$fields = array();
			$types = array();
			$nullable = array();
			$default = array();
			foreach ($rows as $row) {
				$fields[] = $row->Field;
				$types[$row->Field] = $row->Type;
				$nullable[$row->Field] = $row->Null;
				$default[$row->Field] = $row->Default;
				}
			}

		// Prepare data to add any missing columns/fields
		$new_fields = Array();
		$new_fields['display_name'] =
			"ALTER TABLE #__attachments ADD `display_name` VARCHAR(80) NOT NULL DEFAULT ''";
		$new_fields['uri_type'] =
			"ALTER TABLE #__attachments ADD `uri_type` ENUM('file', 'url') DEFAULT 'file'";
		$new_fields['url_valid'] =
			"ALTER TABLE #__attachments ADD `url_valid` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
		$new_fields['user_field_1'] =
			"ALTER TABLE #__attachments ADD `user_field_1` VARCHAR(100) NOT NULL DEFAULT ''";
		$new_fields['user_field_2'] =
			"ALTER TABLE #__attachments ADD `user_field_2` VARCHAR(100) NOT NULL DEFAULT ''";
		$new_fields['user_field_3'] =
			"ALTER TABLE #__attachments ADD `user_field_3` VARCHAR(100) NOT NULL DEFAULT ''";
		$new_fields['create_date'] =
			"ALTER TABLE #__attachments ADD `create_date` DATETIME DEFAULT NULL";
		$new_fields['modification_date'] =
			"ALTER TABLE #__attachments ADD `modification_date` DATETIME DEFAULT NULL";
		$new_fields['download_count'] =
			"ALTER TABLE #__attachments ADD `download_count` INT(11) UNSIGNED DEFAULT '0'";
		$new_fields['parent_type'] =
			"ALTER TABLE #__attachments ADD `parent_type` VARCHAR(100) NOT NULL DEFAULT 'com_content'";
		$new_fields['parent_entity'] =
			"ALTER TABLE #__attachments ADD `parent_entity` VARCHAR(100) NOT NULL DEFAULT 'default'";

		// Add any missing fields
		$num_fields_added = 0;
		$dates_added = false;
		foreach ($new_fields as $field => $query) {
			if ( !in_array( $field , $fields ) ) {
				echo $indent . JText::sprintf('ADDING_MISSING_COLUMN_S', $field) . "<br />";
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = JText::sprintf('ERROR_INSTALLING_FIELD_S', $field) .
						' ' . $db->stderr() . ' (ERR 3)';
					JError::raiseError( 500, $errmsg );
					return false;
					}
				if ( $field == 'create_date' )
					$dates_added = true;
				$num_fields_added++;
				}
			}
		if ( $num_fields_added != 0 )
			echo "<br />";

		// Fix any null dates
		if ( $dates_added ) {
			echo $indent . JText::_('ADDING_MISSING_CREATE_OR_MODIFICATION_DATES') . "<br />";

			$num = AttachmentsUpdate::update_null_dates();
			if ( $num == 0 )
				echo $indent . $indent . JText::_('NO_ATTACHMENTS_NEEDED_UPDATING') . "<br />";
			elseif ( $num == 1 )
				echo $indent . $indent . JText::_('ONE_ATTACHMENT_UPDATED') . "<br />";
			else
				echo $indent . $indent . JText::sprintf('N_ATTACHMENTS_UPDATED', $num) . "<br />";
			}

		// Resize any fields that need resizing
		$resize_fields = Array(
			'description' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'file_type' => "VARCHAR(128) NOT NULL",
			'url' => "TEXT NOT NULL DEFAULT ''",
							   );
		foreach ($resize_fields as $field => $new_size) {
			if ( $types[$field] != strtolower($new_size) ) {
				echo $indent . JText::sprintf('RESIZING_COLUMN_S', $field) . "<br />";
				$query = "ALTER TABLE #__attachments MODIFY `$field` $new_size";
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = JText::sprintf('ERROR_MODIFYING_FIELD_S', 'description') .
						 ' ' . $db->stderr() . ' (ERR 4)';
					JError::raiseError(500, $errmsg);
					return false;
					}
				}
			}

		// Drop the default values for fields that are not supposed to be nullable
		$default_null_fields =
			Array( 'filename', 'filename_sys', 'file_size', 'icon_filename',
				   'parent_id', 'uploader_id' );
		foreach ($default_null_fields as $field) {
			if ( $default[$field] != 'NULL' ) {
				$query = "ALTER TABLE `#__attachments` ALTER `$field` DROP DEFAULT";
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = Text::sprintf('ERROR MODIFYING FIELD S', $field) .
						 ' ' . $db->stderr() . ' (ERR 5)';
					JError::raiseError( 500, $errmsg );
					return false;
					}
				}
			}

		// If there are any old attachments, make sure they correspond with the new DB scheme
		$query = "UPDATE `#__attachments` SET `parent_entity` = 'ARTICLE' " .
				 "WHERE `parent_type` = 'com_content' AND  `parent_entity` = 'default'";
		$db->setQuery($query);
		if (!$db->query()) {
			$errmsg = JText::sprintf('ERROR_UPDATING_EXISTING_ATTACHMENTS')  .
				' ' . $db->stderr() . ' (ERR 6)';
			JError::raiseError( 500, $errmsg );
			return false;
			}

		// Get the existing indexed column names
		$query = "show index from #__attachments";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$indexes = array();
		foreach ($rows as $row) {
			$indexes[] = $row->Key_name;
			}

		// Define the indexes that may need to get added
		$new_indexes = Array();
		$new_indexes['attachment_parent_id_index'] =
			"CREATE INDEX attachment_parent_id_index ON `#__attachments` (`parent_id`)";

		// NOTE: Adding indeces to `filename`, `file_size`, `create_data`, and
		// modification_date` probably does not add much benefit.
		// SEE: http://dev.mysql.com/doc/refman/5.0/en/order-by-optimization.html

		// Add any missing indexes
		echo "<br />";
		$num_indexes_added = 0;
		foreach ($new_indexes as $index => $query) {
			if ( !in_array( $index , $indexes ) ) {
				echo $indent . JText::sprintf('ADDING_INDEX_S', $index) . "<br />";
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = JText::sprintf('ERROR_INSTALLING_INDEX_S', $index) .
						' ' . $db->stderr() . ' (ERR 7)';
					JError::raiseError(500, $errmsg);
					return false;
					}
				$num_indexes_added++;
				}
			}
		echo "<h3>" . JText::_('THE_ATTACHMENTS_TABLE_IS_NOW_UP_TO_DATE') . "</h3>\n";

		return true;
	}

	/**
	 * Disable uninstallation of attachments when the Attachments component is
	 * uninstalled.
	 *
	 * This is accomplished by modifying the uninstall.mysql.sql table to
	 * comment out the line that deletes the attachments table.  Note that
	 * this only affects the table, not the attachments files.
	 */
	function disable_sql_uninstall($dbtype = 'mysql')
	{
		jimport('joomla.filesystem.file');

		// Construct the filenames
		$filename = JPATH_COMPONENT_ADMINISTRATOR.DS."uninstall.$dbtype.sql";
		$tempfilename = $filename.'.tmp';
		$msg = '';

		// Read the content of the old version of the uninstall sql file
		$contents = JFile::read($filename);
		$lines = explode("\n", $contents);
		$new_lines = Array();
		for ($i=0; $i < count($lines); $i++) {
			$line = JString::trim($lines[$i]);
			if ( JString::strlen($line) != 0 ) {
				if ( $line[0] != '#' ) {
					$line = '# ' . $line;
					}
				$new_lines[] = $line;
				}
			}

		// Overwrite the old file with a commented out version
		$new_contents = implode("\n", $new_lines);
		JFile::write($tempfilename, $new_contents);
		if ( ! JFile::copy( $tempfilename, $filename) ) {
			$msg = JText::_('ERROR_UPDATING_FILE') . ": $filename!";
			}

		// Let the user know what happened
		if ( $msg == '' ) {
			$msg = JText::_('DISABLED_UNINSTALLING_MYSQL_ATTACHMENTS_TABLE');
			}

		return $msg;
	}

	/**
	 * Regenerate the system filenames for all attachments.
	 *
	 * This function may need to run if the admin has moved the attachments
	 * from one computer to another and the actual file paths need to be
	 * updated.
	 */
	function regenerate_system_filenames()
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Define where the attachments go
		$upload_url = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE . DS . $upload_url;

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__attachments WHERE uri_type='file'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_ATTACHMENTS_WITH_FILES');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Update the system filenames for all the attachments
		$attachment =& JTable::getInstance('Attachments', 'Table');
		$numUpdated = 0;
		foreach ($IDs as $id) {

			$attachment->load($id);

			// Construct the updated system filename
			$filename_info = pathinfo($attachment->filename_sys);
			$basename = $filename_info['basename'];
			$filename_sys = $upload_dir.DS.$basename;

			$attachment->filename_sys = $filename_sys;

			// Update the record
			if (!$attachment->store()) {
				$errmsg = $attachment->getError() . ' (ERR 8)';
				JError::raiseError(500, $errmsg);
				}

			$numUpdated++;
			}

		return JText::sprintf( 'REGENERATED_SYSTEM_FILENAMES_FOR_N_ATTACHMENTS',
							   $numUpdated );
	}

	/**
	 * Update the system filenames for all attachments to attachments-2.0 format
	 *
	 * When upgrading from Attachments 1.3.4 to 2.0+, you should run this
	 * function to update the filenames appropriately.
	 */
	function update_system_filenames()
	{
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Define where the attachments go
		$upload_url = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE . DS . $upload_url;

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__attachments WHERE uri_type='file'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_ATTACHMENTS_WITH_FILES');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Get the parent plugin manager
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();

		// Update the system filenames for all the attachments
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$attachment =& JTable::getInstance('Attachments', 'Table');
		$numUpdated = 0;
		foreach ($IDs as $id) {

			$attachment->load($id);

			// Construct the updated system filename
			$old_filename_sys = $attachment->filename_sys;
			$filename_info = pathinfo($old_filename_sys);
			$basename = $filename_info['basename'];

			// Check for old-style sytem filenames
			if ( preg_match('|^[0-9]{3}_(?<fname>.+\..+$)|', $basename, $match) ) {
				if ( realpath(rtrim($upload_dir,DS)) != realpath(rtrim($filename_info['dirname'],DS)) ) {
					// Skip any files that are NOT at the top level, since they cannot be old-style files
					continue;
					}
				$basename = $match[1];
				}
			else {
				// Do not update anything but pre attachments-2.0 system filenames
				continue;
				}

			// Construct the new system filename and url (based on entities, etc)
			$parent = $apm->getAttachmentsPlugin($attachment->parent_type);
			$newdir = $parent->getAttachmentPath($attachment->parent_entity,
												 $attachment->parent_id, null);
			$newpath = $upload_dir.DS.$newdir;
			$new_filename_sys = $newpath.$basename;
			$new_url = JString::str_ireplace(DS, '/', $upload_url . '/' . $newdir . $basename);

			// Make sure the target directory exists
			if ( !JFile::exists($newpath) ) {
				if ( !JFolder::create($newpath) ) {
					$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $newpath) . ' (ERR 97)';
					JError::raiseError(500, $errmsg);
					}
				AttachmentsHelper::write_empty_index_html($newpath);
				}

			// Move the file!
			if ( !JFile::move($old_filename_sys, $new_filename_sys) ) {
				$errmsg = JText::sprintf('ERROR_RENAMING_FILE_S_TO_S',
										 $old_filename_sys, $new_filename_sys) . ' (ERR 98)';
				JError::raiseError(500, $errmsg);
				}

			// Update the record
			$attachment->filename_sys = $new_filename_sys;
			$attachment->url = $new_url;
			if (!$attachment->store()) {
				$errmsg = $attachment->getError() . ' (ERR 99)';
				JError::raiseError(500, $errmsg);
				}

			$numUpdated++;
			}

		return JText::sprintf( 'UPDATED_SYSTEM_FILENAMES_FOR_N_ATTACHMENTS',
							   $numUpdated );
	}

	/**
	 * Remove spaces from the system filenames for all attachments
	 *
	 * The spaces are replaces with underscores '_'
	 */
	function remove_spaces_from_system_filenames()
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Define where the attachments go
		$upload_url = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE . DS . $upload_url;

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__attachments WHERE uri_type='file'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_ATTACHMENTS_WITH_FILES');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Get ready to rename files
		jimport( 'joomla.filesystem.file' );

		// Update the system filenames for all the attachments
		$attachment =& JTable::getInstance('Attachments', 'Table');
		$numUpdated = 0;

		foreach ($IDs as $id) {

			$attachment->load($id);

			// Make sure the file exists
			$old_filename_sys = $attachment->filename_sys;
			if ( !JFile::exists( $old_filename_sys ) ) {
				echo JText::sprintf('ERROR_FILE_S_NOT_FOUND_ON_SERVER', $old_filename_sys);
				exit();
				}

			// Construct the new system filename
			$filename_info = pathinfo($old_filename_sys);
			$basename = $filename_info['basename'];
			$filename_sys = $upload_dir.DS.$basename;
			$new_basename = JString::str_ireplace(' ', '_', $basename);
			$new_filename_sys = $filename_info['dirname'].DS.$new_basename;

			// If the filename has not changed, do not change anything
			if ( $new_filename_sys == $old_filename_sys ) {
				continue;
				}

			// Rename the file
			if ( !JFile::move($old_filename_sys, $new_filename_sys) ) {
				echo JText::sprintf('ERROR_RENAMING_FILE_S_TO_S',
									$old_filename_sys, $new_filename_sys);
				exit();
				}

			// Construct the new URL (figuire it out from the system filename)
			$attachments_dir = JString::str_ireplace(JPATH_SITE, '', $filename_info['dirname']);
			$attachments_dir = JString::trim($attachments_dir, DS);
			$attachments_dir = JString::str_ireplace(DS, '/', $attachments_dir);
			$new_url = $attachments_dir . '/' . $new_basename;

			// Update the record
			$attachment->filename_sys = $new_filename_sys;
			$attachment->filename = $new_basename;
			$attachment->url = $new_url;

			if (!$attachment->store()) {
				$errmsg = $attachment->getError() . ' (ERR 9)';
				JError::raiseError(500, $errmsg);
				}

			$numUpdated++;
			}

		return JText::sprintf( 'UPDATED_N_ATTACHMENTS', $numUpdated );
	}


	/**
	 * Update the file sizes for all attachments (only applies to files)
	 */
	function update_file_sizes()
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Define where the attachments go
		$upload_url = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE . DS . $upload_url;

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__attachments WHERE uri_type='file'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_ATTACHMENTS_WITH_FILES');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Update the system filenames for all the attachments
		$attachment =& JTable::getInstance('Attachments', 'Table');
		$numUpdated = 0;
		foreach ($IDs as $id) {

			$attachment->load($id);

			// Update the file size
			$attachment->file_size = filesize($attachment->filename_sys);

			// Update the record
			if (!$attachment->store()) {
				$errmsg = $attachment->getError() . ' (ERR 10)';
				JError::raiseError(500, $errmsg);
				}

			$numUpdated++;
			}

		return JText::sprintf( 'UPDATED_FILE_SIZES_FOR_N_ATTACHMENTS', $numUpdated );
	}

	/**
	 * Check all files and make sure they exist
	 */
	function check_files_existance()
	{
		jimport('joomla.filesystem.file');

		$msg = '';

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__attachments WHERE uri_type='file'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_ATTACHMENTS_WITH_FILES');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Update the system filenames for all the attachments
		$attachment =& JTable::getInstance('Attachments', 'Table');
		$numMissing = 0;
		$numChecked = 0;
		foreach ($IDs as $id) {

			$attachment->load($id);

			if ( !JFile::exists($attachment->filename_sys) ) {
				$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
					$attachment->filename_sys . '<br >';
				$numMissing++;
				}

			$numChecked++;
			}

		if ( $msg ) {
			$msg = ':<br />' . $msg;
			}
		$msg = JText::sprintf( 'CHECKED_N_ATTACHMENT_FILES_M_MISSING', $numChecked, $numMissing ) . $msg;

		return $msg;
	}



	/**
	 * Validate all URLS and update their "valid" status
	 */
	function validate_urls()
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get all the attachment IDs
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__attachments WHERE uri_type='url'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return JText::_('NO_ATTACHMENTS_WITH_URLS');
			}
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row->id;
			}

		// Update the system filenames for all the attachments
		$attachment =& JTable::getInstance('Attachments', 'Table');
		$numUpdated = 0;
		$numChecked = 0;
		foreach ($IDs as $id) {

			require_once(JPATH_BASE.DS.'..'.DS.'components'.DS.'com_attachments'.DS.'helper.php');

			$attachment->load($id);

			$a = new JObject();

			AttachmentsHelper::get_url_info($attachment->url, $a, false, false);

			if ( $attachment->url_valid != $a->url_valid ) {
				$attachment->url_valid = $a->url_valid;

				// Maybe update the file info with fresh info
				if ( $a->url_valid ) {
					$attachment->file_size = $a->file_size;
					$attachment->file_type = $a->file_type;
					}

				// Update the record
				if (!$attachment->store()) {
					$errmsg = $attachment->getError() . ' (ERR 11)';
					JError::raiseError(500, $errmsg);
					}
				$numUpdated++;
				}
			$numChecked++;
			}

		return JText::sprintf( 'VALIDATED_N_URL_ATTACHMENTS_M_CHANGED', $numChecked, $numUpdated );
	}


}

// Local Variables:
// tab-width: 4
// End:

?>
