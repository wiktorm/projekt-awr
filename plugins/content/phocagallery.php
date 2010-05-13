<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @plugin Phoca Gallery
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
if (!JComponentHelper::isEnabled('com_phocagallery', true)) {
	return JError::raiseError(JText::_('Phoca Gallery Error'), JText::_('Phoca Gallery is not installed on your system'));
}
if (! class_exists('PhocaGalleryLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocagallery'.DS.'libraries'.DS.'loader.php');
}
phocagalleryimport('phocagallery.path.path');
phocagalleryimport('phocagallery.path.route');
phocagalleryimport('phocagallery.library.library');
phocagalleryimport('phocagallery.text.text');
phocagalleryimport('phocagallery.access.access');
phocagalleryimport('phocagallery.file.file');
phocagalleryimport('phocagallery.file.filethumbnail');
phocagalleryimport('phocagallery.image.image');
phocagalleryimport('phocagallery.image.imagefront');
phocagalleryimport('phocagallery.render.renderfront');
phocagalleryimport('phocagallery.render.renderadmin');
phocagalleryimport('phocagallery.ordering.ordering');
phocagalleryimport('phocagallery.picasa.picasa');

class plgContentPhocaGallery extends JPlugin
{	
	var $_plugin_number	= 0;
	
	function _setPluginNumber() {
		$this->_plugin_number = (int)$this->_plugin_number + 1;
	}
	
	function plgContentPhocaGallery( &$subject, $params ) {
        parent::__construct( $subject, $params  );
    }

