<?php

namespace App\View;

class GALandingPage extends \App\UI\HTMLEditor implements \Stringable
	{
	protected string $settingName = 'GALandingPage';

	private readonly \App\Table\Setting $settingTable;

	public function __construct(\App\View\Page $page)
		{
		$attributes = [];
  $this->settingTable = new \App\Table\Setting();
		parent::__construct($page, true);
		$page->addStyleSheet('/slick/slick.css');
		$page->addStyleSheet('/slick/slick-theme.css');
		$page->addTailScript('/slick/slick.min.js');
		$attributes['arrows'] = true;
		$attributes['dots'] = true;
		$attributes['lazyLoad'] = "'ondemand'";
		$attributes['mobileFirst'] = true;
		$attributes['swipeToSlide'] = true;
		$attributes['autoplay'] = true;
		$attributes['autoplaySpeed'] = 3000;
		$js = '$("#id10").slick(' . \App\Tools\TextHelper::arrayToJS($attributes) . ')';
		$this->page->addJavaScript($js);

		if ($this->editable && \App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']) && 'saveContent' == $_POST['action'])
				{
				$this->settingTable->save($this->settingName, $_POST['body']);
				$this->page->done();
				$this->page->setResponse('Saved');
				}
			}
		}

	public function __toString() : string
		{
		$text = $this->settingTable->value($this->settingName);
		$idTag = '';

		if ($this->editable)
			{
			$this->makeEditable($this->settingName);
			$idTag = " id='{$this->settingName}'";
			}

		return "<div{$idTag}>{$text}</div>";
		}
	}
