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
	
	var $graphic_block;
	var $graphic_block_offset;	
	
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
	/* Read a byte (1 byte)              */
	///////////////////////////////////////////// 
	function readByte($negativeRange = false)
	{
		if($negativeRange === true) 
			return $this->dec2negdec($this->str2dec(fread($this->handle,1)), 8);
		else 
			return $this->str2dec(fread($this->handle,1));                // Read two bytes of data (halfword)
	}	
	
	///////////////////////////////////////////// 
	/* Read a half word (2 bytes)              */
	///////////////////////////////////////////// 
	function readHalfWord($negativeRange = false)
	{
		if($negativeRange === true) 
			return $this->dec2negdec($this->str2dec(fread($this->handle,2)), 16);
		else 
			return $this->str2dec(fread($this->handle,2));                // Read two bytes of data (halfword)
	}


	///////////////////////////////////////////// 
	/* Read a two halfwords (4 bytes)          */
	/////////////////////////////////////////////
	function readWord($negativeRange = false)
	{
		if($negativeRange === true) 
			return $this->dec2negdec($this->str2dec(fread($this->handle,4)), 32);
		else 
			return $this->str2dec(fread($this->handle,4));                 // Read four bytes of data (Two Halfwords / 1 Word)
	}
	
	///////////////////////////////////////////// 
	/* Read 4 bit RLE data                     */
	/////////////////////////////////////////////
	function parseRLE()
	{
		$valueArray = array();
		
		$data = bin2hex(fread($this->handle,1));
		$split_data = str_split($data,1);
		
		$length = hexdec($split_data[0]);
		$value = hexdec($split_data[1]);
		
		// Reduce the color values if the radar is in clean air mode and the current product is one of many Base Reflectivity products
		if($this->description_block['mode'] == 1 && ($this->description_block['code'] >= 16 && $this->description_block['code'] <= 21) )
		{
			if($value >= 8) $value -= 8;
			elseif($value < 8) $value = 0;
		}
		
		for($i=1; $i <= $length; $i++)
		{
			$valueArray[] = $value;
		}
		
		return $valueArray;

	}


	/////////////////////////////////////////////
	/* Convert a binary value into decimal.    */
	/////////////////////////////////////////////	
	function str2dec($binaryString)
	{
		// Oddly enough bindec does not convert the binary data to decimal correctly.  The work 
		// around is to first convert the data to hex and then convert from hex to decimal.
		return hexdec(bin2hex($binaryString));
	}

	/////////////////////////////////////////////
	/* Convert a decimal value into the decimal*/
	/* value of it's negative binary form.     */
	/* Save us all!!! There must be a better   */
	/* way!!!                                  */
	/////////////////////////////////////////////		
	function dec2negdec($value, $bits)
	{
		$binaryPadding = null;
		$binaryValue = decbin($value);
		
		// decbin does not padd the resulting binary with 0's, which screws up my idea
		// to check the MSB for a negative flag.  Now I must append 0's if the binary
		// number requires it.
		if(strlen($binaryValue) < $bits)
		{
			$paddingBits = $bits - strlen($binaryValue);
			for($i=1;$i<=$paddingBits;$i++)
			{
				$binaryPadding .= '0';
			}
			$binaryValue = $binaryPadding . $binaryValue;
		}
		
		// If the most significant bit is 1, then handle as a negative binary number
		if($binaryValue[0] == 1)
		{
			$binaryValue = str_replace("0", "x", $binaryValue);
			$binaryValue = str_replace("1", "0", $binaryValue);
			$binaryValue = str_replace("x", "1", $binaryValue);
			$negDecimalValue = (bindec($binaryValue) + 1) * -1;
			
			return $negDecimalValue;
		}
		
		// If the MSB is not 1, then return the original value
		else return $value;
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
	/* Parse the Graphic Alphanumeric Block    */
    /* pages into an array and return it.  To  */
	/* be called by the parseGAB() method.     */
	/////////////////////////////////////////////
	private function _parsePages()
	{
		$page['number'] = $this->readHalfWord();
		$page['length'] = $this->readHalfWord();
		$totalBytesToRead = $page['length'];
		$vectorID = 0;
		$messageID = 0;
		
		while($totalBytesToRead > 0)
		{
			$packetCode = $this->readHalfWord();
			$packetLength = $this->readHalfWord();	

			// If the packet code is 8 then decode it as a Text & Special Symbol Packet
			if($packetCode == 8)
			{
				$messageID++;
				$page['data']['messages'][$messageID]['text_color'] = $this->readHalfWord();
				$page['data']['messages'][$messageID]['pos-i'] = $this->readHalfWord(true);
				$page['data']['messages'][$messageID]['pos-j'] = $this->readHalfWord(true);
				$page['data']['messages'][$messageID]['message'] = null;
				
				// We have already 6 bytes of this packet.  Subtract it from the amount of 
				// bytes thare still need to be read.
				$packetBytesToRead = $packetLength - 6;

				// Read the remaining bytes ($packetBytesToRead) to obtain the actual message
				// that is encoded in the packet
				for($j = 0; $j < $packetBytesToRead; $j++)
				{
					$page['data']['messages'][$messageID]['message'] .= chr($this->readByte());
				}

				// Subtract the total length of the packet ($packetLength) from the total bytes
				// in the page ($totalBytesToRead).  We must account for the 4 bytes that were
				// read while reading the packet code and packet length, because they are not included
				// in the Packet Length.
				$totalBytesToRead -= ($packetLength + 4);
			}
			
			
			// If the packet code is 10 then decode it as a Unlinked Vector Packet
			elseif ($packetCode == 10)
			{
				
				$page['data']['vectors']['color'] = $this->readHalfWord();
				
				// We have already 2 bytes of this packet.  Subtract it from the amount of 
				// bytes thare still need to be read.
				$packetBytesToRead = $packetLength - 2;
				
				$vectorID = 0;
				while($packetBytesToRead > 0)
				{
					$vectorID++;
					
					$page['data']['vectors'][$vectorID]['pos-i_begin'] = $this->readHalfWord(true);
					$page['data']['vectors'][$vectorID]['pos-j_begin'] = $this->readHalfWord(true);
					$page['data']['vectors'][$vectorID]['pos-i_end'] = $this->readHalfWord(true);
					$page['data']['vectors'][$vectorID]['pos-j_end'] = $this->readHalfWord(true);
					
					// Subtract the 8 bytes that we just read from the amount of packet bytes remaining 
					// to be read ($packetBytesToRead).
					$packetBytesToRead -= 8;
				}				

				// Subtract the total length of the packet ($packetLength) from the total bytes
				// in the page ($totalBytesToRead).  We must account for the 4 bytes that were
				// read while reading the packet code and packet length, because they are not included
				// in the Packet Length.
				$totalBytesToRead -= ($packetLength + 4);
			}
				
		}
		return $page;
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

		$this->description_block['divider'] = $this->readHalfWord(true);                                //HW 10	
		$this->description_block['latitude'] = $this->readWord() / 1000;                                //HW 11 - 12
		$this->description_block['longitude'] = $this->readWord(true) / 1000;                           //HW 13 - 14
		$this->description_block['height'] = $this->readHalfWord(true);                                 //HW 15
		$this->description_block['code'] = $this->readHalfWord(true);                                   //HW 16
		$this->description_block['mode'] = $this->readHalfWord();                                       //HW 17
		$this->description_block['volumecoveragepattern'] = $this->readHalfWord();                      //HW 18
		$this->description_block['sequencenumber'] = $this->readHalfWord();                             //HW 19

		$this->description_block['scannumber'] = $this->readHalfWord();                                 //HW 20
		$this->description_block['scandate'] = jdtogregorian($this->readHalfWord() + 2440586.5);        //HW 21
		$this->description_block['scantime'] = $this->sec2hms($this->readWord(), true);                 //HW 22 - 23
		$this->description_block['generationdate'] = jdtogregorian($this->readHalfWord() + 2440586.5);  //HW 24
		$this->description_block['generationtime'] = $this->sec2hms($this->readWord(), true);           //HW 25 - 26
		$this->description_block['productspecific_1'] = $this->readHalfWord();                          //HW 27
		$this->description_block['productspecific_2'] = $this->readHalfWord();                          //HW 28
		$this->description_block['elevationnumber'] = $this->readHalfWord();                            //HW 29

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

		$this->description_block['threshold_10'] = $this->readHalfWord();                               //HW 40
		$this->description_block['threshold_11'] = $this->readHalfWord();                               //HW 41
		$this->description_block['threshold_12'] = $this->readHalfWord();                               //HW 42
		$this->description_block['threshold_13'] = $this->readHalfWord();                               //HW 43
		$this->description_block['threshold_14'] = $this->readHalfWord();                               //HW 44
		$this->description_block['threshold_15'] = $this->readHalfWord();                               //HW 45
		$this->description_block['threshold_16'] = $this->readHalfWord();                               //HW 46
		$this->description_block['productspecific_4'] = $this->readHalfWord();                          //HW 47
		$this->description_block['productspecific_5'] = $this->readHalfWord();                          //HW 48
		$this->description_block['productspecific_6'] = $this->readHalfWord();                          //HW 49

		$this->description_block['productspecific_7'] = $this->readHalfWord();                          //HW 50
		$this->description_block['productspecific_8'] = $this->readHalfWord();                          //HW 51
		$this->description_block['productspecific_9'] = $this->readHalfWord();                          //HW 52
		$this->description_block['productspecific_10'] = $this->readHalfWord();                         //HW 53
		$this->description_block['version'] = $this->readByte();                                        //HW 54
		$this->description_block['spot_blank'] = $this->readByte();                                     //HW 54		
		$this->description_block['symbologyoffset'] = $this->readWord();                                //HW 55 - 56
		$this->description_block['graphicoffset'] = $this->readWord();                                  //HW 57 - 58
		$this->description_block['tabularoffset'] = $this->readWord();                                  //HW 59 - 60
		
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

	/////////////////////////////////////////////
	/* Parse the Graphic Alphanumeric Block    */
    /* into an array and return it.            */
	/////////////////////////////////////////////
	function parseGAB()
	{
		$this->graphic_block_offset = ($this->description_block['graphicoffset'] * 2) + $this->msg_header_block_offset;
		
		
		fseek($this->handle, $this->graphic_block_offset);
		
		$this->graphic_block['divider'] = $this->readHalfWord(true);
		$this->graphic_block['blockid'] = $this->readHalfWord();
		$this->graphic_block['block_length'] = $this->readWord();
		$this->graphic_block['num_of_pages'] = $this->readHalfWord();
		
		
		for($i = 1; $i <= $this->graphic_block['num_of_pages']; $i++)
		{
			$this->graphic_block['pages'][$i] = $this->_parsePages();
		}
		
		return $this->graphic_block;
	}


}


?>