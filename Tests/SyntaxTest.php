<?php

class SyntaxTest extends \PHPFUI\PHPUnitSyntaxCoverage\Extensions
	{
	/** @group SyntaxTest */
	public function testDirectory() : void
		{
		$this->addSkipDirectory('makefont');
		$this->assertValidPHPDirectory(__DIR__ . '/../App', 'App directory has an error');
		$this->assertValidPHPDirectory(__DIR__ . '/../NoNameSpace', 'NoNameSpace directory has an error');
		}

	/** @group SyntaxTest */
	public function testValidPHPFile() : void
		{
		$this->assertValidPHPFile(__DIR__ . '/../common.php', 'common file is bad');
		$this->assertValidPHPFile(__DIR__ . '/../commonbase.php', 'commonbase file is bad');
		}
	}
