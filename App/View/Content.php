<?php

namespace App\View;

class Content extends \App\UI\ContentEditor
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

	public function getDisplayCategoryHTML(string $pageName, int | string $year = 0) : \PHPFUI\Container
		{
		$year = (int)$year;
		$blog = new \App\Record\Blog(['name' => $pageName]);

		if (! $blog->loaded())
			{
			$blog->setFrom(['name' => $pageName]);
			$blog->insert();
			}
		$count = $blog->count;

		$container = new \PHPFUI\Container();
		$blogTable = new \App\Table\Blog();
		$stories = $blogTable->getStoriesForBlog($blog, \App\Model\Session::isSignedIn(), $year);

		if (isset($_GET['order']) && $this->addContent)
			{
			$container->add($this->order($stories, $blog));
			}
		else
			{
			if ($this->addContent)
				{
				$buttonGroup = new \PHPFUI\ButtonGroup();
				$button = new \PHPFUI\Button('Add Content Here', "/Content/newStory/{$blog->blogId}?url=" . $this->page->getBaseURL());
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

	public function getStoryHTML(\PHPFUI\ORM\DataObject $story) : \PHPFUI\HTML5Element
		{
		if ($story->empty()) // content not there, show deleted message
			{
			return new \PHPFUI\Header('This content has been deleted', 2);
			}
		$abbrevText = '';
		$blogId = $story->isset('blogId') ? $story->blogId : 0;
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

		while (($pos = \strpos($storyText, $view->getInsertionText())) !== false)
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
			$story->editorId = \App\Model\Session::signedInMemberId();
			$this->editSettings(new \App\Record\Story($story), $settingsItem);

			if (! $abbreviated && $story instanceof \App\Record\Story)
				{
				$getContent = new \PHPFUI\AJAX('getContent');
				$getContent->addFunction('success', '$("#" + data.id).html(data.response);');
				$saveContent = new \PHPFUI\AJAX('saveContent');
				$this->page->addJavaScript($getContent->getPageJS());
				$this->page->addJavaScript($saveContent->getPageJS());
				$csrf = \App\Model\Session::csrf('"');
				$bodySelector = '$("#' . $id . '")';
				$saveContentJS = $saveContent->execute(['id' => '"' . $id . '"', 'csrf' => $csrf, 'body' => $bodySelector . '.html()']);

				$replaceBlobImages = 'uploadImage(' . $bodySelector . '.html(),' . $csrf . ').' .
					'then(resultHtml => { ' . $bodySelector . '.html(resultHtml);' . $saveContentJS . '});';

				$editItem = new \PHPFUI\MenuItem('Edit', '#');
				$icon = new \PHPFUI\FAIcon('far', 'edit');
				$iconId = $icon->getId();
				$editItem->setIcon($icon);
				$editId = $editItem->getId();
				$js = 'var editId=$("#' . $editId . '"),textId=editId.find("span"),iconId=$("#' . $iconId . '");' .
					'if(iconId.hasClass("fa-edit")){textId.html("Save ");iconId.removeClass("fa-edit");' .
					'iconId.addClass("fa-save");' . $this->tinyMCE->getActivateCode($this->page, $id) . $getContent->execute(['id' => '"' . $id . '"', 'csrf' => $csrf]) .
					'}else{var color=$("#' . $settingsItem->getId() . '").css("background-color");' . $replaceBlobImages .
					'textId.html("Saved");editId.css("background-color","lime");setTimeout(function(){textId.html("Save ");' .
					'editId.css("background-color",color)},2000)};return false;';
				$editItem->addAttribute('onclick', $js);
				$iconBar->addMenuItem($editItem);
				$settingsItem->addAttribute('onclick', $replaceBlobImages);
				$iconBar->addMenuItem($settingsItem);

				$javaScriptButton = new \PHPFUI\MenuItem('Script', '#');
				$javaScriptButton->addAttribute('onclick', $replaceBlobImages);
				$javaScriptButton->setIcon(new \PHPFUI\FAIcon('fab', 'js-square'));
				$this->editJavaScript(new \App\Record\Story($story), $javaScriptButton);
				$iconBar->addMenuItem($javaScriptButton);
				}
			else
				{
				$editItem = new \PHPFUI\MenuItem('Edit', '/Content/view/' . $storyId);
				$editItem->setIcon(new \PHPFUI\FAIcon('far', 'edit'));
				$iconBar->addMenuItem($editItem);
				}
			}

		\App\Model\Session::csrf();

		if ($blogId)
			{
			$url = $this->page->getBaseURL();

			if ($this->page->getPermissions()->isAuthorized('Delete Item From Blog', 'Content'))
				{
				$deleteFromPage = new \PHPFUI\AJAX('removeContentFromPage', 'Are you sure you want to remove this story from the page? You can get it back in the Content section.');
				$deleteFromPage->addFunction('success', '$("#storyId-"+data.response).css("background-color","red").hide("fast").remove()');
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
			$deleteContent->addFunction('success', '$("#storyId-"+data.response).css("background-color","red").hide("fast").remove()');
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

		$tabs = new \PHPFUI\Tabs();

		$container = new \PHPFUI\Container();
		$author = new \PHPFUI\Input\Text('author', 'Author', $story->author);
		$author->setToolTip("If set, the author's name will be shown with the last edited date.");
		$container->add($author);
		$fieldSet = new \PHPFUI\FieldSet('Active Dates');
		$date = new \PHPFUI\Input\Date($this->page, 'date', 'Story Date', $story->date);
		$date->setToolTip('Date of the story.  Defaults to today');
		$fieldSet->add($date);
		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $story->startDate);
		$startDate->setToolTip('Content will be shown on start date, leave blank to start immediately');
		$fieldSet->add($startDate);
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $story->endDate);
		$endDate->setToolTip('Content will be shown on end date, leave blank to run forever.');
		$fieldSet->add($endDate);

		$container->add($fieldSet);
		$tabs->addTab('Dates', $container, true);

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
		$tabs->addTab('Special Handling', $fieldSet);

		$blogs = \App\Table\Blog::getBlogsByNameForStory($storyId);
		$multiSelect = new \PHPFUI\Input\MultiSelect('blog', 'Assign to Pages');
		$multiSelect->setColumns(3);

		foreach ($blogs as $blog)
			{
			$multiSelect->addOption($blog->name, $blog->blogId, (bool)$blog->storyId);
			}
		$tabs->addTab('Pages', $multiSelect);
		$form->add($modal->getButtonAndCancel(new \PHPFUI\Submit()));
		$form->add('<br>');
		$form->add($tabs);

		$modal->add($form);
		}

	private function getStoriesHTML(\PHPFUI\ORM\DataObjectCursor $stories, int $count) : string
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

	private function getStorySeparatorHTML() : string
		{
		return '<hr>';
		}

	private function order(\PHPFUI\ORM\DataObjectCursor $stories, \App\Record\Blog $blog) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save Order', 'action');
		$form = new \PHPFUI\Form($this->page, $submit);

		$form->add(new \PHPFUI\Input\Hidden('blogId', (string)$blog->blogId));
		$form->add(new \PHPFUI\SubHeader('Drag and drop stories, then press Save'));
		$countInput = new \PHPFUI\Input\Number('count', 'Number of Stories to Display', $blog->count);
		$countInput->setToolTip('Maximum number of stories to show, zero for all');

		$table = new \PHPFUI\OrderableTable($this->page);
		$rowId = 'storyId';
		$delete = new \PHPFUI\AJAX('removeContentFromPage', 'Are you sure you want to remove this story from this page?');
		$delete->addFunction('success', "$('#{$rowId}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());
		$table->setRecordId($rowId);
		$table->addHeader('headline', 'Story');
		$table->addHeader('membersOnly', 'Members Only');
		$table->addHeader('noTitle', 'No Title');
		$table->addHeader('onTop', 'Always on Top');
		$table->addHeader('showFull', 'Show Full');
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
			$index = '[' . $story->storyId . ']';
			$page['membersOnly'] = new \PHPFUI\Input\CheckBoxBoolean('membersOnly' . $index, value:$story->membersOnly);
			$page['noTitle'] = new \PHPFUI\Input\CheckBoxBoolean('noTitle' . $index, value:$story->noTitle);
			$page['onTop'] = new \PHPFUI\Input\CheckBoxBoolean('onTop' . $index, value:$story->onTop);
			$page['showFull'] = new \PHPFUI\Input\CheckBoxBoolean('showFull' . $index, value:$story->showFull);
			$table->addRow($page);
			}
		$form->add($table);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$backButton = new \PHPFUI\Button('Back To Stories', $this->page->getBaseURL());
		$backButton->addClass('secondary hollow');
		$buttonGroup->addButton($backButton);
		$form->add(new \PHPFUI\MultiColumn($buttonGroup, $countInput));

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
					$storyFileName = $_POST['fileName'] ?? 'unknown';
					$widthHidden = (int)($_POST['widthHidden'] ?? 0);
					$width = (int)($_POST['width'] ?? 0);
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
						$fileName = $photo->getFullPath();

						if (\is_file($fileName))
							{
							[$width, $height, $type, $attr] = \getimagesize($fileName);
							}
						else
							{
							$width = $height = 0;
							}
						$fieldset->add($photo->getImage());

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

					$blog = new \App\Record\Blog($_POST['blogId']);
					$blog->count = (int)$_POST['count'];
					$blog->update();

					foreach ($_POST['storyId'] ?? [] as $ranking => $storyId)
						{
						$blogItem = new \App\Record\BlogItem(['blogId' => $_POST['blogId'], 'storyId' => $storyId]);
						$blogItem->ranking = $ranking + 1;
						$blogItem->membersOnly = (int)$_POST['membersOnly'][$storyId];
						$blogItem->noTitle = (int)$_POST['noTitle'][$storyId];
						$blogItem->onTop = (int)$_POST['onTop'][$storyId];
						$blogItem->showFull = (int)$_POST['showFull'][$storyId];

						$blogItem->update();
						}
					$this->page->setResponse('Saved');

					break;

				case 'getContent':

					[$type, $storyId] = \explode('-', (string)$_POST['id']);
					$story = new \App\Record\Story($storyId);
					$body = \str_replace(['&lt;', '&gt;'], ['<', '>'], $story->body);

					if (\str_contains($body, 'contenteditable'))
						{
						$dom = new \voku\helper\HtmlDomParser($body);
						$div = $dom->findOneOrFalse('div');

						if ($div && $div->hasAttribute('contenteditable'))
							{
							$body = $div->innerHtml();
							}
						}
					$this->page->setRawResponse(\json_encode(['response' => $body, 'id' => $_POST['id'], ], JSON_THROW_ON_ERROR));

					break;


				case 'saveContent':

					[$type, $storyId] = \explode('-', (string)$_POST['id']);
					$story = new \App\Record\Story($storyId);
					$story->editorId = \App\Model\Session::signedInMemberId();
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
					$story->editorId = \App\Model\Session::signedInMemberId();
					$story->update();
					$this->page->redirect();
					$this->page->done();

					break;

				case 'saveSettings':

					$story = new \App\Record\Story((int)$_POST['storyId']);
					$story->setFrom($_POST);
					$story->editorId = \App\Model\Session::signedInMemberId();
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
							$blogItem->setFrom(\array_merge($story->toArray(), ['blogId' => $blog['blogId'], 'ranking' => 0, ]));
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
	}
