<?php

$d = nl2br($d);


$error[0] = 'unkown error';
$error[1] = 'fail operation';
$error[2] = 'malloced memory not enough';
$error[4] = 'fail to malloc main memory';

$warning[1] = 'malloced memory not enough';

$opt = array(
  'New','Copy','Move','Delete','Rename','Download','Execute','Chmod','Chown','Wget','Upload',
);
?>