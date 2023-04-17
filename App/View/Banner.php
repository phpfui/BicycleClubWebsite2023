<?php

namespace App\View;

class Banner
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function edit(\App\Record\Banner $banner) : string | \PHPFUI\Form
		{
		$addBannerButton = new \PHPFUI\Button('Upload Banner');
		$addBannerButton->addClass('success');
		$field = 'banner';

		if ($banner->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'add');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			$_POST['bannerId'] = $banner->bannerId;
			$banner = new \App\Record\Banner($_POST);
			$banner->update();
			$this->page->setResponse('Saved');

			return '';
			}
		elseif (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['add']))
				{
				$fields = $_POST;
				$banner = new \App\Record\Banner();
				$banner->setFrom($fields);
				$id = $banner->insert();
				$this->page->redirect('/Banners/edit/' . $id);
				}
			elseif (isset($_POST['submit']) && $_POST['submit'] == $addBannerButton->getText())
				{
				$fileModel = new \App\Model\BannerFiles();

				if ($fileModel->upload($banner->bannerId, $field, $_FILES))
					{
					$banner->fileNameExt = $fileModel->getExtension();
					$banner->update();
					}
				else
					{
					$error = $fileModel->getLastError();
					\App\Model\Session::setFlash('alert', $error);
					}
				$this->page->redirect();
				}
			}
		else
			{
			$infoFields = new \PHPFUI\FieldSet('Required Information');
			$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $banner->startDate);
			$startDate->setMinDate(\App\Tools\Date::todayString());
			$startDate->setRequired()->setToolTip('The date the banner will start to be displayed');
			$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $banner->endDate);
			$endDate->setMinDate(\App\Tools\Date::todayString());
			$endDate->setRequired()->setToolTip('The last date the banner will be shown.');
			$infoFields->add(new \PHPFUI\MultiColumn($startDate, $endDate));
			$title = new \PHPFUI\Input\Text('description', 'Banner Title', $banner->description);
			$title->setRequired()->setToolTip('The title of the banner, for identifiation purposes only, never shown to users.');
			$infoFields->add($title);
			$url = new \PHPFUI\Input\Text('url', 'Destination URL', $banner->url);
			$url->setRequired()->setToolTip('The page you want to user taken to when they click on the banner. # for current page');
			$infoFields->add($url);
			$pending = new \PHPFUI\Input\RadioGroup('pending', '', (string)$banner->pending);
			$pending->addButton('Approved', (string)0);
			$pending->addButton('Pending', (string)1);
			$infoFields->add($pending);
			$form->add($infoFields);

			if ($banner->fileNameExt && $banner->bannerId)
				{
				$photoSet = new \PHPFUI\FieldSet('Banner');
				$photoSet->add(\App\Model\BannerFiles::getBanner($banner));
				$form->add($photoSet);
				}

			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);

			if ($banner->loaded())
				{
				$form->saveOnClick($addBannerButton);
				$this->uploadImageReveal($addBannerButton, $field);
				$buttonGroup->addButton($addBannerButton);

				if (! $banner->fileNameExt)
					{
					$addHtmlButton = new \PHPFUI\Button('Edit HTML');
					$addHtmlButton->addClass('warning');
					$form->saveOnClick($addHtmlButton);
					$this->uploadHtmlReveal($addHtmlButton, $banner);
					$buttonGroup->addButton($addHtmlButton);
					}
				$testButton = new \PHPFUI\Button('Test', '/Banners/test/' . $banner->bannerId);
				$testButton->addClass('info');
				$buttonGroup->addButton($testButton);
				}

			$form->add($buttonGroup);
			}

		return $form;
		}

	public function listBanners(\App\Table\Banner $bannerTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\count($bannerTable))
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $bannerTable);
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $bannerTable, 'Are you sure you want to permanently delete this banner?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$view->addCustomColumn('description', static fn (array $row) => new \PHPFUI\Link('/Banners/edit/' . $row['bannerId'], $row['description'], false));
			$view->addCustomColumn('pending', static fn (array $row) => $row['pending'] ? new \PHPFUI\FAIcon('fas', 'asterisk') : '');
			$headers = ['startDate', 'endDate', 'description'];
			$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['pending', 'del']))->setSortableColumns($headers);
			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('No Banners Found'));
			}

		return $container;
		}

	public function listBannersByYear(\App\Table\Banner $bannerTable, string $url, int $year = 0) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$firstYear = \App\Tools\Date::todayString();
		$oldest = $bannerTable->getOldest();

		if ($oldest->loaded())
			{
			$firstYear = $oldest->endDate;
			}
		$firstYear = (int)$firstYear;
		$subnav = new \App\UI\YearSubNav("/Banners/{$url}", $year, $firstYear);
		$container->add($subnav);

		if (! $year)
			{
			$year = \App\Tools\Date::formatString('Y');
			}

		$endDate = $year . '-12-31';
		$startDate = $year . '-01-01';
		$condition = $bannerTable->getWhereCondition();
		$condition->and(new \PHPFUI\ORM\Condition('endDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual()));
		$condition->and(new \PHPFUI\ORM\Condition('startDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$bannerTable->setWhere($condition);

		$container->add($this->listBanners($bannerTable));

		return $container;
		}

	public function settings() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$settingTable = new \App\Table\Setting();
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$settingTable->save('bannerWeeklyPrice', $_POST['bannerWeeklyPrice']);
			$settingTable->save('bannerDailyPrice', $_POST['bannerDailyPrice']);
			$this->page->setResponse('Saved');
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Please select a Banner Administrator');
			$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Banner Administrator'));
			$email = $chair->getEditControl();
			$email->setToolTip('This address will be used to email the administrator about pending actions needed.');
			$fieldSet->add($email);
			$container->add($fieldSet);
			$form->add($submit);
			$container->add($form);
			}

		return $container;
		}

	public function test(\App\Record\Banner $banner) : \PHPFUI\Container
		{
		$this->page->turnOffBanner();
		$container = new \PHPFUI\Container();

		if ($banner->loaded())
			{
			$slider = new \PHPFUI\SlickSlider($this->page);
			$slider->addSlide('<a href="' . $banner->url . '">' . \App\Model\BannerFiles::getBanner($banner) . '</a>');
			$container->add($slider);
			$container->add(new \PHPFUI\Header('Test Banner'));
			$container->add(new \App\View\Member\HomePage($this->page, \App\Model\Session::signedInMemberRecord()));
			}
		else
			{
			$container->add(new \PHPFUI\Header('Banner not found'));
			}

		return $container;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteBanner':
						$id = (int)$_POST['bannerId'];
						$banner = new \App\Record\Banner($id);
						$banner->delete();
						$this->page->setResponse((string)$id);

						break;

					case 'Save':
						if (isset($_POST['html']))
							{
							$_POST['html'] = \htmlspecialchars($_POST['html']);
							}
						$banner = new \App\Record\Banner($_POST);
						$banner->update();
						$this->page->redirect();

						break;
					}
				}
			}
		}

	private function uploadHtmlReveal(\PHPFUI\HTML5Element $element, \App\Record\Banner $banner) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $element);
		$submitBanner = new \PHPFUI\Submit('Save', 'action');
		$uploadForm = new \PHPFUI\Form($this->page);
		$uploadForm->add(new \PHPFUI\SubHeader('Edit HTML and CSS'));
		$html = new \PHPFUI\Input\TextArea('html', 'Banner HTML', $banner->html ?? '');
		$uploadForm->add($html);
		$css = new \PHPFUI\Input\TextArea('css', 'Banner CSS', $banner->css ?? '');
		$uploadForm->add($css);
		$uploadForm->add(new \PHPFUI\Input\Hidden('bannerId', (string)$banner->bannerId));
		$uploadForm->add($modal->getButtonAndCancel($submitBanner));
		$modal->add($uploadForm);
		}

	private function uploadImageReveal(\PHPFUI\HTML5Element $element, string $field) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $element);
		$submitBanner = new \PHPFUI\Submit('Upload Banner');
		$uploadForm = new \PHPFUI\Form($this->page);
		$uploadForm->setAreYouSure(false);
		$uploadForm->add(new \PHPFUI\SubHeader('Upload Banner File'));
		$callout = new \PHPFUI\Callout('warning');
		$width = \App\Model\BannerFiles::MIN_WIDTH;
		$proportion = \App\Model\BannerFiles::PROPORTION;
		$callout->add("Banner must be at least {$width} pixels wide and have an aspect ratio of {$proportion}:1.");
		$uploadForm->add($callout);
		$file = new \PHPFUI\Input\File($this->page, $field, 'Select Banner');
		$file->setAllowedExtensions(['png', 'jpg', 'jpeg']);
		$file->setToolTip('Banner should be in the right proportions.');
		$uploadForm->add($file);
		$uploadForm->add($modal->getButtonAndCancel($submitBanner));
		$modal->add($uploadForm);
		}
	}
