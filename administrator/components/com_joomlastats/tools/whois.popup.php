<?php
/**
 * @version $Id: whois.popup.php 000 2008-10-25 12:46:46Z mic $
 * @package JoomlaStats
 * @subpackage Tools Admin
 * @copyright Copyright (C) 2008 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL
 * @author mic [ http://www.joomx.com ]
 */


echo '<?xml version="1.0" encoding="utf-8"?'.'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<title><?php echo JTEXT::sprintf( 'WHOIS query for [%s] address', $address_to_check ); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
body			{ background-color:#FFFFF6; color:#4F4F4F; border:1px solid #CFCFCF; margin:5px auto 5px auto; padding:5px; link:#8080FF; visited:#FF0000; font-family:arial,"lucida console",sans-serif; font-size:0.84em; width:96%; }
.serverAccess	{ color:#00ACCF; border-top:2px solid #00ACCF; border-bottom:2px solid #00ACCF; background-color:#EFFCFF; margin:5px auto 5px auto; padding:5px; width:80%; text-align:center; }
.serverError, .error	{ color:#FF0000; border-top:2px solid #FF0000; border-bottom:2px solid #FF0000; background-color:#FFEFEF; margin:5px auto 5px auto; padding:5px; width:80%; text-align:center; }
.success		{ color:#409F3B; border-top:2px solid #409F3B; border-bottom:2px solid #409F3B; background-color:#EFFFF0; margin:5px auto 5px auto; padding:5px; width:80%; text-align:center; }
.noMatch		{ color:#000000; border-top:2px solid #FDFF00; border-bottom:2px solid #FDFF00; background-color:#FFFFDF; margin:5px auto 5px auto; padding:5px; width:80%; text-align:center; }
.debug			{ color:#FFF8AF; }
.debug2			{ color:#FF5F5F; }
.text			{ margin:5px; padding:5px; }
.var			{ float:left; width:200px; }
.vartext		{ float:left; }
.vartext1		{ float:left; margin-left:200px; }
.closeWindow	{ margin:5px auto 5px auto; text-align:center; }
.clear			{ clear:both; }
</style>
</head>
<html>
	<body>
		<div>
			<?php
			if( !( $retVal = $whois->whoisSock( $domainName ) ) ) { ?>
				<div class="serverError">
					<?php echo JTEXT::_( 'No result - maybe the server is not reachable at this moment' ); ?>
				</div>
				<?php
			}else{ ?>
				<div class="text">
					<?php
					if( !$whois->error ) { ?>
						<div class="success">
							<?php echo JTEXT::_( 'Your request was successful, below the result' ); ?>
						</div>
						<?php
					}
					echo $retVal; ?>
				</div>
				<?php
			} ?>
		</div>
		<div class="clear"></div>

		<div class="closeWindow">
			<a href="javascript:void(0);" onclick="window.close();" class="button" title="<?php echo JTEXT::_( 'Close Window' ); ?>"><?php echo JTEXT::_( 'Close Window' ); ?></a>
		</div>
	</body>
</html>