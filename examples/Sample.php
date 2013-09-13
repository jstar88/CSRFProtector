<?php

require ("../CSFRProtector.php");

$csfr = new CSFRProtector(null,null,3);
$csfr->run();
echo "<html><body><a href=\"Sample.php\">click me</a></body></html>";
?>