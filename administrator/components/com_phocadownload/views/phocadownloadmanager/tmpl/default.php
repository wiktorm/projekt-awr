<?php
defined('_JEXEC') or die('Restricted access');
echo $this->loadTemplate('up');

if (count($this->folders) > 0 ) {
		echo '<div>';
		for ($i=0,$n=count($this->folders); $i<$n; $i++) {
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		}
		echo '</div>';
} else { /* ?>
<div>
	<center style="clear:both;font-size:large;font-weight:bold;color:#b3b3b3;font-family: Helvetica, sans-serif;">
		<?php echo JText::_( 'There is no folder' ); ?>
	</center>
</div>
<?php */ }

if (count($this->files) > 0 ) {
		echo '<div>';
		for ($i=0,$n=count($this->files); $i<$n; $i++) {
			$this->setFile($i);
			echo $this->loadTemplate('file');
		}
		echo '</div>';
} else { ?>
<div>
	<center style="clear:both;font-size:large;font-weight:bold;color:#b3b3b3;font-family: Helvetica, sans-serif;">
		<?php echo JText::_( 'There is no file' ); ?>
	</center>
</div>
<?php } ?>


<div style="border-bottom:1px solid #cccccc;margin-bottom: 10px">&nbsp;</div>

<?php
$currentFolder = '';
if (isset($this->state->folder) && $this->state->folder != '') {
	$currentFolder = $this->state->folder;
}
?>

<form action="<?php echo JURI::base(); ?>index.php?option=com_phocadownload&controller=phocadownloadupload&amp;task=upload&amp;tmpl=component&amp;<?php echo $this->session->getName().'='.$this->session->getId(); ?>&amp;<?php echo JUtility::getToken();?>=1&amp;viewback=phocadownloadmanager:<?php echo $this->manager;?>&amp;folder=<?php echo $currentFolder?>" id="uploadForm" method="post" enctype="multipart/form-data">

<!-- File Upload Form -->
<?php if ($this->require_ftp): ?>

	<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
		<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>
		<?php echo JText::_('DESCFTP2'); ?>
		<table class="adminform nospace">
			<tr>
				<td width="120">
					<label for="username"><?php echo JText::_('Username'); ?>:</label>
				</td>
				<td>
					<input type="text" id="username" name="username" class="input_box" size="70" value="" />
				</td>
			</tr>
			<tr>
				<td width="120">
					<label for="password"><?php echo JText::_('Password'); ?>:</label>
				</td>
				<td>
					<input type="password" id="password" name="password" class="input_box" size="70" value="" />
				</td>
			</tr>
		</table>
	</fieldset>

<?php endif; ?>

	<fieldset>
		<legend><?php echo JText::_( 'Upload File' ); ?> [ <?php echo JText::_( 'Max' ); ?>&nbsp;<?php echo ($this->uploadmaxsize / 1000000); ?>M ]</legend>
		<fieldset class="actions">
			<input type="file" id="file-upload" name="Filedata" />
			<input type="submit" id="file-upload-submit" value="<?php echo JText::_('Start Upload'); ?>"/>
			<span id="upload-clear"></span>
		</fieldset>
		<ul class="upload-queue" id="upload-queue">
			<li style="display: none" ></li>
		</ul>
	</fieldset>
	<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_phocadownload&view=phocadownloadmanager&manager='.$this->manager.'&tmpl=component'); ?>" />
</form>

<form action="<?php echo JURI::base(); ?>index.php?option=com_phocadownload&controller=phocadownloadupload&amp;task=createfolder&amp;<?php echo $this->session->getName().'='.$this->session->getId(); ?>&amp;<?php echo JUtility::getToken();?>=1&amp;viewback=phocadownloadmanager:<?php echo $this->manager;?>&amp;folder=<?php echo $currentFolder?>" name="folderForm" id="folderForm" method="post">
	<fieldset id="folderview">
		<legend><?php echo JText::_( 'Folder' ); ?></legend>
		<div class="path">
			<input class="inputbox" type="text" id="foldername" name="foldername"  />
			<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $currentFolder; ?>" />
			<button type="submit"><?php echo JText::_( 'Create Folder' ); ?></button>
		</div>
    </fieldset>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

</div>
