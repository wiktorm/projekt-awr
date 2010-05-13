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
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view' );

/**
 * View for a list of attachments
 *
 * @package Attachments
 */
class AttachmentsViewAttachments extends JView
{

	/**
	 * Construct the output for the view/template.
	 *
	 * NOTE: This only constructs the output; it does not display it!
	 *		 Use getOutput() to actually display it.
	 *
	 * @param string $tpl template name (optional)
	 *
	 * @return if there are no attachments for this article,
	 *		   if everything is okay, return true
	 *		   if there is an error, return the error code
	 */
	function display($tpl = null)
	{
		global $mainframe;

		// Get the model
		$model =& $this->getModel('Attachments');
		if ( !$model ) {
			$errmsg = JText::_('ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 83)';
			JError::raiseError( 500, $errmsg);
			}

		// See if there are any attachments
		$list = $model->getAttachmentsList();
		if ( ! $list ) {
			return null;
			}

		$this->addTemplatePath(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.
							   'views'.DS.'attachments'.DS.'tmpl');

		// Load the language files from the backend
		$lang =&  JFactory::getLanguage();
		$lang->load('plg_frontend_attachments', JPATH_ADMINISTRATOR);

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =&	JComponentHelper::getParams('com_attachments');
		$this->assignRef('params', $params);

		// Set up for the template
		$parent_id = $model->getParentId();
		$parent_type = $model->getParentType();
		$parent_entity = $model->getParentEntity();
		$this->assign('parent_id', $parent_id);
		$this->assign('parent_type', $parent_type);
		$this->assign('parent_entity', $parent_entity);
		$this->assign('parent_title', $model->getParentTitle());
		$this->assign('parent_entity_name', $model->getParentEntityName());

		$this->assign('some_attachments_visible', $model->someVisible());
		$this->assign('some_attachments_modifiable', $model->someModifiable());
		$this->assign('superimpose_link_icons', $params->get('superimpose_url_link_icons', true));

		$this->assignRef( 'list', $list );

		// Get the display options
		$this->assign('style', $params->get('attachments_table_style', 'attachmentsList'));
		$this->assign('secure', $params->get('secure', false));
		$this->assign('who_can_see', $params->get('who_can_see', 'logged_in'));
		$this->assign('show_column_titles', $params->get('show_column_titles', false));
		$this->assign('show_description', $params->get('show_description', true));
		$this->assign('show_uploader',	$params->get('show_uploader', false));
		$this->assign('show_file_size', $params->get('show_file_size', true));
		$this->assign('show_downloads', $params->get('show_downloads', false));
		$this->assign('show_mod_date',	$params->get('show_modification_date', false));
		$this->assign('file_link_open_mode',
					  $params->get('file_link_open_mode', 'in_same_window'));
		if ( $this->show_mod_date ) {
			$this->assign('mod_date_format',
						  $params->get('mod_date_format', '%Y-%m-%d %I:%M%P'));
			}

		// Get the attachments list title
		$title = $this->title;
		if ( !$title OR (JString::strlen($title) == 0) ) {
			$title = 'ATTACHMENTS_TITLE';
			}
		$parent =& $model->getParentClass();
		$title = $parent->attachmentsListTitle($title, $params, $parent_id, $parent_entity);
		$this->assign('title', JText::_($title));

		// Construct the path for the icons
		if ( $mainframe->isAdmin() ) {
			$base_url = $mainframe->getSiteURL();
			}
		else {
			$base_url = JURI::Base();
			}
		$this->assign('base_url', $base_url);
		$this->assign('icon_url_base', $base_url . 'components/com_attachments/media/icons/');

		// Get the output of the template
		$result = $this->loadTemplate($tpl);
		if (JError::isError($result)) {
			return $result;
			}

		return true;
	}

	/**
	 * Get the output
	 *
	 * @return string the output
	 */
	function getOutput()
	{
		return $this->_output;
	}


}

// Local Variables:
// tab-width: 4
// End:

?>
