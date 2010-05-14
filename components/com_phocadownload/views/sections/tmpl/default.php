<?php
defined('_JEXEC') or die('Restricted access'); 

if ( $this->params->def( 'show_page_title', 1 ) ) {
	echo '<div class="componentheading'. $this->params->get( 'pageclass_sfx' ).'">'
	    . $this->params->get('page_title')
	    . '</div>';
}

echo '<div id="phoca-dl-sections-box">';

if ( $this->tmpl['description'] != '') {
	echo '<div class="pd-desc">'. $this->tmpl['description']. '</div>';
}

if (!empty($this->section)) {
	$i = 1;
	foreach ($this->section as $value) {
		// Categories
		$numDoc 	= 0;
		$catOutput 	= '';
		foreach ($value->categories as $valueCat) {
			
			// USER RIGHT - Access of categories - - - - -
			// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
			$rightDisplay	= 0;
			if (!empty($valueCat)) {
				$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $valueCat->accessuserid, $valueCat->access, $this->tmpl['user']->get('aid', 0), $this->tmpl['user']->get('id', 0), 0);
			}
			// - - - - - - - - - - - - - - - - - - - - - -
			if ($rightDisplay == 1) {
				$catOutput 	.= '<p class="pd-category">';
				//$catOutput 	.= '<a href="'. JRoute::_('index.php?option=com_phocadownload&view=category&id='.$valueCat->id.':'.$valueCat->alias.'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'">'. $valueCat->title.'</a>';
			
				$catOutput 	.= '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($valueCat->id, $valueCat->alias, $value->id)).'">'. $valueCat->title.'</a>';
			
				if ($this->tmpl['displaynumdocsecs'] == 1) {
					$catOutput  .=' <small>('.$valueCat->numdoc .')</small>';
				}
			
				$catOutput 	.= '</p>' . "\n";
				$numDoc = (int)$valueCat->numdoc + (int)$numDoc;
			}
		}
		
		// Don't display section if there is no catoutput
		
		if ($catOutput != '') {
		
			echo '<div class="pd-sections"><div><div><div><h3>';
			//echo '<a href="'. JRoute::_('index.php?option=com_phocadownload&view=section&id='.$value->id.':'.$value->alias.'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'">'. $value->title.'</a>';
			echo '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getSectionRoute($value->id, $value->alias)).'">'. $value->title.'</a>';
			
			if ($this->tmpl['displaynumdocsecsheader'] == 1) {
				echo ' <small>('.$value->numcat.'/' . $numDoc .')</small>';
			}
			echo '</h3>';
			echo $catOutput;	
			echo '</div></div></div></div>';
			if ($i%3==0) {
				echo '<div style="clear:both"></div>';
			}
			$i++;
		}
	}
}
echo '</div>'
    .'<div style="clear:both"></div>';

// Most viewed docs (files)
$outputFile		= '';
if (!empty($this->mostvieweddocs) && $this->tmpl['displaymostdownload'] == 1) {
	foreach ($this->mostvieweddocs as $value) {
		// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
		// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
		$rightDisplay	= 0;
		if (!empty($value)) {
			$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $value->cataccessuserid, $value->cataccess, $this->tmpl['user']->get('aid', 0), $this->tmpl['user']->get('id', 0), 0);
		}
		// - - - - - - - - - - - - - - - - - - - - - -
		
		if ($rightDisplay == 1) {
			// FILESIZE
			if ($value->filename !='') {
				$absFile = str_replace('/', DS, JPath::clean($this->absfilepath . $value->filename));
				if (JFile::exists($absFile))
				{
					$fileSize = PhocaDownloadHelper::getFileSizeReadable(filesize($absFile));
				} else {
					$fileSize = '';
				}
			}
			
			// IMAGE FILENAME
			$imageFileName = '';
			if ($value->image_filename !='') {
				$thumbnail = false;
				$thumbnail = preg_match("/phocathumbnail/i", $value->image_filename);
				if ($thumbnail) {
					$imageFileName 	= '';
				} else {
					$imageFileName = 'style="background: url(\''.$this->cssimagepath.$value->image_filename.'\') 0 center no-repeat;"';
				}
			}
		
			$outputFile .= '<div class="pd-document'.$this->tmpl['file_icon_size_md'].'" '.$imageFileName.'>';
			//echo '<a href="'. JRoute::_('index.php?option=com_phocadownload&view=category&id='.$value->categoryid.':'.$value->categoryalias.'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'">'. $value->title.'</a> <small>(' .$value->sectiontitle. '/'.$value->categorytitle.')</small>';
		
			$outputFile .= '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($value->categoryid,$value->categoryalias, $value->sectionid)).'">'. $value->title.'</a> <small>(' .$value->sectiontitle. '/'.$value->categorytitle.')</small>';
			
			$outputFile .= PhocaDownloadHelper::displayNewIcon($value->date, $this->tmpl['displaynew']);
			$outputFile .= PhocaDownloadHelper::displayHotIcon($value->hits, $this->tmpl['displayhot']);		

			$outputFile .= '</div>' . "\n";
		}
	}
	
	if ($outputFile != '') {
		echo '<div class="phoca-dl-hr" style="clear:both">&nbsp;</div>';
		echo '<div id="phoca-dl-most-viewed-box">';
		echo '<div class="pd-documents"><h3>'. JText::_('Most downloaded files').'</h3>';
		echo $outputFile;
		echo '</div></div>';
	}
}
echo $this->tmpl['pddl'];
?>
