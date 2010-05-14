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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaUserStatHelper
{
	function createUserStatEntry($downloadId) {
		$db 		= &JFactory::getDBO();
		$user		= &JFactory::getUser();
		

		$query =  ' SELECT * FROM '.$db->nameQuote('#__phocadownload_user_stat')
				 .' WHERE '. $db->nameQuote('userid')
				 .' = '
				 .$db->Quote((int)$user->id)
				 .' AND '. $db->nameQuote('fileid')
				 .' = '
				 .$db->Quote((int)$downloadId);
				 
		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		$date = gmdate('Y-m-d H:i:s');
		if ($results) {
			// Update count
			$query = 'UPDATE '.$db->nameQuote('#__phocadownload_user_stat')
					.' SET count = (count + 1),'
					.' date = '.$db->Quote($date)
					.' WHERE userid = '.$db->Quote((int)$user->id)
					.' AND fileid = '.$db->Quote((int)$downloadId);
					
			$db->setQuery($query);
			$db->query();
		} else {
			
			$query = 'INSERT INTO '.$db->nameQuote('#__phocadownload_user_stat')
					.' ('.$db->nameQuote('count').','
					.' '.$db->nameQuote('userid').','
					.' '.$db->nameQuote('fileid').','
					.' '.$db->nameQuote('date').')'
					.' VALUES ('.$db->Quote(1).','
					.' '.$db->Quote((int)$user->id).','
					.' '.$db->Quote((int)$downloadId).','
					.' '.$db->Quote($date).')';
			$db->setQuery($query);
			$db->query();
		}
		return true;
	}
}
?>