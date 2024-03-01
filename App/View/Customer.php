<?php

namespace App\View;

class Customer
	{
	public function __construct(private readonly \App\View\Page $page, private readonly \App\Model\Customer $customerModel)
		{
		if (\App\Model\Session::checkCSRF())
			{
			if ('Save' == ($_POST['submit'] ?? ''))
				{
				$post = $_POST;

				if (isset($post['stateText']) && empty($post['state']))
					{
					$post['state'] = $post['stateText'];
					}
				$this->customerModel->save($post);
				$page->redirect();
				}
			}
		}

	public function edit(int $id, bool $email = true) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$customer = $this->customerModel->read($id);
		$form->add(new \PHPFUI\Input\Hidden('customerId', (string)$id));
		$form->add('PayPal requires your address information to validate your credit card. Please fill out the following information:');
		$fieldSet = new \PHPFUI\FieldSet('Customer Information');
		$firstName = new \PHPFUI\Input\Text('firstName', 'First Name', $customer->firstName);
		$firstName->setRequired();
		$lastName = new \PHPFUI\Input\Text('lastName', 'Last Name', $customer->lastName);
		$lastName->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($firstName, $lastName));

		if ($email)
			{
			$email = new \PHPFUI\Input\Email('email', 'email', $customer->email);
			$email->setRequired();
			$fieldSet->add($email);
			}
		$address = new \PHPFUI\Input\Text('address', 'Address', $customer->address);
		$address->setRequired();
		$town = new \PHPFUI\Input\Text('town', 'Town', $customer->town);
		$town->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($address, $town));
		$state = new \App\UI\State($this->page, 'state', 'State', $customer->state ?? '');
		$state->setRequired();
		$zip = new \PHPFUI\Input\Zip($this->page, 'zip', 'Zip Code', $customer->zip);
		$zip->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($state, $zip));
		$email = new \PHPFUI\Input\Email('email', 'Your email address', $customer->email);
		$email->setRequired();
		$fieldSet->add($email);
		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit());

		return $form;
		}
	}
