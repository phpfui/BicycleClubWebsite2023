<?php

namespace App\Model;

class Event
	{
	public static function getActualPrice(\App\Record\Event $event) : float
		{
		$price = $event->price;

		if (\App\Model\Event::newMemberDiscountQualified($event))
			{
			$price -= $event->newMemberDiscount;
			}

		return $price;
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return array{Event: string, door: string, paypal: string, checks: string, free: string}
	 */
	public static function getEmailTypes() : array
		{
		return [
			'Event' => 'General Event',
			'door' => 'Pay At Door',
			'paypal' => 'Pay With PayPal',
			'checks' => 'Pay With Check',
			'free' => 'No Payment Required',
		];
		}

	public static function newMemberDiscountQualified(\App\Record\Event $event) : bool
		{
		// check for member discounts
		if ($event->membersOnly)
			{
			if (1 == $event->membersOnly)
				{
				// current member, see if they qualify for a discount
				$member = \App\Model\Session::signedInMemberRecord();

				if ($member->loaded() && (($member->membership->joined ?? '1000-01-01') >= $event->newMemberDate) && ($member->discountCount < $event->maxDiscounts))
					{
					return $event->newMemberDiscount > 0;
					}
				}
			else
				{
				// new member (either free or paid) they automatically get a discount
				return $event->newMemberDiscount > 0;
				}
			}

		return false;
		}
	}
