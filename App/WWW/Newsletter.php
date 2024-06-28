<?php

namespace App\WWW;

class Newsletter extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		}

	public function all(int $year = 0) : void
		{
		if ($this->page->addHeader('Newsletters'))
			{
			$view = new \App\View\Newsletter($this->page);

			$this->page->addPageContent($view->display($year));
			}
		}

	public function download(\App\Record\Newsletter $newsletter = new \App\Record\Newsletter()) : void
		{
		if (! $newsletter->loaded())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Newsletter not found'));

			return;
			}

		$file = new \App\Model\NewsletterFiles($newsletter);

		try
			{
			$file->download($newsletter->newsletterId, '.pdf', $file->getPrettyFileName());
			$this->page->done();
			}
		catch (\Exception $e)
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader($e->getMessage()));
			}
		}

	public function settings() : void
		{
		if ($this->page->addHeader('Newsletter Settings'))
			{
			$view = new \App\View\Newsletter($this->page);
			$this->page->addPageContent($view->settings());
			}
		}

	public function upload() : void
		{
		$field = 'file';

		if (\App\Model\Session::checkCSRF())
			{
			if (! empty($_POST['date']))
				{
				$date = $_POST['date'];
				}
			else
				{
				$uploadName = $_FILES[$field]['name'] ?? \App\Tools\Date::todayString();
				$time = \strtotime($uploadName);

				if ($time < \strtotime('1970-01-01'))
					{
					// look for text month
					for ($i = 1; $i <= 12; ++$i)
						{
						$monthName = \date('M', \strtotime('2000-' . $i . '-01'));
						$pos = \stripos($uploadName, $monthName);

						if (false !== $pos)
							{
							$uploadName = \substr($uploadName, $pos);

							break;
							}
						}
					$pos = \strrpos($uploadName, '.');

					if ($pos)
						{
						$uploadName = \substr($uploadName, 0, $pos);
						}
					$time = \strtotime($uploadName);

					if ($time < \strtotime('1970-01-01'))
						{
						\App\Model\Session::setFlash('alert', 'File name ' . $_FILES[$field]['name'] . ' could not be parsed to a valid date, please specify a date.');
						$this->page->redirect();

						return;
						}
					}
				$date = \date('Y-m-d', $time);
				}

			$newsletter = new \App\Record\Newsletter(['date' => $date]);

			if (isset($_POST['delete']))
				{
				if ($newsletter->loaded())
					{
					\App\Model\Session::setFlash('success', $newsletter->date . ' newsletter deleted');
					$newsletter->delete();
					}
				else
					{
					\App\Model\Session::setFlash('alert', 'No newsletter found for ' . $date);
					}
				$this->page->redirect();

				return;
				}

			if (! $newsletter->loaded())
				{
				$created = true;
				$newsletter->date = $date;
				$newsletter->dateAdded = \App\Tools\Date::todayString();
				$newsletter->size = 0;
				$id = $newsletter->insert();
				$newsletter->reload();
				}
			else
				{
				$created = false;
				$id = $newsletter->newsletterId;
				}
			$fileModel = new \App\Model\NewsletterFiles($newsletter);

			if ($date > '' && $fileModel->upload((string)$id, $field, $_FILES, ['.pdf' => 'application/pdf']))
				{
				$newsletter->size = $fileModel->getUploadSize();
				$newsletter->update();
				\App\Model\Session::setFlash('success', $date . ' Newsletter uploaded successfully!');
				}
			else
				{
				if ($created)
					{
					$newsletter->delete();
					}

				if ($date < 1)
					{
					$error = "Invalid date ({$date}) {$_POST['date']}";
					}
				else
					{
					$error = $fileModel->getLastError();
					}
				\App\Model\Session::setFlash('alert', $error);
				}
			$this->page->redirect();
			}
		elseif ($this->page->addHeader('Add A Newsletter'))
			{
			$form = new \PHPFUI\Form($this->page);
			$fieldSet = new \PHPFUI\FieldSet('Upload Newsletter');
			$date = new \PHPFUI\Input\Date($this->page, 'date', 'Newsletter Date');
			$date->setToolTip('This should be the first of the month for a traditional newsletter, or the date of the email for an email update.');
			$file = new \PHPFUI\Input\File($this->page, $field, 'Newsletter PDF File');
			$file->setAllowedExtensions(['pdf']);
			$file->setRequired();
			$file->setToolTip('Should be a PDF file');
			$delete = new \PHPFUI\Input\CheckBox('delete', 'Delete the newsletter on the above date');
			$delete->addAttribute('onclick', '$("#' . $file->getId() . '").removeAttr("required")');
			$fieldSet->add(new \PHPFUI\MultiColumn($date . '<br>' . $delete, $file));
			$form->add($fieldSet);
			$form->add(new \App\UI\CancelButtonGroup(new \PHPFUI\Submit()));
			$this->page->addPageContent($form);
			}
		}
	}
