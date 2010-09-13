<?PHP

include('classes/NexradDecoder.php');
include('classes/RadialPacketDecoder.php');

$reflectivityDecoder = new RadialPacketDecoder();
$reflectivityDecoder->setFileResource('c:\nexrad\sn.last.br');

$headers = $reflectivityDecoder->parseMHB();
$description = $reflectivityDecoder->parsePDB();
$symbology = $reflectivityDecoder->parsePSB();

$width = 600;
$height = 600;

$im = @imagecreatetruecolor ($width, $height);
imageantialias($im, true);
imagealphablending($im, true);

$background_color = ImageColorAllocate ($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, $width-1, $height-1, $background_color);

$color[0] = ImageColorAllocate ($im, 0, 0, 0);
$color[1] = ImageColorAllocate ($im, 153, 255, 255);
$color[2] = ImageColorAllocate ($im, 102, 153, 255);
$color[3] = ImageColorAllocate ($im, 0, 0, 204);
$color[4] = ImageColorAllocate ($im, 153, 255, 0);
$color[5] = ImageColorAllocate ($im, 51, 204, 0);
$color[6] = ImageColorAllocate ($im, 51, 102, 0);
$color[7] = ImageColorAllocate ($im, 255, 255, 51);
$color[8] = ImageColorAllocate ($im, 255, 204, 0);
$color[9] = ImageColorAllocate ($im, 255, 153, 0);
$color[10] = ImageColorAllocate ($im, 255, 0, 0);
$color[11] = ImageColorAllocate ($im, 204, 0, 0);
$color[12] = ImageColorAllocate ($im, 153, 0, 0);
$color[13] = ImageColorAllocate ($im, 255, 0, 204);
$color[14] = ImageColorAllocate ($im, 204, 0, 255);
$color[15] = ImageColorAllocate ($im, 255, 255, 255);


for($i = 0; $i <= 359; $i++)
{

	$start_angle = $i;
	
	for($j = 0; $j <= count($symbology['radial'][$start_angle]['colorValues']) - 1; $j++)
	{
		
		$points = array();
		$colorValuePosition = $j + 1;
		
		$colorValueCode = $symbology['radial'][$start_angle]['colorValues'][$j];
		
		$angle_delta = $symbology['radial'][$start_angle]['angledelta'];

		$points[] = (cos(deg2rad($start_angle)) * ($colorValuePosition - 1)) + ($width / 2);
		$points[] = (sin(deg2rad($start_angle)) * ($colorValuePosition - 1)) + ($height / 2);
		
		$points[] = (cos(deg2rad($start_angle + $angle_delta)) * ($colorValuePosition - 1)) + ($width / 2);
		$points[] = (sin(deg2rad($start_angle + $angle_delta)) * ($colorValuePosition - 1)) + ($height / 2);
		
		$points[] = (cos(deg2rad($start_angle)) * $colorValuePosition) + ($width / 2);
		$points[] = (sin(deg2rad($start_angle)) * $colorValuePosition) + ($height / 2);
		
		$points[] = (cos(deg2rad($start_angle + $angle_delta)) * $colorValuePosition) + ($width / 2);
		$points[] = (sin(deg2rad($start_angle + $angle_delta)) * $colorValuePosition) + ($height / 2);

		imagefilledpolygon($im, $points, 4, $color[$colorValueCode]);
	}

}

header("Content-type: image/png");
imagepng($im);
?>