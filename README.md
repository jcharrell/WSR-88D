# WSR-88D

> This was an early proof of concept to decode WSR-88D Nexrad radar data obtained from [NOAA](http://www.nws.noaa.gov/tg/radfiles.php).  While the data structure of the radar product files should ramain the same, it is possible that the mappings are dated, as this was developed in 2010.  Do not expect further updates within this repository.


## Install

```
$ npm install node-spc-storm-reports
```


## Usage
Further examples may be found within the [examples/](https://github.com/jcharrell/WSR-88D/tree/master/examples) folder.

```php
include('classes/NexradDecoder.php');
include('classes/RasterPacketDecoder.php');


$reflectivityDecoder = new RasterPacketDecoder();
$reflectivityDecoder->setFileResource('/tmp/sn.last');
$headers = $reflectivityDecoder->parseMHB();
$description = $reflectivityDecoder->parsePDB();
$symbology = $reflectivityDecoder->parsePSB();
if($description['graphicoffset'] != 0)
{
	$graphic = $reflectivityDecoder->parseGAB();
}
```
 