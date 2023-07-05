<?php

// PDF_Label
//
// Class to print labels in Avery or custom formats
//
//
// Copyright (C) 2003 Laurent PASSEBECQ (LPA)
// Based on code by Steve Dillon : steved@mad.scientist.com
//
//-------------------------------------------------------------------
// VERSIONS :
// 1.0  : Initial release
// 1.1  : + : Added unit in the constructor
//        + : Now Positions start @ (1,1).. then the first image @top-left of a page is (1,1)
//        + : Added in the description of a label :
//                font-size    : defaut char size (can be changed by calling Set_Char_Size(xx);
//                paper-size    : Size of the paper for this sheet (thanx to Al Canton)
//                metric        : type of unit used in this description
//                              You can define your label properties in inches by setting metric to 'in'
//                              and printing in millimiter by setting unit to 'mm' in constructor.
//              Added some labels :
//                5160, 5161, 5162, 5163,5164 : thanx to Al Canton : acanton@adams-blake.com
//                8600                         : thanx to Kunal Walia : kunal@u.washington.edu
//        + : Added 3mm to the position of labels to avoid errors
// 1.2  : + : Added Set_Font_Name method
//        = : Bug of positioning
//        = : Set_Font_Size modified -> Now, just modify the size of the font
//        = : Set_Char_Size renamed to Set_Font_Size
/**
 * PDF_Label - PDF label editing
 *
 * @package PDF_Label
 * @author Laurent PASSEBECQ <lpasseb@numericable.fr>
 * @copyright 2003 Laurent PASSEBECQ
 **/
