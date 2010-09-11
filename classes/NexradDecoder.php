<?php

//
// Created by Chris Harrell (john.c.harrell@gmail.com), 09/10/2010
//
// $Id: NexradDecoder, v1.1
// $Revision: 1.1 $
//
// Base NexradDecoder class.

class NexradDecoder
{

	var $handle;
	var $filename;
	
	var $msg_header_block;
	var $msg_header_block_offset;
	
	var $description_block;
	var $description_block_offset;
	
	var $symbology_block;
	var $symbology_block_offset;
	
	
	///////////////////////////////////////////// 
	/* This constructor is executed when the   */
	/* object is first created                 */
	///////////////////////////////////////////// 
	function __construct()
	{
		
		$this->initializeVariables();                           // Initialize method variables
	}


	///////////////////////////////////////////// 
	/* Initialize method variables             */
	///////////////////////////////////////////// 
	function initializeVariables()
	{

		$this->msg_header_block = array();                      // Array to hold Message Header Block data.
		$this->description_block = array();                     // Array to hold Product Description Block data.
		$this->symbology_block = array();                       // Array to hold the Product Symbology Block data.
		
		$this->msg_header_block_offset = 30;
		$this->description_block_offset = 48;
	}

	///////////////////////////////////////////// 
	/* Create file handle resource             */
	///////////////////////////////////////////// 
	function setFileResource($fileName)
	{
		$this->filename = $fileName;
		$this->handle = fopen($this->filename, "rb");	
	}

	///////////////////////////////////////////// 
	/* Read a half word (4 bytes)              */
	///////////////////////////////////////////// 
	function readHalfWord()
	{
		return $this->str2dec(fread($this->handle,2));                 // Read two bytes of data (halfword)
	}


	///////////////////////////////////////////// 
	/* Read a whole word (4 bytes)             */
	/////////////////////////////////////////////
	function readWord()
	{
		return $this->str2dec(fread($this->handle,4));                 // Read four bytes of data (Two Halfwords / 1 Word)
	}
	
	///////////////////////////////////////////// 
	/* Read 4 bit RLE data                     */
	/////////////////////////////////////////////
	function parseRLE($startAngle)
	{
		$data = bin2hex(fread($this->handle,1));
		$split_data = str_split($data,1);

		$length = hexdec($split_data[0]);
		$colorValue = hexdec($split_data[1]);
		
		for($k=1; $k <= $length; $k++)
		{
			$this->symbology_block['radial'][$startAngle]['colorValues'][] = $colorValue;
		}

	}


	/////////////////////////////////////////////
	/* Convert a binary value into decimal.    */
	/////////////////////////////////////////////	
	function str2dec($binaryString)
	{
		$hexidecimalValue = bin2hex($binaryString);             // Convert the binary string into hexidecimal
		$decimalValue = hexdec($hexidecimalValue);              // Convert the hexideximal value into decima
		return $decimalValue;
	}

	/////////////////////////////////////////////
	/* Convert seconds to HH:MM:SS format      */
    /*                                         */
	/* Created by: jon@laughing-buddha.net.    */
	/////////////////////////////////////////////
	function sec2hms ($sec, $padHours = false) 
	{
 
		$hms = "";                                               // start with a blank string
		$hours = intval(intval($sec) / 3600);
		
		$hms .= ($padHours)                                     // Add hours to $hms (with a leading 0 if specified in function parameters)
			  ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
			  : $hours. ":";
			  
		$minutes = intval(($sec / 60) % 60); 
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";   // add minutes to $hms (with a leading 0 if needed)
		$seconds = intval($sec % 60); 
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);        // add seconds to $hms (with a leading 0 if needed)

