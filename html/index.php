<?php

$a = microtime(true);

ob_start();

require_once("../app/local_config.php");

App::run();

ob_flush();

if(false && ENV == 'dev')
{
	echo "<br/><hr size=\"1\" /><p style=\"color:silver;font-size:11px;\">". round(microtime(true)-$a, 4) ."s";
	echo "<br/>". round(memory_get_peak_usage()/1024/1024,4) ."MB";
    echo "<br/>". Session::getRequestKey() ."</p>";
}
?>
