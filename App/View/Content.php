<?php

namespace App\View;

class Content extends \App\UI\HTMLEditor
	{
	protected bool $addContent;

	protected int $charsToShow = 500;

	protected \App\Model\ContentFiles $contentFileModel;

	protected int $next = 0;

	protected int $prior = 0;

	private static bool $processed = false;

	public function __construct(\App\View\Page $page)
		{
		parent::__construct($page, $page->getPermissions()->isAuthorized('Edit Content', 'Content'));
		$this->contentFileModel = new \App\Model\ContentFiles();
		$this->addContent = $page->getPermissions()->isAuthorized('Add Content', 'Content');

		if (! self::$processed && ($this->editable || $this->addContent))
			{
			if (\App\Model\Session::checkCSRF())
				{
				$this->processRequest();
				self::$processed = true;
				}
			}
		}

	public function getDisplayCategoryHTML(string $pageName, int $count = 5, int | string $year = 0) : \PHPFUI\Container
		{
		$count = (int)$count;
		$year = (int)$year;
		$blogTable = new \App\Table\Blog();
		$blogTable->setWhere(new \PHPFUI\ORM\Condition('name', $pageName));
		$cursor = $blogTable->getRecordCursor();

		if (! \count($cursor))
			{
			$blog = new \App\Record\Blog();
			$blog->setFrom(['name' => $pageName]);
			$blog->insert();
			}
		else
			{
			$blog = $cursor->current();
			}
		$container = new \PHPFUI\Container();
		$stories = $blogTable->getStoriesForBlog($pageName, \App\Model\Session::isSignedIn(), $year);

		if (isset($_GET['order']) && $this->addContent)
			{
			$container->add($this->order($stories, $blog->blogId));
			}
		else
			{
			if ($this->addContent)
				{
				$buttonGroup = new \PHPFUI\ButtonGroup();
				$button = new \PHPFUI\Button('Add content to this page', "/Content/newStory/{$blog->blogId}?url=" . $this->page->getBaseURL());
				$button->addClass('secondary');
				$button->setConfirm('Are you sure you want to add a story to this page?');
				$buttonGroup->addButton($button);

				if (\count($stories))
					{
					$button = new \PHPFUI\Button('Order Content', $this->page->getBaseURL() . '?order');
					$button->addClass('warning');
					$buttonGroup->addButton($button);
					}
				$container->add($buttonGroup);
				}
			$container->add($this->getStoriesHTML($stories, $count));
			}

		return $container;
		}

	public function getStoriesHTML(iterable $stories, int $count = 0) : string
		{
		$output = '';
		$next = [];
		$i = 0;
		$today = \App\Tools\Date::todayString();

		foreach ($stories as $content)
			{
			if ($count && $i >= $count)
				{
				break;
				}

			if ((! (int)$content->startDate || ($content->startDate <= $today)) && (! (int)$content->endDate || ($content->endDate >= $today)))
				{
				$next[] = clone $content;
				}
			++$i;
			}
		$last = \count($next) - 1;

		foreach ($next as $index => $content)
			{
			if ($index)
				{
				$output .= $this->getStorySeparatorHTML();
				}

			if ($index < $last)
				{
				$this->next = $next[$index + 1]['storyId'];
				}
			else
				{
				$this->next = 0;
				}
			$output .= $this->getStoryHTML($content);
			}

		return $output;
		}

	public function getStoryHTML(\PHPFUI\ORM\DataObject $storyData) : \PHPFUI\HTML5Element
		{
		if ($storyData->empty()) // content not there, show deleted message
			{
			return new \PHPFUI\Header('This content has been deleted', 2);
			}
		$abbrevText = '';
		$blogId = $storyData->isset('blogId') ? $storyData->blogId : 0;
		$story = new \App\Record\Story($storyData->storyId);
		$storyId = $story->storyId;
		$headline = $story->headline;
		$subhead = $story->subhead ?? '';
		$noTitle = $story->noTitle;
		$author = $story->author;
		$date = '';

		if (! empty($story->javaScript))
			{
			$this->page->addJavaScript($story->javaScript);
			}

		if ((int)$story->lastEdited && $story->lastEdited !== $story->date)
			{
			$date = 'updated ' . $story->lastEdited;
			}
		$byline = '';

		if ((int)$story->date)
			{
			$byline = 'on ' . $story->date;
			}
		$output = new \PHPFUI\HTML5Element('div');
		$output->addClass('row');
		$output->setId("storyId-{$storyId}");

		if (! $noTitle && \strlen($headline))
			{
			$output->add(new \PHPFUI\SubHeader($headline));

			if (\strlen($subhead))
				{
				$output->add("<h5>{$subhead}</h5>");
				}
			}

		if (\strlen($author))
			{
			$authorDiv = new \PHPFUI\HTML5Element('div');
			$authorDiv->addClass('row');
			$authorDiv->add("By <strong>{$author}</strong> {$byline} {$date}");

			$output->add($authorDiv);
			}
		$iconBar = new \PHPFUI\Menu();
		$iconBar->setIconAlignment('top');
		$storyText = \App\Tools\TextHelper::unhtmlentities($story->body);

		$view = new \App\View\SlideShow($this->page);

		while ($pos = (int)\strpos($storyText, $view->getInsertionText()))
			{
			$endShow = (int)\strpos($storyText, '~', $pos + 1);
			$show = \substr($storyText, $pos, $endShow - $pos + 1);
			$parts = \explode('-', \trim($show, '~'));
			$slideShow = new \App\Record\SlideShow($parts[1] ?? 0);
			$slideShowHtml = $view->show($slideShow, true);
			$storyText = \str_replace($show, (string)$slideShowHtml, $storyText);
			}
		$abbreviated = false;

		if ($this->charsToShow && ! $story->showFull)
			{
			$abbrevText = \App\Tools\TextHelper::abbreviate($storyText, $this->charsToShow);
			$abbreviated = $abbrevText != $storyText;
			}

		$id = '';
		$storyDiv = new \PHPFUI\HTML5Element('div');

		if ($this->editable)
			{
			$id = 'story-' . $story->storyId;
			$storyDiv->setId($id);
			$this->makeEditable($id);

			$settingsItem = new \PHPFUI\MenuItem('Settings', '#');
			$settingsItem->setIcon(new \PHPFUI\FAIcon('fas', 'cog'));
			$this->editSettings($story, $settingsItem);

			if (! $abbreviated)
				{
				$getContent = new \PHPFUI\AJAX('getContent');
				$getContent->addFunction('success', '$("#" + data.id).html(data.response);');
				$saveContent = new \PHPFUI\AJAX('saveContent');
				$this->page->addJavaScript($getContent->getPageJS());
				$this->page->addJavaScript($saveContent->getPageJS());
				$csrf = \App\Model\Session::csrf('"');
				$editItem = new \PHPFUI\MenuItem('Edit', '#');
				$icon = new \PHPFUI\FAIcon('far', 'edit');
				$iconId = $icon->getId();
				$editItem->setIcon($icon);
				$editId = $editItem->getId();
				$js = 'var editId=$("#' . $editId . '"),textId=editId.find("span"),iconId=$("#' . $iconId . '");' .
					'if(iconId.hasClass("fa-edit")){textId.html("Save ");iconId.removeClass("fa-edit");' .
					'iconId.addClass("fa-save");' . $this->tinyMCE->getActivateCode($this->page, $id) .
					$getContent->execute(['id' => '"' . $id . '"', 'csrf' => $csrf]) .
					'}else{var color=$("#' . $settingsItem->getId() . '").css("background-color");' .
					$saveContent->execute(['id' => '"' . $id . '"', 'csrf' => $csrf, 'body' => '$("#' . $id . '").html()']) .
					'textId.html("Saved");editId.css("background-color","lime");setTimeout(function(){textId.html("Save ");' .
					'editId.css("background-color",color)},2000)};return false;';
				$editItem->addAttribute('onclick', $js);
				$iconBar->addMenuItem($editItem);
				$iconBar->addMenuItem($settingsItem);

				$imagesButton = new \PHPFUI\MenuItem('Images', '#');
				$imagesButton->setIcon(new \PHPFUI\FAIcon('far', 'images'));
				$this->showImages($story, $imagesButton);
				$iconBar->addMenuItem($imagesButton);

				$javaScriptButton = new \PHPFUI\MenuItem('Script', '#');
				$javaScriptButton->setIcon(new \PHPFUI\FAIcon('fab', 'js-square'));
				$this->editJavaScript($story, $javaScriptButton);
				$iconBar->addMenuItem($javaScriptButton);
				}
			else
				{
				$iconBar->addMenuItem($settingsItem);
				}
			}

		\App\Model\Session::csrf();

		if ($blogId)
			{
			$url = $this->page->getBaseURL();

			if ($this->page->getPermissions()->isAuthorized('Delete Item From Blog', 'Content'))
				{
				$deleteFromPage = new \PHPFUI\AJAX('removeContentFromPage', 'Are you sure you want to remove this story from the page? You can get it back in the Content section.');
				$deleteFromPage->addFunction('success', '$("#storyId-"+data.response).css("background-color","red").hide("fast")');
				$this->page->addJavaScript($deleteFromPage->getPageJS());
				$removeItem = new \PHPFUI\MenuItem('Remove', '#');
				$removeItem->setIcon(new \PHPFUI\FAIcon('far', 'trash-alt'));
				$removeItem->addAttribute('onclick', $deleteFromPage->execute(['blogId' => $blogId,
					'storyId' => $storyId, ]));
				$iconBar->addMenuItem($removeItem);
				}
			$this->prior = $storyId;
			}
		elseif ($this->page->getPermissions()->isAuthorized('Delete Content', 'Content'))
			{
			$deleteContent = new \PHPFUI\AJAX('deleteContent', 'Permanently delete! Are you sure?');
			$deleteContent->addFunction('success', '$("#storyId-"+data.response).css("background-color","red").hide("fast")');
			$this->page->addJavaScript($deleteContent->getPageJS());
			$deleteItem = new \PHPFUI\MenuItem('Delete', '#');
			$deleteItem->addAttribute('onclick', $deleteContent->execute(['storyId' => $storyId]));
			$deleteItem->setIcon(new \PHPFUI\FAIcon('far', 'trash-alt'));
			$iconBar->addMenuItem($deleteItem);
			}
		$output->add($iconBar);

		if ($abbreviated)
			{
			$output->add($abbrevText);
			$output->add(new \PHPFUI\Link("/Content/view/{$storyId}", "({$headline} continues ...)", false));

			return $output;
			}

		$storyDiv->add($storyText);
		$output->add($storyDiv);

		return $output;
		}

	public function setCharsToShow(int $chars = 500) : void
		{
		$this->charsToShow = $chars;
		}

	public function showContinuousScrollTable(\App\Table\Story $storyTable, string $title = '') : string
		{
		$view = new \App\UI\ContinuousScrollTable($this->page, $storyTable);
		$record = $storyTable->getRecord();
		$record->addDisplayTransform('startDate', $record->blankDate(...));
		$record->addDisplayTransform('endDate', $record->blankDate(...));
		$record->addDisplayTransform('lastEdited', $record->blankDate(...));
		$view->addCustomColumn('headline', static fn (array $row) => new \PHPFUI\Link('/Content/view/' . $row['storyId'], $row['headline'], false));
		$view->addCustomColumn('Editor', static function(array $row)
			{
			$member = new \App\Record\Member($row['editorId'] ?? 0);

			return $member->fullName();
			});

		$headers = ['headline', 'author', 'startDate', 'endDate', 'lastEdited', ];

		if ($this->page->isAuthorized('Delete Content'))
			{
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $storyTable, 'Are you sure you want to permanently delete this story?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			}
		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['del']))->setSortableColumns($headers);

		$headline = empty($title) ? '' : "<h2>{$title}</h2>";

		return $headline . $view;
		}

	public function showStoriesInTable(\App\Table\Story $storyTable, string $headline = '') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($headline)
			{
			$container->add(new \PHPFUI\SubHeader($headline));
			}

		$headers = ['headline', 'Editor', 'lastEdited'];

		if ($this->page->isAuthorized('Delete Item From Blog'))
			{
			$headers[] = 'Delete From Category';
			}
		$view = new \App\UI\ContinuousScrollTable($this->page, $storyTable);


		$view = new \App\UI\ContinuousScrollTable($this->page, $storyTable);
		$record = $storyTable->getRecord();
		$record->addDisplayTransform('lastEdited', $record->blankDate(...));
		$view->addCustomColumn('headline', static fn (array $row) => new \PHPFUI\Link('/Content/view/' . $row['storyId'], $row['headline'], false));
		$view->addCustomColumn('Editor', static function(array $row)
			{
			$member = new \App\Record\Member($row['editorId'] ?? 0);

			return $member->fullName();
			});

		$headers = ['headline', 'startDate', 'endDate', 'lastEdited', ];

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $storyTable);
		$view->addCustomColumn('Delete From Category', $deleter->columnCallback(...));
		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['Delete From Category']))->setSortableColumns($headers);

		$container->add($view);

		return $container;
		}

	private function editJavaScript(\App\Record\Story $story, \PHPFUI\HTML5Element $modalLink) : void
		{
		$storyId = $story->storyId;
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('storyId', (string)$story->storyId));
		$form->add(new \PHPFUI\Input\Hidden('action', 'saveJavaScript'));
		$form->add(new \PHPFUI\SubHeader('JavaScript'));
		$form->add('Enter JavaScript.  Do not include the &lt;script&gt; or &lt;/script&gt; tags.');
		$javaScript = new \PHPFUI\Input\TextArea('javaScript', 'JavaScript', $story->javaScript);
		$javaScript->setToolTip('Enter any JavaScript you want on the page.  DO NOT include the open or close script tags.');
		$form->add($javaScript);
		$form->add($modal->getButtonAndCancel(new \PHPFUI\Submit()));
		$modal->add($form);
		}

	private function editSettings(\App\Record\Story $story, \PHPFUI\HTML5Element $modalLink) : void
		{
		$storyId = $story->storyId;
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('storyId', (string)$story->storyId));
		$form->add(new \PHPFUI\Input\Hidden('action', 'saveSettings'));
		$fieldSet = new \PHPFUI\FieldSet('Headlines');
		$headline = new \PHPFUI\Input\Text('headline', 'Headline', $story->headline);
		$headline->setRequired()->setToolTip('Headline of the story. If you don\'t want a headline, check the "Don\'t Show Title" box below');
		$fieldSet->add($headline);
		$subhead = new \PHPFUI\Input\Text('subhead', 'Sub Head', $story->subhead);
		$subhead->setToolTip('Sub Headline for more secondary information. You can leave blank if you want');
		$fieldSet->add($subhead);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Active Dates');
		$column = new \PHPFUI\Cell(12, 3, 3);
		$date = new \PHPFUI\Input\Date($this->page, 'date', 'Story Date', $story->date);
		$date->setToolTip('Date of the story.  Defaults to today');
		$column->add($date);
		$fieldSet->add($column);
		$column = new \PHPFUI\Cell(12, 3, 3);
		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $story->startDate);
		$startDate->setToolTip('Content will be shown on start date, leave blank to start immediately');
		$column->add($startDate);
		$fieldSet->add($column);
		$column = new \PHPFUI\Cell(12, 3, 3);
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $story->endDate);
		$endDate->setToolTip('Content will be shown on end date, leave blank to run forever.');
		$column->add($endDate);
		$fieldSet->add($column);
		$column = new \PHPFUI\Cell(12, 3, 3);
		$author = new \PHPFUI\Input\Text('author', 'Author', $story->author);
		$author->setToolTip("If set, the author's name will be shown with the last edited date.");
		$column->add($author);
		$fieldSet->add($column);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Special Handling');
		$column = new \PHPFUI\Cell(12, 6, 3);
		$cb = new \PHPFUI\Input\CheckBoxBoolean('showFull', 'Show Full Content', (bool)$story->showFull);
		$cb->setToolTip('If set, the entire story will appear on the page.  If not checked, and the story is long, it will show the first part of the story and a continue link.');
		$column->add($cb);
		$fieldSet->add($column);
		$column = new \PHPFUI\Cell(12, 6, 3);
		$cb = new \PHPFUI\Input\CheckBoxBoolean('noTitle', "Don't Show Title", (bool)$story->noTitle);
		$cb->setToolTip("Sometimes you don't want the title to show, this will turn the title off.");
		$column->add($cb);
		$fieldSet->add($column);
		$column = new \PHPFUI\Cell(12, 6, 3);
		$cb = new \PHPFUI\Input\CheckBoxBoolean('onTop', 'Always On Top', (bool)$story->onTop);
		$cb->setToolTip('If you check this, the story will always appear at the top of the page (with other stories with this checked as well).');
		$column->add($cb);
		$fieldSet->add($column);
		$column = new \PHPFUI\Cell(12, 6, 3);
		$cb = new \PHPFUI\Input\CheckBoxBoolean('membersOnly', 'Show to Members Only', (bool)$story->membersOnly);
		$cb->setToolTip('If you check this, the story will only be shown to members even if it is on a public page.');
		$column->add($cb);
		$fieldSet->add($column);
		$form->add($fieldSet);
		$blogs = \App\Table\Blog::getBlogsByNameForStory($storyId);
		$multiSelect = new \PHPFUI\Input\MultiSelect('blog', 'Assign to Pages');
		$multiSelect->setColumns(3);

		foreach ($blogs as $blog)
			{
			$multiSelect->addOption($blog->name, $blog->blogId, (bool)$blog->storyId);
			}
		$form->add($multiSelect);
		$form->add($modal->getButtonAndCancel(new \PHPFUI\Submit()));
		$modal->add($form);
		}

	private function getStorySeparatorHTML() : string
		{
		return '<hr>';
		}

	private function order(iterable $stories, int $blogId) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save Order', 'action');
		$form = new \PHPFUI\Form($this->page, $submit);

		$form->add(new \PHPFUI\Input\Hidden('blogId', (string)$blogId));
		$form->add(new \PHPFUI\SubHeader('Drag and drop stories, then press Save'));

		$table = new \PHPFUI\OrderableTable($this->page);
		$rowId = 'storyId';
		$delete = new \PHPFUI\AJAX('removeContentFromPage', 'Are you sure you want to remove this story from this page?');
		$delete->addFunction('success', "$('#{$rowId}-'+data.response).css('background-color','red').hide('fast')");
		$this->page->addJavaScript($delete->getPageJS());
		$table->setRecordId($rowId);
		$table->addHeader('headline', 'Story');
		$table->addHeader('delete', 'Del');

		foreach ($stories as $story)
			{
			$page = $story->toArray();
			$storyId = $story->storyId;
			$hidden = new \PHPFUI\Input\Hidden("{$rowId}[]", $storyId);
			$link = new \PHPFUI\Link('/Content/view/' . $storyId, $story->headline, false);
			$page['headline'] = $hidden . $link;
			$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$trash->addAttribute('onclick', $delete->execute([$rowId => $storyId]));
			$page['delete'] = $trash;
			$table->addRow($page);
			}
		$form->add($table);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$buttonGroup->addButton(new \PHPFUI\Button('Back to Stories', $this->page->getBaseURL()));
		$form->add($buttonGroup);

		return $form;
		}

	private function processRequest() : void
		{
		if (isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'Add and Copy':
					$photo = new \App\Record\Photo($_POST['photoId']);
					$storyFileName = $_POST['fileName'];
					$widthHidden = (int)$_POST['widthHidden'];
					$width = (int)$_POST['width'];
					$contentFileModel = new \App\Model\ContentFiles();
					$storyImageFullPath = $contentFileModel->get($storyFileName);

					\copy($photo->getFullPath(), $storyImageFullPath);

					if ($width != $widthHidden)
						{
						$contentFileModel->resizeToWidth($storyImageFullPath, $width);
						}
					else
						{
						$contentFileModel->processFile($storyImageFullPath);
						}
					$this->page->redirect();

					break;

				case 'getPhotoInfo':
					$photo = new \App\Record\Photo($_POST['photoId']);
					$fieldset = new \PHPFUI\FieldSet('Choosen Photo');

					if (\file_exists($photo->getFullPath()))
						{
						$fieldset->add($photo->getImage());
						[$width, $height, $type, $attr] = \getimagesize($photo->getFullPath());
						$sizeSet = new \PHPFUI\FieldSet('Current Size (Enter a smaller width if desired)');

						$widthHidden = new \PHPFUI\Input\Hidden('widthHidden', (string)$width);
						$widthHidden->setId('widthHiddenId');
						$sizeSet->add($widthHidden);

						$heightHidden = new \PHPFUI\Input\Hidden('heightHidden', (string)$height);
						$heightHidden->setId('heightHiddenId');
						$sizeSet->add($heightHidden);

						$widthInput = new \PHPFUI\Input\Number('width', 'Width', $width);
						$widthInput->setId('widthId');
						$widthInput->addAttribute('onchange', 'var height=$("#heightHiddenId").val();var width=$("#widthHiddenId").val();var newWidth=$("#widthId").val();if(newWidth>width){newWidth=width;$("#widthId").val(width);1};height=newWidth/width*height;$("#heightId").text(Math.round(height));');

						$heightText = new \App\UI\Display('Height', $height);
						$heightText->getTextElement()->setId('heightId');
						$sizeSet->add(new \PHPFUI\MultiColumn($widthInput, $heightText));
						$fieldset->add($sizeSet);

						$fileName = 'story' . $_POST['storyId'] . '-' . \bin2hex(\random_bytes(7)) . $photo->extension;
						$fieldset->add(new \PHPFUI\Input\Hidden('fileName', $fileName));

						$url = $this->page->getSchemeHost() . '/images/content/' . $fileName;
						$imageUrl = new \PHPFUI\Input('text', 'copyUrlId', $url);
						$imageUrl->setId('copyUrlId');
						$imageUrl->addClass('hide');
						$fieldset->add($imageUrl);
						}
					else
						{
						$fieldset->add(new \PHPFUI\Header('Image is missing', 4));
						}

					$this->page->setRawResponse($fieldset, false);

					break;

				case 'Save Order':
					foreach ($_POST['storyId'] as $ranking => $storyId)
						{
						$blogItem = new \App\Record\BlogItem(['blogId' => $_POST['blogId'], 'storyId' => $storyId]);
						$blogItem->ranking = $ranking + 1;
						$blogItem->update();
						}
					$this->page->setResponse('Saved');

					break;

				case 'getContent':

					[$type, $storyId] = \explode('-', (string)$_POST['id']);
					$story = new \App\Record\Story($storyId);
					$this->page->setRawResponse(\json_encode(['response' => $story->body, 'id' => $_POST['id'], ], JSON_THROW_ON_ERROR));

					break;


				case 'saveContent':

					[$type, $storyId] = \explode('-', (string)$_POST['id']);
					$story = new \App\Record\Story($storyId);
					$story->body = $_POST['body'];
					$story->update();
					$this->page->setResponse($storyId);
					$this->page->done();

					break;

				case 'removeContentFromPage':

					$id = -1;

					if ($this->page->getPermissions()->isAuthorized('Delete Item From Blog', 'Content'))
						{
						$blogItem = new \App\Record\BlogItem();
						$blogItem->setFrom($_POST);
						$blogItem->delete();
						$id = $_POST['storyId'];
						}
					$this->page->setResponse($id);

					break;


				case 'deleteContent':

					if ($this->page->getPermissions()->isAuthorized('Delete Content', 'Content'))
						{
						$story = new \App\Record\Story((int)$_POST['storyId']);
						$story->delete();
						$this->contentFileModel->delete("story{$_POST['storyId']}-*");
						$this->page->setResponse($_POST['storyId']);
						}

					break;

				case 'deleteStoryPhoto':
					$fileName = $_POST['fileName'];
					$this->contentFileModel->delete(\substr($fileName, 0, \strrpos($fileName, '.')));
					$this->page->setResponse($_POST['index']);

					break;

				case 'saveJavaScript':

					$story = new \App\Record\Story((int)$_POST['storyId']);
					$story->setFrom($_POST);
					$story->update();
					$this->page->redirect();
					$this->page->done();

					break;

				case 'saveSettings':

					$story = new \App\Record\Story((int)$_POST['storyId']);
					$story->setFrom($_POST);
					$story->update();
					$blogs = \App\Table\Blog::getBlogsByNameForStory($storyId = $_POST['storyId']);
					$activeBlogs = \array_flip($_POST['blog'] ?? []);

					foreach ($blogs as $blog)
						{
						if ($blog['storyId'] && ! isset($activeBlogs[$blog['blogId']])) // previouly existed, not now, delete
							{
							$blogItem = new \App\Record\BlogItem();
							$blogItem->setFrom(['blogId' => $blog['blogId'], 'storyId' => $blog['storyId']]);
							$blogItem->delete();
							}
						elseif (empty($blog['storyId']) && isset($activeBlogs[$blog['blogId']])) // was not set, so set it
							{
							$blogItem = new \App\Record\BlogItem();
							$blogItem->setFrom(['blogId' => $blog['blogId'], 'storyId' => $storyId, 'ranking' => 0, ]);
							$blogItem->insert();
							\App\Table\Blog::renumberBlog($blog['blogId']);  // insert at top of blog and renumber
							}
						}
					$this->page->redirect();
					$this->page->done();

					break;

				}
			}
		}

	private function showImages(\App\Record\Story $story, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$callout = new \PHPFUI\HTML5Element('span');
		$callout->add('Copied!');
		$callout->addClass('callout success small hide');
		$form->add(new \PHPFUI\Header('Manage Story Images', 4));
		$form->add($callout);

		$currentFiles = $this->contentFileModel->getAll('story' . $story->storyId . '-*');

		if (\count($currentFiles))
			{
			$existingPhotoSet = new \PHPFUI\FieldSet('Existing Story Photos');
			$table = new \PHPFUI\Table();
			$table->setHeaders(['View', 'Copy', 'Delete']);
			$table->setRecordId($recordIndex = 'index');
			$delete = new \PHPFUI\AJAX('deleteStoryPhoto', 'Permanently delete this photo from this story?');
			$delete->addFunction('success', "$('#{$recordIndex}-'+data.response).css('background-color','red').hide('fast');");
			$this->page->addJavaScript($delete->getPageJS());

			foreach ($currentFiles as $index => $file)
				{
				$row['index'] = $index;
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute([$recordIndex => $index, 'fileName' => '"' . $file . '"']));
				$row['Delete'] = $icon;
				$view = new \PHPFUI\FAIcon('far', 'eye', '#');
				$reveal = new \PHPFUI\Reveal($this->page, $view);
				$reveal->addAttribute('data-multiple-opened', 'true');
				$div = new \PHPFUI\HTML5Element('div');
				$reveal->add($div);
				$close = $reveal->getCloseButton('Close');
				$reveal->closeOnClick($close);
				++$index;
				$reveal->add(new \PHPFUI\Image('/images/content/' . $file, "Photo {$index} for story"));
				$reveal->add('<br>');
				$reveal->add($close);
				$row['View'] = $view;

				$copyIcon = new \PHPFUI\FAIcon('far', 'clipboard');
				$url = $this->page->getSchemeHost() . '/images/content/' . $file;
				$this->page->addCopyToClipboard($url, $copyIcon, $callout);
				$row['Copy'] = $copyIcon;
				$table->addRow($row);
				}
			$existingPhotoSet->add($table);
			$form->add($existingPhotoSet);
			}

		$link = new \PHPFUI\Link('/Photo/browse', 'Photo Section', false);
		$link->addAttribute('target', '_blank');
		$photoPicker = new \App\UI\PhotoPicker($this->page, $name = 'photoId', 'Start typing to select a photo from our ' . $link);
		$imageDiv = new \PHPFUI\HTML5Element('div');
		$imageDivId = $imageDiv->getId();

		$editControl = $photoPicker->getEditControl();
		$editControl->addAutoCompleteOption('minChars', 1);

		$csrf = \PHPFUI\Session::csrf("'");
		$csrfField = \PHPFUI\Session::csrfField();
		$className = \basename(\str_replace('\\', '/', \get_class($editControl)));
		$loading = \str_replace("\n", '', new \App\UI\Loading());
		$addAndCopyButton = new \PHPFUI\Submit('Add and Copy', 'action');
		$addAndCopyButton->addClass('disabled');
		$addAndCopyButtonId = $addAndCopyButton->getId();
		$dollar = '$';
		$js = "function(suggestion){if(noFF){{$dollar}('#'+id).attr('placeholder',suggestion.value).attr('value','');};" .
			"{$dollar}('#'+id+'hidden').val(suggestion.data).change();{$dollar}('#{$imageDivId}').html(`{$loading}`);" .
			"{$dollar}.ajax({type:'POST',traditional:true,data:{{$csrfField}:{$csrf},save:true,fieldName:'{$name}',{$className}:suggestion.data}}).done(" .
			"{$dollar}.ajax({type:'POST',traditional:true,data:{{$csrfField}:{$csrf},action:'getPhotoInfo',photoId:suggestion.data,storyId:{$story->storyId}}})" .
			".done(function(resp){{$dollar}('#{$addAndCopyButtonId}').removeClass('disabled');{$dollar}('#{$imageDivId}').html(resp)}))}";

		$editControl->addAutoCompleteOption('onSelect', $js);
		$form->add($editControl);
		$form->add($imageDiv);
		$js = '$("#copyUrlId").toggleClass("hide").select();document.execCommand("copy");$("#copyUrlId").toggleClass("hide");';
		$addAndCopyButton->addAttribute('onclick', $js);
		$js = '$("#widthId").on("change",function(){alert("change");var height=$("#heightHiddenId").val();var width=$("#widthHiddenId").val();$("#heightId").val("fred");})';
		$this->page->addJavaScript($js);
		$form->add($modal->getButtonAndCancel($addAndCopyButton));
		$modal->add($form);
		}
	}
