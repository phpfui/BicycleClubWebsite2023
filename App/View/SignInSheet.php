<?php

namespace App\View;

class SignInSheet
	{
	private readonly \App\Model\SignInSheet $model;

	private readonly \App\Table\SigninSheetRide $signinSheetRideTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->signinSheetRideTable = new \App\Table\SigninSheetRide();
		$this->model = new \App\Model\SignInSheet();
		$this->processRequest();
		}

	public function Edit(\App\Record\SigninSheet $signinSheet) : string
		{
		$submit = new \PHPFUI\Submit('Save');
		$form = new \App\UI\ErrorFormSaver($this->page, $signinSheet, $submit);

		if ($form->save())
			{
			return '';
			}

		$fieldSet = new \PHPFUI\FieldSet('Sign In Sheet Information');
		$fieldSet->add(new \PHPFUI\Input\Hidden('signinSheetId', (string)$signinSheet->signinSheetId));
		$fieldSet->add(new \App\UI\Display('Number', $signinSheet->signinSheetId));
		$fieldSet->add(new \App\UI\Display('Date Added', \App\Tools\Date::formatString('l, F j, Y', $signinSheet->dateAdded)));
		$imageName = '';

		if ('.pdf' == $signinSheet->ext)
			{
			$fieldSet->add(new \App\UI\Display('PDF File', new \PHPFUI\FAIcon('fas', 'file-download', '/SignInSheets/download/' . $signinSheet->signinSheetId)));
			}
		elseif (\in_array($signinSheet->ext, $this->model->validExtensions()))
			{
			$imageName = '/SignInSheets/image/' . $signinSheet->signinSheetId;
			$fieldSet->add("<img id='signinSheetPhoto' alt='' src='{$imageName}'>");
			}
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave(), 'memberId', $signinSheet->member->toArray());
		$fieldSet->add(new \App\UI\Display('Leader', $memberPicker->getEditControl()));
		$fieldSet->add($submit);

		if ($imageName)
			{
			$fieldSet->add(' &nbsp; ');
			$fieldSet->add($this->getRotateIcon('left', $signinSheet->signinSheetId));
			$fieldSet->add(' &nbsp; ');
			$fieldSet->add($this->getRotateIcon('right', $signinSheet->signinSheetId));
			$csrf = \PHPFUI\Session::csrf("'");
			$js = <<<JAVASCRIPT
function rotate(id,direction,signinSheetId){
$.ajax({type:'POST',dataType:'html',data:'action=Rotate'+direction+'&signinSheetId='+signinSheetId+'&csrf='+{$csrf},
beforeSend:function(){ $(id).addClass('fa-spin');},
success:function(data){
var date = new Date();
$(id).removeClass('fa-spin');
$('#signinSheetPhoto').attr('src','{$imageName}?time='+date.toLocaleTimeString());
}});
}
JAVASCRIPT;
			$this->page->addJavaScript($js);
			}
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Associated Rides');
		$rides = $this->signinSheetRideTable->rides($signinSheet->signinSheetId);
		$table = new \PHPFUI\Table();
		$recordIndex = 'rideId';
		$table->setRecordId($recordIndex);
		$table->setHeaders(['title' => 'Ride', 'rideDate' => 'Date', 'edit' => 'Edit', 'del' => 'Del', ]);
		$delete = new \PHPFUI\AJAX('deleteSignInSheetRide', 'Delete this ride from this sign in sheet?');
		$delete->addFunction('success', "$('#id-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());

		foreach ($rides as $ride)
			{
			$row = $ride->toArray();
			$row['edit'] = new \PHPFUI\FAIcon('far', 'edit', '/Rides/edit/' . $ride->rideId);
			$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$trash->addAttribute('onclick', $delete->execute([$recordIndex => $ride->rideId, 'signinSheetId' => $signinSheet->signinSheetId]));
			$row['del'] = $trash;
			$table->addRow($row);
			}
		$fieldSet->add($table);
		$addRideButton = new \PHPFUI\Button('Add Ride');
		$form->saveOnClick($addRideButton);
		$this->getAddRideModal($addRideButton, $signinSheet);
		$fieldSet->add($addRideButton);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();

		if ($signinSheet->pending && $this->page->isAuthorized('Approve Sign In Sheets'))
			{
			$buttonGroup->addButton(new \PHPFUI\Button('Approve', "/SignInSheets/approve/{$signinSheet->signinSheetId}"));
			$buttonGroup->addButton(new \PHPFUI\Button('Reject', "/SignInSheets/reject/{$signinSheet->signinSheetId}"));
			}
		$form->add($buttonGroup);

		return $form;
		}

	public function listSheets(iterable $signinSheets, \App\Record\Ride $ride) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$row = new \PHPFUI\GridX();
		$row->add(new \PHPFUI\Button('Original Sign In Sheet', "/Rides/waiver/{$ride->rideId}?type=printed"));
		$container->add($row);
		$count = \count($signinSheets) > 1 ? 1 : null;

		foreach ($signinSheets as $sheet)
			{
			$row = new \PHPFUI\GridX();
			$row->add(new \PHPFUI\Button("Submitted Sign In Sheet {$count}", "/Rides/waiver/{$ride->rideId}/{$sheet->signinSheetId}?type=submitted"));
			$container->add($row);
			++$count;
			}

		return $container;
		}

	public function reject(\App\Record\SigninSheet $signinSheet) : string
		{
		$form = new \PHPFUI\Form($this->page);
		$select = new \PHPFUI\Input\Select('reason', 'Common reasons for rejection');
		$select->addOption('');
		$select->addOption('The submitted file does not appear to be a sign in sheet.');
		$select->addOption('The submitted scan / photo is too blurry to be legible.');
		$select->addOption('The submitted scan / photo is of too low a resolution to be legible.');
		$select->addOption('The submitted file is cut off or not complete.');
		$form->add($select);
		$message = new \PHPFUI\Input\TextArea('message', 'Additional details on why the sign in sheet was rejected.');
		$message->setToolTip('Add any specific comments to the submitter here.  This will be added to the standard boilerplate for rejected sign in sheets.');
		$form->add($message);
		$form->add(new \PHPFUI\Input\Hidden('signinSheetId', (string)$signinSheet->signinSheetId));
		$row = new \PHPFUI\GridX();
		$row->add(new \PHPFUI\Submit('Reject Sign In Sheet', 'action'));
		$form->add($row);

		return $form;
		}

	public function show(\App\Table\SigninSheet $signinSheetTable, string $noSheets = 'No sign in sheets found') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$signinSheetTable->addJoin('signinSheetRide', new \PHPFUI\ORM\Condition('signinSheetRide.signinSheetId', new \PHPFUI\ORM\Literal('signinSheet.signinSheetId')), 'LEFT OUTER');
		$signinSheetTable->addJoin('ride', new \PHPFUI\ORM\Condition('signinSheetRide.rideId', new \PHPFUI\ORM\Literal('ride.rideId')), 'LEFT OUTER');
		$signinSheetTable->addJoin('member');

		$view = new \App\UI\ContinuousScrollTable($this->page, $signinSheetTable);
		$sortableHeaders = ['rideDate' => 'Ride Date', 'title' => 'Ride', 'dateAdded' => 'Received', 'pending' => 'Pending', ];
		$otherHeaders = ['memberId' => 'Leader', 'view' => 'View', ];

		if ($this->page->isAuthorized('Edit Sign In Sheet'))
			{
			$otherHeaders['edit'] = 'Edit';
			}

		new \App\Model\EditIcon($view, $signinSheetTable, '/SignInSheets/edit/');

		$view->addCustomColumn('pending', static fn (array $sheet) => $sheet['pending'] ? 'Pending' : 'Approved');
		$view->addCustomColumn('memberId', static fn (array $sheet) => $sheet['firstName'] . ' ' . $sheet['lastName']);

		$view->addCustomColumn('view', static function(array $sheet) {
			$type = '.pdf' == $sheet['ext'] ? 'file-pdf' : 'image';

			return new \PHPFUI\FAIcon('fas', $type, '/SignInSheets/download/' . $sheet['signinSheetId']);
		});

		$view->setHeaders(\array_merge($sortableHeaders, $otherHeaders))
			->setSortableColumns(\array_keys($sortableHeaders));
		unset($sortableHeaders['pending']);
		$view->setSearchColumns(\array_keys($sortableHeaders));

		$container->add($view);

		return $container;
		}

	private function getAddRideModal(\PHPFUI\HTML5Element $modalLink, \App\Record\SigninSheet $signinSheet) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('signinSheetId', (string)$signinSheet->signinSheetId));
		$date = new \PHPFUI\Input\Date($this->page, 'rideDate', 'Date of the ride');
		$date->setRequired();
		$date->setToolTip('Enter the date of the ride listed on the sign in sheet.  Then select the ride.');
		$ajax = new \PHPFUI\AJAX('addRideDate');
		$ajax->addFunction('success', '$("#RideSelect").html(data.response);');
		$date->addAttribute('onchange', $ajax->execute(['rideDate' => 'this.value', 'leader' => $signinSheet->memberId]));
		$this->page->addJavaScript($ajax->getPageJS());
		$form->add($date);
		$rideSelect = new \PHPFUI\HTML5Element('div');
		$rideSelect->setId('RideSelect');
		$rideSelect->add($this->getRideSelect());
		$form->add($rideSelect);
		$submit = new \PHPFUI\Submit('Add Ride', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getRideSelect(string $date = '', int $leader = 0) : \PHPFUI\Input\Select
		{
		$rideSelect = new \PHPFUI\Input\Select('rideId', 'Select the ride');
		$rideSelect->setToolTip('Enter the date of the ride first, then select the ride.');
		$rideTable = new \App\Table\Ride();
		$rides = $rideTable->getDateRange(\App\Tools\Date::fromString($date), \App\Tools\Date::fromString($date));

		foreach ($rides as $ride)
			{
			$rideSelect->addOption($ride['rideId'] . ' - ' . $ride['title'], $ride['rideId'], $leader == $ride['memberId']);
			}

		return $rideSelect;
		}

	private function getRotateIcon(string $direction, int $signinSheetId) : \PHPFUI\FAIcon
		{
		$icon = new \PHPFUI\FAIcon('fas', 'rotate-' . $direction, '#');
		$icon->addAttribute('onclick', "rotate(\"#{$icon->getId()}\",\"{$direction}\",{$signinSheetId})");

		return $icon;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'Rotateright':
					case 'Rotateleft':
						$fileModel = new \App\Model\SignInSheetFiles();
						$signinSheet = new \App\Record\SigninSheet($_POST['signinSheetId']);
						$fileName = $fileModel->get($signinSheet->signinSheetId . $signinSheet->ext);

						if ('Rotateleft' == $_POST['action'])
							{
							$fileModel->rotateLeft($fileName);
							}
						else
							{
							$fileModel->rotateRight($fileName);
							}
						$this->page->setResponse('Saved');

						break;

					case 'deleteSignInSheetRide':
						$this->model->deleteRide((int)$_POST['signinSheetId'], (int)$_POST['rideId']);
						$this->page->setResponse($_POST['signinSheetId']);

						break;

					case 'Add Ride':
						$signinSheetRide = new \App\Record\SigninSheetRide();
						$signinSheetRide->setFrom($_POST);
						$signinSheetRide->insert();
						$this->page->redirect();

						break;

					case 'addRideDate':
						$html = $this->getRideSelect($_POST['rideDate'], $_POST['leader']);
						$this->page->setResponse("{$html}");  // stringize to save instance

						break;

					case 'Reject Sign In Sheet':
						$signinSheet = new \App\Record\SigninSheet();
						$signinSheet->setFrom($_POST);
						$signinSheet->reload();
						$this->model->reject($signinSheet, $_POST['message'], $_POST['reason']);
						$this->page->redirect();

						break;
					}
				}
			}
		}
	}
