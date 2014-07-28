<?php

include "../include/config.inc.php";

$out  = pack("III",$runtime['max_response_unit'], $runtime['max_request_size'], $runtime['max_response_size']);
echo "$out";
exit;
?>