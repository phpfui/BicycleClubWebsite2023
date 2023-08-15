<?php

namespace App\View;

class SlideShow
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function editShow(\App\Record\SlideShow $slideShow) : \App\UI\ErrorFormSaver
		{
		if ($slideShow->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $slideShow, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'action');
			$form = new \App\UI\ErrorFormSaver($this->page, $slideShow);
			}

		$fields = $_POST['settings'] ?? [];
		unset($_POST['settings']);

		if ($form->save())
			{
			$slideShow->memberId = \App\Model\Session::signedInMemberId();

			// convert settings to correct type
			foreach ($fields as $name => $value)
				{
				switch ($_POST['type'][$name])
					{
					case 'bool':
						$fields[$name] = (bool)$value;

						break;

					case 'int':
						$fields[$name] = (int)$value;

						break;
					}
				}
			// turn on autoplay and fade if speeds are set
			$fields['autoplay'] = ($fields['autoplaySpeed'] ?? 0) > 0;
			$fields['fade'] = ($fields['speed'] ?? 0) > 0;
			$slideShow->settings = \json_encode($fields, JSON_PRETTY_PRINT);
			$slideShow->update();

			$sequence = 0;

			foreach ($_POST['slideId'] ?? [] as $slideId)
				{
				$slide = new \App\Record\Slide($slideId);

				if ($slide->sequence != ++$sequence)
					{
					$slide->sequence = $sequence;
					$slide->update();
					}
				}

			return $form;
			}
		$fieldSet = new \PHPFUI\FieldSet('Slide Show Details');
		$name = new \PHPFUI\Input\Text('name', 'Slide Show Name', $slideShow->name);
		$name->setRequired();
		$name->setToolTip('You should choose an name that describes the slide show for future reference');
		$fieldSet->add($name);
		$fieldSet->add(new \PHPFUI\Input\Hidden('slideShowId', (string)$slideShow->slideShowId));

		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $slideShow->startDate);
		$endtDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $slideShow->endDate);
		$active = new \PHPFUI\Input\CheckBoxBoolean('active', 'Active', (bool)$slideShow->active);
		$multiColumn = new \PHPFUI\MultiColumn($startDate, $endtDate, $active);
		$fieldSet->add($multiColumn);

		$settings = (array)$slideShow->allSettings();

		$multiColumn = new \PHPFUI\MultiColumn();
		$width = new \PHPFUI\Input\Number('width', 'Width', $slideShow->width);
		$width->setToolTip('Widths from 1 to 100 are percentages, > 100 are in pixels. Zero is full width');
		$multiColumn->add($width);
		$alignment = new \PHPFUI\Input\Select('alignment', 'Alignment');
		$alignments = ['None' => '',
			'Left' => 'align-left',
			'Center' => 'align-center',
			'Right' => 'align-right',
		];

		foreach ($alignments as $label => $value)
			{
			$alignment->addOption($label, $value, $value == $slideShow->alignment);
			}
		$multiColumn->add($alignment);
		$fieldSet->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add($this->makeField($settings, 'slidesToShow', 1, 'Slides to show in row'));
		$multiColumn->add($this->makeField($settings, 'slidesToScroll', 1, 'Slides to scroll at one time'));
		$fieldSet->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add($this->makeField($settings, 'autoplaySpeed', 3000, 'Display Time (1000/sec)'));
		$multiColumn->add($this->makeField($settings, 'speed', 300, 'Fade speed in milliseconds'));
		$fieldSet->add($multiColumn);

		$fieldSet->add($this->makeField($settings, 'arrows', true, 'Enable next and previous arrows'));
		$fieldSet->add($this->makeField($settings, 'dots', true, 'Show dots (one per page)'));
		$fieldSet->add($this->makeField($settings, 'infinite', true, 'Repeat the slide show after the last slide'));
		$fieldSet->add($this->makeField($settings, 'pauseOnFocus', true, 'Pause Autoplay when slide show has focus'));
		$fieldSet->add($this->makeField($settings, 'pauseOnHover', true, 'Pause Autoplay when mouse hovers over slide show'));
		$fieldSet->add($this->makeField($settings, 'pauseOnDotsHover', true, 'Pause Autoplay when mouse hovers over dots'));
		$fieldSet->add($this->makeField($settings, 'swipe', true, 'Allow swiping to change slides'));
		$fieldSet->add($this->makeField($settings, 'swipeToSlide', false, 'Allow users to drag or swipe directly to a slide irrespective of slidesToScroll'));
		$fieldSet->add($this->makeField($settings, 'touchMove', true, 'Enable slide motion with touch'));
		$fieldSet->add($this->makeField($settings, 'variableWidth', false, 'Support variable width slides'));
		$fieldSet->add($this->makeField($settings, 'rtl', false, 'Slide from right to left'));
		$form->add($fieldSet);

		$photoSet = new \PHPFUI\FieldSet('Slides');

		$orderableTable = new \PHPFUI\OrderableTable($this->page);
		$orderableTable->setHeaders(['Caption', 'View', 'Del']);
		$orderableTable->setRecordId($recordIndex = 'slideId');

		$slideTable = new \App\Table\Slide();
		$slideTable->setWhere(new \PHPFUI\ORM\Condition('slideShowId', $slideShow->slideShowId));
		$slideTable->addOrderBy('sequence');
		$table = new \PHPFUI\Table();
		$delete = new \PHPFUI\AJAX('deleteSlide', 'Permanently delete this slide?');
		$delete->addFunction('success', "$('#{$recordIndex}-'+data.response).css('background-color','red').hide('fast').remove();");
		$this->page->addJavaScript($delete->getPageJS());

		foreach ($slideTable->getRecordCursor() as $slide)
			{
			$row = $slide->toArray();
			$row['Caption'] = "<a href='/Content/SlideShow/slide/{$slide->slideShowId}/{$slide->slideId}'>{$slide->caption}</a>";
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordIndex => $slide[$recordIndex]]));
			$container = new \PHPFUI\Container();
			$container->add(new \PHPFUI\Input\Hidden('slideId[]', $slide->slideId));
			$container->add($icon);
			$row['Del'] = $container;
			$view = new \PHPFUI\FAIcon('far', 'eye', '#');
			$reveal = new \PHPFUI\Reveal($this->page, $view);
			$div = new \PHPFUI\HTML5Element('div');
			$reveal->add($div);
			$close = $reveal->getCloseButton('Close');
			$reveal->closeOnClick($close);
			$reveal->add($close);
			$reveal->loadUrlOnOpen('/Content/SlideShow/photo/' . (string)$slide->slideId, $div->getId());
			$row['View'] = $view;
			$orderableTable->addRow($row);
			}
		$photoSet->add($orderableTable);
		$addSlideButton = new \PHPFUI\Button('Add Slide', "/Content/SlideShow/slide/{$slideShow->slideShowId}/0");
		$addSlideButton->addClass('success');
		$photoSet->add($addSlideButton);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);

		if ($slideShow->loaded())
			{
			$form->saveOnClick($addSlideButton);
			$form->add($photoSet);
			$showButton = new \PHPFUI\Button('Show Slide Show', '/Content/SlideShow/show/' . $slideShow->slideShowId . '/debug');
			$showButton->addClass('warning');
			$buttonGroup->addButton($showButton);
			}

		$listButton = new \PHPFUI\Button('All Slide Shows', '/Content/SlideShow/list');
		$listButton->addClass('secondary');

		if ($slideShow->slideShowId)
			{
			$fieldSet = new \PHPFUI\FieldSet('Content');
			$contentLink = new \App\UI\Display('Insertion Text', $this->getInsertionText($slideShow->slideShowId));
			$fieldSet->add($contentLink);
			$form->add($fieldSet);
			}

		$buttonGroup->addButton($listButton);
		$form->add($buttonGroup);

		return $form;
		}

	public function editSlide(\App\Record\SlideShow $slideShow, \App\Record\Slide $slide = new \App\Record\Slide()) : \App\UI\ErrorFormSaver
		{
		$addPhotoButton = new \PHPFUI\Button('Add Photo');
		$addPhotoButton->addClass('success');

	 if ($slide->loaded())
		 {
		 $submit = new \PHPFUI\Submit();
		 $form = new \App\UI\ErrorFormSaver($this->page, $slide, $submit);
		 $form->saveOnClick($addPhotoButton);
		 }
	 else
		 {
		 $submit = new \PHPFUI\Submit('Add Slide', 'action');
		 $submit->addClass('success');
		 $form = new \App\UI\ErrorFormSaver($this->page, $slide);
		 }

	 $slide->slideShowId = $slideShow->slideShowId;

	 if ($form->save())
		 {
		 return $form;
		 }

	 $fieldSet = new \PHPFUI\FieldSet('Slide Details');
	 $caption = new \PHPFUI\Input\Text('caption', 'Slide Show Caption', $slide->caption);
	 $caption->setRequired();
	 $caption->setToolTip("The caption is required to describe the photo, but you don't have to show it.");
	 $fieldSet->add($caption);
	 $fieldSet->add(new \PHPFUI\Input\Hidden('slideShowId', (string)$slideShow->slideShowId));
   $fieldSet->add(new \PHPFUI\Input\CheckBoxBoolean('showCaption', 'Show the caption', (bool)$slide->showCaption));
	 $form->add($fieldSet);

	 $url = new \PHPFUI\Input\Text('url', 'Click URL', $slide->url);
	 $url->setToolTip('User will be taken to this URL if they click on this slide');
	 $fieldSet->add($url);

	 $photoSet = new \PHPFUI\FieldSet('Photo');

	 $modal = new \PHPFUI\Reveal($this->page, $addPhotoButton);
	 $submitPhoto = new \PHPFUI\Submit('Add Photo', 'action');
	 $uploadForm = new \PHPFUI\Form($this->page);
	 $uploadForm->setAreYouSure(false);
	 $slideIdField = new \PHPFUI\Input\Hidden('slideId', (string)$slide->slideId);
	 $uploadForm->add($slideIdField);
	 $photoPicker = new \App\UI\PhotoPicker($this->page, 'photoId', 'Select Photo From Library');
	 $uploadForm->add($photoPicker->getEditControl());
	 $uploadForm->add($this->getFileInput());
	 $uploadForm->add($modal->getButtonAndCancel($submitPhoto));
	 $modal->add($uploadForm);

	 // slide has photo
	 if ($slide->photoId || ! empty($slide->extension))
		 {
		 $deleteSpan = new \PHPFUI\HTML5Element('span');
		 $row = new \PHPFUI\GridX();
		 $imageModel = new \App\Model\SlideImage($slide);
		 $row->add($imageModel->getImg());
		 $deleteSpan->add($row);
		 $photoId = new \PHPFUI\Input\Hidden('photoId', (string)$slide->photoId);
		 $deletePhoto = new \PHPFUI\AJAX('deletePhoto', 'Are you sure you want to delete this photo? You will be able to add a new one after it is deleted.');
		 $deletePhoto->addFunction('success', '$("#' . $deleteSpan->getId() . '").css("background-color","red").hide("fast");$("#' . $addPhotoButton->getId() . '").removeClass("hide").show();$("#' . $photoId->getId() . '").val(0);');
		 $this->page->addJavaScript($deletePhoto->getPageJS());
		 $this->page->addJavaScript('$("#' . $addPhotoButton->getId() . '").hide()');
		 $deleteButton = new \PHPFUI\Button('Delete', '#');
		 $deleteButton->addClass('alert');
		 $deleteButton->addAttribute('onclick', $deletePhoto->execute(['slideId' => $slide->slideId]));
		 $row = new \PHPFUI\GridX();
		 $row->add($deleteButton);
		 $deleteSpan->add($row);
		 $photoSet->add($deleteSpan);
		 $addPhotoButton->addClass('hide');
		 $photoSet->add($addPhotoButton);
		 $photoSet->add($photoId);
		 }
	 // slide exists, but no photo
	 elseif ($slide->loaded())
		 {
		 $photoSet->add($addPhotoButton);
		 }
	 // no slide, so show upload control on this page, not in modal
	 else
		 {
		 $photoSet->add($photoPicker->getEditControl());
		 $photoSet->add($this->getFileInput());
		 }

	 $form->add($photoSet);
   $buttonGroup = new \PHPFUI\ButtonGroup();
   $buttonGroup->addButton($submit);
	 $backButton = new \PHPFUI\Button('Edit Show', '/Content/SlideShow/edit/' . $slideShow->slideShowId);
	 $backButton->addClass('secondary');
	 $buttonGroup->addButton($backButton);
	 $form->add($buttonGroup);

	 return $form;
	 }

	public function getInsertionText(?int $slideShowId = 0) : string
		{
		$start = '~SlideShow-';

		if ($slideShowId)
			{
			return $start . $slideShowId . '~';
			}

		return $start;
		}

	public function list(\App\Table\SlideShow $slideShowTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\count($slideShowTable))
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $slideShowTable);
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $slideShowTable, 'Permanently delete this slide show and all it\'s photos?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$view->addCustomColumn('name', static fn (array $row) => new \PHPFUI\Link('/Content/SlideShow/edit/' . $row['slideShowId'], $row['name'], false));
			$view->addCustomColumn('active', static fn (array $row) => $row['active'] ? '&check;' : '');
			$view->addCustomColumn('Author', static function(array $row) {$member = new \App\Record\Member($row['memberId']);

return $member->fullName();});

			$headers = ['name', 'startDate', 'endDate', 'active'];
			$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['Author', 'del']))->setSortableColumns($headers);
			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('No Slide Shows Found'));
			}

		return $container;
		}

	public function show(\App\Record\SlideShow $slideShow, bool $test = true, bool $debug = false) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $slideShow->active)
			{
			if ($debug)
				{
				$container->add('Slide Show is not active<br>');
				}

			if ($test)
				{
				return $container;
				}
			}

		$today = \App\Tools\Date::todayString();

		if ($slideShow->endDate && $slideShow->endDate < $today)
			{
			if ($debug)
				{
				$container->add('Slide Show ended on ' . $slideShow->endDate . '<br>');
				}

			if ($test)
				{
				return $container;
				}
			}

		if ($slideShow->startDate && $slideShow->startDate > $today)
			{
			if ($debug)
				{
				$container->add('Slide Show does not start until ' . $slideShow->startDate . '<br>');
				}

			if ($test)
				{
				return $container;
				}
			}

		$slider = new \PHPFUI\SlickSlider($this->page);

		if ($slideShow->width > 100)
			{
			$slider->addAttribute('style', "width:{$slideShow->width}px");
			}
		elseif ($slideShow->width > 0)
			{
			$slider->addAttribute('style', "width:{$slideShow->width}%");
			}
		$model = new \App\Model\SlideImage(new \App\Record\Slide());

		foreach ($slideShow->SlideChildren as $slide)
			{
			$model->update($slide->toArray());
			$caption = '';

			if ($slide->showCaption && $slide->caption)
				{
				$caption = "<div>{$slide->caption}</div>";
				}
			$content = $model->getImg();

			if ($slide->url)
				{
				$content = new \PHPFUI\Link($slide->url, $content);
				}
			$slider->addSlide($content . $caption);
			}

		foreach ($slideShow->allSettings() as $name => $value)
			{
			$slider->addSliderAttribute($name, $value);
			}

		$gridx = new \PHPFUI\GridX();
		$gridx->setMargin();

		if ($slideShow->alignment)
			{
			$gridx->addClass($slideShow->alignment);
			}
		$slider->addClass('cell');
		$gridx->add($slider);

		$container->add($gridx);

		return $container;
		}

	private function getFileInput() : \PHPFUI\Input\File
		{
		$file = new \PHPFUI\Input\File($this->page, 'photo', 'Upload Photo');
		$file->setAllowedExtensions(['png', 'jpg', 'jpeg', 'webp']);
		$file->setToolTip('Photo should be clear and high quality.  It will be sized correctly, so the higher resolution, the better.');

		return $file;
		}

	private function makeField(array $settings, string $name, string | int | bool $default, string $description) : string
		{
		$type = \get_debug_type($default);

		if (! \array_key_exists($name, $settings))
			{
			$value = $default;
			}
		else
			{
			$value = $settings[$name];
			}
		$hidden = new \PHPFUI\Input\Hidden("type[{$name}]", $type);
		$name = "settings[{$name}]";

		$inputField = 'Unrecognized type: ' . $type;

		if ('bool' == $type)
			{
			$inputField = new \PHPFUI\Input\CheckBoxBoolean($name, $description, $value);
			}
		elseif ('int' == $type)
			{
			$inputField = new \PHPFUI\Input\Number($name, $description, $value);
			}
		elseif ('string' == $type)
			{
			$inputField = new \PHPFUI\Input\Text($name, $description, $value);
			}

		return $hidden . $inputField;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'Add':
						unset($_POST['slideShowId']);
						$_POST['settings'] = \json_encode($_POST['settings'], JSON_THROW_ON_ERROR);
						$_POST['memberId'] = \App\Model\Session::signedInMemberId();
						$slideShow = new \App\Record\SlideShow();
						$slideShow->setFrom($_POST);
						$id = $slideShow->insert();
						$this->page->redirect("/Content/SlideShow/edit/{$id}");

						break;

					case 'Add Slide':
						unset($_POST['slideId']);
						$slide = new \App\Record\Slide();
						$slide->setFrom($_POST);
						$id = $slide->insert();
						$slide->reload();

						$imageModel = new \App\Model\SlideImage($slide);

						if (! $slide->photoId)
							{
							if ($imageModel->upload($slide->slideId, 'photo', $_FILES))
								{
								$slide->extension = $imageModel->getExtension();
								$slide->memberId = \App\Model\Session::signedInMemberId();
								$slide->update();
								$imageModel->update($slide->toArray());
								$imageModel->createThumb(250);
								}
							else
								{
								\App\Model\Session::setFlash('alert', $imageModel->getLastError());
								}
							}

						$this->page->redirect("/Content/SlideShow/edit/{$_POST['slideShowId']}");

						break;

					case 'deleteSlide':
						$slide = new \App\Record\Slide((int)$_POST['slideId']);
						$slide->delete();
						$this->page->setResponse($_POST['slideId']);

						break;


					case 'deletePhoto':
						$slide = new \App\Record\Slide($_POST['slideId']);
						$model = new \App\Model\SlideImage($slide);
						$model->delete($slide->slideId);
						$slide->extension = '';
						$slide->update();
						$this->page->setResponse($_POST['slideId']);

						break;

					case 'Add Photo':
						$slide = new \App\Record\Slide($_POST['slideId']);
						$slide->photoId = ((int)$_POST['photoId']) ?: null;

						if ($slide->loaded())
							{
							if ($slide->photoId)
								{
								$slide->update();
								}
							else
								{
								$imageModel = new \App\Model\SlideImage($slide);

								if ($imageModel->upload($slide->slideId, 'photo', $_FILES))
									{
									$slide->extension = $imageModel->getExtension();
									$slide->memberId = \App\Model\Session::signedInMemberId();
									$slide->update();
									$imageModel->update($slide->toArray());
									$imageModel->createThumb(250);
									}
								}
							}
						$this->page->redirect();

						break;
					}
				}
			}
		}
	}
