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
		$this->symbology_block['row'] = array();
		
		//Appears to work if it iterates through one row of data, but if more than one row is iterated, it gets just won't work
		//for($rowNumber=1; $rowNumber <= $this->symbology_block['num_of_rows']; $rowNumber++)
		for($rowNumber=1; $rowNumber <= 1; $rowNumber++)
		{
			$this->symbology_block['row'][$rowNumber] = array();
			$this->symbology_block['row'][$rowNumber]['data'] = array();
			$rowBytes = $this->readHalfWord();
			
			for($j = 1; $j <= ($rowBytes * 2); $j++)
			{
				//$this->symbology_block['row'][$rowNumber]['data'] = array_merge($this->symbology_block['row'][$rowNumber]['data'], $this->parseRLE());
				$this->parseRLE();
			}
			
			//echo max($this->symbology_block['row'][$rowNumber]['data']) . "\n";
		}
	}


}

?>