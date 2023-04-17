<?php

namespace App\WWW;

class SignInSheets extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\SigninSheet $signinSheetTable;

	private readonly \App\View\SignInSheet $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\SignInSheet($this->page);
		$this->signinSheetTable = new \App\Table\SigninSheet();
		}

	public function acceptEmail() : void
		{
		if ($this->page->addHeader('Edit Accept Sign In Sheet Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'acceptSignInSheet', new \App\Model\Email\SignInSheet());
			$this->page->addPageContent($editor);
			}
		}

	public function approve(\App\Record\SigninSheet $signinSheet = new \App\Record\SigninSheet()) : void
		{
		if ($this->page->addHeader('Approve Sign In Sheets'))
			{
			if (! $signinSheet->empty())
				{
				if ($signinSheet->pending)
					{
					$this->page->addPageContent("<h3>Sign In Sheet {$signinSheet->signinSheetId} has been approved</h3>");
					$model = new \App\Model\SignInSheet();
					$model->approve($signinSheet);
					}
				else
					{
					$this->page->addPageContent('<h3>Sign In Sheet has already been approved</h3>');
					}
				}
			else
				{
				$this->page->addPageContent('<h3>Sign In Sheet not found</h3>');
				}
			$this->page->redirect('/SignInSheets/pending', '', 2);
			}
		}

	public function download(\App\Record\SigninSheet $signinSheet = new \App\Record\SigninSheet()) : void
		{
		if ($this->page->isAuthorized('View Sign In Sheet'))
			{
			$model = new \App\Model\SignInSheet();
			$error = $model->download($signinSheet);

			if ($error)
				{
				$this->page->addPageContent("<h3>{$error}</h3>");
				}
			else
				{
				$this->page->done();
				}
			}
		}

	public function edit(\App\Record\SigninSheet $signinSheet = new \App\Record\SigninSheet()) : void
		{
		if (! $signinSheet->empty())
			{
			if ($this->page->addHeader('Edit Sign In Sheet', '', $signinSheet->memberId == \App\Model\Session::signedInMemberId()))
				{
				$this->page->addPageContent($this->view->Edit($signinSheet));
				}
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader("Sign In Sheet {$signinSheet->signinSheetId} not found"));
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Search Sign In Sheets'))
			{
			$this->page->addPageContent(new \App\View\SignInSheetSearch($this->page));
			}
		}

	public function image(\App\Record\SigninSheet $signinSheet = new \App\Record\SigninSheet()) : void
		{
		if ($this->page->isAuthorized('View Sign In Sheet'))
			{
			if (! $signinSheet->empty())
				{
				$extension = \str_replace('.', '', $signinSheet->ext);
				$fileModel = new \App\Model\SignInSheetFiles();

				if (false !== ($data = @\file_get_contents($fileModel->getPath() . "{$signinSheet->signinSheetId}.{$extension}")))
					{
					if ('jpg' == $extension)
						{
						$extension = 'jpeg';
						}
					\header('Content-type: image/' . $extension);
					echo $data;

					exit;
					}
				}
			}
		}

	public function my() : void
		{
		if ($this->page->addHeader('My Sign In Sheets'))
			{
			$this->signinSheetTable->setWhere(new \PHPFUI\ORM\Condition('member.memberId', \App\Model\Session::signedInMemberId()));
			$this->page->addPageContent($this->view->show($this->signinSheetTable));
			}
		}

	public function pending() : void
		{
		if ($this->page->addHeader('Approve Sign In Sheets'))
			{
			$this->signinSheetTable->setWhere(new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
			$this->page->addPageContent($this->view->show($this->signinSheetTable));
			}
		}

	public function reject(\App\Record\SigninSheet $signinSheet = new \App\Record\SigninSheet()) : void
		{
		if ($this->page->addHeader('Reject Sign In Sheet'))
			{
			if ($signinSheet->empty())
				{
				$this->page->addPageContent('<h3>Sign In Sheet has been rejected</h3>');
				$this->page->redirect('/SignInSheets/pending', '', 2);
				}
			else
				{
				$this->page->addPageContent($this->view->reject($signinSheet));
				}
			}
		}

	public function rejectEmail() : void
		{
		if ($this->page->addHeader('Edit Reject Sign In Sheet Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'rejectSignInSheet', new \App\Model\Email\SignInSheet());
			$this->page->addPageContent($editor);
			}
		}

	public function settings() : void
		{
		if ($this->page->addHeader('Sign In Sheets Configuration'))
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$incentivesChair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Sign In Sheet Coordinator'));
			$email = $incentivesChair->getEditControl();
			$email->setToolTip('This address will be used to email pending sign in sheets.');
			$form->add($email);
			$this->page->addPageContent($form);
			}
		}

	public function tips() : void
		{
		if ($this->page->addHeader('Sign In Sheets Tips'))
			{
			$this->page->addPageContent(new \App\View\SettingEditor($this->page, 'signInSheetTips', true));
			}
		}
	}
