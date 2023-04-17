<?php

namespace App\View;

class CueSheet
	{
	private readonly \App\Table\CueSheet $cueSheetTable;

	private readonly \App\Table\CueSheetVersion $cueSheetVersionTable;

	private ?\PHPFUI\AJAX $deleteVersion = null;

	private readonly \App\Model\CueSheetFiles $fileModel;

	private readonly \App\Model\CueSheet $model;

	private readonly \App\View\StartLocation $startLocationView;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->cueSheetTable = new \App\Table\CueSheet();
		$this->cueSheetVersionTable = new \App\Table\CueSheetVersion();
		$this->startLocationView = new \App\View\StartLocation($page);
		$this->fileModel = new \App\Model\CueSheetFiles();
		$this->model = new \App\Model\CueSheet();
		$this->processRequest();
		}

	public function edit(\App\Record\CueSheet $cuesheet) : string
		{
		if ($cuesheet->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			$id = $cuesheet->cueSheetId;
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add Cue Sheet', 'add');
			$form = new \PHPFUI\Form($this->page);
			$id = 0;
			}

		if ($form->isMyCallback())
			{
			$fields = $_POST;

			foreach ($cuesheet->toArray() as $key => $value)
				{
				if (isset($fields[$key]) && $fields[$key] != $value && ! empty($value) && ! empty($fields[$key]))
					{
					$cuesheet->revisionDate = \App\Tools\Date::todayString();

					break;
					}
				}

			if (isset($_POST['approve']))
				{
				if ($_POST['approve'])
					{
					$cuesheet->pending = 0;
					$this->model->approve($cuesheet, $this);
					}
				}
			$cuesheet->setFrom($_POST);
			$cuesheet->update();
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['add']))
				{
				$fields = $_POST;
				unset($fields['cueSheetId']);
				$cuesheet->setEmpty();
				$cuesheet->setFrom($fields);

				if (empty($cuesheet->memberId))
					{
					$cuesheet->memberId = \App\Model\Session::signedInMemberId();
					}
				$cuesheet->revisionDate = $cuesheet->dateAdded = \App\Tools\Date::todayString();
				$cuesheet->pending = 1;
				$id = $cuesheet->insert();
				$this->page->redirect('/CueSheets/edit/' . $id);
				}
			elseif (isset($_POST['action']) && 'Add' == $_POST['action'])
				{
				$author = \App\Model\Session::signedInMemberId();
				$cueSheetVersion = new \App\Record\CueSheetVersion();
				$cueSheetVersion->cueSheetId = $id;
				$cueSheetVersion->memberId = \App\Model\Session::signedInMemberId();
				$cueSheetVersion->dateAdded = \App\Tools\Date::todayString();
				$cueSheetVersion->link = $_POST['link'];
				$versionId = $cueSheetVersion->insert();

				if ($this->fileModel->upload((string)$versionId, 'userfile', $_FILES) || ! empty($_POST['link']))
					{
					if ($cueSheetVersion->extension = $this->fileModel->getExtension())
						{
						$cueSheetVersion->update();
						}
					$this->page->redirect();
					}
				else  // had an error, delete it
					{
					\App\Model\Session::setFlash('alert', '<b>Error uploading cuesheet:</b> ' . $this->fileModel->getLastError());
					$cueSheetVersion->delete();
					}
				$this->page->redirect();
				}
			}

		if ($id)
			{
			$fieldSet = new \PHPFUI\FieldSet('Cue Sheet Information');
			$fieldSet->add(new \App\UI\Display('Number', $id));
			$fieldSet->add(new \App\UI\Display('Last Revision', \App\Tools\Date::formatString('l, F j, Y', $cuesheet->revisionDate)));
			$member = $cuesheet->member;
			$fieldSet->add(new \App\UI\Display('Original Author', $member->fullName()));

			if ($cuesheet->pending)
				{
				$fieldSet->add(new \App\UI\Display('Status', 'Pending'));
				}
			$form->add($fieldSet);
			}
		else
			{
			$member = new \App\Record\Member();
			}
		$fieldSet = new \PHPFUI\FieldSet('Cue Sheet Data');

		if ($this->page->isAuthorized('Change Cue Sheet Author'))
			{
			$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Cue Sheet Author'), 'memberId', $member->toArray());
			$fieldSet->add($memberPicker->getEditControl());
			}
		$name = new \PHPFUI\Input\Text('name', 'Cue Sheet Name', $cuesheet->name);
		$name->setRequired();
		$fieldSet->add($name);
		$startLocation = $this->startLocationView->getEditControl($cuesheet->startLocationId);
		$fieldSet->add($startLocation);
		$destination = new \PHPFUI\Input\Text('destination', 'Destination', $cuesheet->destination);
		$fieldSet->add($destination);
		$description = new \PHPFUI\Input\TextArea('description', 'Description', $cuesheet->description);
		$fieldSet->add($description);

		if (empty($cuesheet->mileage))
			{
			$cuesheet->mileage = 0.0;
			}
		$mileage = new \PHPFUI\Input\Number('mileage', 'Mileage', $cuesheet->mileage);
		$mileage->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$mileage->setRequired();
		$terrain = $this->getTerrainEditControl($cuesheet->terrain ?? 0);
		$terrain->setRequired();

		if (empty($cuesheet->elevation))
			{
			$cuesheet->elevation = 0;
			}
		$elevation = new \PHPFUI\Input\Number('elevation', 'Elevation Gain (feet)', $cuesheet->elevation);
		$fieldSet->add(new \PHPFUI\MultiColumn($mileage, $terrain, $elevation));
		$form->add($fieldSet);
		$this->cueSheetVersionTable->setDateDescCursor($cuesheet);

		if ($this->cueSheetVersionTable->count())
			{
			$fieldSet = new \PHPFUI\FieldSet('Revisions');
			$fieldSet->add($this->getRevisions($cuesheet, $this->cueSheetVersionTable));
			$form->add($fieldSet);
			}
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$add = new \PHPFUI\Button('Add File / Link');
		$add->addClass('warning');

		if (! $id)
			{
			$add->setDisabled();
			}
		else
			{
			$form->saveOnClick($add);
			$this->getFileUploadModal($add);
			}
		$buttonGroup->addButton($add);

		if ($id && $cuesheet->pending && $this->page->isAuthorized('Approve Cue Sheets'))
			{
			$approve = new \PHPFUI\Button('Approve', "/CueSheets/approve/{$id}");
			$approve->addClass('success');
			$buttonGroup->addButton($approve);
			$deny = new \PHPFUI\Button('Deny', "/CueSheets/deny/{$id}");
			$deny->addClass('alert');
			$buttonGroup->addButton($deny);
			}
		$form->add($buttonGroup);

		return $form;
		}

	public function getEditControl(string $name, string $title, int $startLocation, int $cueSheetId = 0) : \PHPFUI\Input\Select
		{
		$cuesheets = $this->cueSheetTable->getCueSheetsForLocation($startLocation);
		$select = new \PHPFUI\Input\Select($name, $title);

		if (\count($cuesheets))
			{
			$select->addOption('Choose a cue sheet ...', (string)0, 0 == $cueSheetId);

			foreach ($cuesheets as $cuesheet)
				{
				if (empty($cuesheet->pending))
					{
					$select->addOption('#' . $cuesheet->cueSheetId . ', ' . $cuesheet->mileage . ' miles, ' . $cuesheet->name, $cuesheet->cueSheetId, $cueSheetId == $cuesheet->cueSheetId);
					}
				}
			}
		else
			{
			$location = new \App\Record\StartLocation($startLocation);
			$select->addOption('There are no cue sheets for ' . $location->name, (string)0);
			}
		$select->setToolTip('Cue sheets are only available for matching ride start locations.  If you don\'t see a cue sheet here, you may not have the right start location');

		return $select;
		}

	public function getIconLink(\App\Record\CueSheetVersion $revision) : string
		{
		$ext = \str_replace('.', '', $revision->extension ?? '');

		if (! $ext)
			{
			return '';
			}

		return "<a href='{$this->getRevisionUrl($revision->cueSheetVersionId)}'><img class='image-icon' alt='{$ext}' src='/images/icons/{$ext}.png'></a>";
		}

	public function getLinkIcon(\App\Record\CueSheet $cueSheet) : string
		{
		return new \PHPFUI\FAIcon('fas', 'eye', $this->getUrl($cueSheet));
		}

	public function getRevisionUrl(int $versionId) : string
		{
		return "/CueSheets/downloadRevision/{$versionId}";
		}

	public function getTerrainEditControl(int $value, string $name = 'terrain', string $label = 'Terrain', bool $multiselect = false) : \PHPFUI\Input\Select
		{
		if ($multiselect)
			{
			$select = new \PHPFUI\Input\MultiSelect($name, $label);
			}
		else
			{
			$select = new \PHPFUI\Input\Select($name, $label);
			}

		$cueSheet = new \App\Record\CueSheet();

		foreach ($cueSheet->allTerrains() as $key => $label)
			{
			if (! empty($label))
				{
				$select->addOption($label, $key, $key == $value);
				}
			}

		return $select;
		}

	public function getUrl(\App\Record\CueSheet $cueSheet) : string
		{
		return "/CueSheets/download/{$cueSheet->cueSheetId}";
		}

	public function Merge() : string | \PHPFUI\Form
		{
		$error = $form = '';

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['keep']))
				{
				$count = 0;

				for ($i = 0; $i < $_POST['count']; ++$i)
					{
					if (! empty($_POST['delete' . $i]))
						{
						$this->model->merge($_POST['number' . $i], $_POST['keep']);
						++$count;
						}
					}
				}

			if (isset($count))
				{
				if ($count > 1)
					{
					$message = $count . ' cue sheets merged';
					}
				elseif ($count)
					{
					$message = '1 cue sheet merged';
					}
				else
					{
					$message = 'No cue sheets were merged';
					}
				\App\Model\Session::setFlash('success', $message);
				$this->page->redirect();
				}
			}

		if (isset($_GET['name']))
			{
			$search = '%' . $_GET['name'] . '%';
			$condition = new \PHPFUI\ORM\Condition('name', $search, new \PHPFUI\ORM\Operator\Like());
			$condition->or('description', $search, new \PHPFUI\ORM\Operator\Like());
			$this->cueSheetTable->setWhere($condition);
			$this->cueSheetTable->addOrderBy('mileage');
			$found = $this->cueSheetTable->count();

			if ($found > 1)
				{
				$form = new \PHPFUI\Form($this->page);
				$fieldSet = new \PHPFUI\FieldSet('Instructions');
				$fieldSet->add('<ul>');
				$fieldSet->add('<li>Check the <b>Master</b> cue sheet.  This is the cue sheet you want to keep.</li>');
				$fieldSet->add('<li>All rides will now have this cue sheet.</li>');
				$fieldSet->add('<li>Check <b>Delete</b> on the duplicates you want to delete.</li>');
				$fieldSet->add('<li>All attachments and links on deleted cue sheets will be attached to the <b>master cue sheet</b> you are keeping.</li>');
				$fieldSet->add('<li>Don\'t check <b>Delete</b> on unrelated cue sheets and they will be left alone.</li>');
				$fieldSet->add('</ul>');
				$form->add($fieldSet);

				$table = new \PHPFUI\Table();
				$table->setHeaders(['master' => 'Master',
					'delete' => 'Delete',
					'cueSheetId' => 'Cue Sheet Number',
					'mileage' => 'Mileage',
					'name' => 'Cue Sheet',
					'view' => 'View', ]);
				$count = 0;
				$displayed = [];

				foreach ($this->cueSheetTable->getRecordCursor() as $cuesheetRecord)
					{
					if (! isset($displayed[$cuesheetRecord->cueSheetId]))
						{
						$cuesheet = $cuesheetRecord->toArray();
						$displayed[$cuesheetRecord->cueSheetId] = true;
						$number = $cuesheetRecord->cueSheetId;
						$hidden = new \PHPFUI\Input\Hidden('number' . $count, $number);
						$cuesheet['master'] = "<input type='radio' name='keep' value='{$number}'>" . $hidden;
						$cuesheet['delete'] = new \PHPFUI\Input\CheckBoxBoolean('delete' . $count);
						$cuesheet['view'] = $this->getLinkIcon($cuesheetRecord);
						++$count;
						$table->addRow($cuesheet);
						}
					}
				$form->add($table);
				$form->add(new \PHPFUI\Input\Hidden('count', (string)$count));
				$buttonGroup = new \App\UI\CancelButtonGroup();
				$buttonGroup->addButton(new \PHPFUI\Submit('Merge Cue Sheets'));
				$another = new \PHPFUI\Button('Merge Another Cue Sheet', '/CueSheets/merge');
				$another->addClass('secondary');
				$buttonGroup->addButton($another);
				$form->add($buttonGroup);
				unset($error);
				}
			elseif (1 == $found)
				{
				$error = "Only one match found for {$_GET['name']}, nothing to merge.";
				}
			else
				{
				$error = "No matches found for {$_GET['name']}";
				}
			}

		if (isset($error))
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$form->setAttribute('method', 'get');

			if (! empty($error))
				{
				$alert = new \App\UI\Alert($error);
				$alert->addClass('warning');
				$form->add($alert);
				}
			$fieldSet = new \PHPFUI\FieldSet('Enter some text in the name or description of a cue sheet you want to merge');
			$fieldSet->add(new \PHPFUI\Input\Text('name', 'Cue Sheet'));
			$form->add($fieldSet);
			$form->add(new \PHPFUI\Submit('Find Cue Sheets'));
			}

		return $form;
		}

	public function reject(\App\Record\CueSheet $cuesheet) : string
		{
		$form = new \PHPFUI\Form($this->page);
		$message = new \PHPFUI\Input\TextArea('message', 'Details on why the cue sheet was rejected.');
		$message->setToolTip('Add any specific comments to the submitter here.  This will be added to the standard boilerplate for rejected cuesheets.');
		$message->setRequired();
		$form->add($message);
		$form->add(new \PHPFUI\Input\Hidden('cueSheetId', (string)$cuesheet->cueSheetId));
		$row = new \PHPFUI\ButtonGroup();
		$row->addButton(new \PHPFUI\Submit('Reject Cuesheet', 'action'));
		$cancel = new \PHPFUI\Button('Cancel', '/CueSheets/pending');
		$cancel->addClass('hollow')->addClass('alert');
		$row->addButton($cancel);
		$form->add($row);

		return $form;
		}

	public function show(\App\Table\CueSheet $cuesheetTable, string $noSheets = 'No cue sheets found') : string
		{
		if (! \count($cuesheetTable))
			{
			return "<h3>{$noSheets}</h3>";
			}
		$columnWidths = [3, 1, 3, 2, 2, 1, ];
		\reset($columnWidths);
		$row = new \PHPFUI\GridX();
		$name = new \PHPFUI\Cell(\current($columnWidths));
		\next($columnWidths);
		$name->add('Title');
		$row->add($name);
		$mileage = new \PHPFUI\Cell(\current($columnWidths));
		\next($columnWidths);
		$mileage->add('Mile<wbr>age');
		$row->add($mileage);
		$start = new \PHPFUI\Cell(\current($columnWidths));
		\next($columnWidths);
		$start->add('Start Location');
		$row->add($start);
		$destination = new \PHPFUI\Cell(\current($columnWidths));
		\next($columnWidths);
		$destination->add('Destin<wbr>ation');
		$row->add($destination);
		$leader = new \PHPFUI\Cell(\current($columnWidths));
		\next($columnWidths);
		$leader->add('Author');
		$row->add($leader);
		$date = new \PHPFUI\Cell(\current($columnWidths));
		\next($columnWidths);
		$date->add('Revised');
		$row->add($date);
		$header = $row;
		$delete = new \PHPFUI\AJAX('deleteCueSheet', 'Permanently delete this cue sheet?');
		$delete->addFunction('success', '$("#"+data.response).css("background-color","red").hide("fast")');
		$this->page->addJavaScript($delete->getPageJS());
		$accordion = new \App\UI\Accordion();

		foreach ($cuesheetTable->getRecordCursor() as $cuesheet)
			{
			\reset($columnWidths);
			$row = new \PHPFUI\GridX();
			$name = new \PHPFUI\Cell(\current($columnWidths));
			\next($columnWidths);
			$name->add($cuesheet->name);
			$row->add($name);
			$mileage = new \PHPFUI\Cell(\current($columnWidths));
			\next($columnWidths);
			$mileage->add($cuesheet->mileage);
			$row->add($mileage);
			$start = new \PHPFUI\Cell(\current($columnWidths));
			\next($columnWidths);
			$start->add($cuesheet->startLocation->name);
			$row->add($start);
			$destination = new \PHPFUI\Cell(\current($columnWidths));
			\next($columnWidths);
			$destination->add($cuesheet->destination);
			$row->add($destination);
			$leader = new \PHPFUI\Cell(\current($columnWidths));
			\next($columnWidths);
			$leader->add($cuesheet->member->fullName());
			$row->add($leader);
			$date = new \PHPFUI\Cell(\current($columnWidths));
			\next($columnWidths);
			$date->add(\App\Tools\Date::formatString('n/j/Y', $cuesheet->revisionDate));
			$row->add($date);
			$detail = new \PHPFUI\HTML5Element('div');
			$detail->add($cuesheet->description);
			$detail->add(new \App\UI\Display('Cue Sheet Number:', $cuesheet->cueSheetId));
			$detail->add(new \App\UI\Display('Terrain:', $cuesheet->terrain()));

			if ($cuesheet->elevation)
				{
				$detail->add(new \App\UI\Display('Elevation:', $cuesheet->elevation . ' feet'));
				}

			if ($cuesheet->startLocationId)
				{
				$detail->add(new \App\UI\Display('Start Location:', $this->startLocationView->getText($cuesheet->startLocationId)));
				}
			$detail->add(new \App\UI\Display('Added:', \App\Tools\Date::formatString('n/j/Y', $cuesheet->dateAdded)));

			if ($cuesheet->pending)
				{
				$detail->add(new \App\UI\Display('Status:', 'Pending'));
				}

			$rideTable = new \App\Table\Ride();
			$rideTable->setRidesForCueSheetCursor($cuesheet);
			$rideCount = \count($rideTable);

			if ($rideCount)
				{
				$led = 'This cue sheet has been led ' . $rideCount . ' times and most recently on ' . $rideTable->getRecordCursor()->current()->rideDate;
				$led = "<a href='/Rides/cueSheet/{$cuesheet->cueSheetId}'>{$led}</a>";
				}
			else
				{
				$led = 'This cuesheet has never been used for a ride';
				}
			$detail->add($led);
			$bg = new \App\UI\CancelButtonGroup();

			if ($this->page->isAuthorized('Edit Cue Sheet') || $cuesheet->memberId == \App\Model\Session::signedInMemberId())
				{
				$button = new \PHPFUI\Button('Edit', '/CueSheets/edit/' . $cuesheet->cueSheetId);
				$bg->addButton($button);
				}
			$this->cueSheetVersionTable->setDateDescCursor($cuesheet);

			$tab = $accordion->getTab($row);

			if (! $this->cueSheetVersionTable->count() && ! \App\Table\Ride::getCueSheetRideCount($cuesheet) && ($this->page->isAuthorized('Delete Cue Sheet') || $cuesheet->memberId == \App\Model\Session::signedInMemberId()))
				{
				$deleteButton = new \PHPFUI\Button('Del', '#');
				$deleteButton->addClass('alert');
				$deleteButton->addAttribute('onclick', $delete->execute(['cueSheetId' => $cuesheet->cueSheetId,
					'tab' => '"' . $tab->getId() . '"', ]));
				$bg->addButton($deleteButton);
				}
			$detail->add($bg);
			$detail->add($this->getRevisions($cuesheet, $this->cueSheetVersionTable));
			$tab->addContent($detail);
			$accordion->addItem($tab);
			}

		return $header . $accordion;
		}

	public function stats(int | string $year) : string
		{
		$stats = \App\Table\Ride::getCuesheetStats($year);
		$count = \count($stats) ? $stats->current()->count : 0;
		$total = 0;

		foreach ($stats as $row)
			{
			$total += $row['count'];
			}
		$accordion = new \App\UI\Accordion();
		$headers = ['cuesheetname' => 'Cue Sheet',
			'startLocation' => 'Start Location',
			'percent' => 'Percent', ];

		$table = null;

		foreach ($stats as $record)
			{
			$row = $record->toArray();

			if ($row['count'] != $count)
				{
				/** @noinspection PhpUndefinedVariableInspection */
				$accordion->addTab("Cue Sheets Led {$count} time" . ($count > 1 ? 's' : '') . " in {$year}", $table);
				unset($table);
				$count = $row['count'];
				}

			if (! isset($table))
				{
				$table = new \PHPFUI\Table();
				$table->setHeaders($headers)->displayHeaders(false)->addAttribute('width', '100%');
				}
			$row['percent'] = \number_format($row['count'] * 100 / $total, 1) . '%';
			$row['startLocation'] = \App\View\StartLocation::getTextFromArray($row);
			$row['cuesheetname'] = "<a href='/CueSheets/view/{$row['cueSheetId']}'>{$row['cuesheetname']}</a>";
			$table->addRow($row);
			}
		/** @noinspection PhpUndefinedVariableInspection */
		$accordion->addTab("Cue Sheets Led {$count} time" . ($count > 1 ? 's' : '') . " in {$year}", $table ?? new \PHPFUI\Table());

		return $accordion;
		}

	protected function getLinkInput(string $value = '') : \PHPFUI\Input\Url
		{
		$url = new \PHPFUI\Input\Url('link', 'Link to Route on Ride Mapping Site', $value);
		$url->setToolTip('Please keeps links related to the ride');

		return $url;
		}

	protected function getRevisions(\App\Record\CueSheet $cuesheet, \App\Table\CueSheetVersion $revisions) : \App\UI\Accordion
		{
		$delete = 0;

		if ($this->page->isAuthorized('Delete Cue Sheet Version') || $cuesheet->member->memberId == \App\Model\Session::signedInMemberId())
			{
			if (! $this->deleteVersion)
				{
				$this->deleteVersion = new \PHPFUI\AJAX('deleteVersion', 'Permanently delete this revision and associated file?');
				$this->deleteVersion->addFunction('success', '$("#"+data.response).css("background-color","red").hide("fast")');
				$this->page->addJavaScript($this->deleteVersion->getPageJS());
				}
			$delete = $this->deleteVersion;
			}
		$canEdit = $this->page->isAuthorized('Edit Cue Sheet Version') || $cuesheet->memberId == \App\Model\Session::signedInMemberId();
		$revisionAccordion = new \App\UI\Accordion();

		foreach ($revisions->getRecordCursor() as $revision)
			{
			$revisionRow = new \PHPFUI\GridX();
			$ext = new \PHPFUI\Cell(3);

			$parts = [];

			if ($revision->link)
				{
				$parts[] = 'Link';
				}

			if ($revision->extension)
				{
				$parts[] = $revision->extension;
				}
			$ext->add(\implode(' &amp; ', $parts));
			$revisionRow->add($ext);
			$name = new \PHPFUI\Cell(8);
			$name->add($revision->member->fullName());
			$revisionRow->add($name);
			$date = new \PHPFUI\Cell(1);
			$date->add($revision->dateAdded);
			$revisionRow->add($date);
			$revisionDetail = new \PHPFUI\GridX();
			$downloadLink = new \PHPFUI\Cell(3);
			$downloadLink->add($this->getIconLink($revision));
			$revisionDetail->add($downloadLink);
			$htmlLink = new \PHPFUI\Cell(7);

			if ($revision->link)
				{
				$htmlLink->add(new \PHPFUI\Link($revision->link));
				}
			$revisionDetail->add($htmlLink);

			if ($revision->link && ($canEdit || $revision->memberId == \App\Model\Session::signedInMemberId()))
				{
				$editColumn = new \PHPFUI\Cell(1);
				$editIcon = new \PHPFUI\FAIcon('far', 'edit', '#');
				$this->getEditLinkModal($editIcon, $revision, $cuesheet->member);
				$editColumn->add($editIcon);
				$revisionDetail->add($editColumn);
				}
			$tab = $revisionAccordion->getTab($revisionRow);

			if ($delete)
				{
				$deleteIcon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$deleteIcon->addAttribute('onclick', $delete->execute(['cueSheetVersionId' => $revision->cueSheetVersionId,
					'tab' => '"' . $tab->getId() . '"', ]));
				$deleteColumn = new \PHPFUI\Cell(1);
				$deleteColumn->add($deleteIcon);
				$revisionDetail->add($deleteColumn);
				}
			$tab->addContent($revisionDetail);
			$revisionAccordion->addItem($tab);
			}

		return $revisionAccordion;
		}

	private function getEditLinkModal(\PHPFUI\HTML5Element $modalLink, \App\Record\CueSheetVersion $version, \App\Record\Member $author) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('cueSheetId', (string)$version->cueSheetId));
		$form->add(new \PHPFUI\Input\Hidden('cueSheetVersionId', (string)$version->cueSheetVersionId));

		if ($this->page->isAuthorized('Change Cue Sheet Author'))
			{
			$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Cue Sheet Author'), 'memberId', $author->toArray());
			$form->add($memberPicker->getEditControl());
			}
		$form->add($this->getLinkInput($version->link));
		$submit = new \PHPFUI\Submit('Save Link');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getFileUploadModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('File To Upload');

		if ($this->page->isAuthorized('Change Cue Sheet Author'))
			{
			$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Cue Sheet Author'), 'memberId');
			$fieldSet->add($memberPicker->getEditControl());
			}
		$filesize = new \PHPFUI\Input\Hidden('MAX_FILE_SIZE', (string)2_000_000);
		$fieldSet->add($filesize);
		$file = new \PHPFUI\Input\File($this->page, 'userfile', 'File');
		$extensions = [];

		foreach ($this->fileModel->getMimeTypes() as $ext => $mime)
			{
			$extensions[] = \str_replace('.', '', $ext);
			}
		$file->setAllowedExtensions($extensions);
		$fieldSet->add($file);
		$this->fileModel->getMimeTypes();
		$fieldSet->add(new \App\UI\Display('Allowed Types', \implode(' ', \array_keys($this->fileModel->getMimeTypes()))));
		$form->add($fieldSet);
		$form->add($this->getLinkInput());
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteCueSheet':
						$this->model->delete($_POST['cueSheetId']);
						$this->page->setResponse($_POST['tab']);

						break;

					case 'deleteVersion':
						$cueSheetVersion = new \App\Record\CueSheetVersion((int)$_POST['cueSheetVersionId']);
						$cueSheetVersion->delete();
						$this->fileModel->delete($_POST['cueSheetVersionId']);
						$this->page->setResponse($_POST['tab']);

						break;

					case 'Reject Cuesheet':
						$cuesheet = new \App\Record\CueSheet((int)$_POST['cueSheetId']);
						\App\Model\Session::setFlash('success', "Cue Sheet {$cuesheet->name} has been rejected");
						$this->model->reject($cuesheet, $_POST['message']);
						$this->page->redirect('/CueSheets/pending');

						break;
					}
				}
			elseif ('Save Link' == ($_POST['submit'] ?? ''))
				{
				$cueSheetVersion = new \App\Record\CueSheetVersion($_POST);
				$cueSheetVersion->insertOrUpdate();
				$this->page->redirect();
				}
			}
		}
	}
