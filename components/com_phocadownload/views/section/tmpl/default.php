<?php
// no direct access
defined('_JEXEC') or die('Restricted access'); 
if ( $this->params->def( 'show_page_title', 1 ) ) { 
	echo '<div class="componentheading'. $this->params->get( 'pageclass_sfx' ).'">'. $this->params->get('page_title').'</div>';
}
if (!isset($this->section[0])) {
	return JError::raiseError( 404, JText::_( 'Document not found') );
}
echo '<div id="phoca-dl-section-box">';
echo '<div class="pd-section">';
if ($this->tmpl['display_up_icon'] == 1) {
	//echo '<div class="pdtop"><a title="'.JText::_('Sections').'" href="'. JRoute::_('index.php?option=com_phocadownload&view=sections'.'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'" >'.JHTML::_('image', 'components/com_phocadownload/assets/images/up.png', JText::_('Up')).  '</a></div>';
	echo '<div class="pdtop"><a title="'.JText::_('Sections').'" href="'. JRoute::_(PhocaDownloadHelperRoute::getSectionsRoute()).'" >'.JHTML::_('image', 'components/com_phocadownload/assets/images/up.png', JText::_('Up')).  '</a></div>';
}
echo '<h3>'.$this->section[0]->title. '</h3>';


// Description
echo '<div class="contentpane'.$this->params->get( 'pageclass_sfx' ).'">';
if ( (isset($this->tmpl['image']) && $this->tmpl['image'] !='') || (isset($this->section[0]->description) && $this->section[0]->description != '' && $this->section[0]->description != '<p>&#160;</p>')) {
	echo '<div class="contentdescription'.$this->params->get( 'pageclass_sfx' ).'">';
	if ( isset($this->tmpl['image']) ) {
		echo $this->tmpl['image'];
	}
	echo $this->section[0]->description
		.'</div><p>&nbsp;</p>';
}
echo '</div>';
    
if (!empty($this->categorylist)) {  
    foreach ($this->categorylist as $valueCat) {
		// USER RIGHT - Access of categories - - - - -
		// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
		$rightDisplay	= 0;
		if (!empty($valueCat)) {
			$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $valueCat->accessuserid, $valueCat->access, $this->tmpl['user']->get('aid', 0), $this->tmpl['user']->get('id', 0), 0);
		}
		// - - - - - - - - - - - - - - - - - - - - - -
	
		if ($rightDisplay == 1) {
			echo '<p class="pd-category">';
			//echo '<a href="'. JRoute::_('index.php?option=com_phocadownload&view=category&id='.$valueCat->id.':'.$valueCat->alias.'&Itemid='. JRequest::getVar('Itemid', 1, 'get', 'int')).'">'. $valueCat->title.'</a>';
			echo '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($valueCat->id, $valueCat->alias, $this->section[0]->id)).'">'. $valueCat->title.'</a>';
			echo ' <small>('.$valueCat->numdoc.')</small></p>' . "\n";
		}
    }
    //echo '</div>';
} else {
	echo '<p>&nbsp;</p><p>&nbsp;</p>';
}   
echo '</div></div>';

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
			$outputFile .= '<a href="'. JRoute::_(PhocaDownloadHelperRoute::getCategoryRoute($value->categoryid,$value->categoryalias,$this->section[0]->id)).'">'. $value->title.'</a> <small>(' .$value->sectiontitle. '/'.$value->categorytitle.')</small>';
			
			$outputFile .= PhocaDownloadHelper::displayNewIcon($value->date, $this->tmpl['displaynew']);
			$outputFile .= PhocaDownloadHelper::displayHotIcon($value->hits, $this->tmpl['displayhot']);
			
			$outputFile .= '</div>' . "\n";
		}
	}
	
	
	if ($outputFile != '') {
		echo '<div class="phoca-dl-hr" style="clear:both">&nbsp;</div>';
		echo '<div id="phoca-dl-most-viewed-box">';
		echo '<div class="pd-documents"><h3>'. JText::_('Most downloaded files section').'</h3>';
		echo $outputFile;
		echo '</div></div>';
	}	
}
echo $this->tmpl['pdwnl'];
?>