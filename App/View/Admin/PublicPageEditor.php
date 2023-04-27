<?php

namespace App\View\Admin;

class PublicPageEditor
	{
	private array $methods = [''];

	public function __construct(private readonly \App\View\Page $page)
		{
		$viewReflection = new \ReflectionClass(\App\View\Public\PageTrait::class);

		foreach ($viewReflection->getMethods() as $method)
			{
			if ('_' != $method->name[0])
				{
				$name = '';

				foreach (\str_split($method->name) as $char)
					{
					if (\ctype_upper($char))
						{
						$name .= ' ';
						}
					$name .= $char;
					}
				$this->methods[$method->name] = \trim($name);
				}
			}
		}

	public function edit(\App\Record\PublicPage $publicPage = new \App\Record\PublicPage()) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save');
		$form = $this->getForm($publicPage, $submit);

		if ($form->isMyCallback())
			{
			if ('/' != $_POST['url'][0])
				{
				$_POST['url'] = '/' . $_POST['url'];
				}
			$_POST['publicPageId'] = $publicPage->publicPageId;

			if ($publicPage->publicPageId)
				{
				unset($_POST['publicPageId']);
				$publicPage->setFrom($_POST);
				$publicPage->update();
				}
			$this->page->setResponse('Saved');

			return $form;
			}
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);

		$button = new \PHPFUI\Button('Public Pages', '/Admin/publicPage');
		$button->addClass('hollow')->addClass('secondary');
		$buttonGroup->addButton($button);
		$form->add($buttonGroup);

		return $form;
		}

	public function list(\App\Table\PublicPage $publicPageTable) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save Order');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			\PHPFUI\ORM::beginTransaction();
			$sequence = 0;

			if (isset($_POST['publicPageId']))
				{

				foreach ($_POST['publicPageId'] as $publicPageId)
					{
					$publicPage = new \App\Record\PublicPage($publicPageId);
					$publicPage->sequence = ++$sequence;
					$publicPage->update();
					}
				}
			\PHPFUI\ORM::commit();
			$this->page->setResponse('Saved');
			}
		elseif (isset($_POST['action']) && \App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'])
				{
				case 'deletePublicPage':
					$publicPage = new \App\Record\PublicPage((int)$_POST['publicPageId']);
					$publicPage->delete();
					$this->page->setResponse($_POST['publicPageId']);

					break;

				case 'Add':
					if ('/' != $_POST['url'][0])
						{
						$_POST['url'] = '/' . $_POST['url'];
						}
					$publicPage = new \App\Record\PublicPage();
					$publicPage->setFrom($_POST);
					$publicPage->insert();
					$this->page->redirect();

					break;
				}
			}
		else
			{
			$rowId = 'publicPageId';
			$delete = new \PHPFUI\AJAX('deletePublicPage', 'Are you sure you want to delete this page?');
			$delete->addFunction('success', "$('#{$rowId}-'+data.response).css('background-color','red').hide('fast')");
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\OrderableTable($this->page);
			$table->setRecordId($rowId);
			$table->addHeader('name', 'Name');
			$table->addHeader('delete', 'Del');

			foreach ($publicPageTable->getRecordCursor() as $publicPage)
				{
				$page = $publicPage->toArray();
				$publicPageId = $page[$rowId];
				$hidden = new \PHPFUI\Input\Hidden("{$rowId}[]", $publicPageId);
				$link = new \PHPFUI\Link('/Admin/publicEdit/' . $publicPageId, $publicPage->name ?? '', false);
				$page['name'] = $hidden . $link;
				$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$trash->addAttribute('onclick', $delete->execute([$rowId => $publicPageId]));
				$page['delete'] = $trash;
				$table->addRow($page);
				}
			$form->add($table);
			$add = new \PHPFUI\Button('Add Page');
			$add->addClass('warning');
			$form->saveOnClick($add);
			$this->addPublicPageModal($add);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			}

		return $form;
		}

	private function addMethods(\PHPFUI\Input\Select $select, string $default = '') : void
		{
		foreach ($this->methods as $method => $text)
			{
			$select->addOption($text, $method, $method == $default);
			}
		}

	private function addPublicPageModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$modalForm = $this->getForm(new \App\Record\PublicPage());
		$modalForm->setAreYouSure(false);
		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add', 'action')));
		$modal->add($modalForm);
		}

	private function getForm(\App\Record\PublicPage $publicPage, ?\PHPFUI\Submit $submit = null) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Page Settings');
		$name = new \PHPFUI\Input\Text('name', 'Page Header Name', $publicPage->name);
		$name->setRequired();
		$fieldSet->add($name);

		$header = new \PHPFUI\Input\CheckBoxBoolean('header', 'Show Above as Headline', (bool)$publicPage->header);
		$header->setToolTip('Uncheck prevent header from being displayed');
		$select = new \PHPFUI\Input\Select('method', 'Functionality');
		$select->setToolTip('Add additional page functionality besides start and end content.');
		$this->addMethods($select, $publicPage->method ?? '');
		$fieldSet->add(new \PHPFUI\MultiColumn($header, $select));

		$url = new \PHPFUI\Input\Text('url', 'URL of the page', $publicPage->url);
		$url->setToolTip('The URL relative to the root domain.  Should start with a /.');
		$url->setRequired();
		$homePageNotification = new \PHPFUI\Input\CheckBoxBoolean(' homePageNotification', 'Home Page Notification', (bool)$publicPage->homePageNotification);
		$homePageNotification->setToolTip('Latest content headline will be listed on the user Home page');
		$fieldSet->add(new \PHPFUI\MultiColumn($url, $homePageNotification));

		$banner = new \PHPFUI\Input\CheckBoxBoolean('banner', 'Show page with banner', (bool)$publicPage->banner);
		$banner->setToolTip('Check to show photo carousel at top of page');
		$blog = new \PHPFUI\Input\CheckBoxBoolean('blog', 'Allow Custom Content', (bool)$publicPage->blog);
		$blog->setToolTip('Check to display content at top of page (named as the header)');
		$fieldSet->add(new \PHPFUI\MultiColumn($banner, $blog));

		$hidden = new \PHPFUI\Input\RadioGroup('hidden', 'Page Visability', (string)$publicPage->hidden);
		$hidden->setToolTip('PUBLIC links allow third party website to link to them. NO OUTSIDE LINKS will not allow a third party page to access the link, but is accessable via direct links on the website.');
		$hidden->addButton('Public', (string)0);
		$hidden->addButton('No Outside Links', (string)1);

		$publicMenu = new \PHPFUI\Input\CheckBoxBoolean('publicMenu', 'Public Menu', (bool)$publicPage->publicMenu);
		$publicMenu->setToolTip('Check to display on public menu');
		$footer = new \PHPFUI\Input\CheckBoxBoolean('footerMenu', 'Footer', (bool)$publicPage->footerMenu);
		$footer->setToolTip('Check to display on the page footer');
		$fieldSet->add(new \PHPFUI\MultiColumn($hidden, new \PHPFUI\MultiColumn($publicMenu, $footer)));

		$blogAfter = new \PHPFUI\Input\Text('blogAfter', 'Name of content category to display at the bottom of the page', $publicPage->blogAfter);
		$blogAfter->setToolTip('Supply a name to display a content category for the bottom of the page');
		$fieldSet->add($blogAfter);
		$form->add($fieldSet);

		return $form;
		}
	}
