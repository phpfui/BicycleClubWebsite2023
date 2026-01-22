<?php

namespace App\WWW;

class Content extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Content $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteCategory':
						if ($this->page->controller->getPermissions()->isAuthorized('Delete Content Category', 'Content'))
							{
							$blog = new \App\Record\Blog((int)$_POST['blogId']);
							$blog->delete();
							$this->page->setResponse($_POST['blogId']);
							}

						break;
					}
				}
			}
		$this->view = new \App\View\Content($this->page);
		}

	public function blog(string $category = '') : void
		{
		$this->page->addPageContent($this->view->getDisplayCategoryHTML($category));
		}

	public function categories() : void
		{
		$table = new \PHPFUI\Table();
		$blogTable = new \App\Table\Blog();
		$blogTable->setOrderBy('name');
		$blogs = $blogTable->getRecordCursor();
		$table->setRecordId('blogId');
		$ajax = new \PHPFUI\AJAX('deleteCategory', 'Permanently delete this category?');
		$ajax->addFunction('success', '$("#blogId-"+data.response).css("background-color","red").hide("fast").remove()');
		$this->page->addJavaScript($ajax->getPageJS());
		$table->addHeader('name', 'Category');

		if ($this->page->controller->getPermissions()->isAuthorized('Delete Content Category', 'Content'))
			{
			$table->addHeader('delete', 'Delete');
			}

		foreach ($blogs as $blogRecord)
			{
			$blog = $blogRecord->toArray();
			$id = $blog['blogId'];
			$url = \urlencode((string)$blog['name']);
			$blog['name'] = "<a href='/Content/category/{$url}'>{$blog['name']}</a>";
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $ajax->execute(['blogId' => $id]));
			$blog['delete'] = $icon;
			$table->addRow($blog);
			}
		$headline = '<h2>Content By Category</h2>';
		$this->page->addPageContent($headline . $table);
		}

	public function category(string $category = '') : void
		{
		$category = \urldecode($category);
		$storyTable = new \App\Table\Story();
		$storyTable->setAllStoriesOnBlog($category);
		$this->page->addPageContent($this->view->showStoriesInTable($storyTable, "{$category} Content"));
		}

	public function imageDelete(string $category = '') : void
		{
		$result = [];

		if ($this->page->isAuthorized('Delete Image'))
			{
			\App\Tools\File::unlink(PUBLIC_ROOT . $_POST['src']);
			}
		else
			{
			$result['error'] = 'You are not authorized to delete an image.';
			}
		$this->page->setRawResponse(\json_encode($result, JSON_THROW_ON_ERROR));
		}

	public function images(string $category = '') : void
		{
		$path = PUBLIC_ROOT . 'images/' . $category . '/*';
		$dirs = [];

		foreach (\glob($path) as $file)
			{
			$dirs[$file] = \filemtime($file);
			}

		\arsort($dirs);

		$images = [];

		foreach ($dirs as $file => $time)
			{
			$image = [];
			$image['url'] = \substr($file, \strlen((string)PUBLIC_ROOT));
			$parts = \explode('/', $file);
			$thumbParts = \array_splice($parts, \count($parts) - 1, 0, 'thumbs');
			$thumbPath = \implode('/', $thumbParts);

			if (\file_exists($thumbPath))
				{
				$image['thumb'] = \substr($thumbPath, \strlen((string)PUBLIC_ROOT));
				}
			$images[] = $image;
			}
		$this->page->setRawResponse(\json_encode($images, JSON_THROW_ON_ERROR));
		}

	public function newStory(\App\Record\Blog $blog = new \App\Record\Blog()) : void
		{
		if (! $this->page->isAuthorized('Add Content'))
			{
			$this->page->redirect('/');

			return;
			}
		$this->view->setCharsToShow(0);
		$story = new \App\Record\Story();
		$member = \App\Model\Session::signedInMemberRecord();
		$story->body = 'Enter body here...';
		$story->headline = 'Headline';
		$story->author = $member->fullName();
		$story->editorId = \App\Model\Session::signedInMemberId();
		$storyId = $story->insert();

		if (! $blog->empty())
			{
			$blogItem = new \App\Record\BlogItem();
			$blogItem->story = $story;
			$blogItem->blog = $blog;
			$blogItem->ranking = 100;
			$blogItem->insert();
			}
		$this->page->redirect("/Content/view/{$storyId}");
		}

	public function orphan() : void
		{
		$table = new \App\Table\Story();
		$table->addOrderBy('lastEdited', 'desc');
		$ids = \PHPFUI\ORM::getValueArray('select distinct storyId from blogItem');
		$table->setWhere(new \PHPFUI\ORM\Condition('storyId', $ids, new \PHPFUI\ORM\Operator\NotIn()));
		$this->page->addPageContent($this->view->showContinuousScrollTable($table, 'Orphan Content'));
		}

	public function purge() : void
		{
		$output = '';

		$storyTable = new \App\Table\Story();

		if (isset($_POST['purgeAll']) && \App\Model\Session::checkCSRF())
			{
			$storyTable->setStoriesToPurge($_POST['purgeAll']);
			$this->page->addPageContent($this->view->showStoriesInTable($storyTable, 'The Following Stories Were Purged'));
			\App\Table\Story::purgeStories($_POST['purgeAll']);
			}
		elseif (isset($_POST['purgeDate']))
			{
			$storyTable->setStoriesToPurge($_POST['purgeDate']);

			if (\count($storyTable))
				{
				$output .= $this->view->showStoriesInTable($storyTable, 'Purge These Stories?');
				$form = new \PHPFUI\Form($this->page);
				$submit = new \PHPFUI\Submit('Purge Content');
				$form->add(new \PHPFUI\Input\Hidden('purgeAll', $_POST['purgeDate']));
				$form->add($submit);
				$output .= $form;
				}
			else
				{
				$output = '<h3>Nothing to purge</h3>';
				}
			}
		else
			{
			$form = new \PHPFUI\Form($this->page);
			$fieldSet = new \PHPFUI\FieldSet('Purge Content');
			$date = new \PHPFUI\Input\Date($this->page, 'purgeDate', 'Purge Stories Older Than', \App\Tools\Date::todayString(-365));
			$date->setRequired();
			$date->setToolTip('Content older than this date will be deleted');
			$fieldSet->add($date);
			$fieldSet->add(new \PHPFUI\Submit('Show Content To Be Purged'));
			$form->add($fieldSet);
			$output = $form;
			}
		$this->page->addPageContent($output);
		}

	public function recent() : void
		{
		$table = new \App\Table\Story();
		$table->addOrderBy('lastEdited', 'desc');
		$this->page->addPageContent($this->view->showContinuousScrollTable($table, 'Most Recently Added / Edited Content'));
		}

	public function search() : void
		{
		if ($this->page->addHeader('Search Content'))
			{
			$this->page->addPageContent(new \App\View\Content\Search($this->page));
			}
		}

	public function view(\App\Record\Story $story = new \App\Record\Story()) : void
		{
		$this->view->setCharsToShow(0);

		if (! $story->empty())
			{
			$this->page->setPublic(! $story->membersOnly);
			$this->page->addPageContent($this->view->getStoryHTML($story));
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Story not found'));
			}
		}
	}
