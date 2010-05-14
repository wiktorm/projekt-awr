<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
$editor =& JFactory::getEditor();
?>
<script language="javascript" type="text/javascript">
//<![CDATA[
		var sectioncategories = new Array;
		<?php
		$i = 0;
		foreach ($this->sectioncategories as $k => $items) {
			foreach ($items as $v) {
				echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
			}
		}
		?>
	
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		// do field validation
		if (form.sectionid.value == "-1"){
			alert( "<?php echo JText::_( 'You must select a section', true ); ?>" );
		} else if (form.catid.value == "-1" || form.catid.value == ""){
			alert( "<?php echo JText::_( 'You must select a category', true ); ?>" );
		} else if (form.filename.value == ""){
			alert( "<?php echo JText::_( 'You must select a filename', true ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
//]]>
</script>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>


<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<div class="col50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<input class="text_area" type="text" name="title" id="title" size="32" maxlength="250" value="<?php echo $this->phocadownload->title;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="alias">
					<?php echo JText::_( 'Alias' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="alias" id="alias" size="32" maxlength="250" value="<?php echo $this->phocadownload->alias;?>" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td colspan="2">
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_( 'PHOCADOWNLOAD_APPROVED' ); ?>:
			</td>
			<td colspan="2">
				<?php echo $this->lists['approved']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="catid">
					<?php echo JText::_( 'Section' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<?php echo $this->lists['sectionid']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="catid">
					<?php echo JText::_( 'Category' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<?php echo $this->lists['catid']; ?>
			</td>
		</tr>
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="filename">
					<?php echo JText::_( 'Filename' ); ?>:
				</label>
			</td>
			<td valign="middle">
				<input class="text_area" type="text" name="filename" id="filename" value="<?php echo $this->phocadownload->filename; ?>" size="32" maxlength="250" />
			</td>
			<td align="left" valign="middle">
				<div class="button2-left" style="display:inline">
					<div class="<?php echo $this->buttonfile->name; ?>">
						<a class="<?php echo $this->buttonfile->modalname; ?>" title="<?php echo $this->buttonfile->text; ?>" href="<?php echo $this->buttonfile->link; ?>" rel="<?php echo $this->buttonfile->options; ?>"  ><?php echo $this->buttonfile->text; ?></a>
					</div>
				</div>
			</td>
		</tr>
		
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="filename_play">
					<?php echo JText::_( 'Filename - Play' ); ?>:
				</label>
			</td>
			<td valign="middle">
				<input class="text_area" type="text" name="filename_play" id="filename_play" value="<?php echo $this->phocadownload->filename_play; ?>" size="32" maxlength="250" />
			</td>
			<td align="left" valign="middle">
				<div class="button2-left" style="display:inline">
					<div class="<?php echo $this->buttonfile->name; ?>">
						<a class="<?php echo $this->buttonfile->modalname; ?>" title="<?php echo $this->buttonfile->text; ?>" href="<?php echo 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=fileplay&amp;tmpl=component'; ?>" rel="<?php echo $this->buttonfile->options; ?>"  ><?php echo $this->buttonfile->text; ?></a>
					</div>
				</div>
			</td>
		</tr>
		
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="filename_preview">
					<?php echo JText::_( 'Filename - Preview' ); ?>:
				</label>
			</td>
			<td valign="middle">
				<input class="text_area" type="text" name="filename_preview" id="filename_preview" value="<?php echo $this->phocadownload->filename_preview; ?>" size="32" maxlength="250" />
			</td>
			<td align="left" valign="middle">
				<div class="button2-left" style="display:inline">
					<div class="<?php echo $this->buttonfile->name; ?>">
						<a class="<?php echo $this->buttonfile->modalname; ?>" title="<?php echo $this->buttonfile->text; ?>" href="<?php echo 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=filepreview&amp;tmpl=component'; ?>" rel="<?php echo $this->buttonfile->options; ?>"  ><?php echo $this->buttonfile->text; ?></a>
					</div>
				</div>
			</td>
		</tr>
		
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="image_filename">
					<?php echo JText::_( 'Icon' ); ?>:
				</label>
			</td>
			<td valign="middle">
				<input class="text_area" type="text" name="image_filename" id="image_filename" value="<?php echo $this->phocadownload->image_filename; ?>" size="32" maxlength="250" />
			</td>
			<td align="left" valign="middle">
				<div class="button2-left" style="display:inline">
					<div class="<?php echo $this->buttonicon->name; ?>">
						<a class="<?php echo $this->buttonicon->modalname; ?>" title="<?php echo $this->buttonicon->text; ?>" href="<?php echo $this->buttonicon->link; ?>" rel="<?php echo $this->buttonicon->options; ?>"  ><?php echo $this->buttonicon->text; ?></a>
					</div>
				</div>
			</td>
		</tr>
		
		
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="image_filename_spec1">
					<?php echo JText::_( 'Specific Icon 1' ); ?>:
				</label>
			</td>
			<td valign="middle">
				<input class="text_area" type="text" name="image_filename_spec1" id="image_filename_spec1" value="<?php echo $this->phocadownload->image_filename_spec1; ?>" size="32" maxlength="250" />
			</td>
			<td align="left" valign="middle">
				<div class="button2-left" style="display:inline">
					<div class="<?php echo $this->buttonicon->name; ?>">
						<a class="<?php echo $this->buttonicon->modalname; ?>" title="<?php echo $this->buttonicon->text; ?>" href="<?php echo 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=iconspec1&amp;tmpl=component'; ?>" rel="<?php echo $this->buttonicon->options; ?>"  ><?php echo $this->buttonicon->text; ?></a>
					</div>
				</div>
			</td>
		</tr>
		
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="image_filename_spec2">
					<?php echo JText::_( 'Specific Icon 2' ); ?>:
				</label>
			</td>
			<td valign="middle">
				<input class="text_area" type="text" name="image_filename_spec2" id="image_filename_spec2" value="<?php echo $this->phocadownload->image_filename_spec2; ?>" size="32" maxlength="250" />
			</td>
			<td align="left" valign="middle">
				<div class="button2-left" style="display:inline">
					<div class="<?php echo $this->buttonicon->name; ?>">
						<a class="<?php echo $this->buttonicon->modalname; ?>" title="<?php echo $this->buttonicon->text; ?>" href="<?php echo 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager&amp;manager=iconspec2&amp;tmpl=component'; ?>" rel="<?php echo $this->buttonicon->options; ?>"  ><?php echo $this->buttonicon->text; ?></a>
					</div>
				</div>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="directlink">
					<?php echo JText::_( 'Direct Link' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
			
			<?php
			if ($this->phocadownload->directlink == 1) {
				$checkedDirectLink = 'checked="checked"';
			} else {
				$checkedDirectLink = '';
			}
			?>
				<input class="text_area" type="checkbox" name="directlink" id="directlink" <?php echo $checkedDirectLink ?> />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="version">
					<?php echo JText::_( 'Version' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="version" id="version" value="<?php echo $this->phocadownload->version; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="date">
					<?php echo JText::_( 'Date' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<?php echo JHTML::_('calendar', $this->phocadownload->date, 'date', 'date', "%Y-%m-%d", array('class'=>'inputbox', 'size'=>'32',  'maxlength'=>'45')); ?>
			</td>
		</tr>
		
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="publish_up">
					<?php echo JText::_( 'PHOCADOWNLOAD_START_PUBLISHING' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<?php echo JHTML::_('calendar', $this->phocadownload->publish_up, 'publish_up', 'publish_up', "%Y-%m-%d", array('class'=>'inputbox', 'size'=>'32',  'maxlength'=>'45')); ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="publish_down">
					<?php echo JText::_( 'PHOCADOWNLOAD_FINISH_PUBLISHING' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<?php echo JHTML::_('calendar', $this->phocadownload->publish_down, 'publish_down', 'publish_down', "%Y-%m-%d", array('class'=>'inputbox', 'size'=>'32',  'maxlength'=>'45')); ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="author">
					<?php echo JText::_( 'Author' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="author" id="author" value="<?php echo $this->phocadownload->author; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="author_email">
					<?php echo JText::_( 'Author Email' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="author_email" id="author_email" value="<?php echo $this->phocadownload->author_email; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="author_url">
					<?php echo JText::_( 'Author URL' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="author_url" id="author_url" value="<?php echo $this->phocadownload->author_url; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="license">
					<?php echo JText::_( 'License' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="license" id="license" value="<?php echo $this->phocadownload->license; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="license_url">
					<?php echo JText::_( 'License URL' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="license_url" id="license_url" value="<?php echo $this->phocadownload->license_url; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="confirm_license">
					<?php echo JText::_( 'Confirm License' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<?php echo $this->lists['confirm_license']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="link_external">
					<?php echo JText::_( 'External URL Link' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="link_external" id="link_external" value="<?php echo $this->phocadownload->link_external; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="hits">
					<?php echo JText::_( 'Downloads' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
				<input class="text_area" type="text" name="hits" id="hits" value="<?php echo $this->phocadownload->hits; ?>" size="32" maxlength="250" />
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="ordering">
					<?php echo JText::_( 'Ordering' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" class="key">
				<label for="access">
					<?php echo JText::_( 'Access Level' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['access']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" align="right" class="key">
				<label for="unaccessible_file">
					<?php echo JText::_( 'Display Unaccessible File' ); ?>:
				</label>
			</td>
			<td colspan="2" valign="middle">
			
			<?php
			if ($this->phocadownload->unaccessible_file == 1) {
				$checkedUnaccessibleFile = 'checked="checked"';
			} else {
				$checkedUnaccessibleFile = '';
			}
			?>
				<input class="text_area" type="checkbox" name="unaccessible_file" id="unaccessible_file" <?php echo $checkedUnaccessibleFile ?> />
			</td>
		</tr>
		
	</table>
	</fieldset>
	
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Description' ); ?></legend>

		<table class="admintable">
			<tr>
				<td valign="top" colspan="3">
					<?php
					// parameters : areaname, content, width, height, cols, rows, show xtd buttons
					echo $this->editor->display( 'description',  $this->phocadownload->description, '750', '300', '60', '20', array('pagebreak', 'readmore') ) ;
					?>
				</td>
			</tr>
			
			</table>
	</fieldset>
	
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'PHOCADOWNLOAD_META_TAGS' ); ?></legend>

		<table class="admintable">
		<tr>
			<td valign="middle" align="right" class="key">
				<label for="metadesc">
					<?php echo JText::_( 'PHOCADOWNLOAD_METADESC' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<textarea cols="46" rows="4" id="metadesc" name="metadesc"><?php echo $this->phocadownload->metadesc; ?></textarea>
			</td>
		</tr>
		
			<tr>
			<td valign="middle" align="right" class="key">
				<label for="metakey">
					<?php echo JText::_( 'PHOCADOWNLOAD_METAKEY' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<textarea cols="46" rows="4" id="metakey" name="metakey"><?php echo $this->phocadownload->metakey; ?></textarea>
			</td>
		</tr>
		</table>
	</fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_phocadownload" />
<input type="hidden" name="cid[]" value="<?php echo $this->phocadownload->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="phocadownload" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
