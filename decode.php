<?php

include('classes/NexradDecoder.php');
include('classes/RasterPacketDecoder.php');


$reflectivityDecoder = new RasterPacketDecoder();
$reflectivityDecoder->setFileResource('c:\nexrad\sn.last.cr');

$headers = $reflectivityDecoder->parseMHB();
$description = $reflectivityDecoder->parsePDB();
$symbology = $reflectivityDecoder->parsePSB();

print_r($headers);
print_r($description);
print_r($symbology);

?>