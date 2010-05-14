<?php defined('_JEXEC') or die('Restricted access'); 

$heading = '';
if ($this->params->get( 'page_title' ) != '') {
	$heading .= $this->params->get( 'page_title' );
}

if ($this->tmpl['showpagetitle'] != 0) {
	if ( $heading != '') {
	    echo '<div class="componentheading'.$this->params->get( 'pageclass_sfx' ).'">'
	        .$heading
			.'</div>';
	} 
}
$tab = 0;
switch ($this->tmpl['tab']) {
	case 'up':
		$tab = 1;
	break;
	
	case 'cc':
	default:
		$tab = 0;
	break;
}

echo '<div>&nbsp;</div>';

if ($this->tmpl['displaytabs'] > 0) {
	echo '<div id="phocadownload-pane">';
	$pane =& JPane::getInstance('Tabs', array('startOffset'=> $this->tmpl['tab']));
	echo $pane->startPane( 'pane' );


	echo $pane->startPanel( JHTML::_( 'image.site', $this->tmpl['pi'].'icon-document-16.png','', '', '', '', '') . '&nbsp;'.JText::_('PHOCADOWNLOAD_UPLOAD'), 'files' );
	echo $this->loadTemplate('files');
	echo $pane->endPanel();


	echo $pane->endPane();
	echo '</div>';
}
echo $this->tmpl['pdl'];
?>
