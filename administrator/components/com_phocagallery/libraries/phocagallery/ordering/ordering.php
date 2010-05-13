<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Gallery
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocaGalleryOrdering
{
	/*
	 * Set Ordering String if Ordering is defined in Parameters
	 */
	function getOrderingString ($ordering) {
		switch ((int)$ordering) {
			case 2:
				$orderingOutput	= 'ordering DESC';
			break;
			
			case 3:
				$orderingOutput	= 'title ASC';
			break;
			
			case 4:
				$orderingOutput	= 'title DESC';
			break;
			
			case 5:
				$orderingOutput	= 'date ASC';
			break;
			
			case 6:
				$orderingOutput	= 'date DESC';
			break;
			
			case 7:
				$orderingOutput	= 'id ASC';
			break;
			
			case 8:
				$orderingOutput	= 'id DESC';
			break;
			
			// Random will be used e.g. ORDER BY RAND()
			/* if ($imageOrdering == 9) {
					$imageOrdering = ' ORDER BY RAND()'; 
				} else {
					$imageOrdering = ' ORDER BY '.PhocaGalleryOrdering::getOrderingString($image_ordering);
				}
			*/
			case 9:
				$orderingOutput = '';
			break;
			
			// Is not ordered by recursive function needs not to be used
			case 10:
				$orderingOutput = '';
			break;
		
			case 1:
			default:
				$orderingOutput = 'ordering ASC';
			break;
		}
		return $orderingOutput;
	}
}
?>