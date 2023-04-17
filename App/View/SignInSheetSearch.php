<?php

namespace App\View;

class SignInSheetSearch implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search Sign In Sheets');
		$modal = $this->getSearchModal($button, $_GET);
		$output = '';
		$row = new \PHPFUI\GridX();
		$row->add('<br>');

		$view = new \App\View\SignInSheet($this->page);
		$signinSheetTable = new \App\Table\SigninSheet();

		if ($signinSheetTable->search($_GET))
			{
			$output = $view->show($signinSheetTable);
			$output .= $row . $button;
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink, array $parameters) : \PHPFUI\Reveal
		{
		$this->setDefaults($parameters);
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Find a Sign In Sheet');
		$memberPickerModel = new \App\Model\MemberPickerNoSave('Member Name');

		if (! empty($parameters['MemberName']))
			{
			$member = new \App\Record\Member((int)$parameters['MemberName']);
			$memberPickerModel->setMember($member->toArray());
			}
		$memberPicker = new \App\UI\MemberPicker($this->page, $memberPickerModel);
		$fieldSet->add($memberPicker->getEditControl());

		$rideTitle = new \PHPFUI\Input\Text('ride_title', 'Ride Title', $parameters['ride_title']);
		$fieldSet->add($rideTitle);

		$acceptStart = new \PHPFUI\Input\Date($this->page, 'addedStart', 'First Date Added', $parameters['addedStart']);
		$acceptEnd = new \PHPFUI\Input\Date($this->page, 'addedEnd', 'Last Date Added', $parameters['addedEnd']);
		$fieldSet->add(new \PHPFUI\MultiColumn($acceptStart, $acceptEnd));
		$rideStartDate = new \PHPFUI\Input\Date($this->page, 'rideDateStart', 'First Ride Date', $parameters['rideDateStart']);
		$rideEndDate = new \PHPFUI\Input\Date($this->page, 'rideDateEnd', 'Last Ride Date', $parameters['rideDateEnd']);
		$fieldSet->add(new \PHPFUI\MultiColumn($rideStartDate, $rideEndDate));

		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	protected function setDefaults(array &$parameters) : void
		{
		$searchFields = [];
		$searchFields['ride_title'] = '';
		$searchFields['addedEnd'] = '';
		$searchFields['addedStart'] = '';
		$searchFields['rideDateEnd'] = '';
		$searchFields['rideDateStart'] = '';
		$searchFields['MemberName'] = '';

		foreach ($searchFields as $key => $value)
			{
			if (! isset($parameters[$key]))
				{
				$parameters[$key] = $value;
				}
			}
		}
	}
