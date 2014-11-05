<?php

   define('CURSCRIPT', 'readme');
   define('NOROBOT', TRUE);
   require_once "./include/common.inc.php";       

   require_once "./include/template.class.php";   
   require_once "$languagedir"."./common.lang.php";
   require_once "$languagedir"."./readme.lang.php";
  


    
   $i = count ($answers);
   $article = intval($_GET['article']);
   
   if ($i<$article){ 
	   $article = 0;
   }

   
   




   include($template->getfile('readme.htm'));
   exit;


?>