<?php

include('classes/NexradDecoder.php');
include('classes/RadialPacketDecoder.php');


$reflectivityDecoder = new RadialPacketDecoder();
$reflectivityDecoder->setFileResource('c:\nexrad\sn.last.stp');

$headers = $reflectivityDecoder->parseMHB();
$description = $reflectivityDecoder->parsePDB();
$symbology = $reflectivityDecoder->parsePSB();

print_r($headers);
print_r($description);
print_r($symbology);

?>