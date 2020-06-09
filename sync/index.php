<?php
   error_reporting(E_ALL);
   ini_set("display_errors", 1);

   include("../wp-content/themes/process2015/_/modules/sync.php");
   PrcsSync::set_debug(true);
   PrcsSync::sync();
?>
