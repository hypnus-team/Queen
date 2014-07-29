<? if (!class_exists('template')) die('Access Denied');$template->getInstance()->check('index.htm', '2be414c977921e67d1896e1c668b7b78', 1404114054);?>
<div id="<?=$UNIQU?>">
  <div class="alert alert-success">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Success!</strong> Hypnus.2(Borg) Ver.2.0.0<!--[<?=$UNIQU?>]-->.
  </div> 
  <? if((2 == $c)) { ?>
      <div class="alert alert-success">
	  <button type="button" class="close" data-dismiss="alert">&times;</button>
	  <strong>Success!</strong> 指令已接收,客户端将在3秒后开始关闭<!--[<?=$UNIQU?>]-->.
	  </div> 
  <? } else { ?>
      <p><button class="btn btn-danger" OnClick="mod_request('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','shutdown=1',0,0);"><i class="icon-off icon-white"></i> 结束客户端</button></p>
  <? } ?>
</div>