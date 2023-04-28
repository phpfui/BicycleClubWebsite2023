<?php

namespace App\View\Public;

class Page extends \App\View\Page implements \PHPFUI\Interfaces\NanoClass
	{
	use PageTrait;

	private readonly \App\View\Content $content;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		// @phpstan-ignore-next-line
		parent::__construct($controller);
		$this->content = new \App\View\Content($this);
		$this->setPublic();
		}

	public function custom() : void
		{
		$this->setPublic();
		$url = \strtolower(\str_replace('/' . \App\Model\Session::csrf(), '', $this->getBaseUrl()));
		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->setWhere(new \PHPFUI\ORM\Condition('url', $url . '%', new \PHPFUI\ORM\Operator\Like()));
		$publicPageCursor = $publicPageTable->getRecordCursor();

		if (! \count($publicPageCursor))
			{
			$this->addPageContent("Page {$url} is not defined");

			return;
			}

		$publicPage = $publicPageCursor->current();

		if ($publicPage->banner)
			{
			$this->addBanners();
			}

		if ($publicPage->header)
			{
			$this->addHeader($publicPage->name);
			}

		if ($publicPage->blog)
			{
			$this->addPageContent($this->content->getDisplayCategoryHTML($publicPage->name));
			}

		if ($method = $publicPage->method)
			{
			$this->addPageContent($this->{$method}());
			}

		if ($publicPage->blogAfter)
			{
			$this->addPageContent($this->content->getDisplayCategoryHTML($publicPage->blogAfter));
			}
		}

	public function emailBoardMember(\App\Record\BoardMember $boardMember = new \App\Record\BoardMember(), string $csrf = '') : void
		{
		$this->addBanners();

		if (! $boardMember->empty() && $csrf == \App\Model\Session::csrf())
			{
			$this->addPageContent(new \App\View\Email\Member($this, $boardMember->member));
			}
		else
			{
			$this->addPageContent(new \PHPFUI\Header('Contact Us'));
			$boardMemberTable = new \App\Table\BoardMember();
			$this->addPageContent(new \App\View\Public\ContactUs($this, $boardMemberTable->getBoardMembers()));
			}
		}

	public function getUniqueLink(\App\Record\PublicPage $publicPage) : string
		{
		$baseLink = $publicPage->url ?? '';

		if ($publicPage->hidden)
			{
			return $baseLink . '/' . \App\Model\Session::csrf();
			}

		return $baseLink;
		}
	}