class PDF_Label extends FPDF
	{
	private $_Alignment = 'L';        // Alignment

	// Listing of labels size
	private $_Avery_Labels = [
		'5160' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 6, 'marginTop' => 12.7, 'NX' => 3,
			'NY' => 10, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 67, 'height' => 25.4, 'font-size' => 8],
		'5161' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 0.967, 'marginTop' => 10.7, 'NX' => 2,
			'NY' => 10, 'SpaceX' => 3.967, 'SpaceY' => 0, 'width' => 101.6, 'height' => 25.4, 'font-size' => 8],
		'5163' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 1.762, 'marginTop' => 10.7, 'NX' => 2,
			'NY' => 5, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 101.6, 'height' => 50.8, 'font-size' => 8],
		'5164' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 3.759, 'marginTop' => 12.7, 'NX' => 2,
			'NY' => 3, 'SpaceX' => 5.159, 'SpaceY' => 0, 'width' => 101.6, 'height' => 84.582, 'font-size' => 12],
		'5383' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 19.05, 'marginTop' => 25.4, 'NX' => 2,
			'NY' => 4, 'SpaceX' => 0, 'SpaceY' => 0, 'width' => 88.9, 'height' => 57.15, 'font-size' => 32],
		'5384' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 6.35, 'marginTop' => 25.4, 'NX' => 2,
			'NY' => 3, 'SpaceX' => 0, 'SpaceY' => 0, 'width' => 101.6, 'height' => 76.2, 'font-size' => 32],
		'5960' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 6.35, 'marginTop' => 12.7, 'NX' => 3,
			'NY' => 10, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 66.675, 'height' => 25.4, 'font-size' => 10],
		'5X95' => ['paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 17.4625, 'marginTop' => 15.875,
			'NX' => 2, 'NY' => 4, 'SpaceX' => 9.525, 'SpaceY' => 4.826, 'width' => 85.725, 'height' => 59.182,
			'font-size' => 32],
	];

	private $_Char_Size = 10;         // Character size

	private $_COUNTX = 1;

	private $_COUNTY = 1;

	private $_Font_Name = 'helvetica';    // Name of the font

	private $_Height = 0;          // Height of label

	private $_Image_Filename = '';         // Set to background image

	private $_Image_Width_Percent = 100;  // Image Width Percent

	private $_Line_Height = 10;         // Default line height

	// Private properties
	private $_Margin_Left = 0;          // Left margin of labels

	private $_Margin_Top = 0;          // Top margin of labels

	private $_Metric = 'mm';       // Type of metric for labels.. Will help to calculate good values

	private $_Metric_Doc = 'mm';       // Type of metric for the document

	private $_Width = 0;          // Width of label

	private $_X_Number = 0;          // Number of labels horizontally

	private $_X_Space = 0;          // Horizontal space between 2 labels

	private $_Y_Number = 0;          // Number of labels vertically

	private $_Y_Space = 0;          // Vertical space between 2 labels

	// Constructor
	public function __construct($format = '5160', $unit = 'mm', $posX = 1, $posY = 1)
		{
		if (\is_array($format))
			{
			// Custom format
			$Tformat = $format;
			}
		else
			{
			// Avery format
			$Tformat = $this->_Avery_Labels[$format];
			}
		parent::__construct('P', $Tformat['metric'], $Tformat['paper-size']);
		$this->_Set_Format($Tformat);
		$this->Set_Font_Name('helvetica');
		$this->SetMargins(0, 0);
		$this->SetAutoPageBreak(false);
		$this->_Metric_Doc = $unit;
		// Start at the given label position
		if ($posX > 0)
			{
			$posX--;
			}
		else
			{
			$posX = 0;
			}

		if ($posY > 0)
			{
			$posY--;
			}
		else
			{
			$posY = 0;
			}

		if ($posX >= $this->_X_Number)
			{
			$posX = $this->_X_Number - 1;
			}

		if ($posY >= $this->_Y_Number)
			{
			$posY = $this->_Y_Number - 1;
			}
		$this->_COUNTX = $posX;
		$this->_COUNTY = $posY;
		}
	// convert units (in to mm, mm to in)
	// $src and $dest must be 'in' or 'mm'

	public function _Convert_Metric($value, $src, $dest)
		{
		if ($src != $dest)
			{
			$tab['in'] = 39.37008;
			$tab['mm'] = 1000;

			return $value * $tab[$dest] / $tab[$src];
			}


			return $value;

		}

	public function _Get_Height_Chars($pt)
		{
		// Array matching character sizes and line heights
		// 72 points per inch
		return 25.4 * $pt / 72;
		}

	public function _Set_Format($format) : void
		{
		$this->_Metric = $format['metric'];
		$this->_Margin_Left = $this->_Convert_Metric($format['marginLeft'], $this->_Metric, $this->_Metric_Doc);
		$this->_Margin_Top = $this->_Convert_Metric($format['marginTop'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Space = $this->_Convert_Metric($format['SpaceX'], $this->_Metric, $this->_Metric_Doc);
		$this->_Y_Space = $this->_Convert_Metric($format['SpaceY'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Number = $format['NX'];
		$this->_Y_Number = $format['NY'];
		$this->_Width = $this->_Convert_Metric($format['width'], $this->_Metric, $this->_Metric_Doc);
		$this->_Height = $this->_Convert_Metric($format['height'], $this->_Metric, $this->_Metric_Doc);
		$this->Set_Font_Size($format['font-size']);
		}

	// Print a label

	public function Add_PDF_Label($texte) : void
		{
		// We are in a new page, then we must add a page
		if ((0 == $this->_COUNTX) && (0 == $this->_COUNTY))
			{
			$this->AddPage();
			}
		$_PosX = $this->_Margin_Left + ($this->_COUNTX * ($this->_Width + $this->_X_Space));
		$_PosY = $this->_Margin_Top + ($this->_COUNTY * ($this->_Height + $this->_Y_Space));

		if (\strlen($this->_Image_Filename))
			{
			$_PosY += 1;
			$this->SetXY($_PosX, $_PosY);   // go down one mm for better alignment
			$info = \getimagesize($this->_Image_Filename);
			$width = $info[0];

			if ($width > $this->_Width) // image is larger than label, adjust to 100%
				{
				$width = $this->_Width;
				}
			$width = $width * $this->_Image_Width_Percent / 100;
			$this->Image($this->_Image_Filename, $_PosX + ($this->_Width - $width) / 2, null, $width);
			}
		else    // no image, center label
			{
			// compute the offset from the top to center the label
			$offset = 0;
			$lines = \explode("\n", $texte);
			$height = $this->_Line_Height * \count($lines);

			if ($height < $this->_Height)
				{
				$offset = ($this->_Height - $height) / 2;
				}
			$this->SetXY($_PosX, $_PosY + $offset);
			}
		$this->MultiCell($this->_Width, $this->_Line_Height, $texte, 0, $this->_Alignment);
		$this->_COUNTX++;

		if ($this->_COUNTX == $this->_X_Number)
			{
			// Page full, we start a new one
			$this->_COUNTX = 0;
			$this->_COUNTY++;
			}

		if ($this->_COUNTY == $this->_Y_Number)
			{
			// End of column reached, we start a new one
			$this->_COUNTX = 0;
			$this->_COUNTY = 0;
			}
		}

	// Sets the character size
	// This changes the line height too

	public function getLabelStock()
		{
		return $this->_Avery_Labels;
		}
	// Set the maximum font size possible
	// for the given string and returns the points used

	public function Set_Alignment($align) : void
		{
		$this->_Alignment = $align;
		}

	public function Set_Background_Image($fileName, $percentageWidth = 100) : void
		{
		$this->_Image_Filename = $fileName;
		$this->_Image_Width_Percent = $percentageWidth;
		}

	public function Set_Font_Name($fontname) : void
		{
		if ('' != $fontname)
			{
			$this->_Font_Name = $fontname;
			$this->SetFont($this->_Font_Name);
			}
		}

	// Give the height for a char size given.

	public function Set_Font_Size($pt) : void
		{
		if ($pt > 3)
			{
			$this->_Char_Size = $pt;
			$this->_Line_Height = $this->_Get_Height_Chars($pt);
			$this->SetFontSize($this->_Char_Size);
			}
		}

	// Method to change font name

	public function Set_Max_Font_Size($string, $maxFont)
		{
		$size = $maxFont;
		$this->SetFontSize($size); // start with a large font

		while ($this->GetStringWidth($string . ' ') >= $this->_Width)
			{
			$size -= 2;
			$this->SetFontSize($size);
			}
		$this->Set_Font_Size($size);

		return $size;
		}
	}
