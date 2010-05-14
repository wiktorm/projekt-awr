<?php
defined('_JEXEC') or die('Restricted access');
$user 	=& JFactory::getUser();

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'a.ordering');

JHTML::_('behavior.tooltip');

if ($this->tmpl['type'] == 0) {
	$view = 'file';
} else if ($this->tmpl['type'] == 1) {
	$view = 'fileplaylink';
}  else if ($this->tmpl['type'] == 2) {
	$view = 'fileplay';
}  else if ($this->tmpl['type'] == 3) {
	$view = 'filepreviewlink';
}


?>
<script type="text/javascript">
//<![CDATA[
function insertLink() {
	var title = document.getElementById("title").value;
	if (title != '') {
		title = "|text="+title;
	}
	<?php if ($this->tmpl['type'] == 0) { ?>
	var target = document.getElementById("target").value;
	if (target != '') {
		target = "|target="+target;
	}
	<?php } else if ($this->tmpl['type'] == 1 || $this->tmpl['type'] == 2) { ?>
	var playerwidth = document.getElementById("playerwidth").value;
	if (playerwidth != '') {
		playerwidth = "|playerwidth="+playerwidth;
	}
	var playerheight = document.getElementById("playerheight").value;
	if (playerheight != '') {
		playerheight = "|playerheight="+playerheight;
	}
	var playerheightmp3 = document.getElementById("playerheightmp3").value;
	if (playerheightmp3 != '') {
		playerheightmp3 = "|playerheightmp3="+playerheightmp3;
	}
	<?php } else if ($this->tmpl['type'] == 3) { ?>
	var previewwidth = document.getElementById("previewwidth").value;
	if (previewwidth != '') {
		previewwidth = "|previewwidth="+previewwidth;
	}
	var previewheight = document.getElementById("previewheight").value;
	if (previewheight != '') {
		previewheight = "|previewheight="+previewheight;
	}
	
	<?php } ?>
	
	var fileIdOutput;
	fileIdOutput = '';
	len = document.getElementsByName("fileid").length;
	for (i = 0; i <len; i++) {
		if (document.getElementsByName('fileid')[i].checked) {
			fileid = document.getElementsByName('fileid')[i].value;
			if (fileid != '' && parseInt(fileid) > 0) {
				fileIdOutput = "|id="+fileid;
			} else {
				fileIdOutput = '';
			}
		}
	}
	
	if (fileIdOutput != '' &&  parseInt(fileid) > 0) {
		<?php if ($this->tmpl['type'] == 0) { ?>
			var tag = "{phocadownload view=<?php echo $view ?>"+fileIdOutput+title+target+"}";
		<?php } else if ($this->tmpl['type'] == 1) { ?>
			var tag = "{phocadownload view=<?php echo $view ?>"+fileIdOutput+title+playerwidth+playerheight+playerheightmp3+"}";
		<?php } else if ($this->tmpl['type'] == 2) { ?>
			var tag = "{phocadownload view=<?php echo $view ?>"+fileIdOutput+title+playerwidth+playerheight+playerheightmp3+"}";
		<?php } else if ($this->tmpl['type'] == 3) { ?>
			var tag = "{phocadownload view=<?php echo $view ?>"+fileIdOutput+title+previewwidth+previewheight+"}";
		<?php } ?>
		window.parent.jInsertEditorText(tag, '<?php echo $this->tmpl['ename']; ?>');
		window.parent.document.getElementById('sbox-window').close();
		return false;
	} else {
		alert("<?php echo JText::_( 'You must select a file', true ); ?>");
		return false;
	}
}
//]]>
</script>
<div id="phocadownload-links">
<fieldset class="adminform">
<legend><?php echo JText::_( 'File' ); ?></legend>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
	<table class="admintable" width="100%">
		<tr>
			<td class="key" align="right" width="20%">
				<label for="title">
					<?php echo JText::_( 'Filter' ); ?>
				</label>
			</td>
			<td width="80%">
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
		</tr>
		<tr>
			<td class="key" align="right" nowrap="nowrap">
			<label for="title" nowrap="nowrap">
				<?php echo JText::_( 'Section' ); ?>, <?php echo JText::_( 'Category' ); ?>
			</label>
			</td>
			<td>
			<?php
				echo $this->lists['sectionid'];
				echo $this->lists['catid'];
				//echo $this->lists['state'];
				?>
			</td>
		</tr>
	</table>

	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="5px"><?php echo JText::_( 'NUM' ); ?></th>
					<th width="5px"></th>
					<th class="title" width="40%"><?php echo JHTML::_('grid.sort',  'Title', 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="20%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'Filename', 'a.filename', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="10%" nowrap="nowrap"><?php echo JHTML::_('grid.sort',  'ID', 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
					$row = &$this->items[$i];
					
					
					
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
					<td><input type="radio" name="fileid" value="<?php echo $row->id ?>" /></td>
					
					<td><?php echo $row->title; ?></td>
					<td><?php echo $row->filename;?></td>
					<td align="center"><?php echo $row->id; ?></td>
				</tr>
				<?php
				$k = 1 - $k;
				}
			?>
			</tbody>
		</table>
	</div>

	
