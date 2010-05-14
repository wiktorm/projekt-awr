<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
?>
<script type="text/javascript">
function insertLink() {
	
	var urlOutput;
	var url = document.getElementById("url").value;
	if (url != '' ) {
		urlOutput = "|url="+url;
	}

	if (urlOutput != '' && urlOutput) {
		var tag = "{phocadownload view=youtube"+urlOutput+"}";
		window.parent.jInsertEditorText(tag, '<?php echo $this->tmpl['ename']; ?>');
		window.parent.document.getElementById('sbox-window').close();
		return false;
	} else {
		alert("<?php echo JText::_( 'PHOCADOWNLOAD_WARNING_SET_YOUTUBE_URL', true ); ?>");
		return false;
	}
}
</script>
<div id="phocadownload-links">
<fieldset class="adminform">
<legend><?php echo JText::_( 'PHOCADOWNLOAD_YOUTUBE_VIDEO' ); ?></legend>
<form name="adminFormLink" id="adminFormLink">
<table class="admintable" width="100%">
	
	
	<tr >
		<td class="key" align="right" >
			<label for="url">
				<?php echo JText::_( 'PHOCADOWNLOAD_YOUTUBE_URL' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="url" name="url" />
		</td>
	</tr>

	
	<tr>
		<td>&nbsp;</td>
		<td align="right"><button onclick="insertLink();return false;"><?php echo JText::_( 'Insert Link' ); ?></button></td>
	</tr>
</table>
</form>

</fieldset>
<div style="text-align:right;margin:20px 5px;"><span class="icon-16-edb-back"><a style="text-decoration:underline" href="<?php echo $this->tmpl['backlink'];?>"><?php echo JText::_('Back')?></a></span></div>
</div>