		return $hms;
    
	}

	/////////////////////////////////////////////
	/* Parse the Message Header Block into an  */
    /* array and return it.                    */
	/////////////////////////////////////////////
	function parseMHB()
	{
		fseek($this->handle, $this->msg_header_block_offset);
		$this->msg_header_block['code'] = $this->readHalfWord();                            // HW 1
		$this->msg_header_block['date'] = jdtogregorian($this->readHalfWord() + 2440586.5); // HW 2
		$this->msg_header_block['time'] = $this->sec2hms($this->readWord(), true);          // HW 3 & 4
		$this->msg_header_block['length'] = $this->readWord();                              // HW 5 & HW 6
		$this->msg_header_block['sourceID'] = $this->readHalfWord();                        // HW 7
		$this->msg_header_block['destinationID'] = $this->readHalfWord();                   // HW 8
		$this->msg_header_block['numberOfBlocks'] = $this->readHalfWord();                  // HW 9
		
		return $this->msg_header_block;
	}
	
	/////////////////////////////////////////////
	/* Parse the Product Description Block     */
    /* into an array and return it.            */
	/////////////////////////////////////////////
	function parsePDB()
	{
		fseek($this->handle, $this->description_block_offset);

		$this->description_block['divider'] = $this->readHalfWord();  
		
		$this->description_block['latitude'] = $this->readWord() / 1000;                                //HW 11 - 12
		$this->description_block['longitude'] = $this->readWord() / 1000;                               //HW 13 - 14
		$this->description_block['height'] = $this->readHalfWord();                                     //HW 15
		$this->description_block['code'] = $this->readHalfWord();                                       //HW 16
		$this->description_block['mode'] = $this->readHalfWord();                                       //HW 17
		$this->description_block['volumecoveragepattern'] = $this->readHalfWord();                      //HW 18
		$this->description_block['sequencenumber'] = $this->readHalfWord();                             //HW 19

		//Halfword 20
		$this->description_block['scannumber'] = $this->readHalfWord();                                 //HW 20
		$this->description_block['scandate'] = jdtogregorian($this->readHalfWord() + 2440586.5);        //HW 21
		$this->description_block['scantime'] = $this->sec2hms($this->readWord(), true);                 //HW 22 - 23
		$this->description_block['generationdate'] = jdtogregorian($this->readHalfWord() + 2440586.5);  //HW 24
		$this->description_block['generationtime'] = $this->sec2hms($this->readWord(), true);           //HW 25 - 26
		$this->description_block['productspecific_1'] = $this->readHalfWord();                          //HW 27
		$this->description_block['productspecific_2'] = $this->readHalfWord();                          //HW 28
		$this->description_block['elevationnumber'] = $this->readHalfWord();                            //HW 29

		//Halfword 30
		$this->description_block['productspecific_3'] = $this->readHalfWord() / 10;                     //HW 30
		$this->description_block['threshold_1'] = $this->readHalfWord();                                //HW 31
		$this->description_block['threshold_2'] = $this->readHalfWord();                                //HW 32
		$this->description_block['threshold_3'] = $this->readHalfWord();                                //HW 33
		$this->description_block['threshold_4'] = $this->readHalfWord();                                //HW 34
		$this->description_block['threshold_5'] = $this->readHalfWord();                                //HW 35
		$this->description_block['threshold_6'] = $this->readHalfWord();                                //HW 36
		$this->description_block['threshold_7'] = $this->readHalfWord();                                //HW 37
		$this->description_block['threshold_8'] = $this->readHalfWord();                                //HW 38
		$this->description_block['threshold_9'] = $this->readHalfWord();                                //HW 39

		//Halfword 40
		$this->description_block['threshold_10'] = $this->readHalfWord();                                //HW 40
		$this->description_block['threshold_11'] = $this->readHalfWord();                                //HW 41
		$this->description_block['threshold_12'] = $this->readHalfWord();                                //HW 42
		$this->description_block['threshold_13'] = $this->readHalfWord();                                //HW 43
		$this->description_block['threshold_14'] = $this->readHalfWord();                                //HW 44
		$this->description_block['threshold_15'] = $this->readHalfWord();                                //HW 45
		$this->description_block['threshold_16'] = $this->readHalfWord();                                //HW 46
		$this->description_block['productspecific_4'] = $this->readHalfWord();                           //HW 47
		$this->description_block['productspecific_5'] = $this->readHalfWord();                           //HW 48
		$this->description_block['productspecific_6'] = $this->readHalfWord();                           //HW 49

		//Halfword 50
		$this->description_block['productspecific_7'] = $this->readHalfWord();                           //HW 50
		$this->description_block['productspecific_8'] = $this->readHalfWord();                           //HW 51
		$this->description_block['productspecific_9'] = $this->readHalfWord();                           //HW 52
		$this->description_block['productspecific_10'] = $this->readHalfWord();                          //HW 53
		$this->description_block['version'] = $this->readHalfWord();                                     //HW 54
		$this->description_block['symbologyoffset'] = $this->readWord();                                 //HW 55 - 56
		$this->description_block['graphicoffset'] = $this->readWord();                                   //HW 57 - 58
		$this->description_block['tabularoffset'] = $this->readWord();                                   //HW 59 - 60
		
		return $this->description_block;
	}


	/////////////////////////////////////////////
	/* Parse the Product Symbology Block into  */
    /* an array and return it.                 */
	/////////////////////////////////////////////
	function parsePSB()
	{
		$this->symbology_block_offset = ($this->description_block['symbologyoffset'] * 2) + $this->msg_header_block_offset;
		
		fseek($this->handle, $this->symbology_block_offset);
		
		$this->symbology_block['divider'] = $this->readHalfWord();
		$this->symbology_block['blockid'] = $this->readHalfWord();
		$this->symbology_block['blocklength'] = $this->readWord();
		$this->symbology_block['numoflayers'] = $this->readHalfWord();
		
		for($i = 1; $i <= $this->symbology_block['numoflayers']; $i++)
		{
			$this->parseLayers();
		}
		
		return $this->symbology_block;
	}	


}


?>