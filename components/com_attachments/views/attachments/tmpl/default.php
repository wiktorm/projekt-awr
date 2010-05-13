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

// No direct access
defined('_JEXEC') or die('Restricted access');

global $mainframe, $option;


// Set a few variables for convenience
$rows =& $this->list;
$params =& $this->params;
$parent_id = $this->parent_id;
$parent_type = $this->parent_type;

$base_url =& $this->base_url;

$from = $this->from;

// If any attachments are modifiable, add necessary Javascript for iframe
if ( $this->some_attachments_modifiable ) {
	JHTML::_('behavior.modal', 'a.modal-button');
	}

/** Get the attachments helper to add the stylesheet */
require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');
AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/content/attachments.css' );

// Handle RTL styling (if necessary)
$lang =& JFactory::getLanguage();
if ( $lang->isRTL() ) {
	AttachmentsHelper::addStyleSheet( JURI::root() . 'plugins/content/attachments_rtl.css' );
	}

// Construct the starting HTML
$html = "\n<div class=\"$this->style\">\n";
$html .= "<table>\n";
$html .= "<caption>$this->title</caption>\n";

// Add the column titles, if requested
if ( $this->show_column_titles ) {
	$html .= "<thead>\n<tr>";
	$html .= "<th class=\"at_filename\">" . JText::_('FILE') . "</th>";
	if ( $this->show_description ) {
		$html .= "<th class=\"at_description\">" . JText::_('DESCRIPTION') . "</th>";
		}
	if ( $params->get('user_field_1_name', '') != '' ) {
		$html .= "<th class=\"at_user_field\">" . $params->get('user_field_1_name', '') . "</th>";
		}
	if ( $params->get('user_field_2_name', '') != '' ) {
		$html .= "<th class=\"at_user_field\">" . $params->get('user_field_2_name', '') . "</th>";
		}
	if ( $params->get('user_field_3_name', '') != '' ) {
		$html .= "<th class=\"at_user_field\">" . $params->get('user_field_3_name', '') . "</th>";
		}
	if ( $this->show_uploader ) {
		$html .= "<th class=\"at_uploader\">" . JText::_('UPLOADER') . "</th>";
		}
	if ( $this->show_file_size ) {
		$html .= "<th class=\"at_file_size\">" . JText::_('FILE_SIZE') . "</th>";
		}
	if ( $this->secure AND $this->show_downloads ) {
		$html .= "<th class=\"at_downloads\">" . JText::_('DOWNLOADS') . "</th>";
		}
	if ( $this->show_mod_date ) {
		$html .= "<th class=\"at_mod_date\">" . JText::_('LAST_MODIFIED') . "</th>";
		}
	if ( $this->some_attachments_modifiable AND $this->allow_edit ) {
		$html .= "<th class=\"at_edit\">&nbsp;</th>";
		}
	$html .= "</tr>\n</thead>\n";
	}

$html .= "<tbody>\n";

