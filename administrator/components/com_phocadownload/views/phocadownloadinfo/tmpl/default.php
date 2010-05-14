<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('behavior.tooltip'); ?>

<form action="index.php" method="post" name="adminForm">
<div style="float:right;margin:10px;">
	<?php
	echo JHTML::_('image.site',  'logo-phoca.png', '/components/com_phocadownload/assets/images/', NULL, NULL, '' )
	?>
</div>

<?php echo JHTML::_('image.site',  'icon-48-phocadownload.png', '/components/com_phocadownload/assets/images/', NULL, NULL, '' );?>

<h3><?php echo JText::_('Help');?></h3>

<p>
<a href="http://www.phoca.cz/phocadownload/" target="_blank">Phoca Download Main Site</a><br />
<a href="http://www.phoca.cz/documentation/" target="_blank">Phoca Download User Manual</a><br />
<a href="http://www.phoca.cz/forum/" target="_blank">Phoca Download Forum</a><br />
</p>

<h3><?php echo JText::_('Version');?></h3>
<p><?php echo $this->version ;?></p>

<h3><?php echo JText::_('Copyright');?></h3>
<p>© 2007 - <?php echo date("Y"); ?> Jan Pavelka
<br /><a href="http://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>

<h3><?php echo JText::_('License');?></h3>
<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>


<input type="hidden" name="controller" value="phocadownload" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_phocadownload" />
<input type="hidden" name="controller" value="phocadownloadinfo" />
</form>
<p>&nbsp;</p>

<div style="border-top:1px solid #c2c2c2"></div>
<div id="phoca-update" ><a href="http://www.phoca.cz/version/index.php?phocadownload=<?php echo $this->version ;?>" target="_blank"><?php echo JText::_('Check for update'); ?></a></div>