<input type="hidden" name="controller" value="phocadownloadlinkfile" />
<input type="hidden" name="type" value="<?php echo $this->tmpl['type']; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="e_name" value="<?php echo $this->tmpl['ename']?>" />
</form>


<?php if ($this->tmpl['type'] == 0) {
	?>
<form name="adminFormLink" id="adminFormLink">
<table class="admintable" width="100%">
	<tr >
		<td class="key" align="right">
			<label for="title">
				<?php echo JText::_( 'Title' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="title" name="title" />
		</td>
	</tr>
	<tr >
		<td class="key" align="right">
			<label for="target">
				<?php echo JText::_( 'Target' ); ?>
			</label>
		</td>
		<td>
			<select name="target" id="target">
			<option value="s" selected="selected"><?php echo JText::_( 'Target _self' ); ?></option>
			<option value="b"><?php echo JText::_( 'Target _blank' ); ?></option>
			<option value="t"><?php echo JText::_( 'Target _top' ); ?></option>
			<option value="p"><?php echo JText::_( 'Target _parent' ); ?></option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td>&nbsp;</td>
		<td align="right"><button onclick="insertLink();return false;"><?php echo JText::_( 'Insert Link' ); ?></button></td>
	</tr>
</table>
</form>

	<?php
} else if ($this->tmpl['type'] == 1 || $this->tmpl['type'] == 2){
	?>
	
<form name="adminFormLink" id="adminFormLink">
<table class="admintable" width="100%">
	
	<?php if ($this->tmpl['type'] == 1) { ?>
	<tr >
		<td class="key" align="right">
			<label for="title">
				<?php echo JText::_( 'PHOCADOWNLOAD_TITLE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="title" name="title" />
		</td>
	</tr>
	<?php } else { ?>
		<input type="hidden" id="title" name="title" />
	<?php }	?>
	
	<tr >
		<td class="key" align="right">
			<label for="playerwidth">
				<?php echo JText::_( 'PHOCADOWNLOAD_PLAYER_WIDTH' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="playerwidth" name="playerwidth" value="328" />
		</td>
	</tr>
	
	<tr >
		<td class="key" align="right">
			<label for="playerheight">
				<?php echo JText::_( 'PHOCADOWNLOAD_PLAYER_HEIGHT' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="playerheight" name="playerheight" value="200" />
		</td>
	</tr>
	
	<tr >
		<td class="key" align="right">
			<label for="playerheightmp3">
				<?php echo JText::_( 'PHOCADOWNLOAD_PLAYER_HEIGHT_MP3' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="playerheightmp3" name="playerheightmp3" value="30" />
		</td>
	</tr>
	<?php if ($this->tmpl['type'] == 1) { ?>
		<tr><td colspan="2"><?php echo JText::_('PHOCADOWNLOAD_WARNING_PLAYER_SIZE')?></td></tr>
	<?php } ?>
	<tr>
		<td>&nbsp;</td>
		<td align="right"><button onclick="insertLink();return false;"><?php echo JText::_( 'Insert Link' ); ?></button></td>
	</tr>
</table>
</form>	
	
	<?php
} else if ($this->tmpl['type'] == 3){
	?>
	
<form name="adminFormLink" id="adminFormLink">
<table class="admintable" width="100%">
	
	<?php if ($this->tmpl['type'] == 1) { ?>
	<tr >
		<td class="key" align="right">
			<label for="title">
				<?php echo JText::_( 'PHOCADOWNLOAD_TITLE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="title" name="title" />
		</td>
	</tr>
	<?php } else { ?>
		<input type="hidden" id="title" name="title" />
	<?php }	?>
	
	<tr >
		<td class="key" align="right">
			<label for="previewwidth">
				<?php echo JText::_( 'PHOCADOWNLOAD_PREVIEW_WIDTH' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="previewwidth" name="previewwidth" value="640" />
		</td>
	</tr>
	
	<tr >
		<td class="key" align="right">
			<label for="previewheight">
				<?php echo JText::_( 'PHOCADOWNLOAD_PREVIEW_HEIGHT' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="previewheight" name="previewheight" value="480" />
		</td>
	</tr>
	
	<tr>
		<td>&nbsp;</td>
		<td align="right"><button onclick="insertLink();return false;"><?php echo JText::_( 'Insert Link' ); ?></button></td>
	</tr>
</table>
</form>	
	
	<?php
}
	?>
</fieldset>
<div style="text-align:right;margin:20px 5px;"><span class="icon-16-edb-back"><a style="text-decoration:underline" href="<?php echo $this->tmpl['backlink'];?>"><?php echo JText::_('Back')?></a></span></div>
</div>