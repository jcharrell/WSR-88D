<?PHP

include('classes/NexradDecoder.php');

// Composite Reflectivity is encoded as a Raster Image, so we'll use
// the Raster Packet Decoder class.
include('classes/RasterPacketDecoder.php');
$crDecoder = new RasterPacketDecoder();
$crDecoder->setFileResource('c:\nexrad\sn.last.cr.kfws');

// Now we decode all the available blocks.
$headers = $crDecoder->parseMHB();
$description = $crDecoder->parsePDB();
$symbology = $crDecoder->parsePSB();
if($description['graphicoffset'] != 0)
{
	$graphic = $crDecoder->parseGAB();
}


$width = 464;
$height = 464;

$im = @imagecreatetruecolor ($width, $height);
imageantialias($im, true);
imagealphablending($im, true);

$background_color = ImageColorAllocate ($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, $width, $height, $background_color);

$color[0] = ImageColorAllocate ($im, 0, 0, 0);
$color[1] = ImageColorAllocate ($im, 0, 234, 236);
$color[2] = ImageColorAllocate ($im, 1, 160, 246);
$color[3] = ImageColorAllocate ($im, 0, 0, 246);
$color[4] = ImageColorAllocate ($im, 0, 255, 0);
$color[5] = ImageColorAllocate ($im, 0, 200, 0);
$color[6] = ImageColorAllocate ($im, 0, 144, 0);
$color[7] = ImageColorAllocate ($im, 255, 255, 0);
$color[8] = ImageColorAllocate ($im, 231, 192, 0);
$color[9] = ImageColorAllocate ($im, 255, 144, 0);
$color[10] = ImageColorAllocate ($im, 255, 0, 0);
$color[11] = ImageColorAllocate ($im, 214, 0, 0);
$color[12] = ImageColorAllocate ($im, 192, 0, 0);
$color[13] = ImageColorAllocate ($im, 255, 0, 255);
$color[14] = ImageColorAllocate ($im, 153, 85, 201);
$color[15] = ImageColorAllocate ($im, 255, 255, 255);

for($y=0; $y< $symbology['num_of_rows']; $y++)
{
	for($x=0; $x < count($symbology['row'][$y]['data']) - 1; $x++)
	{
		$colorCode = $symbology['row'][$y]['data'][$x];
		imagesetpixel($im, $x, $y, $color[$colorCode]);
	}
}	

header("Content-type: image/png");
imagepng($im);

?>