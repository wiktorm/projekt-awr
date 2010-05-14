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
jimport('joomla.application.component.view');
class phocaDownloadCpViewphocaDownloadLinkCat extends JView
{
	function display($tpl = null) {
		global $mainframe;
		$document	=& JFactory::getDocument();
		$uri		=& JFactory::getURI();
		JHTML::stylesheet( 'phocadownload.css', 'administrator/components/com_phocadownload/assets/' );
		
		$eName				= JRequest::getVar('e_name');
		$tmpl['ename']		= preg_replace( '#[^A-Z0-9\-\_\[\]]#i', '', $eName );
		$tmpl['backlink']	= 'index.php?option=com_phocadownload&amp;view=phocadownloadlinks&amp;tmpl=component&amp;e_name='.$tmpl['ename'];
		
		$model 			= &$this->getModel();
		
		// Sections - - - - - -
		$dataSection	= &$model->getDataSec();
		$javascript 	= "onchange=\"changeDynaList( 'catid', categories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";
		$sections[] 	= JHTML::_('select.option', '-1', '- '.JText::_('Select Section').' -', 'id', 'title');
		$sections 		= array_merge($sections, $dataSection);
		$lists['sectionid'] = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title');
		
		// Sections Data for Categories  - - - - - 
		foreach ($sections as $section) {
			$sectionList[] = (int) $section->id;
		}
		$sectionList = implode('\', \'', $sectionList);
		
		$categories 			= array ();
		$categories[-1] 		= array ();
		$categories[-1][] 	= JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
		
		$dataCategories	= &$model->getDataCat($sectionList);
		
		// Javascript Categories Render - - - - - - -
		if (!empty($sections)) {
			foreach ($sections as $section) {
				$categories[$section->id] = array();
				$rows2 = array();
				if(!empty($dataCategories)) {
					foreach ($dataCategories as $cat) {
						if ($cat->section == $section->id) {
							$rows2[] = $cat;
						}
					}
				}
				foreach ($rows2 as $row2) {
					$categories[$section->id][] = JHTML::_('select.option', $row2->id, $row2->title, 'id', 'title');
				}
				
			}
		}
		$categories['-1'][] = JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
		$lists['categories'] = $categories;
		// End Javascript Categories Render - - - - - 
		
		// Categories Data
		$categoriesOneSection = array();
		if (!empty($categoriesOneSection)) {
			$categoriesOneSection[] 	= JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
			foreach ($dataCategories as $catValue) {
				if($catValue->section == $dataSection[0]->id) {
					$categoriesOneSection[] = $catValue;
				}
			}
		} else {
			$categoriesOneSection[] 	= JHTML::_('select.option', '-1', '- '.JText::_('Select Category').' -', 'id', 'title');
		}

		

//		$categoriesOneSection 		= array_merge($categoriesOneSection, $dataSection);
		$lists['catid'] = JHTML::_('select.genericlist',  $categoriesOneSection, 'catid', 'class="inputbox" size="1"', 'id', 'title');
		

		
		$this->assignRef('lists',	$lists);
		$this->assignRef('tmpl',	$tmpl);
		parent::display($tpl);
	}
}
?>