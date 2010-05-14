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

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class phocaDownloadCpViewPhocaDownload extends JView
{

	function display($tpl = null) {
		global $mainframe;
		
		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}
		parent::display($tpl);
	}

	function _displayForm($tpl) {
		global $mainframe, $option;
		
		//$post	= JRequest::get('post');
		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();
		$editor =& JFactory::getEditor();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );
		//Data from model
		$phocadownload	=& $this->get('Data');
		
		JHTML::_('behavior.calendar');
		JHTML::_('behavior.modal', 'a.modal-button');
		//File button
		$linkFile = 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=file&amp;tmpl=component';
		$buttonFile = new JObject();
		$buttonFile->set('modal', true);
		$buttonFile->set('link', $linkFile);
		$buttonFile->set('text', JText::_( 'File' ));
		$buttonFile->set('name', 'image');
		$buttonFile->set('modalname', 'modal-button');
		$buttonFile->set('options', "{handler: 'iframe', size: {x: 620, y: 400}}");
		
		//Icon button
		$linkIcon = 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=icon&amp;tmpl=component';
		$buttonIcon = new JObject();
		$buttonIcon->set('modal', true);
		$buttonIcon->set('link', $linkIcon);
		$buttonIcon->set('text', JText::_( 'Icon' ));
		$buttonIcon->set('name', 'image');
		$buttonIcon->set('modalname', 'modal-button');
		$buttonIcon->set('options', "{handler: 'iframe', size: {x: 620, y: 400}}");
		
		
		$lists = array();		
		$isNew		= ($phocadownload->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'Phoca Download' ), $phocadownload->title );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}


		// Edit or Create?
		if (!$isNew) {
			$model->checkout( $user->get('id') );
		} else {
			// initialise new record
			$phocadownload->published 	= 1;
			$phocadownload->approved 	= 1;
			$phocadownload->order 		= 0;
			$phocadownload->access			= 0;
			$phocadownload->catid 		= JRequest::getVar( 'catid', 0, 'post', 'int' );
		}

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__phocadownload'
			. ' WHERE catid = ' . (int) $phocadownload->catid
			. ' ORDER BY ordering';
		$lists['ordering'] 	= JHTML::_('list.specificordering',  $phocadownload, $phocadownload->id, $query, false );

		// build the html select list
		$lists['published'] = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $phocadownload->published );
		$lists['approved'] 	= JHTML::_('select.booleanlist',  'approved', 'class="inputbox"', $phocadownload->approved );
		
		// SECTION AND CATEGORY SELECT BOX	
		
		$sectionid	= JRequest::getVar( 'filter_sectionid', 0, '', 'int' );
		$catid		= JRequest::getVar( 'catid', 0, '', 'int' );
		
		if ((int)$phocadownload->sectionid < 1 && (int)$sectionid > 0) {
			$phocadownload->sectionid = $sectionid;
		} else if ((int)$phocadownload->sectionid < 1 && (int)$catid > 0) {
			$query = 'SELECT cc.section' .
				' FROM #__phocadownload_categories AS cc' .
				' WHERE cc.id = '.(int)$catid .
				' ORDER BY cc.ordering';
			$db->setQuery($query);
			$sectionCat = $db->loadObject();
			$phocadownload->sectionid = $sectionCat->section;
		
		}
		
		$javascript = "onchange=\"changeDynaList( 'catid', sectioncategories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";

		$query = 'SELECT s.id, s.title' .
				' FROM #__phocadownload_sections AS s' .
				' ORDER BY s.ordering';
		$db->setQuery($query);

		$sections[] = JHTML::_('select.option', '-1', '- '.JText::_('Select Section').' -', 'id', 'title');
		//$sections[] = JHTML::_('select.option', '0', JText::_('Uncategorized'), 'id', 'title');
		$sections = array_merge($sections, $db->loadObjectList());
		
		$lists['sectionid'] = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($phocadownload->sectionid));

		foreach ($sections as $section) {
			$section_list[] = (int) $section->id;
			/*// get the type name - which is a special category
			if ($phocadownload->sectionid) {
				if ($section->id == $phocadownload->sectionid) {
					$contentSection = $section->title;
				}
			} else {
				if ($section->id == $sectionid) {
					$contentSection = $section->title;
				}
			}*/
		}

		$sectioncategories 			= array ();
		$sectioncategories[-1] 		= array ();
		$sectioncategories[-1][] 	= JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
		$section_list = implode('\', \'', $section_list);

		$query = 'SELECT id, title, section' .
				' FROM #__phocadownload_categories' .
				' WHERE section IN ( \''.$section_list.'\' )' .
				' ORDER BY ordering';
		$db->setQuery($query);
		$cat_list = $db->loadObjectList();

		/*
		// Uncategorized category mapped to uncategorized section
		$uncat = new stdClass();
		$uncat->id = 0;
		$uncat->title = JText::_('Uncategorized');
		$uncat->section = 0;
		$cat_list[] = $uncat;*/
		foreach ($sections as $section) {
			$sectioncategories[$section->id] = array ();
			$rows2 = array ();
			foreach ($cat_list as $cat) {
				if ($cat->section == $section->id) {
					$rows2[] = $cat;
				}
			}
			foreach ($rows2 as $row2) {
				$sectioncategories[$section->id][] = JHTML::_('select.option', $row2->id, $row2->title, 'id', 'title');
			}
		}
		$sectioncategories['-1'][] = JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
		$categories = array();
		foreach ($cat_list as $cat) {
			if($cat->section == $phocadownload->sectionid)
				$categories[] = $cat;
		}

		$categories[] 	= JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
		$lists['catid'] = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', intval($phocadownload->catid));
	
		
		$lists['access'] 			= JHTML::_('list.accesslevel',  $phocadownload );
	
		// - - - - - - - - - - - - - - -
		// Build the list of licenses
		$query = 'SELECT a.title AS title, a.id AS id'
		. ' FROM #__phocadownload_licenses AS a'
	//	. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$licenses = $db->loadObjectList();

		array_unshift($licenses, JHTML::_('select.option', '-1', '- '.JText::_('Select License').' -', 'id', 'title'));
		
		$lists['confirm_license'] 	= JHTML::_('select.genericlist',  $licenses, 'confirm_license', 'class="inputbox" size="1"', 'id', 'title', intval($phocadownload->confirm_license));
		// - - - - - - - - - - - - - - -
	
		//clean component data
		jimport('joomla.filter.output');
		JFilterOutput::objectHTMLSafe( $phocadownload, ENT_QUOTES, 'description' );
		
		// Publish UP and Publish DOWN
		$publish_up 	= $phocadownload->publish_up;
		$publish_down 	= $phocadownload->publish_down;
		$publish_up		= JHTML::_('date', $phocadownload->publish_up, '%Y-%m-%d %H:%M:%S');
		if (JHTML::_('date', $phocadownload->publish_down, '%Y') <= 1969 || $phocadownload->publish_down == $db->getNullDate()) {
			$publish_down = JText::_('Never');
		} else {
			$publish_down = JHTML::_('date', $phocadownload->publish_down, '%Y-%m-%d %H:%M:%S');
		}
		$phocadownload->publish_up 		= $publish_up;
		$phocadownload->publish_down 	= $publish_down;
		$phocadownload->date			= JHTML::_('date', $phocadownload->date, '%Y-%m-%d %H:%M:%S');
		
		
		$this->assignRef('sectioncategories', $sectioncategories);
		$this->assignRef('editor', $editor);
		$this->assignRef('lists', $lists);
		$this->assignRef('phocadownload', $phocadownload);
		$this->assignRef('buttonfile', $buttonFile);
		$this->assignRef('buttonicon', $buttonIcon);
		$this->assignRef('request_url',	$uri->toString());

		
		$text		= JRequest::getVar( 'text', 0, '', 'int' );
		// Display the form only for Text
		if ($text == 1) {
			parent::display('text');
		} else {	
			parent::display($tpl);
		}
		$this->_setToolbar($isNew);
	}
	
	function _setToolbar($isNew) {
		
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Phoca Download Files' ).': <small><small>[ ' . $text.' ]</small></small>', 'file' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		JToolBarHelper::help( 'screen.phocadownload', true );
	}
}
?>
