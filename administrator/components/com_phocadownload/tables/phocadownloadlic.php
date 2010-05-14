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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filter.input');

class TablePhocaDownloadLic extends JTable
{

	var $id 				= null;
	var $title 				= null;
	var $alias 				= null;
	var $description 		= null;
	var $checked_out 		= 0;
	var $checked_out_time 	= 0;
	var $published 			= null;
	var $ordering 			= null;

	function __construct(& $db) {
		parent::__construct('#__phocadownload_licenses', 'id', $db);
	}
}
?>