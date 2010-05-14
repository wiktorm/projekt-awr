<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
$editor =& JFactory::getEditor();
?>
<script language="javascript" type="text/javascript">
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
		/*if (form.title.value == ""){
			alert( "<?php echo JText::_( 'Component item must have a title', true ); ?>" );
		} else*/ if (form.sectionid.value == "-1"){
			alert( "<?php echo JText::_( 'You must select a section', true ); ?>" );
		} else if (form.catid.value == "-1" || form.catid.value == ""){
			alert( "<?php echo JText::_( 'You must select a category', true ); ?>" );
		} /*else if (form.filename.value == ""){
			alert( "<?php echo JText::_( 'You must select a filename', true ); ?>" );
		} */else {
			submitform( pressbutton );
		}
	}
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
</div>

<div class="clr"></div>

<input type="hidden" name="filename" id="filename" value="-" />
<input type="hidden" name="textonly" id="textonly" value="1" />
<input type="hidden" name="option" value="com_phocadownload" />
<input type="hidden" name="cid[]" value="<?php echo $this->phocadownload->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="phocadownload" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
