<? if (!class_exists('template')) die('Access Denied');$template->getInstance()->check('index.htm', '964b45ad3288073bf08f0ef32c0092d5', 1397364276);?>
<? if(((0 == $r) || (6 == $r))) { ?>
<span class="label label-success">
<? } else { ?>
<span class="label label-important">
<? } if(($warning[$r])) { ?>
    <?=$warning[$r]?>
<? } else { ?>
    unkown ret:<?=$r?>
<? } ?>
</span>