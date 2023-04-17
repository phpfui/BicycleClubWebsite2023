<?php

namespace Tests\Functional;

class CSSTest extends \PHPFUI\HTMLUnitTester\Extensions
	{
	public function testCSS() : void
		{
		$this->assertDirectory('ValidCSS', PUBLIC_ROOT . '/css', 'Invalid CSS');
		}
	}
