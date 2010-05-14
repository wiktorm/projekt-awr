<?php defined('_JEXEC') or die('Restricted access'); ?>

<div>
	<a style="text-decoration:none" alt=".." href="index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=<?php echo $this->manager;?>&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" ><?php
	
	echo JHTML::_( 'image.administrator', 'components/com_phocadownload/assets/images/icon-up.png','','', '', JText::_('Up'), 'title="'.JText::_('Up').'"');
	
	?> ..</a></div>