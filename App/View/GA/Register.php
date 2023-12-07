<?php

namespace App\View\GA;

class Register implements \Stringable
	{
	private readonly \App\Model\Cart $cartModel;

	public function __construct(private readonly \App\View\Page $page, private \App\Record\GaEvent $gaEvent = new \App\Record\GaEvent())
		{
		$this->cartModel = new \App\Model\Cart();

		$post = $_POST;

		if (\App\Model\Session::checkCSRF() && ! empty($post['submit']))
			{
			if ('Add Rider' == $post['submit'])
				{
				$rider = $post;
				$rider['gaEventId'] = $this->gaEvent->gaEventId;
				unset($rider['gaRiderId'], $rider['pricePaid']);

				$rider['signedUpOn'] = \date('Y-m-d H:i:s');
				unset($rider['prize']);
				$id = $this->cartModel->addGaRider($rider);

				if (\is_array($post['gaOptionId']))
					{
					$post['gaRiderId'] = $id;
					$gaRiderSelectionTable = new \App\Table\GaRiderSelection();
					$gaRiderSelectionTable->updateFromPost($post);
					}

				$this->page->redirect();

				return;
				}
			}

		if ($this->gaEvent->gaEventId > 0 && ! empty($_GET['add']))
			{
			$memberTable = new \App\Table\Member();
			$membership = $memberTable->getMembership($_GET['add']);
			$membership['gaEventId'] = $this->gaEvent->gaEventId;

			if (! empty($membership['cellPhone']))
				{
				$membership['phone'] = $membership['cellPhone'];
				}
			$membership['contact'] = $membership['emergencyContact'];
			$membership['contactPhone'] = $membership['emergencyPhone'];
			$membership['signedUpOn'] = \date('Y-m-d H:i:s');
			$riderId = $this->cartModel->addGaRider($membership);
			$this->page->redirect('/GA/updateRider/' . $riderId);

			return;
			}
		}

	public function __toString() : string
		{
		$signedInMember = \App\Model\Session::getSignedInMember();
		$container = new \PHPFUI\Container();

		if ($signedInMember)
			{
			$members = \App\Table\Member::membersInMembership((int)$signedInMember['membershipId']);
			$fieldSet = new \PHPFUI\FieldSet('Quick Add Riders In Your Membership');

			foreach ($members as $member)
				{
				$row = new \PHPFUI\GridX();
				$row->add(new \PHPFUI\Button('Add ' . $member['firstName'] . ' ' . $member['lastName'], "/GA/signUp/{$this->gaEvent->gaEventId}?add={$member['memberId']}"));
				$fieldSet->add($row);
				}
			$container->add($fieldSet);
			}
		$this->cartModel->compute(0);

		if ($this->cartModel->getCount())
			{
			$addRider = new \PHPFUI\Button('Add Another Rider');
			}
		else
			{
			$addRider = new \PHPFUI\Button('Add Rider');
			}
		$addRider->addClass('warning');
		$this->addRiderModal($addRider);
		$container->add($addRider);
		$cartView = new \App\View\Store\Cart($this->page, $this->cartModel);
		$cart = $cartView->show(new \PHPFUI\Form($this->page), true);

		if ($this->cartModel->getItems())
			{
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$checkout = new \PHPFUI\Submit('Check Out');
			$checkout->addClass('success');
			$buttonGroup->addButton($checkout);

			if ($this->gaEvent->allowShopping)
				{
				$continueShopping = new \PHPFUI\Button('Continue Shopping', '/Store/shop');
				$buttonGroup->addButton($continueShopping);
				}
			$cart->add($buttonGroup);
			$container->add($cart);
			}
		elseif ($this->gaEvent->allowShopping)
			{
			$container->add($cartView->showEmpty($this->cartModel->getCustomerNumber()));
			}

		return (string)$container;
		}

	protected function addRiderModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$view = new \App\View\GA\Rider($this->page);
		$rider = new \App\Record\GaRider();
		$rider->gaEvent = $this->gaEvent;
		$modalForm->add($view->getEditFields($rider));
		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Rider')));
		$modal->add($modalForm);
		}
	}
