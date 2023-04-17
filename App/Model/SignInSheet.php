<?php

namespace App\Model;

class SignInSheet
	{
	private readonly SignInSheetFiles $fileModel;

	public function __construct()
		{
		$this->fileModel = new \App\Model\SignInSheetFiles();
		}

	public function approve(\App\Record\SigninSheet $signinSheet) : void
		{
		$signinSheet->pending = 0;
		$signinSheet->dateAdded = \App\Tools\Date::todayString();
		$signinSheet->update();
		$email = new \App\Model\Email('acceptSignInSheet', new \App\Model\Email\SignInSheet($signinSheet));
		$email->setHtml();
		$email->setFromMember(\App\Model\Session::getSignedInMember());
		$email->addToMember($signinSheet->member->toArray());
		$prettyName = "SignInSheet-{$signinSheet->signinSheetId}{$signinSheet->ext}";
		$email->addAttachment($this->fileModel->get($signinSheet->signinSheetId . $signinSheet->ext), $prettyName);
		$email->send();
		}

	public function delete(\App\Record\SigninSheet $signinSheet) : void
		{
		$signinSheet->delete();
		$this->fileModel->delete((string)$signinSheet->signinSheetId);
		}

	public function deleteRide(int $signinSheetId, int $rideId) : void
		{
		$signinSheetRideTable = new \App\Table\SigninSheetRide();
		$condition = new \PHPFUI\ORM\Condition('signinSheetId', $signinSheetId);
		$condition->and('rideId', $rideId);
		$signinSheetRideTable->setWhere($condition)->delete();
		}

	public function download(\App\Record\SigninSheet $signinSheet) : string
		{
		if (! $signinSheet->empty())
			{
			$extension = $signinSheet->ext;
			$downloadName = "SignInSheet-{$signinSheet->signinSheetId}{$extension}";
			$error = $this->fileModel->download($signinSheet->signinSheetId, $extension, $downloadName);

			if ($error)
				{
				return "File not found: {$error}";
				}
			}
		else
			{
			return "{$signinSheet->signinSheetId} is not a valid sign in sheet number";
			}

		return '';
		}

	public function reject(\App\Record\SigninSheet $signinSheet, string $message, string $standardReason) : void
		{
		$submitter = $signinSheet->member;

		if (! $submitter->empty())
			{
			$rejectMessage = '';

			if ($standardReason)
				{
				$rejectMessage = "<p><strong>{$standardReason}</strong></p>";
				}

			if ($message)
				{
				$rejectMessage = "<p>{$message}</p>";
				}

			$email = new \App\Model\Email('rejectSignInSheet', new \App\Model\Email\SignInSheet($signinSheet, $rejectMessage));

			$email->setBody($rejectMessage);
			$email->setHtml();
			$email->setFromMember(\App\Model\Session::getSignedInMember());
			$email->addToMember($submitter->toArray());
			$prettyName = "SignInSheet-{$signinSheet->signinSheetId}{$signinSheet->ext}";
			$email->addAttachment($this->fileModel->get($signinSheet->signinSheetId . $signinSheet->ext), $prettyName);
			$email->send();
			}
		$this->delete($signinSheet);
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return array{0: string, 1: string, 2: string}
	 */
	public static function validExtensions() : array
		{
		return ['.jpg', '.pdf', '.png'];
		}
	}
