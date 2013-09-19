<?php

require ("../CSRFProtector.php");



$time = 120; //in seconds
$min = 0; //in seconds

$jsPath = "";


$csrf = new CSRFProtector($jsPath, null, null, $time, $min);
$csrf->run();
if($csrf->isAjax())
{
    echo "Hello World!";
}
echo '
<html>
    <body>
        <a href="" onclick="ajax()" >click me</a>
        <div id ="content">
        <script>
            function ajax()
            {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "/examples/SampleAjax.php", true);
                xhr.send();
                xhr.onload = function() {
                    document.getElementById("content").innerHTML = xhr.responseText;
                };
            }
        
        </script>
    </body>
</html>';

?>