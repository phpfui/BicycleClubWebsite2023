<?php

// CubicleSoft PHP web browser state emulation class.
// (C) 2014 CubicleSoft.  All Rights Reserved.
// Requires the CubicleSoft PHP HTTP functions for HTTP/HTTPS.
class WebBrowser
	{
	private array $data = [];

	private ?\voku\helper\HtmlDomParser $html = null;

	public function __construct(array $prevstate = [])
		{
		$this->ResetState();
		$this->SetState($prevstate);
		}

	public function ResetState() : void
		{
		$this->data = ['allowedprotocols' => ['http' => true, 'https' => true, ],
			'allowedredirprotocols' => ['http' => true, 'https' => true, ],
			'cookies' => [],
			'referer' => '',
			'autoreferer' => true,
			'useragent' => 'firefox',
			'followlocation' => true,
			'maxfollow' => 20,
			'extractforms' => false,
			'httpopts' => [], ];
		}

	public function SetState(array $options = []) : void
		{
		$this->data = \array_merge($this->data, $options);
		}

	public function GetState()
		{
		return $this->data;
		}

	public function Process(string $url, string $profile = 'auto', array $tempoptions = [])
		{
		$startts = \microtime(true);
		$redirectts = $startts;

		if (isset($tempoptions['timeout']))
			{
			$timeout = $tempoptions['timeout'];
			}
		elseif (isset($this->data['httpopts']['timeout']))
			{
			$timeout = $this->data['httpopts']['timeout'];
			}
		else
			{
			$timeout = false;
			}

		if (! isset($this->data['httpopts']['headers']))
			{
			$this->data['httpopts']['headers'] = [];
			}
		$this->data['httpopts']['headers'] = HTTP::NormalizeHeaders($this->data['httpopts']['headers']);
		unset($this->data['httpopts']['method'], $this->data['httpopts']['write_body_callback'], $this->data['httpopts']['body'], $this->data['httpopts']['postvars'], $this->data['httpopts']['files']);

		$httpopts = $this->data['httpopts'];
		$numfollow = $this->data['maxfollow'];
		$numredirects = 0;
		$totalrawsendsize = 0;

		if (! isset($tempoptions['headers']))
			{
			$tempoptions['headers'] = [];
			}
		$tempoptions['headers'] = HTTP::NormalizeHeaders($tempoptions['headers']);

		if (isset($tempoptions['headers']['Referer']))
			{
			$this->data['referer'] = $tempoptions['headers']['Referer'];
			}
		// If a referrer is specified, use it to generate an absolute URL.
		if ('' != $this->data['referer'])
			{
			$url = HTTP::ConvertRelativeToAbsoluteURL($this->data['referer'], $url);
			}
		$urlinfo = HTTP::ExtractURL($url);

		do
			{
			if (! isset($this->data['allowedprotocols'][$urlinfo['scheme']]) || ! $this->data['allowedprotocols'][$urlinfo['scheme']])
				{
				return ['success' => false,
					'error' => HTTP::HTTPTranslate("Protocol '%s' is not allowed in '%s'.", $urlinfo['scheme'], $url),
					'errorcode' => 'allowed_protocols', ];
				}
			$filename = HTTP::ExtractFilename($urlinfo['path']);
			$pos = \strrpos($filename, '.');
			$fileext = (false !== $pos ? \strtolower(\substr($filename, $pos + 1)) : '');
			// Set up some standard headers.
			$headers = [];
			$profile = \strtolower($profile);
			$tempprofile = \explode('-', $profile);

			if (2 == \count($tempprofile))
				{
				$profile = $tempprofile[0];
				$fileext = $tempprofile[1];
				}

			if ('ie' == \substr($profile, 0, 2) || ('auto' == $profile && 'ie' == \substr($this->data['useragent'], 0, 2)))
				{
				if ('css' == $fileext)
					{
					$headers['Accept'] = 'text/css';
					}
				elseif ('png' == $fileext || 'jpg' == $fileext || 'jpeg' == $fileext || 'gif' == $fileext || 'svg' == $fileext)
					{
					$headers['Accept'] = 'image/png, image/svg+xml, image/*;q=0.8, */*;q=0.5';
					}
				elseif ('js' == $fileext)
					{
					$headers['Accept'] = 'application/javascript, */*;q=0.8';
					}
				elseif ('' != $this->data['referer'] || '' == $fileext || 'html' == $fileext || 'xhtml' == $fileext || 'xml' == $fileext)
					{
					$headers['Accept'] = 'text/html, application/xhtml+xml, */*';
					}
				else
					{
					$headers['Accept'] = '*/*';
					}
				$headers['Accept-Language'] = 'en-US';
				$headers['User-Agent'] = HTTP::GetUserAgent('ie' == \substr($profile, 0, 2) ? $profile : $this->data['useragent']);
				}
			elseif ('firefox' == $profile || ('auto' == $profile && 'firefox' == $this->data['useragent']))
				{
				if ('css' == $fileext)
					{
					$headers['Accept'] = 'text/css,*/*;q=0.1';
					}
				elseif ('png' == $fileext || 'jpg' == $fileext || 'jpeg' == $fileext || 'gif' == $fileext || 'svg' == $fileext)
					{
					$headers['Accept'] = 'image/png,image/*;q=0.8,*/*;q=0.5';
					}
				elseif ('js' == $fileext)
					{
					$headers['Accept'] = '*/*';
					}
				else
					{
					$headers['Accept'] = 'text/html, application/xhtml+xml, */*';
					}
				$headers['Accept-Language'] = 'en-us,en;q=0.5';
				$headers['Cache-Control'] = 'max-age=0';
				$headers['User-Agent'] = HTTP::GetUserAgent('firefox');
				}
			elseif ('opera' == $profile || ('auto' == $profile && 'opera' == $this->data['useragent']))
				{
				// Opera has the right idea:  Just send the same thing regardless of the request type.
				$headers['Accept'] = 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1';
				$headers['Accept-Language'] = 'en-US,en;q=0.9';
				$headers['Cache-Control'] = 'no-cache';
				$headers['User-Agent'] = HTTP::GetUserAgent('opera');
				}
			elseif ('safari' == $profile || 'chrome' == $profile || ('auto' == $profile && ('safari' == $this->data['useragent'] || 'chrome' == $this->data['useragent'])))
				{
				if ('css' == $fileext)
					{
					$headers['Accept'] = 'text/css,*/*;q=0.1';
					}
				elseif ('png' == $fileext || 'jpg' == $fileext || 'jpeg' == $fileext || 'gif' == $fileext || 'svg' == $fileext || 'js' == $fileext)
					{
					$headers['Accept'] = '*/*';
					}
				else
					{
					$headers['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
					}
				$headers['Accept-Charset'] = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
				$headers['Accept-Language'] = 'en-US,en;q=0.8';
				$headers['User-Agent'] = HTTP::GetUserAgent('safari' == $profile || 'chrome' == $profile ? $profile : $this->data['useragent']);
				}

			if ('' != $this->data['referer'])
				{
				$headers['Referer'] = $this->data['referer'];
				}
			// Generate the final headers array.
			$headers = \array_merge($headers, $httpopts['headers'], $tempoptions['headers']);
			// Calculate the host and reverse host and remove port information.
			$host = ($headers['Host'] ?? $urlinfo['host']);
			$pos = \strpos($host, ']');

			if ('[' == \substr($host, 0, 1) && false !== $pos)
				{
				$host = \substr($host, 0, $pos + 1);
				}
			else
				{
				$pos = \strpos($host, ':');

				if (false !== $pos)
					{
					$host = \substr($host, 0, $pos);
					}
				}
			$dothost = $host;

			if ('.' != \substr($dothost, 0, 1))
				{
				$dothost = '.' . $dothost;
				}
			// Append cookies and delete old, invalid cookies.
			$secure = ('https' == $urlinfo['scheme']);
			$cookiepath = $urlinfo['path'];

			if ('' == $cookiepath)
				{
				$cookiepath = '/';
				}
			$pos = \strrpos($cookiepath, '/');

			if (false !== $pos)
				{
				$cookiepath = \substr($cookiepath, 0, $pos + 1);
				}
			$cookies = [];

			foreach ($this->data['cookies'] as $domain => $paths)
				{
				if (\substr($domain, -\strlen($dothost)) == $dothost)
					{
					foreach ($paths as $path => $cookies2)
						{
						if (\substr($cookiepath, 0, \strlen($path)) == $path)
							{
							foreach ($cookies2 as $num => $info)
								{
								if (isset($info['expires_ts']) && $this->GetExpiresTimestamp($info['expires_ts']) < \time())
									{
									unset($this->data['cookies'][$domain][$path][$num]);
									}
								elseif ($secure || ! isset($info['secure']))
									{
									$cookies[$info['name']] = $info['value'];
									}
								}

							if (! \count($this->data['cookies'][$domain][$path]))
								{
								unset($this->data['cookies'][$domain][$path]);
								}
							}
						}

					if (! \count($this->data['cookies'][$domain]))
						{
						unset($this->data['cookies'][$domain]);
						}
					}
				}
			$cookies2 = [];

			foreach ($cookies as $name => $value)
				{
				$cookies2[] = \rawurlencode($name) . '=' . \rawurlencode($value);
				}
			$headers['Cookie'] = \implode('; ', $cookies2);

			if ('' == $headers['Cookie'])
				{
				unset($headers['Cookie']);
				}
			// Generate the final options array.
			$options = \array_merge($httpopts, $tempoptions);
			$options['headers'] = $headers;

			if (false !== $timeout)
				{
				$options['timeout'] = HTTP::GetTimeLeft($startts, $timeout);
				}
			// Process the request.
			$result = HTTP::RetrieveWebpage($url, $options);
			$result['url'] = $url;
			$result['options'] = $options;
			$result['firstreqts'] = $startts;
			$result['numredirects'] = $numredirects;
			$result['redirectts'] = $redirectts;

			if (isset($result['rawsendsize']))
				{
				$totalrawsendsize += $result['rawsendsize'];
				}
			$result['totalrawsendsize'] = $totalrawsendsize;
			unset($result['options']['files'], $result['options']['body']);

			if (! $result['success'])
				{
				return ['success' => false,
					'error' => HTTP::HTTPTranslate('Unable to retrieve content.  %s', $result['error']),
					'info' => $result,
					'errorcode' => 'retrievewebpage', ];
				}
			// Set up structures for another round.
			if ($this->data['autoreferer'])
				{
				$this->data['referer'] = $url;
				}

			if (isset($result['headers']['Location']) && $this->data['followlocation'])
				{
				$redirectts = \microtime(true);
				unset($tempoptions['method'], $tempoptions['write_body_callback'], $tempoptions['body'], $tempoptions['postvars'], $tempoptions['files']);

				$tempoptions['headers']['Referer'] = $url;
				$url = $result['headers']['Location'][0];
				// Generate an absolute URL.
				if ('' != $this->data['referer'])
					{
					$url = HTTP::ConvertRelativeToAbsoluteURL($this->data['referer'], $url);
					}
				$urlinfo2 = HTTP::ExtractURL($url);

				if (! isset($this->data['allowedredirprotocols'][$urlinfo2['scheme']]) || ! $this->data['allowedredirprotocols'][$urlinfo2['scheme']])
					{
					return ['success' => false,
						'error' => HTTP::HTTPTranslate("Protocol '%s' is not allowed.  Server attempted to redirect to '%s'.", $urlinfo2['scheme'], $url),
						'info' => $result,
						'errorcode' => 'allowed_redir_protocols', ];
					}

				if ($urlinfo2['host'] != $urlinfo['host'])
					{
					unset($tempoptions['headers']['Host'], $httpopts['headers']['Host']);

					}
				$urlinfo = $urlinfo2;
				$numredirects++;
				}
			// Handle any 'Set-Cookie' headers.
			if (isset($result['headers']['Set-Cookie']))
				{
				foreach ($result['headers']['Set-Cookie'] as $cookie)
					{
					$items = \explode('; ', $cookie);
					$item = \trim(\array_shift($items));

					if ('' != $item)
						{
						$cookie2 = [];
						$pos = \strpos($item, '=');

						if (false === $pos)
							{
							$cookie2['name'] = \urldecode($item);
							$cookie2['value'] = '';
							}
						else
							{
							$cookie2['name'] = \urldecode(\substr($item, 0, $pos));
							$cookie2['value'] = \urldecode(\substr($item, $pos + 1));
							}
						$cookie = [];

						foreach ($items as $item)
							{
							$item = \trim($item);

							if ('' != $item)
								{
								$pos = \strpos($item, '=');

								if (false === $pos)
									{
									$cookie[\strtolower(\trim(\urldecode($item)))] = '';
									}
								else
									{
									$cookie[\strtolower(\trim(\urldecode(\substr($item, 0, $pos))))] = \urldecode(\substr($item, $pos + 1));
									}
								}
							}
						$cookie = \array_merge($cookie, $cookie2);

						if (isset($cookie['expires']))
							{
							$ts = HTTP::GetDateTimestamp($cookie['expires']);
							$cookie['expires_ts'] = \gmdate('Y-m-d H:i:s', (false === $ts ? \time() - 24 * 60 * 60 : $ts));
							}
						elseif (isset($cookie['max-age']))
							{
							$cookie['expires_ts'] = \gmdate('Y-m-d H:i:s', \time() + (int)$cookie['max-age']);
							}
						else
							{
							unset($cookie['expires_ts']);
							}

						if (! isset($cookie['domain']))
							{
							$cookie['domain'] = $dothost;
							}

						if ('.' != \substr($cookie['domain'], 0, 1))
							{
							$cookie['domain'] = '.' . $cookie['domain'];
							}

						if (! isset($cookie['path']))
							{
							$cookie['path'] = $cookiepath;
							}
						$cookie['path'] = \str_replace('\\', '/', $cookie['path']);

						if ('/' != \substr($cookie['path'], -1))
							{
							$cookie['path'] = '/';
							}

						if (! isset($this->data['cookies'][$cookie['domain']]))
							{
							$this->data['cookies'][$cookie['domain']] = [];
							}

						if (! isset($this->data['cookies'][$cookie['domain']][$cookie['path']]))
							{
							$this->data['cookies'][$cookie['domain']][$cookie['path']] = [];
							}
						$this->data['cookies'][$cookie['domain']][$cookie['path']][] = $cookie;
						}
					}
				}

			if ($numfollow > 0)
				{
				$numfollow--;
				}
			}
		while (isset($result['headers']['Location']) && $this->data['followlocation'] && $numfollow);
		$result['numredirects'] = $numredirects;
		$result['redirectts'] = $redirectts;
		// Extract the forms from the page in a parsed format.
		// Call WebBrowser::GenerateFormRequest() to prepare an actual request for Process().
		if ($this->data['extractforms'])
			{
			$result['forms'] = $this->ExtractForms($result['url'], $result['body']);
			}

		return $result;
		}

	public function ExtractForms(string $baseurl, string $data)
		{
		$result = [];

		if (! $this->html)
			{
			$this->html = new \voku\helper\HtmlDomParser($data);
			}
		$html5rows = $this->html->find('input[form],textarea[form],select[form],button[form],datalist[id]');
		$rows = $this->html->find('form');

		foreach ($rows as $row)
			{
			$info = [];

			if (isset($row->id))
				{
				$info['id'] = \trim($row->id);
				}

			if (isset($row->name))
				{
				$info['name'] = (string)$row->name;
				}
			$info['action'] = (isset($row->action) ? HTTP::ConvertRelativeToAbsoluteURL($baseurl, (string)$row->action) : $baseurl);
			$info['method'] = (isset($row->method) && 'post' == \strtolower(\trim($row->method)) ? 'post' : 'get');

			if ('post' == $info['method'])
				{
				$info['enctype'] = (isset($row->enctype) ? \strtolower($row->enctype) : 'application/x-www-form-urlencoded');
				}

			if (isset($row->{'accept-charset'}))
				{
				$info['accept-charset'] = (string)$row->{'accept-charset'};
				}
			$fields = [];
			$rows2 = $row->find('input,textarea,select,button');

			foreach ($rows2 as $row2)
				{
				if (! isset($row2->form))
					{
					$fields = $this->ExtractFieldFromDOM($fields, $row2);
					}
				}
			// Handle HTML5.
			if (isset($info['id']) && '' != $info['id'])
				{
				foreach ($html5rows as $row2)
					{
					if (false !== \strpos(' ' . $info['id'] . ' ', ' ' . $row2->form . ' '))
						{
						$fields = $this->ExtractFieldFromDOM($fields, $row2);
						}
					}
				}
			$form = new WebBrowserForm();
			$form->info = $info;
			$form->fields = $fields;
			$result[] = $form;
			}

		return $result;
		}

	public function DeleteSessionCookies() : void
		{
		foreach ($this->data['cookies'] as $domain => $paths)
			{
			foreach ($paths as $path => $cookies)
				{
				foreach ($cookies as $num => $info)
					{
					if (! isset($info['expires_ts']))
						{
						unset($this->data['cookies'][$domain][$path][$num]);
						}
					}

				if (! \count($this->data['cookies'][$domain][$path]))
					{
					unset($this->data['cookies'][$domain][$path]);
					}
				}

			if (! \count($this->data['cookies'][$domain]))
				{
				unset($this->data['cookies'][$domain]);
				}
			}
		}

	public function DeleteCookies($domainpattern, $pathpattern, $namepattern) : void
		{
		foreach ($this->data['cookies'] as $domain => $paths)
			{
			if ('' == $domainpattern || \substr($domain, -\strlen($domainpattern)) == $domainpattern)
				{
				foreach ($paths as $path => $cookies)
					{
					if ('' == $pathpattern || \substr($path, 0, \strlen($pathpattern)) == $pathpattern)
						{
						foreach ($cookies as $num => $info)
							{
							if ('' == $namepattern || false !== \strpos($info['name'], $namepattern))
								{
								unset($this->data['cookies'][$domain][$path][$num]);
								}
							}

						if (! \count($this->data['cookies'][$domain][$path]))
							{
							unset($this->data['cookies'][$domain][$path]);
							}
						}
					}

				if (! \count($this->data['cookies'][$domain]))
					{
					unset($this->data['cookies'][$domain]);
					}
				}
			}
		}

	private function GetExpiresTimestamp(string $ts)
		{
		$year = (int)\substr($ts, 0, 4);
		$month = (int)\substr($ts, 5, 2);
		$day = (int)\substr($ts, 8, 2);
		$hour = (int)\substr($ts, 11, 2);
		$min = (int)\substr($ts, 14, 2);
		$sec = (int)\substr($ts, 17, 2);

		return \gmmktime($hour, $min, $sec, $month, $day, $year);
		}

	private function ExtractFieldFromDOM(array $fields, $row) : array
		{
		if (isset($row->name) && \is_string($row->name))
			{
			switch ($row->tag)
				{
				case 'input':

					$field = ['id' => (isset($row->id) ? (string)$row->id : false),
						'type' => 'input.' . (isset($row->type) ? \strtolower($row->type) : 'text'),
						'name' => $row->name,
						'value' => (isset($row->value) ? \html_entity_decode($row->value, ENT_COMPAT, 'UTF-8') : ''), ];

					if ('input.radio' == $field['type'] || 'input.checkbox' == $field['type'])
						{
						$field['checked'] = (isset($row->checked));
						}
					$fields[] = $field;

					break;

				case 'textarea':

					$fields[] = ['id' => (isset($row->id) ? (string)$row->id : false),
						'type' => 'textarea',
						'name' => $row->name,
						'value' => \html_entity_decode($row->innertext, ENT_COMPAT, 'UTF-8'), ];

					break;

				case 'select':

					if (isset($row->multiple))
						{
						// Change the type into multiple checkboxes.
						$rows = $row->find('option');

						foreach ($rows as $row2)
							{
							$fields[] = ['id' => (isset($row->id) ? (string)$row->id : false),
								'type' => 'input.checkbox',
								'name' => $row->name,
								'value' => (isset($row2->value) ? \html_entity_decode($row2->value, ENT_COMPAT, 'UTF-8') : ''),
								'display' => (string)$row2->innertext, ];
							}
						}
					else
						{
						$val = false;
						$options = [];
						$rows = $row->find('option');

						foreach ($rows as $row2)
							{
							$options[$row2->value] = (string)$row2->innertext;

							if (false === $val && isset($row2->selected))
								{
								$val = \html_entity_decode($row2->value, ENT_COMPAT, 'UTF-8');
								}
							}

						if (false === $val && \count($options))
							{
							$val = \array_keys($options);
							$val = $val[0];
							}

						if (false === $val)
							{
							$val = '';
							}
						$fields[] = ['id' => (isset($row->id) ? (string)$row->id : false),
							'type' => 'select',
							'name' => $row->name,
							'value' => $val,
							'options' => $options, ];
						}

					break;

				case 'button':

					$fields[] = ['id' => (isset($row->id) ? (string)$row->id : false),
						'type' => 'button.' . (isset($row->type) ? \strtolower($row->type) : 'submit'),
						'name' => $row->name,
						'value' => (isset($row->value) ? \html_entity_decode($row->value, ENT_COMPAT, 'UTF-8') : ''), ];

					break;

				}
			}

		return $fields;
		}
	}
