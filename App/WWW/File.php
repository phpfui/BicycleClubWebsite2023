<?php

namespace App\WWW;

class File extends \App\Common\WWW\File
	{
	public function gearCSV() : void
		{
		$model = new \App\Model\GearCalculator($_GET);
		$model->csv();
		}

	public function gearPrint() : void
		{
		$model = new \App\Model\GearCalculator($_GET);
		$model->print();
		}
	}
