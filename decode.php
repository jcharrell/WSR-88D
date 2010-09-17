<?php

include('classes/NexradDecoder.php');
include('classes/RasterPacketDecoder.php');


$reflectivityDecoder = new RasterPacketDecoder();
//$reflectivityDecoder->setFileResource('c:\nexrad\sn.last.hi.klot');
$reflectivityDecoder->setFileResource('c:\nexrad\sn.last.et.kfws');
$headers = $reflectivityDecoder->parseMHB();
$description = $reflectivityDecoder->parsePDB();
$symbology = $reflectivityDecoder->parsePSB();
if($description['graphicoffset'] != 0)
{
	$graphic = $reflectivityDecoder->parseGAB();
}

//print_r($headers);
//print_r($description);
print_r($symbology);
//print_r($graphic);

?>