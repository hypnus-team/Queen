<? if (!class_exists('template')) die('Access Denied');$template->getInstance()->check('index.htm', '0e3d9c81e500c7f733883cd32e10020a', 1398673182);?>
<? if($e) { ?>
<div class="alert alert-error">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>error!</strong> <? if((!$error[$e])) { ?><?=$error['0']?><? } else { ?><?=$error[$e]?><? } ?>.
</div>
<? } if($w) { ?>
<div class="alert">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>error!</strong> <? if((!$warning[$w])) { ?><?=$warning['0']?><? } else { ?><?=$warning[$w]?><? } ?>.
</div>
<? } ?>

<input type="text" value="<?=$basedir?>" name="basedir" id="basedir_<?=$UNIQU?>"><input type="button" value="Submit" name="submit" onclick="javascript:basedir=document.getElementById('basedir_<?=$UNIQU?>').value;mod_request('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','basedir='+basedir,0,0);">
<table class="table table-striped table-condensed">

<tr><td><input type="checkbox" onclick="javascript:chkAll_<?=$MID?>(this,'checked_<?=$UNIQU?>');"></td><td></td><td></td><td></td><td></td><td></td><td>
<? if(('/' != $basedir)) { ?>
<a href="#TOP_<?=$UNIQU?>" onclick="javascript:mod_request('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','basedir=<?=$basedir?>/../',0,0);">..\</a></td></tr>
<? } else { ?>
</td></tr>
<? } if(is_array($d)) { foreach($d as $key => $value) { ?><tr><td><input type="checkbox" value="<?=$value['5']?>" name="checked_<?=$UNIQU?>"></td><td><?=$value['0']?></td><td><?=$value['1']?></td><td><?=$value['2']?></td><td style="text-align:right;"><?=$value['3']?></td><td><?=$value['4']?></td>
<td>
<? if(('1' == $value['7'])) { ?>
<a href="#TOP_<?=$UNIQU?>" onclick="javascript:mod_request('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','<?=$basedir_array[$key]?>',0,0);"><?=$value['5']?></a>
<? } else { ?>
<?=$value['5']?>
  <? if(($value['6'])) { ?>
     -> <?=$value['6']?><!--<i class="icon-arrow-right" title="<?=$value['6']?>"></i>-->
  <? } } ?>
</td></tr><? } } ?></table>

<center>
<div id="New_<?=$UNIQU?>" style="display:none;">
    <strong>Create New</strong>&nbsp;&nbsp;<input type="radio" value="newF" name="opt_<?=$UNIQU?>">File&nbsp;<input type="radio" value="newD" name="opt_<?=$UNIQU?>" checked>Directory<br>
	<input type="text"  value=""  placeholder="input Name" id="new_input_<?=$UNIQU?>"><br><input class="btn btn-primary" type="submit" value="Submit" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','new','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>');">
</div>
<div id="Delete_<?=$UNIQU?>" style="display:none;">
    <strong>are you sure to delete ? </strong>&nbsp;<input class="btn btn-danger" type="submit" value="Yes" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','dele','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="No" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
</div>
<div id="Copy_<?=$UNIQU?>" style="display:none;">
    <strong>copy to : </strong>
	<input type="text"  value=""  placeholder="destination path" id="copy_input_<?=$UNIQU?>"><br>
	<input type="checkbox" id="copy_box_<?=$UNIQU?>">Overwrite existing files &nbsp;
	<input class="btn btn-primary" type="submit" value="Copy" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','copy','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
</div>
<div id="Move_<?=$UNIQU?>" style="display:none;">
    <strong>move to : </strong>
	<input type="text"  value=""  placeholder="destination path" id="move_input_<?=$UNIQU?>"><br>
	<input type="checkbox" id="move_box_<?=$UNIQU?>">Overwrite existing files &nbsp;
	<input class="btn btn-primary" type="submit" value="Move" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','move','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
