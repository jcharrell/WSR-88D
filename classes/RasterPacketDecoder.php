<?php

//
// Created by Chris Harrell (john.c.harrell@gmail.com), 09/12/2010
//
// $Id: RasterPacketDecoder, v1.0
// $Revision: 1.0 $
//
// WSR-88D Raster Image Decoder class.
//
// This will decode all Raster Data Packets (Packet Codes: 0xBA0F & 0xBA07)

class RasterPacketDecoder extends NexradDecoder

{


	function parseLayers()
	{
		$this->symbology_block['layerdivider'] = $this->readHalfWord();
		$this->symbology_block['layerlength'] = $this->readWord();
		$this->symbology_block['layerpacketcode'] = dechex($this->readHalfWord()); 
		$this->symbology_block['layerpacketcode2'] = dechex($this->readHalfWord());
		$this->symbology_block['layerpacketcode3'] = dechex($this->readHalfWord());	
		$this->symbology_block['i_coord_start'] = $this->readHalfWord();
		$this->symbology_block['j_coord_start'] = $this->readHalfWord();
		$this->symbology_block['x_scale_int']  = $this->readHalfWord();
		$this->symbology_block['x_scale_fraction'] = $this->readHalfWord();
		$this->symbology_block['y_scale_int']  = $this->readHalfWord();
		$this->symbology_block['y_scale_fraction'] = $this->readHalfWord();
		$this->symbology_block['num_of_rows'] = $this->readHalfWord();
		$this->symbology_block['packing_descriptor'] = $this->readHalfWord();
		
		for($i=1; $i <= $this->symbology_block['num_of_rows'] * 2; $i++)
		{
			$this->symbology_block['bytes_in_row'] = $this->readHalfWord();
			
			//Incomplete
			$this->parseRLE();
		}
	}


}

?>