<?php

namespace App\View;

class Cropper
	{
	private array $fields = [];

	private array $parameters = [];

	public function __construct(private readonly \App\View\Page $page, private readonly ?\PHPFUI\Image $image)
		{
		if ($this->image)
			{
			$this->page->addTailScript('rcrop.min.js');
			$this->page->addStyleSheet('/css/rcrop.min.css');
			}
		}

	public function editor() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $this->image)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('Image not found');
			$callout->add($callout);
			$container->add($callout);

			return $container;
			}

		$validFields = ['x', 'y', 'height', 'width'];
		$values = [];

		foreach ($validFields as $field)
			{
			if (! isset($this->fields[$field]))
				{
				throw new \Exception("Field {$field} was not set in " . self::class);
				}
			$values[$field] = $this->fields[$field]->getValue();
			}

		$imageId = $this->image->getId();
		$container->add($this->image);

		$js = "var {$imageId}=$('#{$imageId}');{$imageId}.rcrop(" . \PHPFUI\TextHelper::arrayToJS($this->parameters) . ');';

		$function = "function(value){var v={$imageId}.rcrop('getValues');";

		foreach ($validFields as $field)
			{
			$function .= "$('#{$this->fields[$field]->getId()}').val(v.{$field});";
			}
		$function .= '}';

		$js .= "{$imageId}.on('rcrop-changed',{$function});";
		$js .= "{$imageId}.on('rcrop-ready',function(){ {$imageId}.rcrop('resize',{$values['width']},{$values['height']},{$values['x']},{$values['y']})})";

		$this->page->addJavaScript($js);

		foreach ($this->fields as $field)
			{
			$container->add($field);
			}

		return $container;
		}

	public function setHeightField(\PHPFUI\Input $field) : Cropper
		{
		return $this->setField('height', $field);
		}

	public function setOption(string $name, mixed $value) : static
		{
		$this->parameters[$name] = $value;

		return $this;
		}

	public function setWidthField(\PHPFUI\Input $field) : Cropper
		{
		return $this->setField('width', $field);
		}

	public function setXField(\PHPFUI\Input $field) : Cropper
		{
		return $this->setField('x', $field);
		}

	public function setYField(\PHPFUI\Input $field) : Cropper
		{
		return $this->setField('y', $field);
		}

	private function setField(string $key, \PHPFUI\Input $field) : static
		{
		$this->fields[$key] = $field;

		return $this;
		}
	}
