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

class PhocaDownloadViewSections extends JView
{

	function display($tpl = null)
	{		
		global $mainframe;
		
		$params 		= &$mainframe->getParams();
		$tmpl['user'] 	= &JFactory::getUser();
		$model			= &$this->getModel();
		$document		= &JFactory::getDocument();
		$section		= $model->getSectionList($params);
		$mostViewedDocs	= $model->getMostViewedDocsList($params);
		
		$tmpl['displaynew']				= $params->get( 'display_new', 0 );
		$tmpl['displayhot']				= $params->get( 'display_hot', 0 );
		$tmpl['displaymostdownload']	= $params->get( 'display_most_download', 1 );
		$tmpl['displaynumdocsecs']		= $params->get( 'display_num_doc_secs', 0 );
		$tmpl['displaynumdocsecsheader']= $params->get( 'display_num_doc_secs_header', 1 );
		$tmpl['file_icon_size_md'] 		= $params->get( 'file_icon_size_md', 16 );
		$tmpl['download_metakey'] 		= $params->get( 'download_metakey', '' );
		$tmpl['download_metadesc'] 		= $params->get( 'download_metadesc', '' );
		$tmpl['description']			= PhocaDownloadHelper::getSettings( 'description', '' );
		
		$css			= $params->get( 'theme', 'phocadownload-grey' );
		$document->addStyleSheet(JURI::base(true).'/components/com_phocadownload/assets/'.$css.'.css');
		
		$document->addCustomTag("<!--[if lt IE 7]>\n<link rel=\"stylesheet\" href=\""
		.JURI::base(true)
		."/components/com_phocadownload/assets/".$css."-ie6.css\" type=\"text/css\" />\n<![endif]-->");
		
		// Meta data
		if ($tmpl['download_metakey'] != '') {
			$mainframe->addMetaTag('keywords', $tmpl['download_metakey']);
		}
		if ($tmpl['download_metadesc'] != '') {
			$mainframe->addMetaTag('description', $tmpl['download_metadesc']);
		}
		
		// CSS Image Path
		$imagePath		= PhocaDownloadHelper::getPathSet('icon');
		$cssImagePath	= str_replace ( '../', JURI::base(true).'/', $imagePath['orig_rel_ds']);
		$filePath		= PhocaDownloadHelper::getPathSet('file');
		$tmpl['pddl']	= PhocaDownloadHelper::renderPhocaDownload();
		
		$this->assignRef('cssimagepath',		$cssImagePath);
		$this->assignRef('absfilepath',			$filePath['orig_abs_ds']);
		$this->assignRef('section',				$section);
		$this->assignRef('mostvieweddocs',		$mostViewedDocs);
		$this->assignRef('params',				$params);
		$this->assignRef('tmpl',				$tmpl);
		parent::display($tpl);
		
	}
}
?>