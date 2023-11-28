<?php

namespace App\UI;

class ScrollOffHeader extends \PHPFUI\HTML5Element
	{
	protected \PHPFUI\HTML5Element $stickyDiv;

	public function __construct(private \App\View\Page $page, \PHPFUI\TopBar $topBar)
		{
		parent::__construct('div');
		$this->stickyDiv = new \PHPFUI\HTML5Element('div');
		$this->stickyDiv->addAttribute('data-sticky')->addAttribute("data-margin-top='0'");
		$topBar->addClass('topbar-sticky-shrink');
		$this->stickyDiv->add($topBar);
		$this->add($this->stickyDiv);
		$this->addAttribute('data-sticky-container');
		}

	public function getHeader(\App\Record\HeaderContent $content) : \PHPFUI\HTML5Element
		{
		$header = new \PHPFUI\HTML5Element('header');
		$this->stickyDiv->addAttribute('data-top-anchor="' . $header->getId() . ':bottom"');
		$this->page->addJavaScript($content->javaScript);
		$this->page->addCSS($content->css);
		$header->add($content->content);

		return $header;
		}
	}
