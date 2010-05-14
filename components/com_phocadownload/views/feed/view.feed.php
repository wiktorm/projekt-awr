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

jimport( 'joomla.application.component.view');

class PhocaDownloadViewFeed extends JView
{

	function display($tpl = null)
	{	
		global $mainframe;
	
		$user 		= &JFactory::getUser();
		$db 		= &JFactory::getDBO();
		$menu 		= &JSite::getMenu();
		$document	= &JFactory::getDocument();
		$aid 		= $user->get('aid', 0);	
		$params 	= &$mainframe->getParams();
		
		$tmpl['download_metakey'] 		= $params->get( 'download_metakey', '' );
		$tmpl['download_metadesc'] 		= $params->get( 'download_metadesc', '' );
		
		// Meta data
		if ($tmpl['download_metakey'] != '') {
			$mainframe->addMetaTag('keywords', $tmpl['download_metakey']);
		}
		if ($tmpl['download_metadesc'] != '') {
			$mainframe->addMetaTag('description', $tmpl['download_metadesc']);
		}
		
		$moduleId	= JRequest::getVar('id', 0, '', 'int');
		$table 		= &JTable::getInstance('module');

		if($table->load((int)$moduleId)) {
			$paramsM = new JParameter($table->params);
			
			// Params
			$categories 		= $paramsM->get( 'category_ids', '' );
			$ordering			= $paramsM->get( 'file_ordering', 6 );
			$fileCount			= $paramsM->get( 'file_count', 5 );
			$feedTitle			= $paramsM->get( 'feed_title', JText::_('Download') );
			$displayDateType	= $paramsM->get( 'display_date_type', 1 );

			$document->setTitle($this->escape( html_entity_decode($feedTitle)));
			
			if (count($categories) > 1) {
				JArrayHelper::toInteger($categories);
				$categoriesString	= implode(',', $categories);
				$wheres[]	= ' c.catid IN ( '.$categoriesString.' ) ';
			} else if ((int)$categories > 0) {
				$wheres[]	= ' c.catid IN ( '.$categories.' ) ';
			}

			$wheres[]	= ' c.catid= cc.id';
			if ($aid !== null) {

				// IF unaccessible file = 1 then display unaccessible file for all
				// IF unaccessible file = 0 then display it only for them who have access to this file
				$wheres[] = '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND c.access <= ' . (int) $aid.') )';
				$wheres[] = '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND cc.access <= ' . (int) $aid.') )';
				//$wheres[] = 'c.access <= ' . (int) $aid;
				//$wheres[] = 'cc.access <= ' . (int) $aid;
			}
			$wheres[] = ' c.published = 1';
			$wheres[] = ' c.approved = 1';
			$wheres[] = ' cc.published = 1';
			$wheres[] = ' c.textonly = 0';
			// Active
			$jnow		=& JFactory::getDate();
			$now		= $jnow->toMySQL();
			$nullDate	= $db->getNullDate();
			$wheres[] = ' ( c.publish_up = '.$db->Quote($nullDate).' OR c.publish_up <= '.$db->Quote($now).' )';
			$wheres[] = ' ( c.publish_down = '.$db->Quote($nullDate).' OR c.publish_down >= '.$db->Quote($now).' )';
			$fileOrdering	= PhocaDownloadHelperFront::getOrderingText($ordering);

			$query =  ' SELECT c.*, cc.id AS categoryid, cc.title AS categorytitle, cc.alias AS categoryalias, s.id AS sectionid, s.title AS sectiontitle, s.alias AS sectionalias, cc.access as cataccess, cc.accessuserid as cataccessuserid '
					. ' FROM #__phocadownload AS c'
					. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = c.catid'
					. ' LEFT JOIN #__phocadownload_sections AS s ON s.id = cc.section'
					. ' WHERE ' . implode( ' AND ', $wheres )
					. ' ORDER BY c.'.$fileOrdering;
					


			$db->setQuery( $query , 0, $fileCount );	
			$files = $db->loadObjectList( );

			foreach ($files as $keyDoc => $valueDoc) {
				
				// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
				// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
				$rightDisplay	= 0;
				if (!empty($valueDoc)) {
					$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $valueDoc->cataccessuserid, $valueDoc->cataccess, $user->get('aid', 0), $user->get('id', 0), 0);
				}
				// - - - - - - - - - - - - - - - - - - - - - -
				if ($rightDisplay == 1) {

				
					$item = new JFeedItem();
					
					$title 				= $this->escape( $valueDoc->title . ' ('.PhocaDownloadHelper::getTitleFromFilenameWithExt( $valueDoc->filename ).')' );
					$title 				= html_entity_decode( $title );
					$item->title 		= $title;

					$link 				= PhocaDownloadHelperRoute::getCategoryRoute($valueDoc->categoryid, $valueDoc->categoryalias);
					$item->link 		= JRoute::_($link);
					
					
					// FILEDATE
					$fileDate = '';
					if ((int)$displayDateType > 0) {
						if ($valueDoc->filename !='') {
							$fileDate = PhocaDownloadHelper::getFileTime($valueDoc->filename, $displayDateType, "%Y-%m-%d %H:%M:%S");
						}
					} else {
						$fileDate = JHTML::Date($valueDoc->date, "%Y-%m-%d %H:%M:%S");
					}
					
					if ($fileDate != '') {
						$item->date			= $fileDate;
					}
					$item->description 	= $valueDoc->description;
					$item->category   	= $valueDoc->categorytitle;
				//	$item->section   	= $valueDoc->sectiontitle;
					if ($valueDoc->author != '') {
						$item->author		= $valueDoc->author;
					}
					$document->addItem( $item );
				}
			}
		}
	
		
	}
}
?>