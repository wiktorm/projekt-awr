<?php
/*********** XML PARAMETERS AND VALUES ************/
$xml_item = "component";// component | template
$xml_file = "phocadownload.xml";		
$xml_name = "PhocaDownload";
$xml_creation_date = "12/05/2010";
$xml_author = "Jan Pavelka (www.phoca.cz)";
$xml_author_email = "";
$xml_author_url = "www.phoca.cz";
$xml_copyright = "Jan Pavelka";
$xml_license = "GNU/GPL";
$xml_version = "1.3.5";
$xml_description = "Phoca Download";
$xml_copy_file = 1;//Copy other files in to administration area (only for development), ./front, ./language, ./other

$xml_menu = array (0 => "Phoca Download", 1 => "option=com_phocadownload", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu.png");
$xml_submenu[0] = array (0 => "Control Panel", 1 => "option=com_phocadownload", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-control-panel.png");
$xml_submenu[1] = array (0 => "Files", 1 => "option=com_phocadownload&view=phocadownloads", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-files.png");
$xml_submenu[2] = array (0 => "Sections", 1 => "option=com_phocadownload&view=phocadownloadsecs", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-section.png");
$xml_submenu[3] = array (0 => "Categories", 1 => "option=com_phocadownload&view=phocadownloadcats", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-category.png");
$xml_submenu[4] = array (0 => "Licenses", 1 => "option=com_phocadownload&view=phocadownloadlics", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-lic.png");
$xml_submenu[5] = array (0 => "Settings", 1 => "option=com_phocadownload&view=phocadownloadset", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-settings.png");
$xml_submenu[6] = array (0 => "Statistics", 1 => "option=com_phocadownload&view=phocadownloadstat", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-stat.png");
$xml_submenu[7] = array (0 => "PHOCADOWNLOAD_USERS", 1 => "option=com_phocadownload&view=phocadownloadusers", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-users.png");
$xml_submenu[8] = array (0 => "Info", 1 => "option=com_phocadownload&view=phocadownloadinfo", 2 => "components/com_phocadownload/assets/images/icon-16-pdl-menu-info.png");

$xml_install_file = 'install.phocadownload.php'; 
$xml_uninstall_file = 'uninstall.phocadownload.php';
/*********** XML PARAMETERS AND VALUES ************/
?>