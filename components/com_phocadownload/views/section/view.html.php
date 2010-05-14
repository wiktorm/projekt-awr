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

class PhocaDownloadViewSection extends JView
{

	function display($tpl = null)
	{		
		global $mainframe;
		
		$params 		= &$mainframe->getParams();
		$tmpl['user'] 	= &JFactory::getUser();
		$model			= &$this->getModel();
		$document		= &JFactory::getDocument();
		$sectionId		= JRequest::getVar('id', 0, '', 'int');
		$section		= $model->getSection($sectionId, $params);
		$categoryList	= $model->getCategoryList($sectionId, $params);
		$mostViewedDocs	= $model->getMostViewedDocsList($sectionId, $params);
		
		$tmpl['displaynew']			= $params->get( 'display_new', 0 );
		$tmpl['displayhot']			= $params->get( 'display_hot', 0 );
		$tmpl['displaymostdownload']= $params->get( 'display_most_download', 1 );
		$tmpl['file_icon_size_md'] 	= $params->get( 'file_icon_size_md', 16 );
		$tmpl['display_up_icon'] 	= $params->get( 'display_up_icon', 1 );
		$tmpl['download_metakey'] 	= $params->get( 'download_metakey', '' );
		$tmpl['download_metadesc'] 	= $params->get( 'download_metadesc', '' );
		
		$css			= $params->get( 'theme', 'phocadownload-grey' );
		$document->addStyleSheet(JURI::base(true).'/components/com_phocadownload/assets/'.$css.'.css');

		// CSS Image Path
		$imagePath		= PhocaDownloadHelper::getPathSet('icon');
		$cssImagePath	= str_replace ( '../', JURI::base(true).'/', $imagePath['orig_rel_ds']);
		$filePath		= PhocaDownloadHelper::getPathSet('file');
		$tmpl['pdwnl']	= PhocaDownloadHelper::renderPhocaDownload();
		
		
		
		// Meta data
		if (isset($section[0]) && $section[0]->metakey != '') {
			$mainframe->addMetaTag('keywords', $section[0]->metakey);
		} else if ($tmpl['download_metakey'] != '') {
			$mainframe->addMetaTag('keywords', $tmpl['download_metakey']);
		}
		if (isset($section[0]) && $section[0]->metadesc != '') {
			$mainframe->addMetaTag('description', $section[0]->metadesc);
		} else if ($tmpl['download_metadesc'] != '') {
			$mainframe->addMetaTag('description', $tmpl['download_metadesc']);
		}
		
		// Define image tag attributes
		if (!empty($section[0]->image)) {
			$attribs['align'] = '"'.$section[0]->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$tmpl['image'] = JHTML::_('image', 'images/stories/'.$section[0]->image, JText::_('Phoca Download'), $attribs);
		} else {
			$tmpl['image'] = '';
		}
		
		// Breadcrumbs
		if (!empty($section[0]->title)) {
			$pathway 		=& $mainframe->getPathway();
			$pathway->addItem($section[0]->title, JRoute::_(PhocaDownloadHelperRoute::getSectionsRoute()));
		}

		$this->assignRef('tmpl',			$tmpl);
		
		$this->assignRef('cssimagepath',	$cssImagePath);
		$this->assignRef('absfilepath',		$filePath['orig_abs_ds']);
		$this->assignRef('section',			$section);
		$this->assignRef('categorylist',	$categoryList);
		$this->assignRef('mostvieweddocs',	$mostViewedDocs);
		$this->assignRef('params',			$params);
		parent::display($tpl);
		
	}
}
?>