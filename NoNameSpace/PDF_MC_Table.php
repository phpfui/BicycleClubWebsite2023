<?php

class HMTLAttribs
	{
	public $color;

	public $fontFace;

	public $fontSize;

	public $fontStyle;

	public $justify;

	protected $pdf;

	public function __construct($pdf)
		{
		$this->pdf = $pdf;
		$this->SaveAttributes();
		}

	public function RestoreAttributes() : void
		{
		$this->pdf->SetFont($this->fontFace, $this->fontStyle, $this->fontSize);
		}

	public function SaveAttributes() : void
		{
		$this->fontStyle = $this->pdf->FontStyle;          //current font style
		$this->fontFace = $this->pdf->FontFamily;        //current font info
		$this->fontSize = $this->pdf->FontSizePt;         //current font size in points
		$this->justify = 'L';
		$this->color = [0,
			0,
			0, ]; // black
		}
	}

class PDF_MC_Table extends FPDF
	{
	public $aligns;

	public $attributes;

	public $attributeStack;

	public $docTitle;

	public $fill;

	public $fillCount;

	public $headerAligns = [];

	public $headerFont;

	public $headerFontSize;

	public $headerFontStyle;

	public $headers = [];

	public $HREF;

	public $index;

	public $noLines;

	public $numberPages;

	public $pageNumber;

	public $PRE;

	public $printingHTML;

	public $printingHTMLFirstTime;

	public $tag;

	public $widths = [];

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'Letter')
		{
		parent::__construct($orientation, $unit, $format);
		$this->fill = 0;
		$this->SetHeaderFont();
		$this->fillCount = 0;
		$this->noLines = false;
		$this->numberPages = $this->pageNumber = 0;
		$this->index = [];
		$this->HREF = '';
		$this->tag = '';
		$this->PRE = false;
		$this->printingHTML = false;
		$this->attributes = new HMTLAttribs($this);
		$this->attributes->SaveAttributes();
		$this->attributeStack = [];
		}

	public function CheckPageBreak($h) : void
		{
		//If the height h would cause an overflow, add a new page immediately
		$height = $h;

		if ($this->numberPages > 0)
			{
			$height += $this->GetLineHeight(10);
			}

		if ($this->GetY() + $height > $this->PageBreakTrigger)
			{
			$this->PrintPageNumber();
			$this->AddPage($this->CurOrientation, $this->CurPageSize);
			$this->PrintHeader();
			}
		}

	public function CloseTag() : void
		{
		//Closing tag
		$this->PopAttributes();
		}

	public function Footer() : void
		{
		if ($this->printingHTML)
			{
			$this->SetAutoPageBreak(true, 5);
			$this->PrintPageNumber();
			$this->SetAutoPageBreak(true, 12);
			}
		}

	public function GetLineHeight($points)
		{
		return $points * 25.4 / 72 * 1.20;
		}

	public function hex2dec($color = '#000000')
		{
		$tbl_color = [];
		$tbl_color['R'] = \hexdec(\substr($color, 1, 2));
		$tbl_color['G'] = \hexdec(\substr($color, 3, 2));
		$tbl_color['B'] = \hexdec(\substr($color, 5, 2));

		return $tbl_color;
		}

	public function IndexPage() : void
		{
		if (! isset($this->index[$this->docTitle]))
			{
			$this->index[$this->docTitle] = $this->pageNumber;
			}
		}

	public function LinesLeftOnPage($points)
		{
		return \floor(($this->PageBreakTrigger - $this->GetY()) / $this->GetLineHeight($points));
		}

	public function LoadIndex($filename) : void
		{
		$this->index = \unserialize(\file_get_contents($filename));
		}

	public function mySetTextColor($r, $g = 0, $b = 0) : void
		{
		static $_r = 0, $_g = 0, $_b = 0;

		if (-1 == $r)
			{
			$this->SetTextColor($_r, $_g, $_b);
			}
		else
			{
			$this->SetTextColor($r, $g, $b);
			$_r = $r;
			$_g = $g;
			$_b = $b;
			}
		}

	public function NbLines($w, $txt)
		{
		//Computes the number of lines a MultiCell of width w will take
		$cw = &$this->CurrentFont['cw'];

		if (0 == $w)
			{
			$w = $this->w - $this->rMargin - $this->x;
			}
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$s = \str_replace("\r", '', $txt);
		$nb = \strlen($s);

		if ($nb > 0 && "\n" == $s[$nb - 1])
			{
			$nb--;
			}
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;

		while ($i < $nb)
			{
			$c = $s[$i];

			if ("\n" == $c)
				{
				$i += 1;
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl += 1;

				continue;
				}

			if (' ' == $c)
				{
				$sep = $i;
				}
			$l += $cw[$c];

			if ($l > $wmax)
				{
				if (-1 == $sep)
					{
					if ($i == $j)
						{
						$i += 1;
						}
					}
				else
					{
					$i = $sep + 1;
					}
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl += 1;
				}
			else
				{
				$i += 1;
				}
			}

		return $nl;
		}

	public function OpenTag($tag, $attr) : void
		{
		$this->PushAttributes();
		//Opening tag
		switch (\strtoupper($tag))
			{
			case 'STRONG':
			case 'B':
				$this->SetStyle('B', true);

				break;

			case 'H1':
				$this->SetTextColor(150, 0, 0);
				$this->SetFontSize(22);

				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;

				break;

			case 'H2':
				$this->SetFontSize(18);
				$this->SetStyle('U', true);

				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;

				break;

			case 'H3':
				$this->SetFontSize(16);
				$this->SetStyle('U', true);

				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;

				break;

			case 'H4':
				$this->SetTextColor(102, 0, 0);
				$this->SetFontSize(14);

				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;
				$this->SetStyle('B', true);

				break;

			case 'PRE':
				$this->SetFont('Courier', '', 11);
				$this->SetStyle('B', false);
				$this->SetStyle('I', false);
				$this->PRE = true;

				break;

			case 'BLOCKQUOTE':
				$this->mySetTextColor(100, 0, 45);

				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;

				break;

			case 'I':
			case 'EM':
				$this->SetStyle('I', true);

				break;

			case 'U':
				$this->SetStyle('U', true);

				break;

			case 'A':
				$this->HREF = $attr['HREF'];

				break;

			case 'IMG':
				if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT'])))
					{
					if (! isset($attr['WIDTH']))
						{
						$attr['WIDTH'] = 0;
						}

					if (! isset($attr['HEIGHT']))
						{
						$attr['HEIGHT'] = 0;
						}
					$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $this->px2mm($attr['WIDTH']), $this->px2mm($attr['HEIGHT']));
					$this->Ln(3);
					}

				break;

			case 'LI':
				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;
				$this->Write($this->px2mm($this->FontSizePt), '       ');

				break;

			case 'TR':
				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt));
					}
				$this->printingHTMLFirstTime = false;
				$this->PutLine();

				break;

			case 'BR':
				$this->Ln($this->px2mm($this->FontSizePt));

				break;

			case 'P':
				$this->attributes->justify = '';

				if (! $this->printingHTMLFirstTime)
					{
					$this->Ln($this->px2mm($this->FontSizePt) * 2);
					}
				$this->printingHTMLFirstTime = false;

				break;

			case 'HR':
				$this->PutLine();

				break;

			case 'FONT':
				if (isset($attr['COLOR']) && '' != $attr['COLOR'])
					{
					$coul = $this->hex2dec($attr['COLOR']);
					$this->mySetTextColor($coul['R'], $coul['G'], $coul['B']);
					}

				if (isset($attr['FACE']))
					{
					$this->SetFont(\strtolower($attr['FACE']));
					}

				if (isset($attr['SIZE']))
					{
					$this->SetFont(\strtolower($attr['FACE']));
					}

				break;
			}

		foreach ($attr as $type => $command)
			{
			switch ($type)
				{
				case 'ALIGN':

					switch ($command)
						{
						case 'CENTER':

							$this->attributes->justify = 'C';

						}

					break;

				case 'STYLE':

					$style = \explode(':', $command);
					$type = \strtoupper(\array_shift($style));
					$command = \strtoupper(\array_shift($style));

					switch ($type)
						{
						case 'TEXT-INDENT':

							$num = \str_replace('IN', '', $command);
							$padding = ' ';

							while ($this->GetStringWidth($padding) < $num * 12)
								{
								$padding .= ' ';
								}
							$this->Write($this->px2mm($this->FontSizePt), $padding);

							break;

						case 'TEXT-DECORATION':

							if (\strpos($command, 'UNDERLINE'))
								{
								$this->SetStyle('U', true);
								}

							break;

						case 'TEXT-ALIGN':

							if (\strpos($command, 'LEFT'))
								{
								$this->attributes->justify = 'L';
								}
							elseif (\strpos($command, 'CENTER'))
								{
								$this->attributes->justify = 'C';
								}
							elseif (\strpos($command, 'RIGHT'))
								{
								$this->attributes->justify = 'R';
								}

						}

				}
			}
		}

	public function PopAttributes() : void
		{
		\array_pop($this->attributeStack);
		$last = \count($this->attributeStack);

		if ($last)
			{
			$this->attributes->fontFace = $this->attributeStack[$last - 1]->fontFace;
			$this->attributes->fontSize = $this->attributeStack[$last - 1]->fontSize;
			$this->attributes->fontStyle = $this->attributeStack[$last - 1]->fontStyle;
			$this->attributes->justify = $this->attributeStack[$last - 1]->justify;
			$this->attributes->color = $this->attributeStack[$last - 1]->color;
			}
		$this->attributes->RestoreAttributes();
		}

	public function PrintColumnHeaders() : void
		{
		$this->fill = 0;
		$family = $this->FontFamily;         //current font family
		$style = $this->FontStyle;          //current font style
		$size = $this->FontSizePt;
		$aligns = $this->aligns;
		$this->aligns = $this->headerAligns;
		$this->SetFont($family, 'B', $size);
		$this->fill = $this->fillCount;
		$this->Row($this->headers);
		$this->SetFont($family, $style, $size);
		$this->aligns = $aligns;
		$this->fill = 0;
		}

	public function PrintHeader() : void
		{
		$this->fill = 0;
		$family = $this->FontFamily;         //current font family
		$style = $this->FontStyle;          //current font style
		$size = $this->FontSizePt;
		$aligns = $this->aligns;
		$this->aligns = $this->headerAligns;
		$this->SetFont($this->headerFont, $this->headerFontStyle, $this->headerFontSize);
		$this->Cell(0, 10, $this->docTitle, 0, 1, 'C');
		$this->SetFont($family, 'B', $size);
		$this->fill = $this->fillCount;
		$this->Row($this->headers);
		$this->SetFont($family, $style, $size);
		$this->aligns = $aligns;
		$this->fill = 0;
		}

	public function PrintPageNumber() : void
		{
		if ($this->numberPages > 0)
			{
			$this->SetY($this->PageBreakTrigger - $this->GetLineHeight(10));
			$family = $this->FontFamily;         //current font family
			$style = $this->FontStyle;          //current font style
			$size = $this->FontSizePt;
			$this->SetFont('Times', '', 10);
			$this->Cell(0, $this->GetLineHeight(10), $this->pageNumber, 0, 1, 'C');
			$this->SetFont($family, $style, $size);
			$this->pageNumber += 1;
			}
		}

	public function PushAttributes() : void
		{
		$this->attributes->SaveAttributes();
		$newAttributes = new HMTLAttribs($this);
		$newAttributes->fontFace = $this->attributes->fontFace;
		$newAttributes->fontSize = $this->attributes->fontSize;
		$newAttributes->fontStyle = $this->attributes->fontStyle;
		$newAttributes->justify = $this->attributes->justify;
		$newAttributes->color = $this->attributes->color;
		$this->attributeStack[] = $newAttributes;
		}

	public function PutLine() : void
		{
		$this->Ln(2);
		$width = $this->w - $this->rMargin - $this->lMargin;
		$this->SetLineWidth(.3);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX() + $width, $this->GetY());
		$this->Ln(3);
		}

	public function PutLink($URL, $txt) : void
		{
		//Put a hyperlink
		$this->SetTextColor(0, 0, 255);
		$this->SetStyle('U', true);
		$this->Write($this->px2mm($this->FontSizePt), $txt, $URL);
		$this->SetStyle('U', false);
		$this->mySetTextColor(-1);
		}

	public function px2mm($px)
		{
		return $px * 25.4 / 72;
		}

	public function Row(array $data) : void
		{
		//Calculate the height of the row
		$nb = 0;

		foreach ($this->headers as $key => $value)
			{
			if (isset($this->widths[$key], $data[$key]))
				{
				$nb = \max($nb, $this->NbLines($this->widths[$key], $data[$key]));
				}
			}
		$fontHeight = $this->GetLineHeight($this->FontSizePt);
		$h = $fontHeight * $nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		foreach ($this->headers as $key => $value)
			{
			$w = $this->widths[$key] ?? 20;
			$a = $this->aligns[$key] ?? 'L';
			//Save the current position
			$x = $this->GetX();
			$y = $this->GetY();
			//Draw the border
			//Print the text
			$output = $data[$key] ?? '';
			$this->MultiCell($w, $fontHeight, $output, 0, $a, $this->fillCount && $this->fill == $this->fillCount);

			if (! $this->noLines)
				{
				$this->Rect($x, $y, $w, $h);
				}
			//Put the position to the right of the cell
			$this->SetXY($x + $w, $y);
			}
		$this->fill += 1;

		if ($this->fill > $this->fillCount)
			{
			$this->fill = 0;
			}
		//Go to the next line
		$this->Ln($h);
		}

	public function SaveIndex($filename) : void
		{
		\file_put_contents($filename, \serialize($this->index));
		}

	public function SetAligns(array $a) : void
		{
		//Set the array of column alignments
		$this->aligns = $a;
		$this->headerAligns = $a;
		}

	public function SetDocumentTitle($w) : void
		{
		$this->docTitle = $w;
		}

	public function SetFillLines($n) : void
		{
		$this->fillCount = $n;
		}

	public function SetFont($font, $style = '', $size = 0) : void
		{
		$this->attributes->fontSize = $size;
		$this->attributes->fontFace = $font;
		$this->attributes->fontStyle = $style;
		FPDF::SetFont($font, $this->attributes->fontStyle, $size);
		}

	public function SetFontSize($size) : void
		{
		$this->attributes->fontSize = $size;
		$this->SetFontSize($size);
		}

	public function SetHeader(array $w) : void
		{
		//Set the array of column headers
		$this->headers = $w;
		}

	public function SetHeaderAligns(array $a) : void
		{
		//Set the array of column alignments
		$this->headerAligns = $a;
		}

	public function SetHeaderFont($font = 'Times', $style = 'B', $points = 16) : void
		{
		$this->headerFontSize = $points;
		$this->headerFont = $font;
		$this->headerFontStyle = $style;
		}

	public function setNoLines($n) : void
		{
		$this->noLines = $n;
		}

	public function SetPageNumber($number) : void
		{
		$this->numberPages = $this->pageNumber = $number;
		}

	public function SetStyle($tag, $enable) : void
		{
		$len = \strlen($tag);

		for ($i = 0; $i < $len; ++$i)
			{
			$s = $tag[$i];

			if (\strstr($this->attributes->fontStyle, $s))
				{
				if (! $enable)
					{
					\str_replace($s, '', $this->attributes->fontStyle);
					}
				}
			elseif ($enable)
				{
				$this->attributes->fontStyle .= $s;
				}
			}
		FPDF::SetFont($this->attributes->fontFace, $this->attributes->fontStyle, $this->attributes->fontSize);
		}

	public function SetWidths(array $w) : void
		{
		//Set the array of column widths
		$this->widths = $w;
		}

	public function WriteHTML($html) : void
		{
		//remove all unsupported tags
		$this->attributes = new HMTLAttribs($this);
		$this->attributeStack = [];
		$this->PushAttributes();
		$this->printingHTML = true;
		$this->printingHTMLFirstTime = true;
		$html = \strip_tags($html, '<a><span><div><img><p><br><font><tr><blockquote><h1><h2><h3><h4><pre><ul><li><hr><b><i><u><strong><em>');
		$html = \str_replace("\n", ' ', $html); //replace carriage returns by spaces
		$html = \str_replace('&amp;', '&', $html);
		$html = \str_replace('&trade;', '�', $html);
		$html = \str_replace('&copy;', '�', $html);
		$html = \str_replace('&euro;', '�', $html);
		$html = \str_replace('&quot;', '"', $html);
		$html = \str_replace('&rdquo;', '"', $html);
		$html = \str_replace('&rsquo;', "'", $html);
		$html = \str_replace('&ldquo;', '"', $html);
		$html = \str_replace('&apos;', "'", $html);
		$html = \str_replace('&lt;', '<', $html);
		$html = \str_replace('&gt;', '>', $html);
		$a = \preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		$skip = false;

		foreach ($a as $i => $e)
			{
			if (! $skip)
				{
				if ($this->HREF)
					{
					$e = \str_replace("\n", '', \str_replace("\r", '', $e));
					}

				if (0 == $i % 2)
					{
					// new line
					if ($this->PRE)
						{
						$e = \str_replace("\r", "\n", $e);
						}
					else
						{
						$e = \str_replace("\r", '', $e);
						}
					//Text
					if ($this->HREF)
						{
						$this->PutLink($this->HREF, $e);
						$skip = true;
						}
					else
						{
						$text = \stripslashes(\html_entity_decode($e));

						if ('C' == $this->attributes->justify)
							{
							$length = $this->GetStringWidth($text);
							$width = $this->w - $this->rMargin - $this->lMargin;
							$x = ($width - $length) / 2 + $this->lMargin;
							$this->SetX($x);
							}
						$h = $this->px2mm($this->FontSizePt);
						$text = \str_replace('  ', ' ', $text);
						$this->Write($h, $text);
						}
					}
				else
					{
					//Tag
					if ('/' == \substr(\trim($e), 0, 1))
						{
						$this->CloseTag();
						}
					else
						{
						//Extract attributes.  Look for = sign, before is index, after (in quotes) is value
						$e = \strtoupper($e);
						$e = \str_replace('"', "'", $e); // just deal with one type of quotes
						// get the tag (first thing before the space)
						$index = \strpos($e, ' ');
						$attr = [];
						$tag = $e;

						if ($index)
							{
							$tag = \substr($e, 0, $index);
							$e = \substr($e, $index + 1);
							$len = \strlen($e);

							if ($len)
								{
								$i = 0;

								while ($i < \strlen($e))
									{
									if ('=' == $e[$i])
										{
										$key = \substr($e, 0, $i);
										$key = \str_replace(' ', '', $key);
										$value = '';
										$e = \substr($e, $i + 2);  // remove ='
										$x = 0;
										// find end quote
										while ($x < \strlen($e))
											{
											if ("'" == $e[$x])
												{
												$value = \substr($e, 0, $x);
												$e = \substr($e, $x + 1);
												$i = 0;

												break;
												}
											$x += 1;
											}
										$attr[\strtoupper($key)] = $value;
										}
									$i += 1;
									}
								}
							}
						$this->OpenTag($tag, $attr);
						}
					}
				}
			else
				{
				$this->HREF = '';
				$skip = false;
				}
			}
		$this->printingHTML = false;
		}
	}
