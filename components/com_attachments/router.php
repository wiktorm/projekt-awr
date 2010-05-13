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

defined('_JEXEC') or die('Restricted access');

/**
 * Build the route
 *
 * @param &object &$query the query to construct
 *
 * @return the segments
 */
function AttachmentsBuildRoute(&$query)
{
	// Syntax to upload an attachment:
	//		index.php?option=com_attachments&task=upload&article_id=44
	//		   --> index.php/attachments/upload/article/44
	// or:
	//		index.php?option=com_attachments&task=upload&parent_id=44
	//		   --> index.php/attachments/upload/parent/44

	// Syntax to delete an attachment:
	//		index.php?option=com_attachments&task=delete&id=4&article_id=44&from=article
	//			--> /attachments/delete/4/artcile/44
	//			--> /attachments/delete/4/article/44/from/article
	// or:
	//		index.php?option=com_attachments&task=delete&id=4&parent_id=44&from=parent
	//			--> /attachments/delete/4/parent/44
	//			--> /attachments/delete/4/parent/44/from/parent

	// Note: The 'from' clause indicates which view the item was called from (eg, article or frontpage)

	$segments = array();

	$task = $query['task'];

	if ( isset($query['task']) ) {
		$segments[] = $task;
		unset($query['task']);
		}

	if ( isset($query['id']) ) {
		if ( $task == 'update' ) {
			$segments[] = 'id';
			}
		$segments[] = $query['id'];
		unset($query['id']);
		}

	if ( isset($query['article_id']) ) {
		$segments[] = 'article';
		$segments[] = $query['article_id'];
		unset($query['article_id']);
		}

	if ( isset($query['parent_id']) ) {
		$segments[] = 'parent';
		$segments[] = $query['parent_id'];
		unset($query['parent_id']);
		}

	if ( isset($query['parent_type']) ) {
		$segments[] = 'parent_type';
		$segments[] = $query['parent_type'];
		unset($query['parent_type']);
		}

	if ( isset($query['controller']) ) {
		$segments[] = 'controller';
		$segments[] = $query['controller'];
		unset($query['controller']);
		}

	if ( isset($query['uri']) ) {
		$segments[] = 'uri';
		$segments[] = $query['uri'];
		unset($query['uri']);
		}

	if ( isset($query['update']) ) {
		$segments[] = 'update';
		$segments[] = $query['update'];
		unset($query['update']);
		}

	if ( isset($query['from']) ) {
		$segments[] = 'from';
		$segments[] = $query['from'];
		unset($query['from']);
		}

	if ( isset($query['tmpl']) ) {
		$segments[] = 'tmpl';
		$segments[] = $query['tmpl'];
		unset($query['tmpl']);
		}

	return $segments;
}

/**
 * Parse the route
 *
 * @param array $segments
 *
 * @return the variables parsed from the route
 */
function AttachmentsParseRoute($segments)
{
	$vars = array();

	// NOTE: The task=taskname pair must ALWAYS on the right after opton=com_attachments.
	//		 if a controller is specified, it must appear later in the URL.
	
	$task = $segments[0];
	$vars['task'] = $task;
	$i = 1;

	// Handle the the main keyword clause that have an id immediately following them
	if ( ($task == 'delete') OR ($task == 'delete_warning') OR ($task == 'download') )	{
		$vars['id'] = $segments[$i];
		$i = 2;
		}

	// Handle the other clauses
	while ( $i < count($segments) ) {

		// Look for article IDs
		if ( ($segments[$i] == 'article') AND ($segments[$i-1] != 'from') ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'article' without a following article ID!<br>";
				exit;
				}
			$vars['article_id'] = $segments[$i+1];
			$i++;
			}

		// Look for parent ID clause
		if ( (($segments[$i] == 'parent') OR ($segments[$i] == 'parent_id')) AND ($segments[$i-1] != 'from') ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'parent' without a following parent ID!<br>";
				exit;
				}
			$vars['parent_id'] = $segments[$i+1];
			$i++;
			}

		// Look for 'parent_type' clause
		if ( $segments[$i] == 'parent_type' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'parent_type' without the actual type!<br>";
				exit;
				}
			$vars['parent_type'] = $segments[$i+1];
			$i++;
			}

		// Look for 'controller' clause
		if ( $segments[$i] == 'controller' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'controller' without controller type!<br>";
				exit;
				}
			$vars['controller'] = $segments[$i+1];
			$i++;
			}

		// Look for 'id' clause
		if ( $segments[$i] == 'id' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'id' without any info!<br>";
				exit;
				}
			$vars['id'] = $segments[$i+1];
			}

		// Look for 'uri' clause
		if ( $segments[$i] == 'uri' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'uri' without any info!<br>";
				exit;
				}
			$vars['uri'] = $segments[$i+1];
			$i++;
			}

		// Look for 'update' clause
		if ( $segments[$i] == 'update' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'update' without any info!<br>";
				exit;
				}
			$vars['update'] = $segments[$i+1];
			$i++;
			}

		// Look for 'from' clause
		if ( $segments[$i] == 'from' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'from' without any info!<br>";
				exit;
				}
			$vars['from'] = $segments[$i+1];
			$i++;
			}

		// Look for 'tmpl' clause
		if ( $segments[$i] == 'tmpl' ) {
			if ( $i+1 >= count($segments) ) {
				echo "<br>Error in AttachmentsParseRoute:  Found 'tmpl' without any template name!<br>";
				exit;
				}
			$vars['tmpl'] = $segments[$i+1];
			$i++;
			}

		$i++;
		}

	return $vars;
}


// Local Variables:
// tab-width: 4
// End:

?>
