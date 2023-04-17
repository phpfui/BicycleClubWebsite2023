<?php

namespace App\View\Member;

class OfMonth
	{
	private readonly \App\Model\MemberOfMonthFile $fileModel;

	private readonly \App\Table\MemberOfMonth $memberOfMonthTable;

	public function __construct(private \PHPFUI\Page $page)
		{
		$this->page = $page;
		$this->memberOfMonthTable = new \App\Table\MemberOfMonth();
		$this->fileModel = new \App\Model\MemberOfMonthFile();
		$this->processRequest();
		}

	public function edit(\App\Record\MemberOfMonth $memberOfMonth) : string | \PHPFUI\Form
		{
		if ($memberOfMonth->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			$_POST['bio'] = \App\Tools\TextHelper::cleanUserHtml($_POST['bio']);
			unset($_POST['memberOfMonthId']);
			$memberOfMonth->setFrom($_POST);
			$memberOfMonth->update();
			$this->page->setResponse('Saved');

			return '';
			}

		if ($memberOfMonth->month < '2000')
			{
			$memberOfMonth->month = \App\Tools\Date::todayString();
			}
		$member = $memberOfMonth->member;

		$fieldSet = new \PHPFUI\FieldSet('Member Info');
		$month = $memberOfMonth->month;
		$model = new \App\Model\MemberPickerNoSave('Member Of The Month');
		$monthPicker = new \PHPFUI\Input\MonthYear($this->page, 'month', 'Member of the Month', $month);
		$monthPicker->setRequired();
		$memberPicker = new \App\UI\MemberPicker($this->page, $model, 'memberId', $member->toArray());
		$multiColumn = new \PHPFUI\MultiColumn();
		$memberEdit = $memberPicker->getEditControl();
		$memberEdit->setRequired();
		$multiColumn->add($memberEdit);
		$multiColumn->add($monthPicker);
		$fieldSet->add($multiColumn);
		$bio = new \PHPFUI\Input\TextArea('bio', 'Write Up', $memberOfMonth->bio);
		$bio->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$bio->setRequired();
		$fieldSet->add($bio);
		$form->add($fieldSet);

		if ($memberOfMonth->loaded())
			{
			$fieldSet = new \PHPFUI\FieldSet('Member Photo');
			$fileName = $this->fileModel->getPath() . $memberOfMonth->memberOfMonthId . $memberOfMonth->fileNameExt;

			if (\file_exists($fileName))
				{
				$row = new \PHPFUI\GridX();
				$row->add($this->fileModel->getImage($memberOfMonth->toArray()));
				$row->add('<br><br>');
				$fieldSet->add($row);
				$buttonText = 'Update Photo';
				}
			else
				{
				$buttonText = 'Add Photo';
				}
			$addPhotoButton = new \PHPFUI\Button($buttonText);
			$fieldSet->add($addPhotoButton);
			$form->saveOnClick($addPhotoButton);
			$modal = new \PHPFUI\Reveal($this->page, $addPhotoButton);
			$submitPhoto = new \PHPFUI\Submit($buttonText);
			$uploadForm = new \PHPFUI\Form($this->page);
			$uploadForm->setAreYouSure(false);
			$uploadForm->add(new \PHPFUI\Input\Hidden('memberOfMonthId', (string)$memberOfMonth->memberOfMonthId));
			$file = new \PHPFUI\Input\File($this->page, 'photo', 'Select Photo');
			$file->setAllowedExtensions(['png', 'jpg', 'jpeg']);
			$file->setToolTip('Photo should be clear and high quality.  It will be sized correctly, so the higher resolution, the better.');
			$uploadForm->add($file);
			$uploadForm->add($modal->getButtonAndCancel($submitPhoto));
			$modal->add($uploadForm);
			$form->add($fieldSet);
			}
		$form->add($submit);

		return $form;
		}

	public function navigate(string $url, int $year = 0, \App\Record\MemberOfMonth $memberOfMonth = new \App\Record\MemberOfMonth()) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$last = $this->memberOfMonthTable->getLatest();
		$first = $this->memberOfMonthTable->getFirst();

		if ($year)
			{
			$latestRange = \App\Tools\Date::makeString($year, 12, 31);
			$firstRange = \App\Tools\Date::makeString($year, 1, 1);

			if ($latestRange > \App\Tools\Date::todayString() && ! $this->page->isAuthorized('Future Member Of The Month'))
				{
				$latestRange = \App\Tools\Date::todayString();
				}

			if ($year == (int)\App\Tools\Date::year(\App\Tools\Date::today()) && ! $memberOfMonth->loaded())
				{
				$memberOfMonth = $this->memberOfMonthTable->current();
				}
			}
		else
			{
			$latestRange = $firstRange = '';
			}

		if ($memberOfMonth->loaded())
			{
			$header = new \PHPFUI\Header('Past Members Of The Month', 5);
			$container->add($this->view($memberOfMonth, $header->getId()));
			$container->add($header);
			}

		if (! $first->empty())
			{
			$firstYear = (int)$first->month;
			$firstYear = \max($firstYear, 2017);
			$lastYear = (int)$last->month;

			if ($firstYear == $lastYear)
				{
				$year = $firstYear;
				}
			$subnav = new \App\UI\SubNav();

			for ($i = $lastYear; $i >= $firstYear; --$i)
				{
				$subnav->addTab("{$url}/{$i}", (string)$i, $i == $year);
				}
			$container->add($subnav);

			if ($latestRange)
				{
				$members = $this->memberOfMonthTable->getRange($firstRange, $latestRange);
				$table = new \PHPFUI\Table();
				$recordIndex = 'memberOfMonthId';
				$table->setRecordId($recordIndex);
				$headers = ['month' => 'Month', 'name' => 'Member'];

				if ($this->page->isAuthorized('Edit Member Of The Month'))
					{
					$headers['edit'] = 'Edit';
					}

				if ($this->page->isAuthorized('Delete Member Of The Month'))
					{
					$headers['del'] = 'Del';
					}
				$table->setHeaders($headers);
				$delete = new \PHPFUI\AJAX('deleteMOM', 'Permanently delete this Member Of The Month?');
				$delete->addFunction('success', "$('#{$recordIndex}-'+data.response).css('background-color','red').hide('fast')");
				$this->page->addJavaScript($delete->getPageJS());

				foreach ($members as $MOM)
					{
					$memberId = $MOM[$recordIndex];
					$year = (int)$MOM['month'];
					$MOM['name'] = "<a href='/Membership/mom/{$year}/{$memberId}'>{$MOM['firstName']} {$MOM['lastName']}</a>";
					$MOM['month'] = \App\Tools\Date::formatString('F Y', $MOM['month']);
					$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$icon->addAttribute('onclick', $delete->execute([$recordIndex => $memberId]));
					$MOM['del'] = $icon;
					$MOM['edit'] = new \PHPFUI\FAIcon('far', 'edit', "/Membership/momEdit/{$memberId}");
					$table->addRow($MOM);
					}
				$container->add($table);
				}
			else
				{
				$container->add(new \PHPFUI\Header('Choose a year above', 3));
				}
			}
		else
			{
			$container->add(new \PHPFUI\Header('No Members Of The Month', 4));
			}

		if ($this->page->isAuthorized('Add Member Of The Month'))
			{
			$container->add(new \PHPFUI\Button('Add New', '/Membership/momEdit/0'));
			}

		return $container;
		}

  public function view(\App\Record\MemberOfMonth $memberOfMonth, string $bottomAnchor) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();

		if ($memberOfMonth->loaded())
			{
			$colA = new \PHPFUI\Cell(12, 6);
			$colB = new \PHPFUI\Cell(12, 6);
			$member = $memberOfMonth->member;
			$colA->add(new \PHPFUI\SubHeader(\App\Tools\Date::formatString('F Y', $memberOfMonth->month)));
			$header = new \PHPFUI\Header($member->fullName(), 1);
			$colA->add($header);
			$colA->add($memberOfMonth->bio);
			$sticky = new \PHPFUI\Sticky($colB);
			$sticky->addTopAnchor($header->getId());
			$sticky->addBottomAnchor($bottomAnchor);
			$sticky->add($this->fileModel->getImage($memberOfMonth->toArray()));
			$colB->add($sticky);
			$row->add($colA);
			$row->add($colB);
			}
		else
			{
			$row->add(new \PHPFUI\SubHeader('Not Found'));
			}

		return $row;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteMOM':

						if ($this->page->isAuthorized('Delete Member Of The Month'))
							{
							$memberOfMonthId = (int)$_POST['memberOfMonthId'];
							$memberOfMonth = new \App\Record\MemberOfMonth($memberOfMonthId);
							$memberOfMonth->delete();
							$this->fileModel->delete($memberOfMonthId);
							$this->page->setResponse($_POST['memberOfMonthId']);
							}

						break;

					}
				}
			elseif (isset($_POST['submit']))
				{
				switch ($_POST['submit'])
					{
					case 'Add':

						$_POST['month'] = \App\Tools\Date::toString(\max(\App\Tools\Date::today(), \App\Tools\Date::fromString($_POST['month'])));
						$memberOfMonth = new \App\Record\MemberOfMonth();
						$memberOfMonth->setFrom($_POST);
						$id = $memberOfMonth->insert();
						$url = $this->page->getBaseURL();
						$pos = \strrpos($url, '/');

						if ($pos > 0)
							{
							$url = \substr($url, 0, $pos + 1);
							}
						$this->page->redirect($url . $id);

						break;

					case 'Update Photo':
					case 'Add Photo':

						$memberOfMonth = new \App\Record\MemberOfMonth((int)$_POST['memberOfMonthId']);

						if ($this->fileModel->upload($memberOfMonth->memberOfMonthId, 'photo', $_FILES))
							{
							$memberOfMonth->fileNameExt = $this->fileModel->getExtension();
							$memberOfMonth->update();
							}
						else
							{
							\App\Model\Session::setFlash('alert', $this->fileModel->getLastError());
							}
						$this->page->redirect();
					}
				}
			}
		}
	}
