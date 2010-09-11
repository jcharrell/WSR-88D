
<?php
// git test
// Convert the binary string into hex
// and then convert the hex into decimal;
function str2dec($str)
{
	$hex = bin2hex($str);
	$dec = hexdec($hex);
	//echo "$hex - $dec\n";
	return $dec;
}

// Read a whole word (4 bytes)
function getrle($handle)
{
	$data = bin2hex(fread($handle,1));
	$split_data = str_split($data,1);
	$rle_data['run'] = hexdec($split_data[0]);
	$rle_data['code'] = hexdec($split_data[1]);
	return $rle_data;
}

// Read a half word (1 byte)
function half($handle)
{
	return str2dec(fread($handle,2));
}

// Read a whole word (4 bytes)
function whole($handle)
{
	return str2dec(fread($handle,4));
}

// Convert seconds into HH:MM:SS format
function sec2hms ($sec, $padHours = false) 
{
    $hms = "";

    $hours = intval(intval($sec) / 3600); 
    $hms .= ($padHours) 
          ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
          : $hours. ":";

    $minutes = intval(($sec / 60) % 60); 
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

    $seconds = intval($sec % 60); 
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    return $hms;
}

  
$filename = 'sn.last.br';
$handle = fopen($filename, "rb");
fread($handle, 30);

echo "File Size: ". (filesize($filename) - 30) . "\n";

//Message Header Block
//Halfword 1 - 9
$header['code'] = half($handle);
$header['date'] = jdtogregorian(half($handle) + 2440586.5);
$header['time'] = sec2hms(whole($handle), true);
$header['length'] = whole($handle);
$header['source'] = half($handle);
$header['destination'] = half($handle);
$header['numberblocks'] = half($handle);

print_r($header);

//(-1) Divider
// Block Divider
half($handle);

// Product Description Block
//Halfword 11 - 60
$proddescription['latitude'] = whole($handle) / 1000;
$proddescription['longitude'] = (-1 * whole($handle)) / 1000; // How to handle negative numbers???
$proddescription['height'] = half($handle);
$proddescription['code'] = half($handle);
$proddescription['mode'] = half($handle);
$proddescription['volumecoveragepattern'] = half($handle);
$proddescription['sequencenumber'] = half($handle);

//Halfword 20
$proddescription['scannumber'] = half($handle);
$proddescription['scandate'] = jdtogregorian(half($handle) + 2440586.5);
$proddescription['scantime'] = sec2hms(whole($handle), true);
$proddescription['generationdate'] = jdtogregorian(half($handle) + 2440586.5);
$proddescription['generationtime'] = sec2hms(whole($handle), true);
$proddescription['productspecific1'] = half($handle);
$proddescription['productspecific2'] = half($handle);
$proddescription['elevationnumber'] = half($handle);

//Halfword 30
$proddescription['productspecific3'] = half($handle) / 10; //BR Elevation Angle
$proddescription['threshold1'] = half($handle);
$proddescription['threshold2'] = half($handle);
$proddescription['threshold3'] = half($handle);
$proddescription['threshold4'] = half($handle);
$proddescription['threshold5'] = half($handle);
$proddescription['threshold6'] = half($handle);
$proddescription['threshold7'] = half($handle);
$proddescription['threshold8'] = half($handle);
$proddescription['threshold9'] = half($handle);

//Halfword 40
$proddescription['threshold10'] = half($handle);
$proddescription['threshold11'] = half($handle);
$proddescription['threshold12'] = half($handle);
$proddescription['threshold13'] = half($handle);
$proddescription['threshold14'] = half($handle);
$proddescription['threshold15'] = half($handle);
$proddescription['threshold16'] = half($handle);
$proddescription['productspecific4'] = half($handle);  // BR Max Reflectivity
$proddescription['productspecific5'] = half($handle);
$proddescription['productspecific6'] = half($handle);

//Halfword 50
$proddescription['productspecific7'] = half($handle);
$proddescription['productspecific8'] = half($handle); // BR Cal. Constant (MSB)
$proddescription['productspecific9'] = half($handle); // BR Cal. Constant (LSB)
$proddescription['productspecific10'] = half($handle);
$proddescription['version'] = half($handle);
$proddescription['symbologyoffset'] = whole($handle);
$proddescription['graphicoffset'] = whole($handle);
$proddescription['tabularoffset'] = whole($handle);


print_r($proddescription);  // Print Product Description array to the screen for debugging purposes

// Goto the Symbology Block and skip the block divider
fseek($handle, (($proddescription['symbologyoffset'] * 2) + 30) );
half($handle);

// Begin reading Symbology Block
$symbology['blockid'] = half($handle);
$symbology['blocklength'] = whole($handle);
$symbology['numoflayers'] = half($handle);
$symbology['layerdivider'] = half($handle);
$symbology['layerlength'] = whole($handle);
$symbology['layerpacketcode'] = half($handle);  // BR Packet Type is HEX (0xAF1F)
$symbology['layerindexoffirstrangebin'] = half($handle);
$symbology['layernumberofrangebins'] = half($handle);
$symbology['i_centerofsweep'] = half($handle);
$symbology['j_centerofsweep'] = half($handle);
$symbology['scalefactor'] = half($handle) / 1000; // Number of pixels per range bin
$symbology['numberofradials'] = half($handle);

print_r($symbology); // Print Symbology array to the screen for debugging purposes

// Loop through the radials
for($i=1;$i<=50;$i++)
{

	$numofrle = half($handle);
	$startangle = half($handle) / 10;
	$angledelta = half($handle) / 10;
	//echo "numofrlehalfwordsinradial = " . $numofrle . "\n";
	//echo "radialstartangle = " . half($handle) / 10 . "\n";
	//echo "radialangledelta = " . half($handle) / 10 . "\n";

	echo "Radial Number: $i - $startangle degrees - RLEs: " . $numofrle*2 . "\n";
	
	$run = 0;
	// Loop through the radial data packets
	for($j=1;$j<=$numofrle;$j++)
	{
		$run += $RLE['run'];
		$RLE = getrle($handle);
		//echo "run = " . $RLE['run'] . "\n";
		//echo "colorcode = " . $RLE['code'] . "\n";
		$RLE = getrle($handle);
		$run += $RLE['run'];
		//echo "run = " . $RLE['run'] . "\n";
		//echo "colorcode = " . $RLE['code'] . "\n";
		//echo "\n";	

	}
	echo "$run\n";
}

?>