<? if (!class_exists('template')) die('Access Denied');$template->getInstance()->check('index.htm', 'b2fa618248b4d814d2c35ae3b7814a6e', 1396011691);?>
<? if($e) { ?>
<div class="alert alert-error">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>error!</strong> <? if((!$error[$e])) { ?><?=$error['0']?><? } else { ?><?=$error[$e]?><? } ?>.
</div>
<? } if((($w) and ($warning[$w]))) { ?>
<div class="alert">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>error!</strong> <?=$warning[$w]?>.
</div>
<? } ?>

<input type="text" value="<?=$cmd?>" name="cmd" id="cmd_<?=$UNIQU?>"><input type="button" value="Submit" name="submit" onclick="javascript:cmd=document.getElementById('cmd_<?=$UNIQU?>').value;cmd=encodeURIComponent(cmd);mod_request('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','cmd='+cmd,0,0);">
<? if(($d)) { ?>
<hr>
<?=$d?>
<hr>
<? } ?>