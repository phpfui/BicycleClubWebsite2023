<?php

namespace App\View\Member;

class Search implements \Stringable
	{
	/**
	 * @var array<string,string>
	 */
	protected $exceptions = [];

	/**
	 * @var array<string,string>
	 */
	protected $fields = [];

	/**
	 * @var array<string,string>
	 */
	protected $specialFields = [];

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->fields['phone'] = 'Phone';
		$this->fields['cellPhone'] = 'Cell';

		if ($this->page->isAuthorized('Ride Leader'))
			{
			$this->fields['license'] = 'Plate';
			}
		$this->fields['membership_address'] = 'Address';
		$this->fields['membership_town'] = 'Town';
		$this->specialFields['firstName'] = 'First Name';
		$this->specialFields['lastName'] = 'Last Name';
		$this->specialFields['email'] = 'email';
		$this->specialFields['emergencyPhone'] = 'Emergency Phone';
		$this->specialFields['emergencyContact'] = 'Emergency Contact';

		$this->exceptions['membership_address'] = 'showNoStreet';
		$this->exceptions['membership_town'] = 'showNoTown';
		$this->exceptions['membership_zip'] = 'showNoTown';
		$this->exceptions['phone'] = 'showNoPhone';
		$this->exceptions['cellPhone'] = 'showNoPhone';
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search Members');
		$modal = $this->getSearchModal($button, $_GET);
		$output = '';
		$row = new \PHPFUI\GridX();
		$row->add('<br>');

		if (! empty($_GET['submit']))
			{
			$view = new \App\View\Member($this->page);
			$memberTable = new \App\Table\Member();
			$memberTable->setLimit(50);
			$memberTable->addOrderBy('firstName');

			if (! $this->page->isAuthorized('Membership Chair'))
				{
				$condition = $memberTable->getWhereCondition();
				$condition->and('showNothing', 0);

				foreach ($_GET as $field => $value)
					{
					$excludeField = $this->exceptions[$field] ?? '';

					if ($excludeField && ! empty($value))
						{
						$condition->and($excludeField, 0);
						}
					}
				}
			$_GET['pending'] = 0;
			$members = $memberTable->find($_GET);
			$output = $view->show($members);

			if (\count($members))
				{
				$output .= $row . $button;
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	/**
	 * @param array<string,string> $fields
	 * @param array<string,string> $parameters
	 */
	protected function generateFields(array $fields, array $parameters) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$multiColumn = new \PHPFUI\MultiColumn();

		foreach ($fields as $field => $name)
			{
			$multiColumn->add(new \PHPFUI\Input\Text($field, $name, $parameters[$field] ?? ''));

			if (2 == \count($multiColumn))
				{
				$container->add($multiColumn);
				$multiColumn = new \PHPFUI\MultiColumn();
				}
			}

		if (\count($multiColumn))
			{
			$container->add($multiColumn);
			}

		return $container;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink, array $parameters) : \PHPFUI\Reveal
		{
		$searchFields = [];
		$searchFields['membership_state'] = 'State';
		$searchFields['membership_zip'] = 'Zip';
		$searchFields['categories'] = 'Categories';
		$parameters = $this->setDefaults($searchFields, $parameters);
		$parameters = $this->setDefaults($this->fields, $parameters);

		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$tabs = new \PHPFUI\Tabs();


		$basic = new \PHPFUI\Container();
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Member Name'), 'memberId');
		$basic->add($memberPicker->getEditControl());
		$basic->add($this->generateFields($this->fields, $parameters));
		$tabs->addTab('Basic', $basic, true);

		if ($this->page->isAuthorized('Visible Email Addresses'))
			{
			$advanced = new \PHPFUI\Container();
			$advanced->add($this->generateFields($this->specialFields, $parameters));
			$tabs->addTab('Advanced', $advanced);
			}

		$extra = new \PHPFUI\Container();
		$categoryView = new \App\View\Categories($this->page);

		if (! \is_array($parameters['categories']))
			{
			$parameters['categories'] = [];
			}
		$picker = $categoryView->getMultiCategoryPicker('categories', 'Member Ride Categories', $parameters['categories']);
		$extra->add($picker);
		$state = new \PHPFUI\Input\Text('membership_state', 'State', $parameters['membership_state']);
		$state->addAttribute('size', (string)2);
		$zip = new \PHPFUI\Input\Zip($this->page, 'membership_zip', 'Zip', $parameters['membership_zip']);
		$extra->add(new \PHPFUI\MultiColumn($state, $zip));
		$tabs->addTab('Extra', $extra);

		$form->add($tabs);

		if ($this->page->isAuthorized('Search Past Members'))
			{
			$form->add('<br>');
			$form->add(new \PHPFUI\Input\CheckBox('all', 'Include Past Members'));
			}

		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	/**
	 * @param array<string,string> $searchFields
	 * @param array<string,string> $parameters
	 * @return (mixed|string)[]
	 *
	 * @psalm-return array<array-key, mixed|string>
	 */
	protected function setDefaults(array $searchFields, array $parameters) : array
		{
		foreach ($searchFields as $key => $value)
			{
			if (! isset($parameters[$key]))
				{
				$parameters[$key] = '';
				}
			}

		return $parameters;
		}
	}
