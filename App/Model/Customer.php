<?php

namespace App\Model;

class Customer
	{
	public function getNewNumber() : int
		{
		$customer = new \App\Record\Customer();
		$customerNumber = 0 - $customer->insert();
		$cookie = new \App\Tools\Cookies();
		\App\Model\Session::setCustomerNumber($customerNumber);
		$cookie->set('customerNumber', (string)$customerNumber, true);

		return $customerNumber;
		}

	public function getNumber() : int
		{
		$cookie = new \App\Tools\Cookies();
		$memberId = \App\Model\Session::signedInMemberId();

		if ($memberId)
			{
			\App\Model\Session::setCustomerNumber($memberId);
			}
		elseif ($memberId = \App\Model\Session::getCustomerNumber())
			{
			}
		elseif ($memberId = (int)$cookie->get('customerNumber'))
			{
			\App\Model\Session::setCustomerNumber($memberId);
			}

		if (! $memberId)
			{
			$memberId = $this->getNewNumber();
			}
		else
			{
			$cookie->set('customerNumber', (string)$memberId, true);
			}

		return $memberId;
		}

	public function read(int $id) : \App\DB\MemberCustomer
		{
		return new \App\DB\MemberCustomer($id);
		}

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function save(array $parameters) : string
		{
		$customerNumber = $parameters['customerId'];

		unset($parameters['customerId']);

		if ($customerNumber < 0)
			{
			$customer = new \App\Record\Customer(0 - $customerNumber);
			$customer->setFrom($parameters);
			$customer->update();
			}
		else  // new customer
			{
			$customer = new \App\Record\Customer();
			$customer->setFrom($parameters);
			$customerNumber = 0 - $customer->insert();
			}
		$cookie = new \App\Tools\Cookies();
		$cookie->set('customerNumber', $customerNumber, true);
		\App\Model\Session::setCustomerNumber($customerNumber);

		return $customerNumber;
		}
	}
