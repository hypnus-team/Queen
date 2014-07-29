<? if (!class_exists('template')) die('Access Denied');$template->getInstance()->check('edit.htm', '0dde9d005724b9d2c1f46a39ed2b4215', 1398675322);?>
<? if($e) { ?>
<div class="alert alert-error">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>error!</strong> <?=$edit_error[$e]?>.
</div>
<? } else { ?>
  <? if(($s)) { ?>  
    <div class="alert alert-success">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>Success!</strong> <?=$edit_success[$s]?>.
    </div>      
  <? } else { ?>
    <div id="editdom_<?=$UNIQU?>">
    <?=$b?>/<?=$i?>
    <br>
    <textarea id="savedit_<?=$UNIQU?>" wrap="off"><?=$c?></textarea>
    <br>
	<select id="cr_mode_<?=$UNIQU?>">
	<option value="1">LF only (linux mode)</option>
	<option value="2">CR/LF (windows mode)</option>
	<option value="3">CR only (mac mode)</option>
	</select>	
	<br>
    <input type="button" class="btn btn-primary" value="Save" OnClick="javascript:save_edit_<?=$MID?>('<?=$CID?>','<?=$UNIQU?>','<?=$b?>','<?=$i?>');"> <input type="button" class="btn" value="Cancel" OnClick="javascript:cancel_edit_<?=$MID?>('<?=$UNIQU?>');">
	</div>
  <? } } ?>