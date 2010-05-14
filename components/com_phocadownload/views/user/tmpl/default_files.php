<?php defined('_JEXEC') or die('Restricted access');

$db			= &JFactory::getDBO();
$user 		= &JFactory::getUser();
$config		= &JFactory::getConfig();
$nullDate 	= $db->getNullDate();
$now		= &JFactory::getDate();

echo '<div id="phocadownload-upload">'.$this->tmpl['iepx'];

if ($this->tmpl['displayupload'] == 1) {


?><fieldset>
<legend><?php echo JText::_( 'PHOCADOWNLOAD_UPLOADED_FILES' ); ?></legend>
<form action="<?php echo $this->tmpl['action'];?>" method="post" name="phocadownloadfilesform">
<table>
	<tr>
		<td align="left" width="100%"><?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="search" id="pdsearch" value="<?php echo $this->listsfiles['search'];?>" onchange="document.phocadownloadfilesform.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('pdsearch').value='';document.phocadownloadfilesform.submit();"><?php echo JText::_( 'Reset' ); ?></button></td>
		<td nowrap="nowrap"><?php echo $this->listsfiles['sectionid']; echo $this->listsfiles['catid'];?></td>
	</tr>
</table>
		
<table class="adminlist">
<thead>
	<tr>
	<th width="1"><?php echo JText::_( 'NUM' ); ?></th>
	<th class="title" width="50%"><?php echo JHTML::_('grid.sort',  JText::_('PHOCADOWNLOAD_TITLE'), 'a.title', $this->listsfiles['order_Dir'], $this->listsfiles['order'], 'image'); ?></th>
	<th width="3%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  JText::_('PHOCADOWNLOAD_PUBLISHED'), 'a.published', $this->listsfiles['order_Dir'], $this->listsfiles['order'], 'image' ); ?></th>
	<th width="3%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  JText::_('PHOCADOWNLOAD_APPROVED'), 'a.approved', $this->listsfiles['order_Dir'], $this->listsfiles['order'], 'image' ); ?></th>

	<th width="3%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_( 'PHOCADOWNLOAD_DATE_UPLOAD' ), 'a.date', $this->listsfiles['order_Dir'], $this->listsfiles['order'], 'image' ); ?></th>
	
	<th width="3%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_( 'PHOCADOWNLOAD_SECTION' ), 'a.sectionid', $this->listsfiles['order_Dir'], $this->listsfiles['order'], 'image' ); ?></th>
	<th width="3%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_( 'PHOCADOWNLOAD_CATEGORY' ), 'a.catid', $this->listsfiles['order_Dir'], $this->listsfiles['order'], 'image' ); ?></th>

</thead>
			
<tbody><?php
$k 		= 0;
$i 		= 0;
$n 		= count( $this->tmpl['filesitems'] );
$rows 	= &$this->tmpl['filesitems'];

if (is_array($rows)) {
	foreach ($rows as $row) {

	?><tr class="<?php echo "row$k"; ?>">
	<td><?php echo $this->tmpl['filespagination']->getRowOffset( $i );?></td>

	<td><?php echo $row->title; ?></td>
	
	<?php 

	// Publish Unpublish
	echo '<td align="center">';
	if ($row->published == 1) {
		echo JHTML::_('image', $this->tmpl['pi'].'icon-publish.png', JText::_('PHOCADOWNLOAD_PUBLISHED'));
	}
	if ($row->published == 0) {
		echo JHTML::_('image', $this->tmpl['pi'].'icon-unpublish.png', JText::_('PHOCADOWNLOAD_UNPUBLISHED'));		
	}
	
	// User should get info about active/not active file (if e.g. admin change the active status)			
	$publish_up 	= &JFactory::getDate($row->publish_up);
	$publish_down 	= &JFactory::getDate($row->publish_down);
	$publish_up->setOffset($config->getValue('config.offset'));
	$publish_down->setOffset($config->getValue('config.offset'));
	if ( $now->toUnix() <= $publish_up->toUnix() ) {
		$text = JText::_( 'PHOCADOWNLOAD_PENDING' );
	} else if ( ( $now->toUnix() <= $publish_down->toUnix() || $row->publish_down == $nullDate ) ) {
		//$text = JText::_( 'PHOCADOWNLOAD_ACTIVE' );
		$text = '';
	} else if ( $now->toUnix() > $publish_down->toUnix() ) {
		$text = JText::_( 'PHOCADOWNLOAD_EXPIRED' );
	}
	
	$times = '';
	if (isset($row->publish_up)) {
		if ($row->publish_up == $nullDate) {
			$times .= JText::_( 'Start: Always' );
		} else {
			$times .= JText::_( 'Start' ) .": ". $publish_up->toFormat();
		}
	}
	if (isset($row->publish_down)) {
		if ($row->publish_down == $nullDate) {
			$times .= "<br />". JText::_( 'Finish: No Expiry' );
		} else {
			$times .= "<br />". JText::_( 'Finish' ) .": ". $publish_down->toFormat();
		}
	}
	
	if ( $times ) {
		echo '<span class="editlinktip hasTip" title="'. JText::_( 'Publish Information' ).'::'. $times.'">'
			.'<a href="javascript:void(0);" >'. $text.'</a></span>';
	}
	
	
	echo '</td>';
	
	// Approved
	echo '<td align="center">';
	if ($row->approved == 1) {
		echo JHTML::_('image', $this->tmpl['pi'].'icon-publish.png', JText::_('PHOCADOWNLOAD_APPROVED'));
	} else {	
		echo JHTML::_('image', $this->tmpl['pi'].'icon-unpublish.png', JText::_('PHOCADOWNLOAD_NOT_APPROVED'));	
	}
	echo '</td>';
	
	echo '<td align="center">'. $row->date .'</td>';
	
	echo '<td align="center">'. $row->sectiontitle .'</td>';
	echo '<td align="center">'. $row->categorytitle .'</td>'
	//echo '<td align="center">'. $row->id .'</td>'
	.'</tr>';

		$k = 1 - $k;
		$i++;
	}
}
?></tbody>
<tfoot>
	<tr>
	<td colspan="7" class="footer"><?php 
	
//$this->tmpl['filespagination']->setTab($this->tmpl['currenttab']['files']);
if (count($this->tmpl['filesitems'])) {
	echo '<div><center>';
	echo '<div style="margin:0 10px 0 10px;display:inline;">'
		.JText::_('Display Num') .'&nbsp;'
		.$this->tmpl['filespagination']->getLimitBox()
		.'</div>';
	echo '<div class="sectiontablefooter'.$this->params->get( 'pageclass_sfx' ).'" style="margin:0 10px 0 10px;display:inline;" >'
		.$this->tmpl['filespagination']->getPagesLinks()
		.'</div>';
	echo '<div class="pagecounter" style="margin:0 10px 0 10px;display:inline;">'
		.$this->tmpl['filespagination']->getPagesCounter()
		.'</div>';
	echo '</center></div>';
}




?></td>
	</tr>
</tfoot>
</table>


<?php echo JHTML::_( 'form.token' ); ?>

<input type="hidden" name="controller" value="user" />
<input type="hidden" name="task" value=""/>
<input type="hidden" name="view" value="user"/>
<input type="hidden" name="tab" value="<?php echo $this->tmpl['currenttab']['files'];?>" />
<input type="hidden" name="limitstart" value="<?php echo $this->tmpl['filespagination']->limitstart;?>" />
<input type="hidden" name="Itemid" value="<?php echo JRequest::getVar('Itemid', 0, '', 'int') ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->listsfiles['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />

</form>
</fieldset>
<?php

// Upload		
$currentFolder = '';
if (isset($this->state->folder) && $this->state->folder != '') {
	$currentFolder = $this->state->folder;
}
?><fieldset>
<legend><?php 
	echo JText::_( 'PHOCADOWNLOAD_UPLOAD_FILE' ).' [ '. JText::_( 'PHOCADOWNLOAD_MAX_SIZE' ).':&nbsp;'.$this->tmpl['uploadmaxsizeread'].']';
?></legend>	
				
<?php
if ($this->tmpl['errorcatid'] != '') {
	echo '<div class="error">' . $this->tmpl['errorcatid'] . '</div>';
} ?>
				
<form onsubmit="return OnUploadSubmitFile();" action="<?php echo $this->tmpl['actionamp'] ?>task=upload&amp;<?php echo $this->session->getName().'='.$this->session->getId(); ?>&amp;<?php echo JUtility::getToken();?>=1" name="phocadownloaduploadform" id="phocadownload-upload-form" method="post" enctype="multipart/form-data">
<table>
	<tr>
		<td><strong><?php echo JText::_('PHOCADOWNLOAD_FILENAME');?>:</strong></td><td>
			<input type="file" id="file-upload" name="Filedata" />
			<input type="submit" id="file-upload-submit" value="<?php echo JText::_('Start Upload'); ?>"/>
			<span id="upload-clear"></span></td>
		</tr>
		
		<?php
		if ($this->tmpl['errorfile'] != '') {
			echo '<tr><td></td><td><div class="error">' . $this->tmpl['errorfile'] . '</div></td></tr>';
		} ?>
					
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_FILE_TITLE' ); ?>:</strong></td>
			<td><input type="text" id="phocadownload-upload-title" name="phocadownloaduploadtitle" value="<?php echo $this->formdata->title ?>"  maxlength="255" class="comment-input" /></td>
		</tr>
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_DESCRIPTION' ); ?>:</strong></td>
			<td><textarea id="phocadownload-upload-description" name="phocadownloaduploaddescription" onkeyup="countCharsUpload();" cols="30" rows="10" class="comment-input"><?php echo $this->formdata->description ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><?php echo JText::_('PHOCADOWNLOAD_CHARACTERS_WRITTEN');?> <input name="phocadownloaduploadcountin" value="0" readonly="readonly" class="comment-input2" /> <?php echo JText::_('PHOCADOWNLOAD_AND_LEFT_FOR_DESCRIPTION');?> <input name="phocadownloaduploadcountleft" value="<?php echo $this->tmpl['maxuploadchar'];?>" readonly="readonly" class="comment-input2" />
			</td>
		</tr>
		
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_AUTHOR' ); ?>:</strong></td>
			<td><input type="text" id="phocadownload-upload-author" name="phocadownloaduploadauthor" value="<?php echo $this->formdata->author ?>"  maxlength="255" class="comment-input" /></td>
		</tr>
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_AUTHOR_EMAIL' ); ?>:</strong></td>
			<td><input type="text" id="phocadownload-upload-email" name="phocadownloaduploademail" value="<?php echo $this->formdata->email ?>"  maxlength="255" class="comment-input" /></td>
		</tr>
		
		<?php
		if ($this->tmpl['erroremail'] != '') {
			echo '<tr><td></td><td><div class="error">' . $this->tmpl['erroremail'] . '</div></td></tr>';
		} ?>
		
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_AUTHOR_WEBSITE' ); ?>:</strong></td>
			<td><input type="text" id="phocadownload-upload-website" name="phocadownloaduploadwebsite" value="<?php echo $this->formdata->website ?>"  maxlength="255" class="comment-input" /></td>
		</tr>
		
		<?php
		if ($this->tmpl['errorwebsite'] != '') {
			echo '<tr><td></td><td><div class="error">' . $this->tmpl['errorwebsite'] . '</div></td></tr>';
		} ?>
		
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_LICENSE' ); ?>:</strong></td>
			<td><input type="text" id="phocadownload-upload-license" name="phocadownloaduploadlicense" value="<?php echo $this->formdata->license ?>"  maxlength="255" class="comment-input" /></td>
		</tr>
		
		<tr>
			<td><strong><?php echo JText::_( 'PHOCADOWNLOAD_VERSION' ); ?>:</strong></td>
			<td><input type="text" id="phocadownload-upload-version" name="phocadownloaduploadversion" value="<?php echo $this->formdata->version ?>"  maxlength="255" class="comment-input" /></td>
		</tr>
		
	</table>
	
	<ul class="upload-queue" id="upload-queue"><li style="display: none" ></li></ul>

	<?php /*<input type="hidden" name="controller" value="user" /> */ ?>
	<input type="hidden" name="viewback" value="user" />
	<input type="hidden" name="view" value="user"/>
	<input type="hidden" name="task" value="upload"/>
	<input type="hidden" name="tab" value="<?php echo $this->tmpl['currenttab']['files'];?>" />
	<input type="hidden" name="Itemid" value="<?php echo JRequest::getVar('Itemid', 0, '', 'int') ?>"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->listsfiles['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="catidfiles" value="<?php echo $this->tmpl['catidfiles'] ?>"/>
</form>
<div id="loading-label-file"><center><?php echo JHTML::_('image', $this->tmpl['pi'].'icon-loading.gif', '') . JText::_('Loading'); ?></center></div>
</fieldset>
	<?php
}
echo '</div>';

?>
