<?php

namespace App\View\Public;

class Page implements \PHPFUI\Interfaces\NanoClass
	{
	use PageTrait;

	private ?\App\View\Content $content = null;

	private \App\View\Page $page;

	public function __construct(private \PHPFUI\Interfaces\NanoController $controller) // @phpstan-ignore-line
		{
		}

	public function __toString() : string
		{
		return "{$this->page}";
		}

	public function custom(\App\View\Page $page) : void
		{
		$this->page = $page;
		$url = \strtolower(\str_replace('/' . \App\Model\Session::csrf(), '', $page->getBaseUrl()));
		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->setWhere(new \PHPFUI\ORM\Condition('url', $url . '%', new \PHPFUI\ORM\Operator\Like()));
		$publicPageCursor = $publicPageTable->getRecordCursor();

		if (! \count($publicPageCursor))
			{
			$page->addPageContent("Page {$url} is not defined");

			return;
			}

		$publicPage = $publicPageCursor->current();

		$page->setPublic(\App\Enum\Admin\PublicPageVisibility::MEMBER_ONLY != $publicPage->hidden);

		if ($publicPage->banner)
			{
			$page->addBanners();
			}

		if ($publicPage->header)
			{
			$page->addHeader($publicPage->name, banner:$publicPage->banner);
			}

		if ($publicPage->blog)
			{
			if (! $this->content)
				{
				$this->content = new \App\View\Content($page);
				}
			$page->addPageContent($this->content->getDisplayCategoryHTML($publicPage->name));
			}

		if ($method = $publicPage->method)
			{
			$page->addPageContent($this->{$method}($page));
			}

		if ($publicPage->blogAfter)
			{
			if (! $this->content)
				{
				$this->content = new \App\View\Content($page);
				}
			$page->addPageContent($this->content->getDisplayCategoryHTML($publicPage->blogAfter));
			}
		}

	public static function getUniqueLink(\App\Record\PublicPage $publicPage) : string
		{
		$baseLink = $publicPage->url ?? '';

		if (\App\Enum\Admin\PublicPageVisibility::NO_OUTSIDE_LINKS == $publicPage->hidden)
			{
			return $baseLink . '/' . \App\Model\Session::csrf();
			}

		return $baseLink;
		}
	}
