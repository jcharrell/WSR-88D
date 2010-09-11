<?php

include('classes/NexradDecoder.php');

$nexradDecoder = new NexradDecoder('sn.last.br');
$headers = $nexradDecoder->parseMHB();
$description = $nexradDecoder->parsePDB();
$symbology = $nexradDecoder->parsePSB();

print_r($headers);
print_r($description);
print_r($symbology);
?>