// Construct the lines for the attachments
$row_num = 0;
for ($i=0, $n=count($rows); $i < $n; $i++) {
	$row =& $rows[$i];

	// Skip this one if it should not be visible
	if ( !$row->user_may_see )
		continue;

	$row_num++;
	if ( $row_num & 1 == 1)
		$html .= '<tr class="odd">';
	else
		$html .= '<tr class="even">';

	// Construct some display items
	if ( JString::strlen($row->icon_filename) > 0 )
		$icon_url = $this->icon_url_base . $row->icon_filename;
	else
		$icon_url = $this->icon_url_base . 'generic.gif';
	$link_icon_url = $this->icon_url_base . 'link_arrow.png';
	$link_broken_icon_url = $this->icon_url_base . 'link_broken.png';

	if ( $this->show_file_size) {
		$file_size = intval( $row->file_size / 1024.0 );
		if ( $file_size == 0 ) {
			// For files less than 1K, show the fractional amount (in 1/10 KB)
			$file_size = ( intval( 10.0 * $row->file_size / 1024.0 ) / 10.0 );
			}
		}

	if ( $this->show_mod_date ) {
		jimport( 'joomla.utilities.date' );
		$date = new JDate($row->modification_date, -$mainframe->getCfg('offset'));
		$last_modified = $date->toFormat($this->mod_date_format);
		}

	// Add the filename
	$target = '';
	if ( $this->file_link_open_mode == 'new_window')
		$target = ' target="_blank"';
	$html .= '<td class="at_filename">';
	if ( JString::strlen($row->display_name) == 0 )
		$filename = $row->filename;
	else
		$filename = $row->display_name;
	if ( $this->show_file_links ) {
		if ( $row->uri_type == 'file' ) {
			if ( $this->secure ) {
				$url = JRoute::_("index.php?option=com_attachments&task=download&id=" . $row->id);
				$url = JRoute::_($url);
				}
			else {
				$url = $base_url . $row->url;
				}
			$tooltip = JText::sprintf('DOWNLOAD_THIS_FILE_S', $row->filename);
			}
		else {
			$user =& JFactory::getUser();
			if ( $this->secure AND ($this->who_can_see != 'anyone') AND ($user->get('username') == '') ) {
				$url = JRoute::_('index.php?option=com_attachments&task=request_login');
				}
			else {
				$url = $row->url;
				}
			$tooltip = JText::sprintf('ACCESS_THIS_URL_S', $row->url);
			}
		$html .= "<a class=\"at_icon\" href=\"$url\"$target title=\"$tooltip\"><img src=\"$icon_url\" alt=\"$tooltip\" />";
		if ( $row->uri_type == 'url' AND $this->superimpose_link_icons ) {
			if ( $row->url_valid ) {
				$html .= "<img id=\"link\" src=\"$link_icon_url\">";
				}
			else {
				$html .= "<img id=\"link\" src=\"$link_broken_icon_url\">";
				}
			}
		$html .= "</a>";
		$html .= "<a class=\"at_url\" href=\"$url\"$target title=\"$tooltip\">$filename</a>";
		}
	else {
		$tooltip = JText::sprintf('DOWNLOAD_THIS_FILE_S', $row->filename);
		$html .= "<img src=\"$icon_url\" alt=\"$tooltip\" />&nbsp;";
		$html .= $filename;
		}
	$html .= "</td>";

	// Add description (maybe)
	if ( $this->show_description ) {
		$description = $row->description;
		if ( JString::strlen($description) == 0)
			$description = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_description\">$description</td>";
		else
			$html .= "<td class=\"at_description\">[$description]</td>";
		}

	// Show the USER DEFINED FIELDs (maybe)
	if ( $params->get('user_field_1_name', '') != '' ) {
		$user_field = $row->user_field_1;
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}
	if ( $params->get('user_field_2_name', '') != '' ) {
		$user_field = $row->user_field_2;
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}
	if ( $params->get('user_field_3_name', '') != '' ) {
		$user_field = $row->user_field_3;
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}

	// Add the uploader's username (if requested)
	if ( $this->show_uploader ) {
		$html .= "<td class=\"at_uploader\">{$row->uploader_name}</td>";
		}

	// Add file size (maybe)
	if ( $this->show_file_size ) {
		$html .= "<td class=\"at_file_size\">$file_size Kb</td>";
		}

	// Show number of downloads (maybe)
	if ( $this->secure AND $this->show_downloads ) {
		$num_downloads = intval($row->download_count);
		$label = '';
		if ( ! $this->show_column_titles ) {
			if ( $num_downloads == 1 )
				$label = '&nbsp;' . JText::_('DOWNLOAD_NOUN');
			else
				$label = '&nbsp;' . JText::_('DOWNLOADS');
			}
		$html .= '<td class="at_downloads">'. $num_downloads.$label.'</td>';
		}

	// Add the modification date (maybe)
	if ( $this->show_mod_date ) {
		$html .= "<td class=\"at_mod_date\">$last_modified</td>";
		}

	// Add the link to delete the parent, if requested
	if ( $this->some_attachments_modifiable AND $row->user_may_edit AND $this->allow_edit ) {

		// Create the edit link
		$url = "index.php?option=com_attachments&task=update&id={$row->id}";
		$url .= "&from=closeme&tmpl=component";
		$url = JRoute::_($url);
		$update_img = $base_url . 'components/com_attachments/media/pencil.gif';
		$tooltip = JText::_('UPDATE_THIS_FILE') . ' (' . $row->filename . ')';
		$update_link = '<a class="modal-button" type="button" href="' . $url . '"';
		$update_link .= " rel=\"{handler: 'iframe', size: {x: 900, y: 530}}\"";
		$update_link .= " title=\"$tooltip\"><img src=\"$update_img\" alt=\"$tooltip\" /></a>";

		// Create the delete link
		if ( $option == 'com_content' ) {
			$url = "index.php?option=com_attachments&task=delete_warning&id={$row->id}&article_id=$parent_id";
			}
		else {
			$url = "index.php?option=com_attachments&task=delete_warning&id={$row->id}&parent_id=$parent_id&parent_type=$option";
			}
		if ( $from ) {
			// Add a var to give a hint of where to return to
			$url .= "&from=$from";
			}
		else {
			$url .= "&from=closeme";
			}
		$url .= "&tmpl=component";
		$url = JRoute::_($url);
		$delete_img = $base_url . 'components/com_attachments/media/delete.gif';
		$tooltip = JText::_('DELETE_THIS_FILE') . ' (' . $row->filename . ')';
		$del_link = '<a class="modal-button" type="button" href="' . $url . '"';
		$del_link .= " rel=\"{handler: 'iframe', size: {x: 600, y: 300}}\"";
		$del_link .= " title=\"$tooltip\"><img src=\"$delete_img\" alt=\"$tooltip\" /></a>";
		$html .= "<td class=\"at_edit\">$update_link $del_link</td>";
		}

	$html .= "</tr>\n";
	}

// Close the HTML
$html .= "</tbody></table></div>\n";

echo $html;

// Local Variables:
// tab-width: 4
// End:

?>
