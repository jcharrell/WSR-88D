<?php

include('classes/NexradDecoder.php');
include('classes/ReflectivityDecoder.php');


$reflectivityDecoder = new ReflectivityDecoder();
$reflectivityDecoder->setFileResource('c:\nexrad\sn.last.br');

$headers = $reflectivityDecoder->parseMHB();
$description = $reflectivityDecoder->parsePDB();
$symbology = $reflectivityDecoder->parsePSB();

//print_r($headers);
//print_r($description);
//print_r($symbology);
?>