<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('behavior.tooltip'); ?>

<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton, parent_id) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			
		/*	if (form.parentid.value == "0"){
			alert( "<?php echo JText::_( 'You must select a category', true ); ?>" );
		} else */ if ( form.title.value == "" ) {
				alert("<?php echo JText::_( 'Category must have a title', true ); ?>");
			} else if (form.sectionid.value == "0"){
			alert( "<?php echo JText::_( 'You must select a section', true ); ?>" );
			}else {
				<?php
				echo $this->editor->save( 'description' ) ; ?>
				submitform(pressbutton);
			}
		}
		</script>

		<form action="index.php" method="post" name="adminForm">

		<div class="col60">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

					<table class="admintable">
					<tr>
						<td class="key">
							<label for="title" width="100">
								<?php echo JText::_( 'Title' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<input class="text_area" type="text" name="title" id="title" value="<?php echo $this->phocadownload->title; ?>" size="50" maxlength="255" title="<?php echo JText::_( 'A long name to be displayed in headings' ); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="alias">
								<?php echo JText::_( 'Alias' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<input class="text_area" type="text" name="alias" id="alias" value="<?php echo $this->phocadownload->alias; ?>" size="50" maxlength="255" title="<?php echo JText::_( 'A short name to appear in menus' ); ?>" />
						</td>
					</tr>
					
					<tr>
						<td valign="top" align="right" class="key">
							<label for="sectionid">
								<?php echo JText::_( 'Section' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<?php echo $this->lists['sectionid'];; ?>
						</td>
					</tr>
					
					<tr>
						<td width="120" class="key">
							<?php echo JText::_( 'Published' ); ?>:
						</td>
						<td>
							<?php echo $this->lists['published']; ?>
						</td>
					</tr>
				
					<tr>
						<td class="key">
							<label for="ordering">
								<?php echo JText::_( 'Ordering' ); ?>:
							</label>
						</td>
						<td colspan="2">
							<?php echo $this->lists['ordering']; ?>
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
				<td valign="top" class="key">
					<label for="uploaduserid">
						<?php echo JText::_( 'PHOCADOWNLOAD_UPLOAD_RIGHTS' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $this->lists['uploadusers']; ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="accessuserid">
						<?php echo JText::_( 'PHOCADOWNLOAD_ACCESS_RIGHTS' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $this->lists['accessusers']; ?>
				</td>
			</tr>
			
			
					
					
					<tr>
						<td class="key">
							<label for="image">
								<?php echo JText::_( 'Image' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->lists['image']; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="image_position">
								<?php echo JText::_( 'Image Position' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->lists['image_position']; ?>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
						<script language="javascript" type="text/javascript">
						if (document.forms.adminForm.image.options.value!=''){
							jsimg='../images/stories/' + getSelectedValue( 'adminForm', 'image' );
						} else {
							jsimg='../images/M_images/blank.png';
						}
						document.write('<img src=' + jsimg + ' name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_( 'Preview', true ); ?>" />');
						</script>
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
		<input type="hidden" name="controller" value="phocadownloadcat" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>

	
