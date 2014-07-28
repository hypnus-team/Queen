<?php

$k=unserialize(strtolower(serialize($k)));
array_multisort($k,SORT_ASC,$d);

$basedir_array = array();
if (isset($d)){
	foreach ($d as $a => $b){
		$tmp=htmlspecialchars_decode($b[5]);
		$tmp=rawurlencode($tmp);
		$basedir_array[$a]= 'basedir='.$basedir.'/'.$tmp;
	}
}

$error[0] = 'unkown error';
$error[1] = 'fail to malloc memory';
$error[2] = 'fail to open directory';
$error[3] = 'the directory does not exist';
$error[4] = 'fail to malloc main memory';
$error[5] = 'return data more than 1M';

$warning[0] = 'unkown fail';
$warning[1] = 'operation is not fully completed';
$warning[2] = 'operation params illegal';

$warning[3] = 'The uploaded file exists already';
$warning[4] = 'The uploaded file transmission fail';
$warning[5] = 'The uploaded file was only partially uploaded';
$warning[6] = 'The uploaded file create fail';

$edit_error[1] = 'file not exists';
$edit_error[2] = 'fail to read file';
$edit_error[3] = 'fail to open file';
$edit_error[4] = 'fail,include illegal chars,not plain text files';
$edit_error[5] = 'fail,too large file to open';
$edit_error[6] = 'fail to write file';
$edit_error[7] = 'fail,too large file to open';
$edit_error[8] = 'fail,too large file to open';
$edit_success[1] = 'File has been saved completed';

$opt = array(
  'Copy','Chmod','Chown','Delete','Move','New','Rename','Upload',
);
?>