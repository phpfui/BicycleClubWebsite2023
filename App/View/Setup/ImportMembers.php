<?php

namespace App\View\Setup;

class ImportMembers extends \PHPFUI\Container
	{
	private readonly \App\Model\ImportFile $importModel;

	private readonly \App\Table\Membership $membershipTable;

	private readonly \App\Table\Member $memberTable;

	public function __construct(private readonly \PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->memberTable = new \App\Table\Member();
		$this->membershipTable = new \App\Table\Membership();
		$this->importModel = new \App\Model\ImportFile('members.csv');

		$this->add(new \PHPFUI\Header('Import Members', 4));

		if (isset($_GET['delete']))
			{
			\App\Tools\File::unlink($this->importModel->getFileName());
			\PHPFUI\ORM::execute('truncate table member');
			\PHPFUI\ORM::execute('truncate table membership');

			$this->page->redirect();

			return;
			}

		if (isset($_GET['export']))
			{
			$membershipModel = new \App\Model\Membership();
			$csvWriter = new \App\Tools\CSVWriter('members.csv');
			$membershipModel->export($csvWriter);
			$this->page->done();

			return;
			}

		if (\App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'] ?? '')
				{
				case 'Upload':

					\PHPFUI\Session::setFlash('separator', $_POST['separator'] ?? ',');

					if ($this->importModel->upload(null, 'importFile', $_FILES))
						{
						\App\Model\Session::setFlash('success', 'File uploaded successfully');
						}
					else
						{
						\App\Model\Session::setFlash('alert', $this->importModel->getLastError());
						}

					break;

				case 'Import Members':
					$this->doImport();

					break;
				}
			$this->page->redirect();

			return;
			}

		\PHPFUI\Session::setFlash('separator', $separator = \PHPFUI\Session::getFlash('separator'));

		$form = new \PHPFUI\Form($this->page);

		$totalMembers = $this->memberTable->count();
		$callout = new \PHPFUI\Callout('info');
		$callout->add("There are {$totalMembers} members in the database currently");
		$form->add($callout);

		$wizardBar->nextAllowed($totalMembers > 0);

		if (\file_exists($this->importModel->getFileName()))
			{
			$form->add($this->importWidget($wizardBar, $separator ?? ',', $totalMembers));
			}
		else
			{
			$form->add($this->uploadWidget($wizardBar));
			}

		$form->setAreYouSure(false);

		$this->add($wizardBar);
		$this->add($form);
		}

	private function doImport() : void
		{
		$csvReader = new \App\Tools\CSVReader($this->importModel->getFileName(), true, \PHPFUI\Session::getFlash('separator') ?? ',');
		$membershipModel = new \App\Model\Membership();
		$settingsTable = new \App\Table\Setting();
		$paidMembers = $settingsTable->value('PaidMembers');
		$membershipModel->import($csvReader, $_POST, 'Paid' == $paidMembers);
		}

	private function getMatchFields(array $importFields, array $dataFields) : string
		{
		$container = new \PHPFUI\Container();

		$removedFields = [
			'acceptedWaiver',
			'deceased',
			'discountCount',
			'loginAttempts',
			'memberId',
			'membershipId',
			'password',
			'pendingLeader',
			'profileHeight',
			'profileWidth',
			'profileX',
			'profileY',
			'verifiedEmail',
			'membershipId',
			'pending',
			'subscriptionId',
		];

		foreach ($removedFields as $field)
			{
			unset($dataFields[$field]);
			}

		$dataFields = \array_keys($dataFields);
		\sort($dataFields);

		foreach ($dataFields as $field)
			{
			$select = new \PHPFUI\Input\Select($field, $field);
			$select->addOption('');

			foreach ($importFields as $option)
				{
				$select->addOption($option, $option, $option == $field);
				}
			$override = new \PHPFUI\Input\Text($field . 'Override', $field . ' Override');

			$container->add(new \PHPFUI\MultiColumn($select, $override));
			}

		return $container;
		}

	private function importWidget(\App\View\Setup\WizardBar $wizardBar, string $separator, int $count) : string
		{
		$csvReader = new \App\Tools\CSVReader($this->importModel->getFileName(), true, $separator);
		$headers = \array_keys($csvReader->current());
		\sort($headers);
		$container = new \PHPFUI\Container();
		$fieldSet = new \PHPFUI\FieldSet('Match Member Fields To Import');
		$fieldSet->add($this->getMatchFields($headers, $this->memberTable->getFields()));
		$container->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Match Membership Fields To Import');
		$fieldSet->add($this->getMatchFields($headers, $this->membershipTable->getFields()));
		$container->add($fieldSet);

		$importButton = new \PHPFUI\Submit('Import Members', 'action');
		$importButton->addClass('success');
		$wizardBar->addButton($importButton);

		if ($count)
			{
			$exportButton = new \PHPFUI\Button('Export Members', $this->page->getBaseURL() . '?export');
			$exportButton->addClass('warning');
			$wizardBar->addButton($exportButton);
			}

		$deleteButton = new \PHPFUI\Button('Delete Members', $this->page->getBaseURL() . '?delete');
		$deleteButton->addClass('alert');
		$wizardBar->addButton($deleteButton);

		return $container;
		}

	private function uploadWidget(\App\View\Setup\WizardBar $wizardBar) : string
		{
		$fieldSet = new \PHPFUI\FieldSet('File To Upload');
		$callout = new \PHPFUI\Callout('info');
		$callout->add('First line MUST be a header row of field names');
		$fieldSet->add($callout);
		$filesize = new \PHPFUI\Input\Hidden('MAX_FILE_SIZE', (string)4_000_000);
		$fieldSet->add($filesize);
		$file = new \PHPFUI\Input\File($this->page, 'importFile', 'CSV or compatible file to upload');
		$extensions = [];

		foreach ($this->importModel->getMimeTypes() as $ext => $mime)
			{
			$extensions[] = \str_replace('.', '', $ext);
			}
		$file->setAllowedExtensions($extensions);
		$fieldSet->add($file);
		$this->importModel->getMimeTypes();
		$fieldSet->add(new \App\UI\Display('Allowed Types', \implode(' ', \array_keys($this->importModel->getMimeTypes()))));
		$separator = new \PHPFUI\Input\Select('separator', 'Field Delimiter');
		$separator->addOption(',');
		$separator->addOption('~');
		$separator->addOption('`');
		$separator->addOption('|');
		$separator->addOption('^');
		$separator->addOption('TAB');
		$fieldSet->add($separator);

		$submit = new \PHPFUI\Submit('Upload', 'action');
		$submit->addClass('success');
		$wizardBar->addButton($submit);

		return $fieldSet;
		}
	}
