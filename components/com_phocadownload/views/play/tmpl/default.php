<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
$rightDisplay	= 0;
if (!empty($this->file[0])) {
	$rightDisplay = PhocaDownloadHelper::getUserRight('accessuserid', $this->file[0]->cataccessuserid, $this->file[0]->cataccess, $this->tmpl['user']->get('aid', 0), $this->tmpl['user']->get('id', 0), 0);
}
// - - - - - - - - - - - - - - - - - - - - - -

if ($rightDisplay == 1) {

	//Flow Player
	$versionFLP 	= '3.1.5';
	$versionFLPJS 	= '3.1.4';
	$document = &JFactory::getDocument();
	$document->addScript($this->tmpl['playerpath'].'flowplayer-'.$versionFLPJS.'.min.js');

	?>
	<div style="text-align:center;margin: 10px auto">
	<div style="margin: 0 auto;text-align:center; width:<?php echo $this->tmpl['playerwidth']; ?>px"><a href="<?php echo $this->tmpl['playfilewithpath']; ?>"  style="display:block;width:<?php echo $this->tmpl['playerwidth']; ?>px;height:<?php echo $this->tmpl['playerheight']; ?>px" id="player"></a><?php

	if ($this->tmpl['filetype'] == 'mp3') {
		?><script>flowplayer("player", "<?php echo $this->tmpl['playerpath']; ?>flowplayer-<?php echo $versionFLP ?>.swf",
		{ 
			plugins: { 
				controls: { 
					fullscreen: false, 
					height: <?php echo $this->tmpl['playerheight']; ?> 
				} 
			}
		}
		);</script><?php
	} else {
		?><script>flowplayer("player", "<?php echo $this->tmpl['playerpath']; ?>flowplayer-<?php echo $versionFLP ?>.swf");</script><?php
	}
	?></div></div>

<?php 
} else {
	echo JText::_('PHOCADOWNLOAD_NO_RIGHTS_ACCESS_CATEGORY');
}


