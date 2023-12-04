<?php

namespace Tests\Functional;

class Base extends \PHPFUI\HTMLUnitTester\Extensions
	{
	protected static array $allowedExtensions = [];

	protected static bool $defaultFilterReturn = false;

	protected static string $errorFile = PROJECT_ROOT . '/files/PHPErrors/error.log';

	protected static array $restrictedExtensions = [];

	private static ?\GuzzleHttp\Client $client = null;

	public static function setUpBeforeClass() : void
		{
		parent::setUpBeforeClass();

		if (\file_exists(self::$errorFile))
			{
			\unlink(self::$errorFile);
			}
		}

	protected function setUp() : void
		{
		parent::setUp();
		}

	protected function tearDown() : void
		{
		parent::tearDown();
		}

	/**
	 * @return (mixed|string)[][][]
	 *
	 */
	public function requestProvider() : array
		{
	$directory = new \DirectoryIterator(PROJECT_ROOT . '/Tests/Functional/requests');

		$tests = [];

	foreach ($directory as $file)
			{
			if ($file->isFile() && $file->isReadable())
				{
				$path = $file->getPathname();

				if (\stripos($path, '.csv'))
					{
					$reader = new \App\Tools\CSV\FileReader($path);
					$parts = \pathinfo($path);

					foreach ($reader as $row)
						{
						$uri = $row['REQUEST_URI'];

						// filter uris and missing requests out
						if ($uri && $this->filter($uri) && empty($row['missing']))
							{
							$data = [$row['REQUEST_METHOD'], $uri, $row['_get'], $row['_post']];
							$tests[$parts['basename'] . ':' . $reader->key()] = [$parts['basename'] . ' line ' . $reader->key() => $data];
							}
						}
					}
				}
			}

		return $tests;
		}

	protected function getClient() : \GuzzleHttp\Client
		{
		if (! self::$client)
			{
//			$unitTestSettings = new \App\Settings\UnitTest();
			$options = [
				'base_uri' => 'http://wcc',
				\GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
				\GuzzleHttp\RequestOptions::COOKIES => true,
			];
			self::$client = new \GuzzleHttp\Client($options);

//			$request = ["email" =>
//brucekwells@gmail.com
//Content-Disposition: form-data; name="password"
//rideboard
//Content-Disposition: form-data; name="remember"
//0
//Content-Disposition: form-data; name="SignIn"
//Sign In
//Content-Disposition: form-data; name="csrf"
//-----------------------------340792987140296746673349894566--
//
//				'remember' => '',
//				//				'uname' => $unitTestSettings->getUsername(),
//				//				'pwd' => $unitTestSettings->getPassword(),
//				'resetpass' => '',
//				'confirmpass' => '',
//			];
//			$res = self::$client->post($url = '/saya/formularios/acceso.form.php', [
//				\GuzzleHttp\RequestOptions::FORM_PARAMS => $request,
//				\GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
//			]);
			$this->assertEquals(200, $res->getStatusCode(), "Status code ({$res->getStatusCode()}) from {$url} is not 200");
			}

		return self::$client;
		}

	protected function performDocumentTests(string $html) : void
		{
		$dom = new \simple_html_dom($html);

		foreach ($dom->find('.phperror') as $node)
			{
			$error = \strip_tags(\str_replace('<br />', "\n", $node->innertext()));
			$this->assertEmpty($error, $error);
			}

		foreach ($dom->find('style') as $node)
			{
			$css = $node->innertext();
			$this->assertValidCss($css);
			}

		foreach ($dom->find('link') as $node)
			{
			$this->validatePublicFile($node->href, 'Missing CSS file: ');
			}

		foreach ($dom->find('script') as $node)
			{
			if ($node->src)
				{
				$this->validatePublicFile($node->src, 'Missing JS file: ');
				}
			}

		foreach ($dom->find('img') as $node)
			{
			if ($node->src && false === \stripos($node->src, 'data:image'))
				{
				$this->validatePublicFile($node->src, 'Missing IMG scr file: ');
				}
			}
		}

	private function filter(string $uri) : bool
		{
		foreach (static::$allowedExtensions as $extension)
			{
			if (\stripos($uri, $extension))
				{
				return true;
				}
			}

		foreach (static::$restrictedExtensions as $extension)
			{
			if (\stripos($uri, $extension))
				{
				return false;
				}
			}

		return static::$defaultFilterReturn;
		}

	private function validatePublicFile(string $path, string $message) : void
		{
		$parts = \explode('?', $path);
		$base = $parts[0];

		if (false === \stripos($base, 'http'))
			{
			$file = PUBLIC_ROOT . $base;
			$this->assertFileExists($file, $message . $file);
			}
		}
	}
