<?php
/**
 * @version		$Id: media.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
class PhocaDownloadHelperUpload
{

	function canUpload( $file, &$err, $manager = '', $frontend = 0) {
		
	$paramsC 									= &JComponentHelper::getParams( 'com_phocadownload' );
	//$paramsComponent['allowed_file_types']		= $paramsC->get( 'allowed_file_types', '' );
	//$paramsComponent['disallowed_file_types']	= $paramsC->get( 'allowed_file_types', '' );
	$phocaSet['allowed_file_types']		= PhocaDownloadHelper::getSettings( 'allowed_file_types', '' );
	$phocaSet['disallowed_file_types']	= PhocaDownloadHelper::getSettings( 'disallowed_file_types', '' );	
		
	$allowedMimeType 	= PhocaDownloadHelper::getMimeTypeString($phocaSet['allowed_file_types']);
	$disallowedMimeType = PhocaDownloadHelper::getMimeTypeString($phocaSet['disallowed_file_types']);

		$paramsL = array();
		
		if ($manager == 'file') {
			// FILES
			$paramsL['upload_extensions'] 	= $allowedMimeType['ext'];
			$paramsL['image_extensions'] 	= 'bmp,gif,jpg,png,jpeg';
			$paramsL['upload_mime']			= $allowedMimeType['mime'];
			$paramsL['upload_mime_illegal']	= $disallowedMimeType['mime'];
			$paramsL['upload_ext_illegal']	= $disallowedMimeType['ext'];
		} else {
			// IMAGES (e.g. flags if language files are downloaded)
			$paramsL['upload_extensions'] 	= 'gif,jpg,png,jpeg';
			$paramsL['image_extensions'] 	= 'gif,jpg,png,jpeg';
			$paramsL['upload_mime']			= 'image/jpeg,image/gif,image/png';
			$paramsL['upload_mime_illegal']	='application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip,text/html';
			$paramsL['upload_ext_illegal']	= $disallowedMimeType['ext'];
		
		}

		// The file doesn't exist
		if(empty($file['name'])) {
			$err = 'Please input a file for upload';
			return false;
		}

		
		
		// Not safe file
		jimport('joomla.filesystem.file');
		if ($file['name'] !== JFile::makesafe($file['name'])) {
			$err = 'WARNFILENAME';
			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));
		// Allowable extension
		$allowable = explode( ',', $paramsL['upload_extensions']);
		
		
		$notAllowable = explode( ',', $paramsL['upload_ext_illegal']);
		if(in_array($format, $notAllowable)) {
			$err = 'WARNFILETYPE';
			return false;
		}
		
		if (!in_array($format, $allowable)) {
			$err = 'WARNFILETYPE';
			return false;
		}

		
		// Max size of image
		if ($frontend == 1) {
			$maxSize = (int) $paramsC->get( 'user_file_upload_size', 3145728 );	
		} else {
			$maxSize = (int) PhocaDownloadHelper::getSettings( 'upload_maxsize', 3145728 );
		}
		if ($maxSize > 0 && (int) $file['size'] > $maxSize) {
			$err = 'WARNFILETOOLARGE';
			return false;
		}

		$user = JFactory::getUser();
		$imginfo = null;
		// Image check
		$images = explode( ',', $paramsL['image_extensions']);
		if(in_array($format, $images)) { // if its an image run it through getimagesize
			if(($imginfo = getimagesize($file['tmp_name'])) === FALSE) {
				$err = 'WARNINVALIDIMG';
				return false;
			}
		} else if(!in_array($format, $images)) {
			// if its not an image...and we're not ignoring it
			$allowed_mime = explode(',', $paramsL['upload_mime']);
			$illegal_mime = explode(',', $paramsL['upload_mime_illegal']);
			if(function_exists('finfo_open')) {
				// We have fileinfo
				$finfo = finfo_open(FILEINFO_MIME);
				$type = finfo_file($finfo, $file['tmp_name']);
				
				
				if(strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
					$err = 'WARNINVALIDMIME';
					return false;
				}
				finfo_close($finfo);
			} else if(function_exists('mime_content_type')) {
				// we have mime magic
				$type = mime_content_type($file['tmp_name']);
				if(strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
					$err = 'WARNINVALIDMIME';
					return false;
				}
			}/* else if(!$user->authorize( 'login', 'administrator' )) {
				$err = 'WARNNOTADMIN';
				return false;
			}*/
		}
		
		// XSS Check
		$xss_check =  JFile::read($file['tmp_name'],false,256);
		$html_tags = array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
		foreach($html_tags as $tag) {
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if(stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
				$err = 'WARNIEXSS';
				return false;
			}
		}
		return true;
	}
	
	function uploader($id='file-upload', $params = array())
	{
		
		$path = 'administrator/components/com_phocadownload/assets/upload/';
		JHTML::script('swf.js', $path, false ); // mootools are loaded yet
		JHTML::script('uploader.js', $path, false );// mootools are loaded yet

		static $uploaders;

		if (!isset($uploaders)) {
			$uploaders = array();
		}

		if (isset($uploaders[$id]) && ($uploaders[$id])) {
			return;
		}

		// Setup options object
		$opt['url']					= (isset($params['targetURL'])) ? $params['targetURL'] : null ;
		$opt['swf']					= (isset($params['swf'])) ? $params['swf'] : JURI::root(true).'/media/system/swf/uploader.swf';
		$opt['multiple']			= (isset($params['multiple']) && !($params['multiple'])) ? '\\false' : '\\true';
		$opt['queued']				= (isset($params['queued']) && !($params['queued'])) ? '\\false' : '\\true';
		$opt['queueList']			= (isset($params['queueList'])) ? $params['queueList'] : 'upload-queue';
		$opt['instantStart']		= (isset($params['instantStart']) && ($params['instantStart'])) ? '\\true' : '\\false';
		$opt['allowDuplicates']		= (isset($params['allowDuplicates']) && !($params['allowDuplicates'])) ? '\\false' : '\\true';
		$opt['limitSize']			= (isset($params['limitSize']) && ($params['limitSize'])) ? (int)$params['limitSize'] : null;
		$opt['limitFiles']			= (isset($params['limitFiles']) && ($params['limitFiles'])) ? (int)$params['limitFiles'] : null;
		$opt['optionFxDuration']	= (isset($params['optionFxDuration'])) ? (int)$params['optionFxDuration'] : null;
		$opt['container']			= (isset($params['container'])) ? '\\$('.$params['container'].')' : '\\$(\''.$id.'\').getParent()';
		$opt['types']				= (isset($params['types'])) ?'\\'.$params['types'] : '\\{\'All Files (*.*)\': \'*.*\'}';


		// Optional functions
		$opt['createReplacement']	= (isset($params['createReplacement'])) ? '\\'.$params['createReplacement'] : null;
		$opt['onComplete']			= (isset($params['onComplete'])) ? '\\'.$params['onComplete'] : null;
		$opt['onAllComplete']		= (isset($params['onAllComplete'])) ? '\\'.$params['onAllComplete'] : null;

/*  types: Object with (description: extension) pairs, default: Images (*.jpg; *.jpeg; *.gif; *.png)
 */

		$options = JHTMLBehavior::_getJSObject($opt);

		// Attach tooltips to document
		$document =& JFactory::getDocument();
		$uploaderInit = 'sBrowseCaption=\''.JText::_('Browse Files', true).'\';
				sRemoveToolTip=\''.JText::_('Remove from queue', true).'\';
				window.addEvent(\'load\', function(){
				var Uploader = new FancyUpload($(\''.$id.'\'), '.$options.');
				$(\'upload-clear\').adopt(new Element(\'input\', { type: \'button\', events: { click: Uploader.clearList.bind(Uploader, [false])}, value: \''.JText::_('Clear Completed').'\' }));				});';
		$document->addScriptDeclaration($uploaderInit);

		// Set static array
		$uploaders[$id] = true;
		return;
	}
}