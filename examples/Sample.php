<?php

require ("../CSRFProtector.php");

$error = function ()
{
    die("Nice try dude");
}
;

$token = function ()
{
    return "_" . mt_rand(1, 200) . md5(mt_rand(2, 100));
}
;

$csrf = new CSRFProtector(array(
    'errorFunction' => $error,
    'tokenFunction' => $token,
    'maxTime' => 120,
    'debug'=>true,
    'minSecondBeforeNextClick' => 1));
$csrf->run();
echo "<html><body><a href=\"Sample.php\">click me</a></body></html>";

?>