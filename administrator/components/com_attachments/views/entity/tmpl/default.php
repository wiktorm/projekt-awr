<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2010 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

global $mainframe, $option;

// Add the plugins stylesheet to style the list of attachments
$document =&  JFactory::getDocument();
$document->addStyleSheet(
	$mainframe->getSiteURL() . 'plugins/content/attachments.css',
	'text/css', null, array() );
$document->addStyleSheet(
	$mainframe->getSiteURL() . 'administrator/components/com_attachments/attachments.css',
	'text/css', null, array() );

//global $mainframe;
$lang =& JFactory::getLanguage();
if ( $lang->isRTL() ) {
	$document->addStyleSheet( $mainframe->getSiteURL() . 'plugins/content/attachments_rtl.css',
							  'text/css', null, array() );
	}

$lists = $this->lists;

?>
<form class="attachmentsBackend" enctype="multipart/form-data"
	  name="adminForm" id="adminForm"
	  action="<?php echo $this->post_url ?>" method="post">
<div class="attachments_filter">
	<?php echo JText::_( 'FILTER' ); ?>:
	<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>"
	   class="text_area" onchange="document.adminForm.submit();" />
	<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
	<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('RESET') ?></button>
</div>

	<fieldset class="adminform">
	<legend><?php echo JText::sprintf('SELECT_ENTITY_S', JText::_($this->entity_name)) ?></legend>
	<table class="adminlist" cellspacing="1">
	<thead>
	   <tr>
		 <th width="5"><?php echo JText::_( 'Num' ); ?></th>
		 <th class="title">
			<?php echo JHTML::_('grid.sort', 'Title', 'title', @$lists['order_Dir'], @$lists['order'] ); ?>
		 </th>
		 <th width="2%" class="title">
			<?php echo JHTML::_('grid.sort', 'ID', 'id', @$lists['order_Dir'], @$lists['order'] ); ?>
		 </th>
	   </tr>
	</thead>
	<tbody>

<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$item =& $this->items[$i];
		?>
		<tr class="<?php echo "row$k" ?>">
		   <td><?php echo $i ?></td>
		   <td>
			   <a style="cursor: pointer;" onclick="window.parent.jSelectArticle('<?php echo $item->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$item->title); ?>', '<?php echo JRequest::getVar('object'); ?>');">
			   <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></a>
		   </td>
		   <td><?php echo $item->id; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
		}
?>
	</tbody>
	</table>
	</fieldset>
	<input type="hidden" name="parent_type" value="<?php echo $this->parent_type ?>" />
	<input type="hidden" name="parent_entity" value="<?php echo $this->parent_entity ?>" />
	<input type="hidden" name="option" value="<?php echo $option;?>" />
	<input type="hidden" name="task" value="selectEntity" />
	<input type="hidden" name="from" value="<?php echo $this->from; ?>" />

	<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />

	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php

// Local Variables:
// tab-width: 4
// End:

?>