</div>
<div id="Rename_<?=$UNIQU?>" style="display:none;">
    <strong>new name : </strong>
	<input type="text"  value=""  placeholder="input new name" id="rename_input_<?=$UNIQU?>"><br>
	<input class="btn btn-primary" type="submit" value="Rename" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','rename','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
</div>
<div id="Chmod_<?=$UNIQU?>" style="display:none;">    
	<table border=0>
	<tr><td><strong>set permissions</strong>&nbsp;&nbsp;</td><td>User&nbsp;</td><td>Group&nbsp;</td><td>Others&nbsp;</td></tr>
	<tr><td>Write</td><td><input type="checkbox" value=200 name="chmod_input_<?=$UNIQU?>"></td><td><input type="checkbox" value=20 name="chmod_input_<?=$UNIQU?>"></td><td><input type="checkbox" value=2 name="chmod_input_<?=$UNIQU?>"></td></tr>
	<tr><td>Execute</td><td><input type="checkbox" value=100 name="chmod_input_<?=$UNIQU?>"></td><td><input type="checkbox" value=10 name="chmod_input_<?=$UNIQU?>"></td><td><input type="checkbox" value=1 name="chmod_input_<?=$UNIQU?>"></td></tr>
	<tr><td>Read</td><td><input type="checkbox" value=400 name="chmod_input_<?=$UNIQU?>"></td><td><input type="checkbox" value=40 name="chmod_input_<?=$UNIQU?>"></td><td><input type="checkbox" value=4 name="chmod_input_<?=$UNIQU?>"></td></tr>
	</table>
    <br><input type="checkbox" id="chmod_box_<?=$UNIQU?>">Set Permission Recursively &nbsp;
	<input class="btn btn-primary" type="submit" value="Chmod" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','chmod','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
</div>
<div id="Chown_<?=$UNIQU?>" style="display:none;">
    <strong>new owner : </strong>
	<input type="text"  value=""  placeholder="new user name" id="chown_input2_<?=$UNIQU?>" class="input-small"> : <input type="text"  value=""  placeholder="new group name" id="chown_input_<?=$UNIQU?>" class="input-small">
	<br><input type="checkbox" id="chown_box_<?=$UNIQU?>">Change Owner Recursively &nbsp;
	<input class="btn btn-primary" type="submit" value="Chown" onclick="submit_<?=$MID?>('<?=$CID?>','<?=$basedir?>','chown','<?=$UNIQU?>');">&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
</div>
<div id="Upload_<?=$UNIQU?>" style="display:none;">    
    <strong>Please select a file and click Upload button</strong>
	<br><input id="fileToUpload_<?=$UNIQU?>" type="file" size="45" name="fileToUpload_<?=$UNIQU?>" class="input">
	<div id="Upload_Status_<?=$UNIQU?>">
	<input type="checkbox" id="upload_box_<?=$UNIQU?>">Overwrite existing files &nbsp;	
	<button class="btn btn-primary" id="buttonUpload" onclick="return ajaxFileUpload_<?=$MID?>('<?=$CID?>','<?=$UNIQU?>','<?=$basedir?>');">Upload</button></t>&nbsp;<input class="btn" type="submit" value="Cancel" onclick="opt_panel_close_<?=$MID?>('<?=$UNIQU?>')">
	</div>
</div>
<div id="Download_<?=$UNIQU?>" style="display:none;">    
  <div class="alert">
  <strong>Warning!</strong> No any file has been checked.
  </div>        
</div>
<div id="Edit_<?=$UNIQU?>" style="display:none;"></div>
</center>
<hr><? if(is_array($opt)) { foreach($opt as $key => $value) { ?><input type="button" value="<?=$value?>" name="Opt" onclick="javascript:opt_panel_<?=$MID?>('<?=$value?>','<?=$UNIQU?>');">&nbsp;<? } } ?><input type="button" value="Download" onclick="javascript:download_<?=$MID?>('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','<?=$basedir?>');">
<input type="button" value="Edit" onclick="javascript:edit_<?=$MID?>('<?=$CID?>','<?=$MID?>','<?=$UNIQU?>','<?=$basedir?>');">