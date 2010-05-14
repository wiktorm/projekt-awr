<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'phocadownload.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'route.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownload.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocauserstat.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocadownload'.DS.'helpers'.DS.'phocadownloadupload.php' );
// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}
// Create the controller
$classname    = 'PhocaDownloadController'.ucfirst($controller);
$controller   = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
?>