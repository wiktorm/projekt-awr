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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include library dependencies
jimport('joomla.filter.input');

class TablePhocaDownloadcat extends JTable
{

	var $id 				= null;
	var $parent_id 			= null;
	var $title 				= null;
	var $name 				= null;
	var $alias 				= null;
	var $image	 			= null;
	var $section 			= null;
	var $image_position		= null;
	var $description		= null;
	var $published			= null;
	var $checked_out 		= 0;
	var $checked_out_time 	= 0;
	var $editor				= null;
	var $ordering 			= null;
	var $access				= null;
	var $uploaduserid		= null;
	var $accessuserid		= null;
	var $deleteuserid		= null;
	var $date				= 0;
	var $count 				= null;
	var $params 			= null;
	var $metakey			= null;
	var $metadesc			= null;

	
	function __construct(& $db) {
		parent::__construct('#__phocadownload_categories', 'id', $db);
	}
	
	function check()
	{
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->setError( JText::_( 'CATEGORY MUST HAVE A TITLE') );
			return false;
		}

		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		
		if (function_exists('iconv')) {
		    $this->alias = preg_replace('~[^\\pL0-9_.]+~u', '-', $this->alias);
		    $this->alias = trim($this->alias, "-");
		    $this->alias = iconv("utf-8", "us-ascii//TRANSLIT", $this->alias);
		    $this->alias = strtolower($this->alias);
		    $this->alias = preg_replace('~[^-a-z0-9_.]+~', '', $this->alias);
		} else {
			$this->alias = JFilterOutput::stringURLSafe($this->alias);
			if(trim(str_replace('-','',$this->alias)) == '') {
				$datenow =& JFactory::getDate();
				$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
			}
		}

		return true;
	}
}
?>