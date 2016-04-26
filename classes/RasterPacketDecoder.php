<?php

//
// Created by Chris Harrell, 09/12/2010
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
		$this->symbology_block['row'] = array();
		
		for($rowNumber=0; $rowNumber < $this->symbology_block['num_of_rows']; $rowNumber++)
		{
		
			$rowBytes = $this->readHalfWord();			
			$this->symbology_block['row'][$rowNumber] = array();
			$this->symbology_block['row'][$rowNumber]['data'] = array();
			$this->symbology_block['row'][$rowNumber]['bytes'] = $rowBytes;
			
			for($j = 0; $j < $rowBytes; $j++)
			{
				$tempColorValues = $this->parseRLE();
				$this->symbology_block['row'][$rowNumber]['data'] = array_merge($this->symbology_block['row'][$rowNumber]['data'], $tempColorValues);
			}
			
		}
	}


}

?>
