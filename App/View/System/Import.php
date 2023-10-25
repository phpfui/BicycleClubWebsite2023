<?php

namespace App\View\System;

class Import
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function model(string $model) : \PHPFUI\Form
		{
		$output = '';
		$error = 0;

		if (\App\Model\Session::checkCSRF())
			{
			$class = "\\App\\Record\\{$model}";
			$record = new $class();
			$output = 'No file added';

			if (isset($_FILES['file']))
				{
				$error = $_FILES['file']['error'];

				switch ($error)
					{
					case UPLOAD_ERR_INI_SIZE:
						$output = 'The uploaded file size exceeds the server limit.';

						break;

					case UPLOAD_ERR_PARTIAL:
						$output = 'The upload for the file did not complete.  Please try again.';

						break;

					case UPLOAD_ERR_NO_FILE:
						$output = 'No file was uploaded. Please try again';

						break;
					}

				if (! $error)
					{
					$output = '';

					if (\is_uploaded_file($_FILES['file']['tmp_name']))
						{
						$source_file = $_FILES['file']['tmp_name'];
						$reader = new \App\Tools\CSV\FileReader($source_file);
						$reader->next();
						$error = 0;
						$good = 0;

						foreach ($reader as $row)
							{
							$record->setEmpty();
							$record->setFrom($row);

							if ($record->insert())
								{
								++$good;
								}
							else
								{
								++$error;
								}
							}
						$output = "Imported {$good} with {$error} errors";
						\App\Tools\File::unlink($source_file);
						}
					}
				}
			}
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);

		if ($output)
			{
			$alert = new \App\UI\Alert($output);

			if ($error)
				{
				$alert->addClass('alert');
				}
			$form->add($alert);
			}
		$fieldSet = new \PHPFUI\FieldSet("Upload File containing {$model} entries.");
		$file = new \PHPFUI\Input\File($this->page, 'file', 'CSV File To Import');
		$fieldSet->add($file);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Import'));
		$download = new \PHPFUI\Button('Download Template', "/Import/Template/{$model}");
		$download->addClass('info');
		$buttonGroup->addButton($download);
		$form->add($buttonGroup);

		return $form;
		}

	public function SQL() : \PHPFUI\Form
		{
		$output = '';
		$error = false;
		$form = new \PHPFUI\Form($this->page);

		if (\App\Model\Session::checkCSRF())
			{
			$output = 'Nothing submitted';

			if (! empty($_POST['Statement']))
				{
				$sql = \str_replace('\"', '"', (string)$_POST['Statement']);
				$sql = \str_replace("\'", "'", $sql);

				if (\strlen($sql) > 3)
					{
					if (! \PHPFUI\ORM::execute($sql, []))
						{
						$output = "<b>Error in statement: {$sql}</b> " . \PHPFUI\ORM::getLastError();
						$error = true;
						}
					else
						{
						$output = "<h3>Statement executed.</h3><b>{$sql}</b>";
						}
					}
				}
			elseif (isset($_FILES['file']))
				{
				$error = $_FILES['file']['error'];

				switch ($error)
					{
					case UPLOAD_ERR_INI_SIZE:
						$output = 'The uploaded file size exceeds the server limit.';

						break;

					case UPLOAD_ERR_PARTIAL:
						$output = 'The upload for the file did not complete.  Please try again.';

						break;

					case UPLOAD_ERR_NO_FILE:
						$output = 'No file was uploaded. Please try again';

						break;
					}

				if (! $error)
					{
					$error = false;
					$output = '';

					$source_file = $_FILES['file']['tmp_name'] ?? '';

					if (\is_uploaded_file($source_file))
						{
						$restore = new \App\Model\Restore($source_file);

						if (! $restore->run())
							{
							$error = true;

							foreach ($restore->getErrors() as $error)
								{
								$output .= $error . '<br>';
								}
							}
						else
							{
							$output = 'File imported';
							}
						}
					else
						{
						$error = true;
						$output = "Unable to open file {$source_file}";
						}
					}
				}
			\PHPFUI\Session::setFlash($error ? 'alert' : 'success', $output);
			$this->page->redirect();

			return $form;
			}
		$form->setAreYouSure(false);

		$fieldSet = new \PHPFUI\FieldSet('Upload File containing SQL to execute.');
		$fieldSet->add($tip = 'Statements can span multiple lines but must be terminated by a semi colon.  One statement per line max.');
		$file = new \PHPFUI\Input\File($this->page, 'file');
		$file->setToolTip($tip);
		$fieldSet->add($file);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Or Enter SQL statement to execute.');
		$text = new \PHPFUI\Input\Text('Statement', 'Statement');
		$fieldSet->add($text);
		$form->add($fieldSet);
		$row = new \PHPFUI\GridX();
		$row->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit('Execute SQL')));
		$form->add($row);

		return $form;
		}

	public function template(string $model) : void
		{
		$class = "\\App\Table\\{$model}";
		$record = new $class();
		$writer = new \App\Tools\CSV\FileWriter("{$model}_template.csv");
		$fields = $record->getFields();

		foreach ($record->getPrimaryKeys() as $field)
			{
			unset($fields[$field]);
			}
		$row = \array_keys($fields);
		$writer->outputRow($row);
		}
	}
