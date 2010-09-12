<?php

//
// Created by Chris Harrell (john.c.harrell@gmail.com), 09/11/2010
//
// $Id: RadialDecoder, v1.0
// $Revision: 1.0 $
//
// WSR-88D Radial Image Decoder class.
//
// This will decode all Radial Data Packets (Packet Code: 0xAF1F)

class RadialPacketDecoder extends NexradDecoder

{


	function parseLayers()
	{
		$this->symbology_block['layerdivider'] = $this->readHalfWord();
		$this->symbology_block['layerlength'] = $this->readWord();
		$this->symbology_block['layerpacketcode'] = dechex($this->readHalfWord()); 
		$this->symbology_block['layerindexoffirstrangebin'] = $this->readHalfWord();
		$this->symbology_block['layernumberofrangebins'] = $this->readHalfWord();
		$this->symbology_block['i_centerofsweep'] = $this->readHalfWord();
		$this->symbology_block['j_centerofsweep'] = $this->readHalfWord();
		$this->symbology_block['scalefactor'] = $this->readHalfWord() / 1000;
		$this->symbology_block['numberofradials'] = $this->readHalfWord();
		
		for($i=1; $i <= $this->symbology_block['numberofradials']; $i++)
		{
		
			$number_of_rles =  $this->readHalfWord();
			$startAngle = $this->readHalfWord() / 10;
			$angleDelta = $this->readHalfWord() / 10;

			$this->symbology_block['radial'][$startAngle] = array();
			$this->symbology_block['radial'][$startAngle]['colorValues'] = array();
			
			$this->symbology_block['radial'][$startAngle]['numOfRLE'] = $number_of_rles;
			$this->symbology_block['radial'][$startAngle]['angledelta'] = $angleDelta;		
			
			for($j=1; $j <= ($number_of_rles * 2); $j++)
			{
				$this->parseRLE($startAngle);
			}
		}
		
	}


}

?>