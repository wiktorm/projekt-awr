<?php
/**
 * Manager for plugins for Attachments
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_Framework
 *
 * @copyright Copyright (C) 2009-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * The class for the manager for attachments plugins
 *
 * AttachmentsPluginManager manages plugins for Attachments.
 * It knows how to create handlers for plugins for all
 * supported extensions.
 *
 * @package Attachments
 */
class AttachmentsPluginManager extends JObject
{
	/** A list of known parent_type names
	 */
	var $_parent_types = Array();

	/** An array of info about the installed entities.
	 *	Each item in the array is an associative array with the following entries:
	 *	  'id' - the unique name of entity as stored in the jos_attachments table (all upper case)
	 *	  'id_canonical' - the canonical ID (lower case, may be 'default')
	 *	  'name' - the translated name of the entity
	 *	  'name_plural' - the translated plural name of the entity
	 *	  'parent_type' - the parent type for the entity
	 */
	var $_entity_info = Array();

	/** An associative array of attachment plugins
	 */
	var $_plugin = Array();


	/** Flag indicating if the language file haas been loaded
	 */
	var $_language_loaded = false;


	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->_findInstalledPlugins();
	}


	/**
	 * See if a particular plugin is installed (avaliable)
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return Boolean true if the plugin is available (false if not)
	 */
	function attachmentsPluginInstalled($parent_type)
	{
		return in_array( $parent_type, $this->_parent_types );
	}


	/**
	 * Check to see if an attachments plugin is enabled
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return true if the attachment is enabled (false if disabled)
	 */
	function attachmentsPluginEnabled($parent_type)
	{
		// Extract the component name (the part after 'com_')
		if ( strpos($parent_type, 'com_') == 0 ) {
			$name = substr($parent_type, 4);
			return JPluginHelper::isEnabled('attachments', "attachments_for_$name");
			}

		// If the parent type does not conform to the naming convention, assume it is not enabled
		return false;
	}


	/**
	 * Return the list of installed parent types
	 *
	 * @return an array of the installed parent types
	 */
	function getInstalledParentTypes()
	{
		return $this->_parent_types;
	}

	/**
	 * Return the list of installed parent entities
	 *
	 * @return array of entity info (see var $_entity_info definition above)
	 */
	function &getInstalledEntityInfo()
	{
		if ( count($this->_entity_info) == 0 ) {

			// Add an option for each entity
			JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
			$apm =& getAttachmentsPluginManager();
			$apm->loadLanguage();

			// process all the parent types
			foreach ($this->_parent_types as $parent_type) {
				$parent =& $apm->getAttachmentsPlugin($parent_type);
				$parent->loadLanguage();
				$entities = $parent->getEntities();

				// Process each entity for this parent type
				foreach ($entities as $entity) {
					$centity = $parent->getCanonicalEntity($entity);
					$cename = $parent->getEntityName($centity);
					$this->_entity_info[] = array('id' => $entity,
												  'id_canonical' => $centity,
												  'name' => JText::_($cename),
												  'name_plural' => JText::_($cename . 'S'),
												  'parent_type' => $parent_type
												  );
					}
				}
			}

		return $this->_entity_info;
		}


	/**
	 * Load the langauge for this parent type
	 *
	 * @return true of the language was loaded succesfullly
	 */
	function loadLanguage()
	{
		if ( $this->_language_loaded ) {
			return true;
			}

		$lang =& JFactory::getLanguage();

		$okay = $lang->load('plg_attachments_attachments_plugin_framework', JPATH_ADMINISTRATOR);

		if ( $okay ) {
			$this->_language_loaded = true;
			}

		return $okay;
	}


	/**
	 * Get the plugin (attachments parent handler object)
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return the parent handler object
	 */
	function &getAttachmentsPlugin($parent_type)
	{
		// Make sure the parent type is valid
		if ( !in_array( $parent_type, $this->_parent_types ) ) {
			$errmsg = JText::sprintf('ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 304)';
			JError::raiseError(500, $errmsg);
			}

		// Instantiate the plugin object, if we have not already done it
		if ( !array_key_exists( $parent_type, $this->_plugin ) ) {
			$this->_installPlugin($parent_type);
			}

		return $this->_plugin[$parent_type];
		}


	/**
	 * Install the specified plugin
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return true if successful (false if not)
	 */
	function _installPlugin($parent_type)
	{
		// Do nothing if the plugin is already installed
		if ( array_key_exists( $parent_type, $this->_plugin ) ) {
			return true;
			}

		// Install the plugin
		require_once( dirname(__FILE__).DS.'attachments_plugin.php' );
		require_once( dirname(__FILE__).DS.'plugins'.DS.$parent_type.'.php' );
		$className = 'AttachmentsPlugin_' . $parent_type;
		$this->_plugin[$parent_type] = new $className($parent_type);

		return is_object($this->_plugin[$parent_type]);
	}


	/**
	 * Explore and find all installed attachments plugins
	 */
	function _findInstalledPlugins()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// Scan through and find the parent_types for all installed plugins
		$files = JFolder::files(dirname(__FILE__).DS.'plugins', '[^\.]*\.ini$');
		foreach ($files as $filename) {
			$this->_parent_types[] = basename($filename, '.ini');
			}

		// Sort the list so they appear alphabetically
		sort($this->_parent_types);
	}


}

// Local Variables:
// tab-width: 4
// End:

?>