	function onPrepareContent( &$article, &$params, $limitstart )
	{
		$user		= &JFactory::getUser();
		$gid 		= $user->get('aid', 0);
		$db 		= &JFactory::getDBO();
		$menu 		= &JSite::getMenu();
		$document	= &JFactory::getDocument();
		$path 		= PhocaGalleryPath::getPath();
		
		// PARAMS - direct from Phoca Gallery Global configuration
		$component 		= 'com_phocagallery';
		$table 			=& JTable::getInstance('component');
		$table->loadByOption( $component );
		$paramsC	 	= new JParameter( $table->params );
		
		// LIBRARY
		$library 								= &PhocaGalleryLibrary::getLibrary();
		$libraries['pg-css-sbox-plugin'] 		= $library->getLibrary('pg-css-sbox-plugin');
		$libraries['pg-css-pg-plugin'] 			= $library->getLibrary('pg-css-pg-plugin');
		$libraries['pg-css-ie'] 				= $library->getLibrary('pg-css-ie');
		$libraries['pg-group-shadowbox']		= $library->getLibrary('pg-group-shadowbox');
		$libraries['pg-group-highslide']		= $library->getLibrary('pg-group-highslide');
		$libraries['pg-group-highslide-slideshow']	= $library->getLibrary('pg-group-highslide-slideshow');
		$libraries['pg-overlib-group']			= $library->getLibrary('pg-overlib-group');
		$libraries['pg-group-jak-pl']			= $library->getLibrary('pg-group-jak-pl');
		
		// PicLens CSS and JS will be loaded only one time in the site (pg-pl-piclens)
		// BUT PicLens Category will be loaded everytime new category should be displayed on the site
		$libraries['pg-pl-piclens']	= $library->getLibrary('pg-pl-piclens');
		
		
		// Start Plugin
		$regex_one		= '/({phocagallery\s*)(.*?)(})/si';
		$regex_all		= '/{phocagallery\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$article->text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		$cssPgPlugin	= '';
		$cssSbox		= '';
		
	// Start if count_matches
	if ($count_matches != 0) {
		
		// Start CSS
		$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/phocagallery.css');
	
		for($i = 0; $i < $count_matches; $i++) {
			
			$this->_setPluginNumber();
			// Plugin variables
			$view 					= '';
			$catid					= 0;
			$imageid				= 0;
			$imagerandom			= 0;
			$image_background_shadow	= $paramsC->get( 'image_background_shadow', 'shadow1');
			$limitstart				= 0;
			$limitcount				= 0;
			$switch_width			= $paramsC->get( 'switch_width', 640);
			$switch_height			= $paramsC->get( 'switch_height', 480);
			$basic_image_id			= $paramsC->get( 'switch_image', 0);
			$enable_switch			= 0;
			
			$display_name 			= $paramsC->get( 'display_name', 1);
			$display_icon_detail 	= $paramsC->get( 'display_icon_detail', 1);
			$display_icon_download 	= $paramsC->get( 'display_icon_download', 1);
			$tmpl['detailwindow'] 	= $paramsC->get( 'detail_window', 0);
			$detail_buttons			= $paramsC->get( 'detail_buttons', 1);
			$hide_categories		= $paramsC->get( 'hide_categories', '');
			
			$namefontsize			= $paramsC->get( 'font_size_name', 12);
			$namenumchar			= $paramsC->get( 'char_length_name', 11);
			
			$display_description	= $paramsC->get( 'display_description_detail', 0);
			$description_height		= $paramsC->get( 'description_detail_height', 16);
			$category_box_space		= $paramsC->get( 'category_box_space', 0);
			
			$margin_box 			= $paramsC->get( 'margin_box', 5);
			$padding_box 			= $paramsC->get( 'padding_box', 5);
			
			// CSS
			$font_color 			= $paramsC->get( 'font_color', '#b36b00');
			$background_color 		= $paramsC->get( 'background_color', '#fcfcfc');
			$background_color_hover = $paramsC->get( 'background_color_hover', '#f5f5f5');
			$image_background_color = $paramsC->get( 'image_background_color', '#f5f5f5');
			$border_color 			= $paramsC->get( 'border_color', '#e8e8e8');
			$border_color_hover 	= $paramsC->get( 'border_color_hover', '#b36b00');
			
			$highslide_class		= $paramsC->get( 'highslide_class', 'rounded-white');
			$highslide_opacity		= $paramsC->get( 'highslide_opacity', 0);
			$highslide_outline_type	= $paramsC->get( 'highslide_outline_type', 'rounded-white');
			$highslide_fullimg		= $paramsC->get( 'highslide_fullimg', 0);
			$highslide_slideshow	= $paramsC->get( 'highslide_slideshow', 1);
			$highslide_close_button	= $paramsC->get( 'highslide_close_button', 0);
			$tmpl['displayratingimg']	= $paramsC->get( 'display_img_rating', 0);
			
			$tmpl['jakslideshowdelay']			= $paramsC->get( 'jak_slideshow_delay', 5);
			$tmpl['jakorientation']				= $paramsC->get( 'jak_orientation', 'none');
			$tmpl['jakdescription']				= $paramsC->get( 'jak_description', 1);
			$tmpl['jakdescriptionheight']		= $paramsC->get( 'jak_description_height', 0);
			$tmpl['imageordering']				= $paramsC->get( 'image_ordering', 9);
			$tmpl['highslidedescription']		= $paramsC->get( 'highslide_description', 0 );
			$tmpl['pluginlink']					= 0;
			$tmpl['jakdatajs'] 					= array();
			$minimum_box_width 					= '';
			
			// Component settings - some behaviour is set in component and cannot be set in plugin
			// but plugin needs to accept it 
			$tmplCom['displayicondownload']		= $paramsC->get( 'display_icon_download', 0 );
			
			$plugin_type			= 0;
			$padding_mosaic			= 3;
			$float					= '';
			$enable_piclens			= $paramsC->get( 'enable_piclens', 0);
			$enable_overlib			= $paramsC->get( 'enable_oberlib', 0);
			
			// Image categories
			$img_cat				= 1;
			$img_cat_size			= 'small';
			
			// Get plugin parameters
			$phocagallery	= $matches[0][$i][0];
			preg_match($regex_one,$phocagallery,$phocagallery_parts);
			$parts			= explode("|", $phocagallery_parts[2]);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");

			foreach($parts as $key => $value) {
				$values = explode("=", $value, 2);
				
				foreach ($values_replace as $key2 => $values2) {
					$values = preg_replace($values2, '', $values);
				}
				
				// Get plugin parameters from article
					 if($values[0]=='view')				{$view					= $values[1];}
				else if($values[0]=='categoryid')		{$catid					= $values[1];}
				else if($values[0]=='imageid')			{$imageid				= $values[1];}
				else if($values[0]=='imagerandom')		{$imagerandom			= $values[1];}
				else if($values[0]=='imageshadow')		{$image_background_shadow			= $values[1];}
				else if($values[0]=='limitstart')		{$limitstart			= $values[1];}
				else if($values[0]=='limitcount')		{$limitcount			= $values[1];}
				else if($values[0]=='detail')			{$tmpl['detailwindow']			= $values[1];}
				else if($values[0]=='displayname')		{$display_name			= $values[1];}
				else if($values[0]=='displaydetail')	{$display_icon_detail		= $values[1];}
				else if($values[0]=='displaydownload')	{$display_icon_download	= $values[1];}
				else if($values[0]=='displaybuttons')	{$detail_buttons		= $values[1];}
			//	else if($values[0]=='displayratingimg')	{$tmpl['displayratingimg']	= $values[1];}
				
				else if($values[0]=='namefontsize')		{$namefontsize			= $values[1];}
				else if($values[0]=='namenumchar')		{$namenumchar			= $values[1];}
				
				else if($values[0]=='displaydescription'){$display_description	= $values[1];}
				else if($values[0]=='descriptionheight'){$description_height	= $values[1];}
				else if($values[0]=='hidecategories')	{$hide_categories		= $values[1];}
				else if($values[0]=='boxspace')			{$category_box_space	= $values[1];}
				
				// CSS
				else if($values[0]=='fontcolor')		{$font_color				= $values[1];}
				else if($values[0]=='bgcolor')			{$background_color			= $values[1];}
				else if($values[0]=='bgcolorhover')		{$background_color_hover	= $values[1];}
				else if($values[0]=='imagebgcolor')		{$image_background_color	= $values[1];}
				else if($values[0]=='bordercolor')		{$border_color				= $values[1];}
				else if($values[0]=='bordercolorhover')	{$border_color_hover		= $values[1];}
				
				else if($values[0]=='hsclass')			{$highslide_class			= $values[1];}
				else if($values[0]=='hsopacity')		{$highslide_opacity			= $values[1];}
				else if($values[0]=='hsoutlinetype')	{$highslide_outline_type	= $values[1];}
				else if($values[0]=='hsfullimg')		{$highslide_fullimg			= $values[1];}
				else if($values[0]=='hsslideshow')		{$highslide_slideshow		= $values[1];}
				else if($values[0]=='hsclosebutton')	{$highslide_close_button	= $values[1];}
				
				else if($values[0]=='float')			{$float	= $values[1];}
				
				else if($values[0]=='jakslideshowdelay')	{$tmpl['jakslideshowdelay']		= $values[1];}
				else if($values[0]=='jakorientation')		{$tmpl['jakorientation']		= $values[1];}
				else if($values[0]=='jakdescription')		{$tmpl['jakdescription']		= $values[1];}
				else if($values[0]=='jakdescriptionheight')	{$tmpl['jakdescriptionheight']	= $values[1];}
				else if($values[0]=='imageordering')		{$tmpl['imageordering']			= $values[1];}
				else if($values[0]=='pluginlink')			{$tmpl['pluginlink']			= $values[1];}
				else if($values[0]=='highslidedescription')	{$tmpl['highslidedescription']	= $values[1];}
				else if($values[0]=='type')					{$plugin_type					= $values[1];}
				else if($values[0]=='paddingmosaic')		{$padding_mosaic				= $values[1];}
			
				else if($values[0]=='minboxwidth')			{$minimum_box_width			= $values[1];}
				//Image categories
				else if($values[0]=='imagecategories')		{$img_cat				= $values[1];}
				else if($values[0]=='imagecategoriessize')	{$img_cat_size			= $values[1];}
				else if($values[0]=='switchwidth')			{$switch_width			= $values[1];}
				else if($values[0]=='switchheight')			{$switch_height			= $values[1];}
				else if($values[0]=='basicimageid')			{$basic_image_id		= $values[1];}
				else if($values[0]=='enableswitch')			{$enable_switch			= $values[1];}
				
				else if($values[0]=='piclens')				{$enable_piclens				= $values[1];}
				else if($values[0]=='overlib')				{$enable_overlib				= $values[1];}
			}
			
			// If Module link is to category or categories, the detail window method needs to be set to no popup
			if ((int)$tmpl['pluginlink'] > 0) {
				$tmpl['detailwindow'] = 7;
			}
			// Every loop of plugin has own number
			// Add custom CSS for every image (every image can have other CSS, Hover doesn't work in IE6)
			$iCss = $this->_plugin_number;
			$cssPgPlugin	.= " .pgplugin".$iCss." {border:1px solid $border_color ; background: $background_color ;}\n"
							." .pgplugin".$iCss.":hover, .pgplugin".$i.".hover {border:1px solid $border_color_hover ; background: $background_color_hover ;}\n";
								
			
			// PARAMS - direct from Phoca Gallery Global configuration
			$component 		= 'com_phocagallery';
			$table 			=& JTable::getInstance('component');
			$table->loadByOption( $component );
			$paramsC	 	= new JParameter( $table->params );
			
			$tmpl['formaticon'] 		= $paramsC->get( 'icon_format', 'gif' );
			
			$medium_image_width 		= $paramsC->get( 'medium_image_width', 100 );
			$medium_image_height 		= $paramsC->get( 'medium_image_height', 100 );
			$popup_width 				= $paramsC->get( 'front_modal_box_width', 680 );
			$popup_height 				= $paramsC->get( 'front_modal_box_height', 560 );
			$small_image_width 			= $paramsC->get( 'small_image_width', 50 );
			$small_image_height 		= $paramsC->get( 'small_image_height', 50 );
			$large_image_width 			= $paramsC->get( 'large_image_width', 640 );
			$large_image_height 		= $paramsC->get( 'large_image_height', 480 );
			

			
			// Correct Picasa Images - get Info
			switch($img_cat_size) {
				// medium
				case 1:
				case 5:
					$tmpl['picasa_correct_width']	= (int)$paramsC->get( 'medium_image_width', 100 );	
					$tmpl['picasa_correct_height']	= (int)$paramsC->get( 'medium_image_height', 100 );
				break;
				
				case 0:
				case 4:
				default:
					$tmpl['picasa_correct_width']	= (int)$paramsC->get( 'small_image_width', 50 );	
					$tmpl['picasa_correct_height']	= (int)$paramsC->get( 'small_image_height', 50 );
				break;
			}
			
			if ($plugin_type == 1) {
				$imgSize	= 'small';
			} else if ($plugin_type == 2) {
				$imgSize	= 'large';
			} else {
				$imgSize	= 'medium';
			}
			
			if ($display_description == 1) {
				$popup_height	= $popup_height + $description_height;
			}
			
			// Detail buttons in detail view
			if ($detail_buttons != 1) {
				$popup_height	= $popup_height - 45;
			}
			$popup_height_rating = $popup_height;
			if ($tmpl['displayratingimg'] == 1) {
				$popup_height_rating	= $popup_height + 35;
			}
			
			$modal_box_overlay_color 	= $paramsC->get( 'modal_box_overlay_color','#000000' );
			$modal_box_overlay_opacity 	= $paramsC->get( 'modal_box_overlay_opacity', 0.3 );
			$modal_box_border_color 	= $paramsC->get( 'modal_box_border_color', '#6b6b6b' );
			$modal_box_border_width 	= $paramsC->get( 'modal_box_border_width', 2 );
			
			$tmpl['olbgcolor']				= $paramsC->get( 'ol_bg_color', '#666666' );
			$tmpl['olfgcolor']				= $paramsC->get( 'ol_fg_color', '#f6f6f6' );
			$tmpl['oltfcolor']				= $paramsC->get( 'ol_tf_color', '#000000' );
			$tmpl['olcfcolor']				= $paramsC->get( 'ol_cf_color', '#ffffff' );
			$tmpl['overliboverlayopacity']	= $paramsC->get( 'overlib_overlay_opacity', 0.7 );
			
			
			
			// Window
			// =======================================================
			// DIFFERENT METHODS OF DISPLAYING THE DETAIL VIEW
			// =======================================================
			
			// MODAL - will be displayed in case e.g. highslide or shadowbox too, because in there are more links 
			JHTML::_('behavior.modal', 'a.modal-button');

			if ($cssSbox == '') {
				$cssSbox .= " #sbox-window {background-color:".$modal_box_border_color.";padding:".$modal_box_border_width."px} \n"
				." #sbox-overlay {background-color:".$modal_box_overlay_color.";} \n";
			}
			
			// BUTTON (IMAGE - standard, modal, shadowbox)
			$button = new JObject();
			$button->set('name', 'image');

			// BUTTON (ICON - standard, modal, shadowbox)
			$button2 = new JObject();
			$button2->set('name', 'icon');

			// BUTTON OTHER (geotagging, downloadlink, ...)
			$buttonOther = new JObject();
			$buttonOther->set('name', 'other');

			$tmpl ['highslideonclick']	= '';// for using with highslide
			
			// Random Number - because of more plugins on the site
			$randName	= 'PhocaGalleryPl' . $iCss;
			$randName2	= 'PhocaGalleryPl2' . $iCss;
			
			// -------------------------------------------------------
			// STANDARD POPUP
			// -------------------------------------------------------

			if ($tmpl['detailwindow'] == 1) {
				
				$button->set('methodname', 'js-button');
				$button->set('options', "window.open(this.href,'win2','width=".$popup_width.",height=".$popup_height.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
				$button->set('optionsrating', "window.open(this.href,'win2','width=".$popup_width.",height=".$popup_height_rating.",scrollbars=yes,menubar=no,resizable=yes'); return false;");
						
				$button2->methodname 		= &$button->methodname;
				$button2->options 			= &$button->options;
				$buttonOther->methodname  	= &$button->methodname;
				$buttonOther->options 		= &$button->options;
				$buttonOther->optionsrating = &$button->optionsrating;
				
			}
			
			// -------------------------------------------------------
			// MODAL BOX
			// -------------------------------------------------------

			else if ($tmpl['detailwindow'] == 0 || $tmpl['detailwindow'] == 2) { 
				
				// Button
				$button->set('modal', true);
				$button->set('methodname', 'modal-button');
				
				$button2->modal 			= &$button->modal;
				$button2->methodname 		= &$button->methodname;
				$buttonOther->modal 		= &$button->modal;
				$buttonOther->methodname  	= &$button->methodname;
				
				// Modal - Image only
				if ($tmpl['detailwindow'] == 2) {
					
					$button->set('options', "{handler: 'image', size: {x: 200, y: 150}, overlayOpacity: ".$modal_box_overlay_opacity."}");
					$button2->options 		= &$button->options;
					$buttonOther->set('options', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
					$buttonOther->set('optionsrating', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height_rating."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
				
				// Modal - Iframe 			
				} else {
					$button->set('options', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
					$buttonOther->set('optionsrating', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height_rating."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
				
					$button2->options 		= &$button->options;
					$buttonOther->options  	= &$button->options;
				
				}
			}
			
			// -------------------------------------------------------
			// SHADOW BOX
			// -------------------------------------------------------

			else if ($tmpl['detailwindow'] == 3) {

				
				
				$sb_slideshow_delay			= $paramsC->get( 'sb_slideshow_delay', 5 );
				$sb_lang					= $paramsC->get( 'sb_lang', 'en' );
				
				$button->set('methodname', 'shadowbox-button-rim');
				$button->set('options', "shadowbox[".$randName."];options={slideshowDelay:".$sb_slideshow_delay."}");
					
				$button2->methodname 		= &$button->methodname;
				$button2->set('options', "shadowbox[".$randName2."];options={slideshowDelay:".$sb_slideshow_delay."}");
				
				$buttonOther->set('modal', true);
				$buttonOther->set('methodname', 'modal-button');
				$buttonOther->set('options', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
				$buttonOther->set('optionsrating', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height_rating."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
				
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/shadowbox/shadowbox.js');	
					
				if ( $libraries['pg-group-shadowbox']->value == 0 ) {
					$document->addCustomTag('<script type="text/javascript">
			Shadowbox.loadSkin("classic", "'.JURI::base(true).'/components/com_phocagallery/assets/js/shadowbox/src/skin");
			Shadowbox.loadLanguage("'.$sb_lang.'", "'.JURI::base(true).'/components/com_phocagallery/assets/js/shadowbox/src/lang");
			Shadowbox.loadPlayer(["img"], "'.JURI::base(true).'/components/com_phocagallery/assets/js/shadowbox/src/player");
			window.addEvent(\'domready\', function(){
                        Shadowbox.init()
                        });
			</script>');
					$library->setLibrary('pg-group-shadowbox', 1);
				}

			}
			
			// -------------------------------------------------------
			// HIGHSLIDE JS
			// -------------------------------------------------------

			else if ($tmpl['detailwindow'] == 4) {
				
				$button->set('methodname', 'highslide');
				$button2->methodname 		= &$button->methodname;
				$buttonOther->methodname 	= &$button->methodname;
				
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/highslide/highslide-full.js');
				$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/js/highslide/highslide.css');
						
				if ( $libraries['pg-group-highslide']->value == 0 ) {
					$document->addCustomTag( PhocaGalleryRenderFront::renderHighslideJSAll());
					$document->addCustomTag('<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="'.JURI::base(true).'/components/com_phocagallery/assets/js/highslide/highslide-ie6.css" /><![endif]-->');
			$library->setLibrary('pg-group-highslide', 1);
				}
				
				$document->addCustomTag( PhocaGalleryRenderFront::renderHighslideJS('pl', $popup_width, $popup_height, $highslide_outline_type, $highslide_opacity));
				$tmpl['highslideonclick'] = 'return hs.htmlExpand(this, phocaZoomPl )';
			}
			
			// -------------------------------------------------------
			// HIGHSLIDE JS IMAGE ONLY
			// -------------------------------------------------------

			else if ($tmpl['detailwindow'] == 5) {

				$button->set('methodname', 'highslide');
				$button2->methodname 		= &$button->methodname;
				$buttonOther->methodname 	= &$button->methodname;
				
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/highslide/highslide-full.js');
			$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/js/highslide/highslide.css');
				
				
				if ( $libraries['pg-group-highslide']->value == 0 ) {
					$document->addCustomTag( PhocaGalleryRenderFront::renderHighslideJSAll());
					$document->addCustomTag('<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="'.JURI::base(true).'/components/com_phocagallery/assets/js/highslide/highslide-ie6.css" /><![endif]-->');

					$library->setLibrary('pg-group-highslide', 1);
				}
				
				/* @deprecated	for each new plugin (with a new _plugin_number ) there has to be inserted another hs.addslideShow script with another slideshowGroup */
			/*	if ($libraries['pg-group-highslide-slideshow']->value == 0) {
					if((int)$highslide_slideshow > 0) {
						$library->setLibrary('pg-group-highslide-slideshow', 1);
					}
				} else {
					// if we have added the slideshow to plugin code
					// we cannot add it again
					$highslide_slideshow = 0;
				}*/
				
				
				/*
				$document->addCustomTag( PhocaGalleryRenderFront::renderHighslideJS('pl', $popup_width, $popup_height, $highslide_slideshow, $highslide_class, $highslide_outline_type, $highslide_opacity, $highslide_close_button));
				
				$tmpl['highslideonclick2']	= 'return hs.htmlExpand(this, phocaZoomPl )';
				//$tmpl['highslideonclick']	= 'return hs.expand(this, phocaImageRI )';
				$tmpl['highslideonclick']	= PhocaGalleryRenderFront::renderHighslideJSImage('pl', $highslide_class, $highslide_outline_type, $highslide_opacity, $highslide_fullimg);
				*/
				
			

				/* this would better use addScriptDeclaration, but this would need further changes (-> removing the <script> tags from the return value of renderHighslideJS) */
				$document->addCustomTag( PhocaGalleryRenderFront::renderHighslideJS('pl', $popup_width, $popup_height, $highslide_slideshow, $highslide_class, $highslide_outline_type, $highslide_opacity, $highslide_close_button, $this->_plugin_number));

				
				$tmpl['highslideonclick2']	= 'return hs.htmlExpand(this, phocaZoomPl )';
				//$tmpl['highslideonclick']	= 'return hs.expand(this, phocaImageRI )';
				$tmpl['highslideonclick']	= PhocaGalleryRenderFront::renderHighslideJSImage('pl', $highslide_class, $highslide_outline_type, $highslide_opacity, $highslide_fullimg, $this->_plugin_number);

			}
			// -------------------------------------------------------
			// JAK LIGHTBOX
			// -------------------------------------------------------

			else if ($tmpl['detailwindow'] == 6) {

				$button->set('methodname', 'jaklightbox');
				$button2->methodname 	= &$button->methodname;

				$buttonOther->set('modal', true);
				$buttonOther->set('methodname', 'modal-button');
				$buttonOther->set('options', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height."}, overlayOpacity: ".$modal_box_overlay_opacity."}");
				$buttonOther->set('optionsrating', "{handler: 'iframe', size: {x: ".$popup_width.", y: ".$popup_height_rating."}, overlayOpacity: ".$modal_box_overlay_opacity."}");


				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/jak/jak_compressed.js');
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/jak/lightbox_compressed.js');
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/jak/jak_slideshow.js');
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/jak/window_compressed.js');
				$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/js/jak/interpolator_compressed.js');		
				$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/js/jak/lightbox-slideshow.css');
				
				$lHeight 		= 472 + (int)$tmpl['jakdescriptionheight'];
				$lcHeight		= 10 + (int)$tmpl['jakdescriptionheight'];
				$customJakTag	= '';
				if ($tmpl['jakorientation'] == 'horizontal') {
					$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/js/jak/lightbox-horizontal.css');
				} else if ($tmpl['jakorientation'] == 'vertical'){
					$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/js/jak/lightbox-vertical.css');
					$customJakTag .= '.lightBox {height: '.$lHeight.'px;}'
									.'.lightBox .image-browser-caption { height: '.$lcHeight.'px;}';
				} else  {
					$document->addStyleSheet(JURI::base(true).'/components/com_phocagallery/assets/js/jak/lightbox-vertical.css');
					$customJakTag .= '.lightBox {height: '.$lHeight.'px;width:800px;}'
								.'.lightBox .image-browser-caption { height: '.$lcHeight.'px;}'
								.'.lightBox .image-browser-thumbs { display:none;}'
								.'.lightBox .image-browser-thumbs div.image-browser-thumb-box { display:none;}';
				}
				
				if ($customJakTag != '') {
					$document->addCustomTag("<style type=\"text/css\">\n". $customJakTag. "\n"."</style>");
				}
				
				//if ( $libraries['pg-group-jak-pl']->value == 0 ) {		
					$document->addCustomTag( PhocaGalleryRenderFront::renderJakJs($tmpl['jakslideshowdelay'], $tmpl['jakorientation'], 'optgjaksPl'.$randName));
				//	$library->setLibrary('pg-group-jak-pl', 1);
				//}
				
			}

			// -------------------------------------------------------
			// NO POPUP
			// -------------------------------------------------------

			else if ($tmpl['detailwindow'] == 7) {

				$button->set('methodname', 'no-popup');
				$button2->methodname 	= &$button->methodname;

				
				$buttonOther->set('modal', true);
				$buttonOther->set('methodname', 'no-popup');
				$buttonOther->set('options', "");
				$buttonOther->set('optionsrating', "");
				
			}

			$folderButton = new JObject();
			$folderButton->set('name', 'image');
			$folderButton->set('options', "");		
			
			
			
			// End open window parameters
			
			// ===============================
			// OUTPUT
			// ===============================
			$output	='';
			$output .= '<div class="phocagallery">' . "\n";
			
			
			//--------------------------
			// DISPLAYING OF CATEGORIES (link doesn't work if there is no menu link)
			//--------------------------
			
			
			$hideCat		= trim( $hide_categories );
			$hideCatArray	= explode( ',', $hide_categories );
			$hideCatSql		= '';
			if (is_array($hideCatArray)) {
				foreach ($hideCatArray as $value) {
					$hideCatSql .= ' AND cc.id != '. (int) trim($value) .' ';
				}
			}
			// by vogo
			$uniqueCatSql	= '';
			if ($catid > 0) {
				$uniqueCatSql	= ' AND cc.id = '. $catid .'';	
			}
			
			if ($view == 'categories') {
				//CATEGORIES
				$queryc = 'SELECT cc.*, a.catid, COUNT(a.id) AS numlinks,'
				. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as slug'
				. ' FROM #__phocagallery_categories AS cc'
				. ' LEFT JOIN #__phocagallery AS a ON a.catid = cc.id'
				. ' WHERE a.published = 1'
				. ' AND cc.published = 1'
				. ' AND cc.approved = 1'
				. ' AND a.approved = 1'
				. $hideCatSql
				. $uniqueCatSql
				. ' GROUP BY cc.id'
				. ' ORDER BY cc.ordering';

				//SUBCATEGORIES
				$querysc = 'SELECT cc.title AS text, cc.id AS value, cc.parent_id as parentid'
				. ' FROM #__phocagallery_categories AS cc'
				. ' WHERE cc.published = 1'
				. ' AND cc.approved = 1'
				. ' ORDER BY cc.ordering';

				$data_outcome 		= '';
				$data_outcome_array = '';
			
				$db->setQuery($queryc);
				$outcome_data = $db->loadObjectList();
			
				$db->setQuery($querysc);
				$outcome_subcategories = $db->loadObjectList();
			
				$tree = array();
				$text = '';
				$tree = PhocaGalleryRenderAdmin::CategoryTreeOption($outcome_subcategories, $tree, 0, $text, -1);
				
				foreach ($tree as $key => $value) {
					foreach ($outcome_data as $key2 => $value2) {
						if ($value->value == $value2->id) {
							
							$data_outcome 					= new JObject();
							$data_outcome->id				= $value2->id;
							$data_outcome->parent_id		= $value2->parent_id;
							$data_outcome->title			= $value->text;
							$data_outcome->name				= $value2->name;
							$data_outcome->alias			= $value2->alias;
							$data_outcome->image			= $value2->image;
							$data_outcome->section			= $value2->section;
							$data_outcome->image_position	= $value2->image_position;
							$data_outcome->description		= $value2->description;
							$data_outcome->published		= $value2->published;
							$data_outcome->editor			= $value2->editor;
							$data_outcome->ordering			= $value2->ordering;
							$data_outcome->access			= $value2->access;
							$data_outcome->accessuserid		= $value2->accessuserid;
							$data_outcome->uploaduserid		= $value2->uploaduserid;
							$data_outcome->deleteuserid		= $value2->deleteuserid;
							$data_outcome->count			= $value2->count;
							$data_outcome->params			= $value2->params;
							$data_outcome->catid			= $value2->catid;
							$data_outcome->numlinks			= $value2->numlinks;
							$data_outcome->slug				= $value2->slug;
							$data_outcome->link				= '';
							$data_outcome->filename			= '';
							$data_outcome->linkthumbnailpath= '';
							$data_outcome->extm				= '';
							$data_outcome->exts				= '';
							$data_outcome->extw				= '';
							$data_outcome->exth				= '';
							$data_outcome->extid			= '';
							
							//FILENAME
							$queryfn = 'SELECT filename, extm, exts, extw, exth, extid'
							.' FROM #__phocagallery'
							.' WHERE catid='.$value2->id
							.' AND published = 1'
							.' AND approved = 1'
							.' ORDER BY ordering LIMIT 1';
							$db->setQuery($queryfn);
							$outcome_filename	    = $db->loadObjectList();
							$data_outcome->filename	= $outcome_filename[0]->filename;
							$data_outcome->extm		= $outcome_filename[0]->extm;
							$data_outcome->exts		= $outcome_filename[0]->exts;
							$data_outcome->extw		= $outcome_filename[0]->extw;
							$data_outcome->exth		= $outcome_filename[0]->exth;
							$data_outcome->extid	= $outcome_filename[0]->extid;
							
							$data_outcome_array[] 	= $data_outcome;
						}	
					}
				}
			
				if ($img_cat == 1) {
					$medium_image_height	= $medium_image_height + 18;
					$medium_image_width 	= $medium_image_width + 18;
					$small_image_width		= $small_image_width +18;
					$small_image_height		= $small_image_height +18;
						
					$output .= '<table border="0">';
					foreach ($data_outcome_array as $category) {
						// ROUTE
						$category->link = JRoute::_(PhocaGalleryRoute::getCategoryRoute($category->id, $category->alias));
						
						$imgCatSizeHelper = 'small';
						
						$mediumCSS 	= 'background: url(\''.JURI::base(true).'/components/com_phocagallery/assets/images/shadow1.'.$tmpl['formaticon'].'\') 50% 50% no-repeat;height:'.$medium_image_height	.'px;width:'.$medium_image_width.'px;';
						$smallCSS	= 'background: url(\''.JURI::base(true).'/components/com_phocagallery/assets/images/shadow3.'.$tmpl['formaticon'].'\') 50% 50% no-repeat;height:'.$small_image_height	.'px;width:'.$small_image_width.'px;';
						
						switch ($img_cat_size) {	
							case 7:
							case 5:							
								$imageBg = $mediumCSS;
							break;
							case 6:
							case 4:							
								$imageBg = $smallCSS;
							break;
							default:
								$imageBg = '';
							break;
						}
						
						// Display Key Icon (in case we want to display unaccessable categories in list view)
						$rightDisplayKey  = 1;
						
						// we simulate that we want not to display unaccessable categories
						// so we get rightDisplayKey = 0 then the key will be displayed
						if (isset($category)) {
							$rightDisplayKey = PhocaGalleryAccess::getUserRight ('accessuserid', $category->accessuserid ,$category->access, $user->get('aid', 0), $user->get('id', 0), 0);
						}
						
						
						if (isset($category->extid) && $category->extid != '') {
								
							$file_thumbnail = PhocaGalleryImageFront::displayCategoriesExtImgOrFolder($category->exts, $category->extm, $category->extw, $category->exth,(int)$img_cat_size, $rightDisplayKey);
							$category->linkthumbnailpath	= $file_thumbnail->rel;
							$category->extw					= $file_thumbnail->extw;
							$category->exth					= $file_thumbnail->exth;
							$category->extpic				= $file_thumbnail->extpic;
						} else {
							$file_thumbnail = PhocaGalleryImageFront::displayCategoriesImageOrFolder($category->filename, (int)$img_cat_size, $rightDisplayKey);
							$category->linkthumbnailpath = $file_thumbnail->rel;
						}

						
						//Output
						$output .= '<tr>'
							.'<td align="center" valign="middle" style="'.$imageBg.'"><a href="'.$category->link.'">';
							
							if (isset($category->extpic) && $category->extpic != '') {
								$correctImageRes = PhocaGalleryPicasa::correctSizeWithRate($category->extw, $category->exth, $tmpl['picasa_correct_width'], $tmpl['picasa_correct_height']);
							
								$output .='<img src="'.$category->linkthumbnailpath.'" alt="'.$category->title.'" style="border:0" width="'. $correctImageRes['width'].'" height="'.$correctImageRes['height'].'" />';
							} else {
								$output .='<img src="'.JURI::base(true).'/'.$category->linkthumbnailpath.'" alt="'.$category->title.'" style="border:0" />';
							}
							$output .='</a></td>'
							.'<td><a href="'.$category->link.'" class="category'.$params->get( 'pageclass_sfx' ).'">'.$category->title.'</a>&nbsp;'
							.'<span class="small">('.$category->numlinks.')</span></td>'
							.'</tr>';
					}
					$output .= '</table>';
				
				} else {
					$output .= '<ul>';
					
					foreach ($data_outcome_array as $category) {
						// ROUTE
						$category->link = JRoute::_(PhocaGalleryRoute::getCategoryRoute($category->id, $category->alias));
					
						$output .='<li>'
								 .'<a href="'.$category->link.'" class="category'.$params->get( 'pageclass_sfx' ).'">'
								 . $category->title.'</a>&nbsp;<span class="small">('.$category->numlinks.')</span>'
								 .'</li>';
					}
					$output .= '</ul>';
				}
			}
				
			
			
			//-----------------------
			// DISPLAYING OF IMAGES
			//-----------------------
			if ($view == 'category') {
				
				$where = '';
				
				// Only one image
				if ($imageid > 0) {
					$where = ' AND a.id = '. $imageid;
				}
				
				// Random image
				if ($imagerandom == 1 && $catid > 0) {
					
					$query = 'SELECT id'
					.' FROM #__phocagallery'
					.' WHERE catid = '.(int) $catid
					.' AND published = 1'
					.' AND approved = 1'
					.' ORDER BY RAND()';
			
					$db->setQuery($query);
					$idQuery =& $db->loadObject();
					if (!empty($idQuery)) {
						$where = ' AND a.id = '. $idQuery->id;
					}
				}
				
				$limit = '';
				
				// Count of images (LIMIT 0, 20)
				if ($limitcount > 0) {
					$limit = ' LIMIT '.$limitstart.', '.$limitcount;
				}
				
			/*	$query = 'SELECT *' .
				' FROM #__phocagallery' .
				' WHERE catid = '.(int) $catid .
				' AND published = 1' . $where .
				' ORDER BY ordering' . $limit;*/
				
				if ($tmpl['imageordering'] == 9) {
					$imageOrdering = ' ORDER BY RAND()'; 
				} else {
					$imageOrdering = ' ORDER BY a.'.PhocaGalleryOrdering::getOrderingString($tmpl['imageordering']);
				}
				
				$query = 'SELECT cc.id, cc.alias as catalias, a.id, a.catid, a.title, a.alias, a.filename, a.description, a.extm, a.exts, a.extw, a.exth, a.extid, a.extl, a.exto,'
				. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as catslug, '
				. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
				. ' FROM #__phocagallery_categories AS cc'
				. ' LEFT JOIN #__phocagallery AS a ON a.catid = cc.id'
				. ' WHERE a.catid = '.(int) $catid
				. ' AND a.published = 1'
				. ' AND a.approved = 1'
				. ' AND cc.published = 1'
				. ' AND cc.approved = 1'
				. $where
				. $imageOrdering
				. $limit;
			
				$db->setQuery($query);
				$category =& $db->loadObjectList();
				
			/*	// current category info
				$query = 'SELECT c.*,' .
					' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as catslug '.
					' FROM #__phocagallery_categories AS c' .
					' WHERE c.id = '. (int) $catid;
				//	' AND c.section = "com_phocagallery"';
	
				$db->setQuery($query, 0, 1);
				$category_info = $db->loadObject();*/
				
				// Output
				$iI = 0;
				foreach ($category as $image) {
					
					
					// PicLens CATEGORY - loaded every time new category will be displayed on the site---------
					if ((int)$enable_piclens > 0) {
						$libName = 'pg-piclens-'.$image->catid;
						$libraries[$libName]	= $library->getLibrary($libName);
						if ($libraries[$libName]->value == 0) {			
							$document->addCustomTag("<link id=\"phocagallerypiclens\" rel=\"alternate\" href=\""
							.JURI::base(true)."/images/phocagallery/"
							.$image->catid.".rss\" type=\"application/rss+xml\" title=\"\" />");
							$library->setLibrary($libName, 1);
						}
						
						// PicLens CSS - will be loaded only one time per site
						$libraries[$libName]	= $library->getLibrary('pg-pl-piclens');
						if ($libraries['pg-pl-piclens']->value == 0) {
							
							$document->addScript('http://lite.piclens.com/current/piclens.js');
							$document->addCustomTag("<style type=\"text/css\">\n"
							." .mbf-item { display: none; }\n"
							." #phocagallery .mbf-item { display: none; }\n"
							." </style>\n");
							$library->setLibrary('pg-pl-piclens', 1);
						}
					}
					// END PICLENS -----------------------------------------------------------------------------
					
					// PICASA - - - - -
					if ($image->extw != '') {
						$extw 				= explode(',',$image->extw);
						if($plugin_type == 1) {
							$image->extw	= $extw[2];//small
						} else if ($plugin_type == 2) {
							$image->extw	= $extw[0];//large
						} else {
							$image->extw	= $extw[1];//medium
						}
						$image->extwswitch	= $extw[0];//used for correcting switch
					
					}
					if ($image->exth != '') {
						$exth 				= explode(',',$image->exth);
						if($plugin_type == 1) {
							$image->exth	= $exth[2];//small
						} else if($plugin_type == 2) {
							$image->exth	= $exth[0];//large
						} else {
							$image->exth	= $exth[1];//medium
						}
						$image->exthswitch	= $exth[0];//used for correcting switch
					}
					
					// - - - - - - - - -
					
					
					$image->slug 	= $image->id.'-'.$image->alias;
					// Get file thumbnail or No Image
					$image->linkthumbnailpath	= PhocaGalleryImageFront::displayCategoryImageOrNoImage($image->filename, $imgSize);
					$file_thumbnail 			= PhocaGalleryFileThumbnail::getThumbnailName($image->filename, $imgSize);
					$image->linkthumbnailpathabs= $file_thumbnail->abs;
					
					// ROUTE
					//$siteLink = JRoute::_(PhocaGalleryRoute::getImageRoute($image->id, $image->catid, $image->alias, $image->catalias, 'detail', 'tmpl=component&detail='.$tmpl['detailwindow'].'&buttons='.$detail_buttons );

					// Different links for different actions: image, zoom icon, download icon
					$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($image->filename, 'large');
					$thumbLinkM	= PhocaGalleryFileThumbnail::getThumbnailName($image->filename, 'medium');
					
					// ROUTE
					if ($tmpl['detailwindow'] == 7) {
						$suffix	= 'detail='.$tmpl['detailwindow'].'&buttons='.$detail_buttons;
					} else {
						$suffix	= 'tmpl=component&detail='.$tmpl['detailwindow'].'&buttons='.$detail_buttons;	
					}
					$siteLink 	= JRoute::_(PhocaGalleryRoute::getImageRoute($image->id, $image->catid, $image->alias, $image->catalias, 'detail', $suffix ));
					$imgLinkOrig= JURI::base(true) . '/' .PhocaGalleryFile::getFileOriginal($image->filename, 1);
					$imgLink	= $thumbLink->rel;
					
					if (isset($image->extid) &&  $image->extid != '') {
						$imgLink		= $image->extl;
						$imgLinkOrig	= $image->exto;
					}
					
					// Different Link - to all categories
					if ((int)$tmpl['pluginlink'] == 2) {
						$siteLink = $imgLinkOrig = $imgLink = PhocaGalleryRoute::getCategoriesRoute();
					}
					// Different Link - to all category
					else if ((int)$tmpl['pluginlink'] == 1) {
						$siteLink = $imgLinkOrig = $imgLink = PhocaGalleryRoute::getCategoriesRoute($image->catid, $image->catalias);
					}
					
					if ($tmpl['detailwindow'] == 2 ) {
						$image->link 		= $imgLink;
						$image->link2		= $imgLink;
						$image->linkother	= $siteLink;
						$image->linkorig	= $imgLinkOrig;
					
					} else if ( $tmpl['detailwindow'] == 3 ) {
					
						$image->link 		= $imgLink;
						$image->link2 		= $imgLink;
						$image->linkother	= $siteLink;
						$image->linkorig	= $imgLinkOrig;
					
					} else if ( $tmpl['detailwindow'] == 5 ) {
						
						$image->link 		= $imgLink;
						$image->link2 	= $siteLink;
						$image->linkother	= $siteLink;
						$image->linkorig	= $imgLinkOrig;
						
					} else if ( $tmpl['detailwindow'] == 6 ) {
				
						$image->link 		= $imgLink;
						$image->link2 		= $imgLink;
						$image->linkother	= $siteLink;
						$image->linkorig	= $imgLinkOrig;
						
						
						// jak data js
						switch ($tmpl['jakdescription']) {
							case 0:
								$descriptionJakJs = '';
							break;
							
							case 2:
								$descriptionJakJs = PhocaGalleryText::strTrimAll(addslashes( $image->description));
							break;
							
							case 3:
								$descriptionJakJs = PhocaGalleryText::strTrimAll(addslashes($image->title));
								if ($image->description != '') {
									$descriptionJakJs .='<br />' .PhocaGalleryText::strTrimAll(addslashes($image->description));
								}
							break;
							
							case 1:
							default:
								$descriptionJakJs = PhocaGalleryText::strTrimAll(addslashes($image->title));
							break;
						}
						$image->linknr		= $iI;
						$tmpl['jakdatajs'][$iI] = "{alt: '".PhocaGalleryText::strTrimAll(addslashes($image->title))."',";
						if ($descriptionJakJs != '') {
							$tmpl['jakdatajs'][$iI] .= "description: '".$descriptionJakJs."',";
						} else {
							$tmpl['jakdatajs'][$iI] .= "description: ' ',";
						}
						
						if(isset($image->extid) && $image->extid != '') {
							$tmpl['jakdatajs'][$iI] .= "small: {url: '".$image->extm."'},"
							."big: {url: '".$image->extl."'} }";
						} else {
							$tmpl['jakdatajs'][$iI] .= "small: {url: '".htmlentities(JURI::base(true).'/'.PhocaGalleryText::strTrimAll(addslashes($thumbLinkM->rel)))."'},"
							."big: {url: '".htmlentities(JURI::base(true).'/'.PhocaGalleryText::strTrimAll(addslashes($imgLink)))."'} }";
						}
						
					} else {
					
						$image->link 		= $siteLink;
						$image->link2 		= $siteLink;
						$image->linkother	= $siteLink;
						$image->linkorig	= $imgLinkOrig;
						
					}

					
					
					// Different types
					switch($plugin_type) {
						case 1:
						case 2:
							if (JFile::exists($image->linkthumbnailpathabs)) {
								list($width, $height) = GetImageSize( $image->linkthumbnailpathabs );
								$imageOrigHeight		= $height;
								$imageOrigWidth			= $width;
							}
							
							if ($float == '') {
								$float = 'left';
							}
							
							$output .= '<div style="float:'.$float.';padding:'.(int)$padding_mosaic.'px;">' . "\n";
							$output .= '<a class="'.$button->methodname.'" title="'.$image->title.'" href="'. JRoute::_($image->link).'"'; 
							
							if ($tmpl['detailwindow'] == 1) {
								$output .= ' onclick="'. $button->options.'"';
							} else if ($tmpl['detailwindow'] == 4 || $tmpl['detailwindow'] == 5) {
								$highSlideOnClick = str_replace('[phocahsfullimg]',$image->linkorig, $tmpl['highslideonclick']);
								$output .= ' onclick="'. $highSlideOnClick.'"';
							} else if ($tmpl['detailwindow'] == 6 ) {
								$output .= ' onclick="gjaksMod'.$randName.'.show('.$image->linknr.'); return false;"';
							} else if ($tmpl['detailwindow'] == 7 ) {
								$output .= '';
							} else {
								$output .= ' rel="'.$button->options.'"';
							}
							
							
							$output .= ' >' . "\n";
							
							if (isset($image->extid) && $image->extid != '') {
								if ($plugin_type == 1) {
									$correctImageRes = PhocaGalleryPicasa::correctSizeWithRate($image->extw, $image->exth, $small_image_width, $small_image_height);
									$imgLink = $image->exts;
								} else {
									$correctImageRes = PhocaGalleryPicasa::correctSizeWithRate($image->extw, $image->exth, $large_image_width, $large_image_height);
									$imgLink = $image->extl;
								}
			
				
								$output .= '<img src="'.$imgLink.'" alt="'.$image->title.'" width="'.$correctImageRes['width'].'" height="'.$correctImageRes['height'].'" />';
							} else {
							
								$output .= '<img src="'.JURI::base(true).'/'.$image->linkthumbnailpath.'" alt="'.$image->title.'" width="'.$imageOrigWidth.'" height="'.$imageOrigHeight.'" />';
							}
							
							
							$output .= '</a>';
							if ( $tmpl['detailwindow'] == 5) {
								if ($tmpl['highslidedescription'] == 1 || $tmpl['highslidedescription'] == 3) {
									$output	.='<div class="highslide-heading">';
									$output	.=$image->title;
									$output	.='</div>';
								}
								if  ($tmpl['highslidedescription'] == 2 || $tmpl['highslidedescription'] == 3) {
									$output	.='<div class="highslide-caption">';
									$output	.= $image->description;
									$output	.= '</div>';
								}
							}
							//$output .= '</div>';
							
						break;
					
						case 0:
						default:
					
							// Float
							$float_code	= '';
							if ($float != '') {
								$float_code = 'position:relative;float:'.$float.';';
							}

							// Maximum size of module image is 100 x 100
							jimport( 'joomla.filesystem.file' );
							$imageWidth['size']		= (int)$medium_image_width; //100;
							$imageHeight['size']	= (int)$medium_image_height;
							$imageHeight['boxsize'] = (int)$medium_image_height;
							$imageWidth['boxsize'] 	= (int)$medium_image_width + 20;//120;
							$imageOrigHeight		= (int)$medium_image_height;
							$imageOrigWidth			= (int)$medium_image_width;//100;
							
							
							if (isset($image->extid) && $image->extid != '') {
								list($width, $height) = getimagesize( $image->extm );
								$correctImageRes = PhocaGalleryPicasa::correctSizeWithRate($image->extw, $image->exth, $medium_image_width, $medium_image_height);
								$imageOrigWidth 	= $correctImageRes['width'];
								$imageOrigHeight 	= $correctImageRes['height'];
								
					
								
								
							} else if (JFile::exists($image->linkthumbnailpathabs)) {
								list($width, $height) = GetImageSize( $image->linkthumbnailpathabs );
								
								$imageHeight 	= PhocaGalleryImage::correctSize($height, $imageHeight['size'], $imageHeight['boxsize'], 0);
								$imageWidth 	= PhocaGalleryImage::correctSize($width, $imageWidth['size'], $imageWidth['boxsize'], 20);
								$imageOrigHeight		= $height;
								$imageOrigWidth			= $width;
							}
							if ((int)$minimum_box_width > 0) {
								$imageWidth['boxsize'] = $minimum_box_width;
							}
							
							$imageHeight['boxsize']	= PhocaGalleryImage::setBoxSize($imageHeight,$imageWidth, $display_name, $display_icon_detail, $display_icon_download, 0, 0, 0, 0, 0, 0, 0, 0,  $category_box_space, $image_background_shadow, 0);
							
							
							
							// PARAMS - Background shadow
							if ( $image_background_shadow != 'none' ) {	
								// IE hack
								$shadowPath = $path->image_abs_front . $image_background_shadow.'.'.$tmpl['formaticon'];
								
								$shadowSize	= @getimagesize($shadowPath);
								if (isset($shadowSize[0]) && isset($shadowSize[0])) {
								
									$w = (int)$medium_image_width + 18 - (int)$shadowSize[0];
									$h = (int)$medium_image_height + 18 - (int)$shadowSize[1];
									
									if ($w != 0) {$w = $w/2;} // plus or minus should be divided, not null
									if ($h != 0) {$h = $h/2;}
								} else {
									$w = $h = 0;
								}
								$imageBgCSS = 'background: url(\''.JURI::base(true).'/components/com_phocagallery/assets/images/'.$image_background_shadow.'.'.$tmpl['formaticon'].'\') 50% 50% no-repeat;';
								
								$imageBgCSSIE = 'background: url(\''.JURI::base(true).'/components/com_phocagallery/assets/images/'.$image_background_shadow.'.'.$tmpl['formaticon'].'\') '.$w.'px '.$h.'px no-repeat;';
								$imageHeight['size'] 	= $imageHeight['size'] + 18;
							$imageWidth['size'] 	= $imageWidth['size'] + 18;
							
							} else {
								$imageBgCSS = 'background: '.$image_background_color .';';
								$imageBgCSSIE = 'background: '.$image_background_color .';';
								
							}
							
							
							
							
							// TODO
							// After IE will be standard browser (no IE7 will be used)
							// change $imageBgCSSIE to $imageBgCSS

							$output .= '<div class="phocagallery-box-file pgplugin'.$iCss.'" style="height:'. $imageHeight['boxsize'] .'px; width:'. $imageWidth['boxsize'].'px;'.$float_code.'margin: '.$margin_box.'px;padding: '.$padding_box.'px;">' . "\n"
								.'<center>'  . "\n"
								.'<div class="phocagallery-box-file-first" style="'.$imageBgCSSIE.'height: '.$imageHeight['size'].'px; width: '.$imageWidth['size'].'px;">' . "\n"
								.'<div class="phocagallery-box-file-second" >' . "\n"
								.'<div class="phocagallery-box-file-third" >' . "\n"
								.'<center>' . "\n"
								.'<a class="'.$button->methodname.'"';

							if ($enable_overlib == 0) {
									$output .= ' title="'.$image->title.'"';
							}
							
							$output .=  ' href="'. JRoute::_($image->link).'"'; 
							
							
							// DETAIL WINDOW
							
							if ($tmpl['detailwindow'] == 1) {
								$output .= ' onclick="'. $button->options.'"';
							} else if ($tmpl['detailwindow'] == 4 || $tmpl['detailwindow'] == 5) {
								$highSlideOnClick = str_replace('[phocahsfullimg]',$image->linkorig, $tmpl['highslideonclick']);
								$output .= ' onclick="'. $highSlideOnClick.'"';
							} else if ($tmpl['detailwindow'] == 6 ) {
								$output .= ' onclick="gjaksPl'.$randName.'.show('.$image->linknr.'); return false;"';
							} else if ($tmpl['detailwindow'] == 7 ) {
								$output .= '';
							} else {
								$output .= ' rel="'.$button->options.'"';
							}
							
							// Enable the switch image
							if ($enable_switch == 1) {
								// Picasa
								if ($image->extl != '') {
									if ((int)$switch_width > 0 && (int)$switch_height > 0) {
										// Custom Size
										$output .=' onmouseover="PhocaGallerySwitchImage(\'PhocaGalleryobjectPicture\', \''. $image->extl.'\', '.$switch_width.', '.$switch_height.');" ';
									} else {
										// Picasa Size
										$correctImageResL = PhocaGalleryPicasa::correctSizeWithRate($image->extwswitch, $image->exthswitch, $switch_width, $switch_height);
										$output .=' onmouseover="PhocaGallerySwitchImage(\'PhocaGalleryobjectPicture\', \''. $image->extl.'\', '.$correctImageResL['width'].', '.$correctImageResL['height'].');" '; 
										// onmouseout="PhocaGallerySwitchImage(\'PhocaGalleryobjectPicture\', \''.$image->extl.'\');"
									}
								} else {
									$switchImg = str_replace('phoca_thumb_m_','phoca_thumb_l_',JURI::base(true).'/'. $image->linkthumbnailpath);
									if ((int)$switch_width > 0 && (int)$switch_height > 0) {
										$output .=' onmouseover="PhocaGallerySwitchImage(\'PhocaGalleryobjectPicture\', \''. $switchImg.'\', '.$switch_width.', '.$switch_height.');" ';
									} else {
										$output .=' onmouseover="PhocaGallerySwitchImage(\'PhocaGalleryobjectPicture\', \''. $switchImg.'\');" ';
										// onmouseout="PhocaGallerySwitchImage(\'PhocaGalleryobjectPicture\', \''.$switchImg.'\');"
									}
								}
							} else {
								// Overlib
								
								if (!empty($image->description)) {
									$divPadding = 'padding:5px;';
								} else {
									$divPadding = 'padding:0px;margin:0px;';
								}
								
								$document->addScript(JURI::base(true).'/includes/js/overlib_mini.js');
								$opacityPer = $opacityPer = (float)$tmpl['overliboverlayopacity'] * 100;
								
								if ( $libraries['pg-overlib-group']->value == 0 ) {
							
									$document->addCustomTag( "<style type=\"text/css\">\n"
						
									. ".bgPhocaClass{
										background:".$tmpl['olbgcolor'].";
										filter:alpha(opacity=".$opacityPer.");
										opacity: ".$tmpl['overliboverlayopacity'].";
										-moz-opacity:".$tmpl['overliboverlayopacity'].";
										z-index:1000;
										}
										.fgPhocaClass{
										background:".$tmpl['olfgcolor'].";
										filter:alpha(opacity=100);
										opacity: 1;
										-moz-opacity:1;
										z-index:1000;
										}
										.fontPhocaClass{
										color:".$tmpl['oltfcolor'].";
										z-index:1001;
										}
										.capfontPhocaClass, .capfontclosePhocaClass{
										color:".$tmpl['olcfcolor'].";
										font-weight:bold;
										z-index:1001;
										}"
									." </style>\n");
									
									
									$library->setLibrary('pg-overlib-group', 1);
								}
								
								if (isset($image->extid) && $image->extid != '') {
									// SIZE WILL BE NOT CORRECTED
									$oImg	= JHTML::_( 'image',$image->extl, '', '', '', $image->title );
								} else {
									$oImg	= JHTML::_( 'image.site', str_replace ('phoca_thumb_m_','phoca_thumb_l_',$image->linkthumbnailpath), '', '', '', $image->title );
			}

								
								
								if ($enable_overlib == 1) { 
									$output .=  " onmouseover=\"return overlib('".htmlspecialchars( addslashes('<center>' . $oImg . "</center>"))."', CAPTION, '". $image->title."', BELOW, RIGHT, BGCLASS,'bgPhocaClass', FGCOLOR, '".$tmpl['olfgcolor']."', BGCOLOR, '".$tmpl['olbgcolor']."', TEXTCOLOR, '".$tmpl['oltfcolor']."', CAPCOLOR, '".$tmpl['olcfcolor']."');\""
									. " onmouseout=\"return nd();\" ";
								} else if ($enable_overlib == 2){ 
									$image->description		= str_replace("\n", '<br />', $image->description);
									$output .=  " onmouseover=\"return overlib('".htmlspecialchars( addslashes('<div style="'.$divPadding.'">'.$image->description.'</div>'))."', CAPTION, '". $image->title."', BELOW, RIGHT, CSSCLASS, TEXTFONTCLASS, 'fontPhocaClass', FGCLASS, 'fgPhocaClass', BGCLASS, 'bgPhocaClass', CAPTIONFONTCLASS,'capfontPhocaClass', CLOSEFONTCLASS, 'capfontclosePhocaClass');\""
									. " onmouseout=\"return nd();\" ";				
								} else if ($enable_overlib == 3){ 
									$image->description		= str_replace("\n", '<br />', $image->description);
									$output .=  " onmouseover=\"return overlib('".PhocaGalleryText::strTrimAll(htmlspecialchars( addslashes( '<div style="text-align:center"><center>' . $oImg . '</center></div><div style="'.$divPadding.'">' . $image->description . '</div>')))."', CAPTION, '". $image->title."', BELOW, RIGHT, BGCLASS,'bgPhocaClass', FGCLASS,'fgPhocaClass', FGCOLOR, '".$tmpl['olfgcolor']."', BGCOLOR, '".$tmpl['olbgcolor']."', TEXTCOLOR, '".$tmpl['oltfcolor']."', CAPCOLOR, '".$tmpl['olcfcolor']."');\""
									. " onmouseout=\"return nd();\" ";				
								} 
							}
							// End Overlib
							
							$output .= ' >' . "\n";
							
							if (isset($image->extid) && $image->extid != '') {
								$correctImageRes = PhocaGalleryPicasa::correctSizeWithRate($image->extw, $image->exth, $medium_image_width, $medium_image_height);
				
								$output .= '<img src="'.$image->extm.'" alt="'.$image->title.'" width="'.$correctImageRes['width'].'" height="'.$correctImageRes['height'].'" />';
							} else {
								$output .= '<img src="'.JURI::base(true).'/'.$image->linkthumbnailpath.'" alt="'.$image->title.'" />';
							}
							if ((int)$enable_piclens > 0) {
								$output .= '<span class="mbf-item">#phocagallerypiclens '.$image->catid .'-phocagallerypiclenscode-'. $image->filename.'</span>';
							}
							
							$output .='</a>';
							
							if ( $tmpl['detailwindow'] == 5) {
								if ($tmpl['highslidedescription'] == 1 || $tmpl['highslidedescription'] == 3) {
									$output	.='<div class="highslide-heading">';
									$output	.=$image->title;
									$output	.='</div>';
								}
								if  ($tmpl['highslidedescription'] == 2 || $tmpl['highslidedescription'] == 3) {
									$output	.='<div class="highslide-caption">';
									$output	.= $image->description;
									$output	.= '</div>';
								}
							}
							
							$output .=	'</center>' . "\n"
								.'</div>' . "\n"
								.'</div>' . "\n"
								.'</div>' . "\n"
								.'</center>' . "\n";

							if ($display_name == 1) {
								$output .= '<div class="name" style="color: '.$font_color.' ;font-size:'.$namefontsize.'px;margin-top:5px;text-align:center;">'.PhocaGalleryText::wordDelete($image->title, $namenumchar, '...').'</div>';
							}
				
							if ($display_icon_detail == 1 || $display_icon_download > 0 || $enable_piclens == 2) {
								
								$output .= '<div class="detail" style="text-align:right">';
								
								if ($enable_piclens == 2) {							
									$output .=' <a href="javascript:PicLensLite.start();" title="PicLens" ><img src="http://lite.piclens.com/images/PicLensButton.png" alt="PicLens" width="16" height="12" border="0" style="margin-bottom:2px" /></a>';
				  
								}
								
								
								if ($display_icon_detail == 1) {
									$output .= ' <a class="'.$button->methodname.'" title="'. JText::_('Image Detail').'" href="'.JRoute::_($image->link2).'"';
									// Detail Window
									if ($tmpl['detailwindow'] == 1) {
										$output .= ' onclick="'. $button2->options.'"';
									} else if ($tmpl['detailwindow'] == 2) {
										$output .= ' rel="'. $button2->options.'"';
									} else if ($tmpl['detailwindow'] == 4 ) {
										$output .= ' onclick="'. $tmpl['highslideonclick'].'"';
									} else if ($tmpl['detailwindow'] == 5 ) {
										$output .= ' onclick="'. $tmpl['highslideonclick2'].'"';
									} else if ($tmpl['detailwindow'] == 6) {
										$output .=  ' onclick="gjaksPl'.$randName.'.show('.$image->linknr.'); return false;"';
									} else if ($tmpl['detailwindow'] == 7 ) {
										$output .= '';
									} else {
										$output .= ' rel="'. $button2->options.'"';
									}
									
									
									$output .= ' >';
									$output .= '<img src="'.JURI::base(true).'/components/com_phocagallery/assets/images/icon-view.'.$tmpl['formaticon'].'" alt="'.$image->title.'" />';
									$output .= '</a>';
								}
								
								if ($display_icon_download > 0) {
									
									// Direct download set in component
									if ((int)$display_icon_download == 2) {
										$output .= ' <a title="'. JText::_('Image Download').'" href="'. JRoute::_($image->linkother . '&amp;phocadownload='.(int)$display_icon_download).'"';
									} else {
										$output .= ' <a class="'. $button->methodname.'" title="'. JText::_('Image Download').'" href="'. JRoute::_($image->linkother . '&amp;phocadownload='.(int)$display_icon_download).'"';
									
										if ($tmpl['detailwindow'] == 1) {
											$output .= ' onclick="'. $buttonOther->options.'"';
										} else if ($tmpl['detailwindow'] == 4 ) {
											$output .= ' onclick="'. $tmpl['highslideonclick'].'"';
										} else if ($tmpl['detailwindow'] == 5 ) {
											$output .= ' onclick="'. $tmpl['highslideonclick2'].'"';
										} else if ($tmpl['detailwindow'] == 7 ) {
											$output .= '';
										} else {
											$output .= ' rel="'. $buttonOther->options.'"';
										}
									}
						
									$output .= ' >';
									$output .= '<img src="'.JURI::base(true).'/components/com_phocagallery/assets/images/icon-download.'.$tmpl['formaticon'].'" alt="'.$image->title.'" />';
									$output .= '</a>';
								
								}
								
								$output .= '</div>';
								if ($float == '') {
									$output .= '<div style="clear:both"> </div>';
								}
							}
						break;
					}
					$output .= '</div>';
					$iI++;
				}
			}
			
			//--------------------------
			// DISPLAYING OF SWITCHIMAGE
			//--------------------------
			if ($view == 'switchimage') {
			
				$path			= PhocaGalleryPath::getPath();
				$waitImage 		= $path->image_rel . 'icon-switch.gif';
				$basicImage		= $path->image_rel  . 'phoca_thumb_l_no_image.' . $tmpl['formaticon'];
				
				if ($basic_image_id > 0) {
				
					$query = 'SELECT *' .
					' FROM #__phocagallery' .
					' WHERE id = '.(int) $basic_image_id;
			
					$db->setQuery($query);
					$basicImageArray =& $db->loadObject();
					
					$switchImage = PhocaGalleryImage::correctSwitchSize($switch_height, $switch_width);

					if ((int)$switch_width > 0 && (int)$switch_height > 0) {
						$wHArray	= array( 'id' => 'PhocaGalleryobjectPicture', 'border' =>'0', 'width' => $switch_width, 'height' => $switch_height);
						$wHString	= ' id="PhocaGalleryobjectPicture"  border="0" width="'. $switch_width.'" height="'.$switch_height.'"';
					} else {
						$wHArray 	= array( 'id' => 'PhocaGalleryobjectPicture', 'border' =>'0');
						$wHString	= ' id="PhocaGalleryobjectPicture"  border="0"';
					}
					
					
					if (isset($basicImageArray->extl) && isset($basicImageArray->extid) && $basicImageArray->extid != '') {
						$basicImage		= JHTML::_( 'image', $basicImageArray->extl, '', $wHArray);
					} else if (isset($basicImageArray->filename)) { 
						$fileBasicThumb = PhocaGalleryFileThumbnail::getThumbnailName($basicImageArray->filename, 'large');
						$basicImage		= JHTML::_( 'image.site', $fileBasicThumb->rel , '', '', '', '', $wHString);
					} else {
						$basicImage  = '';
					}
					
				}
	
				
			
				$document->addCustomTag(PhocaGalleryRenderFront::switchImage($waitImage));
				//$switchImage['height']	= $switchImage['height'] + 5;
			
				$output .='<div><center class="main-switch-image" style="margin:0px;padding:7px 5px 7px 5px;margin-bottom:15px;"><table border="0" cellspacing="5" cellpadding="5" style="border:1px solid #c2c2c2;"><tr><td align="center" valign="middle" style="text-align:center;width:'. $switchImage['width'] .'px;height:'. $switchImage['height'] .'px; background: url(\''. JURI::root().'components/com_phocagallery/assets/images/icon-switch.gif\') '.$switchImage['centerw'].'px '.$switchImage['centerh'].'px no-repeat;margin:0px;padding:0px;">';
				$output .= $basicImage
				.'</td></tr></table></center></div>';
			
			} else {
				// Overlib
				
			
			}
			
			//--------------------------
			// DISPLAYING OF Clear Both
			//--------------------------
			if ($view == 'clearboth') {
				$output .= '<div style="clear:both"> </div>';
			}
			if ($view == 'clearright') {
				$output .= '<div style="clear:right"> </div>';
			}
			if ($view == 'clearleft') {
				$output .= '<div style="clear:left"> </div>';
			}
			
			$output .= '</div>';
			if ($float == '') {
				$output .= '<div style="clear:both"> </div>';
			}
			
			if ($tmpl['detailwindow'] == 6) {
				$output .= '<script type="text/javascript">'
				.'var gjaksPl'.$randName.' = new SZN.LightBox(dataJakJsPl'.$randName.', optgjaksPl'.$randName.');'
				.'</script>';
			}
			
			$article->text = preg_replace($regex_all, $output, $article->text, 1);
			
			// ADD JAK DATA CSS style

			if ( $tmpl['detailwindow'] == 6 ) {
				$scriptJAK = '<script type="text/javascript">'
				. 'var dataJakJsPl'.$randName.' = [';
				if (!empty($tmpl['jakdatajs'])) {
					$scriptJAK .= implode($tmpl['jakdatajs'], ',');
				}
				$scriptJAK .= ']'
				. '</script>';
				$document->addCustomTag($scriptJAK);
			}
			
		}
		
		
		
		
		// CUSTOM CSS - For all items it will be the same
		if ( $libraries['pg-css-sbox-plugin']->value == 0 ) {
			$document->addCustomTag( "<style type=\"text/css\">\n" . $cssSbox . "\n" . " </style>\n");
			$library->setLibrary('pg-css-sbox-plugin', 1);
		}
		// All custom CSS tags will be added into one CSS area
	//	if ( $libraries['pg-css-pg-plugin']->value == 0 ) {
			$document->addCustomTag( "<style type=\"text/css\">\n" . $cssPgPlugin . "\n" . " </style>\n");
			$library->setLibrary('pg-css-pg-plugin', 1);
	//	}
		
		if ( $libraries['pg-css-ie']->value == 0 ) {
			$document->addCustomTag("<!--[if lt IE 8]>\n<link rel=\"stylesheet\" href=\"".JURI::base(true)."/components/com_phocagallery/assets/phocagalleryieall.css\" type=\"text/css\" />\n<![endif]-->");
			$library->setLibrary('pg-css-ie', 1);
		}
		
		
		

		
	  } // end if count_matches
		return true;
	}
}
?>