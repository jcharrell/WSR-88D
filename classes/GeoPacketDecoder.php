<?php

//
// Created by Chris Harrell, 09/15/2010
//
// $Id: RadialDecoder, v1.0
// $Revision: 1.0 $
//
// WSR-88D Geographic and Non-geographic decoder class.
//
// This will decode all Radial Data Packets (Packet Code: 0xAF1F)

class GeoPacketDecoder extends NexradDecoder

{

	function parseLayers()
	{
		$this->symbology_block['layerdivider'] = $this->readHalfWord(true);
		$this->symbology_block['layerlength'] = $this->readWord();
		
		//Begining of packet
		$this->symbology_block['layerpacketcode'] = dechex($this->readHalfWord()); 
		$this->symbology_block['length_of_block'] = $this->readHalfWord();  // Length in bytes
		for($i = 1; $i <= $this->symbology_block['length_of_block'] / 4; $i++)
		{
			$this->symbology_block['hailpos'][$i] = array();
			$this->symbology_block['hailpos'][$i]['pos_i'] = $this->readHalfWord(true);
			$this->symbology_block['hailpos'][$i]['pos_j'] = $this->readHalfWord(true);
		}
		$this->symbology_block['blockdivider'] = $this->readHalfWord();
	}

}

?>
