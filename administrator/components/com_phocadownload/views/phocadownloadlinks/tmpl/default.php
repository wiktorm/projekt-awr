<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');

echo '<div id="phocadownload-links">'
.'<fieldset class="adminform">'
.'<legend>'.JText::_( 'Select Link Type' ).'</legend>'
.'<ul>'
.'<li class="icon-16-edb-sections"><a href="'.$this->tmpl['linksections'].'">'.JText::_('Link to all sections').'</a></li>'
.'<li class="icon-16-edb-section"><a href="'.$this->tmpl['linksection'].'">'.JText::_('Link to section').'</a></li>'
.'<li class="icon-16-edb-category"><a href="'.$this->tmpl['linkcategory'].'">'.JText::_('Link to category').'</a></li>'
.'<li class="icon-16-edb-file"><a href="'.$this->tmpl['linkfile'].'&type=0">'.JText::_('Link to file').'</a></li>'
.'<li class="icon-16-edb-play"><a href="'.$this->tmpl['linkfile'].'&type=1">'.JText::_('PHOCADOWNLOAD_PLAY_FILE_LINK').'</a> <a href="'.$this->tmpl['linkfile'].'&type=2">'.JText::_('PHOCADOWNLOAD_PLAY_FILE_DIRECT').'</a></li>'
.'<li class="icon-16-edb-preview"><a href="'.$this->tmpl['linkfile'].'&type=3">'.JText::_('PHOCADOWNLOAD_PREVIEW_FILE_LINK').'</a></li>'
.'<li class="icon-16-edb-play"><a href="'.$this->tmpl['linkytb'].'">'.JText::_('PHOCADOWNLOAD_YOUTUBE_VIDEO').'</a></li>'
.'</ul>'
.'</div>'
.'</fieldset>'
.'</div>';
?>