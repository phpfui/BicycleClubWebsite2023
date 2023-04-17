<?php

namespace App\View;

class Statistics implements \Stringable
	{
	public function __construct(private readonly \App\Model\Statistics $stats)
		{
		}

	public function __toString() : string
		{
		$memberTable = new \App\Table\Member();
		$membershipTable = new \App\Table\Membership();
		$countMembers = \count($memberTable);
		$countMemberships = \count($membershipTable);
		$output = "<p>There are {$countMembers} people in the database representing {$countMemberships} memberships.<p>";
		$countMembers = $memberTable->currentMemberCount();
		$countMemberships = $membershipTable->currentMembershipCount();
		$countSubscriptions = $membershipTable->currentSubscriptionCount();
		$subscriptionPercent = \number_format($countSubscriptions * 100 / ($countMemberships ?: 1), 2);
		$output .= "There are {$countMembers} current members in the database representing {$countMemberships} current memberships.<p>";
		$output .= "There are {$countSubscriptions} active subscriptions representing {$subscriptionPercent}% of memberships.<p>";
		$output .= (string)(new \PHPFUI\Header('Website usage', 3));
		$signupTable = new \PHPFUI\Table();
		$signupTable->setHeaders(['value' => 'Members Signed In',
			'key' => 'In the Past X Days', ]);
		$signupTable->addColumnAttribute('value', ['style' => 'text-align:center;']);
		$signupTable->addColumnAttribute('key', ['style' => 'text-align:center;']);
		$results = $this->stats->lastSignIns();
		\krsort($results);

		foreach ($results as $key => $value)
			{
			$signupTable->addRow(['key' => $key,
				'value' => $value, ]);
			}
		$output .= $signupTable;
		$output .= (string)(new \PHPFUI\Header('Ride Category Distributions', 3));
		$output .= (string)(new \PHPFUI\Header('(Members can list more than one category)', 6));
		$categoryDistributionTable = new \PHPFUI\Table();
		$categoryDistributionTable->setHeaders(['category' => 'Category',
			'count' => 'Riders', ]);
		$categoryDistributionTable->addColumnAttribute('category', ['style' => 'text-align:center;']);
		$categoryDistributionTable->addColumnAttribute('count', ['style' => 'text-align:center;']);
		$categoryTable = new \App\Table\Category();
		$results = $categoryTable->getDistributions();

		foreach ($results as $row)
			{
			$categoryDistributionTable->addRow($row->toArray());
			}
		$output .= $categoryDistributionTable;
		$output .= (string)(new \PHPFUI\Header('Membership Renewal Stats for the Last 12 Months', 3));
		$renewalTable = new \PHPFUI\Table();
		$renewalTable->setHeaders($headers = ['month' => 'Month',
			'current' => 'Current Members',
			'lapsed' => 'Lapsed',
			'joined' => 'Joined',
			'net' => 'Net Gain', ]);
		unset($headers['month']);

		foreach ($headers as $key => $value)
			{
			$renewalTable->addColumnAttribute($key, ['style' => 'text-align:center;']);
			}

		foreach ($this->stats->getRenewals() as $row)
			{
			$renewalTable->addRow($row);
			}
		$output .= $renewalTable;
		$output .= '<p>Membership attrition rate for the last 12 months ' . $this->stats->getAttrition();
		$output .= (string)(new \PHPFUI\Header('Membership Per Year', 3));
		$membershipTable = new \PHPFUI\Table();
		$membershipTable->setHeaders($headers = ['date' => 'Date',
			'count' => 'Count',
			'lapsed' => 'Lapsed',
			'rate' => 'Attrition Rate', ]);

		foreach ($headers as $key => $value)
			{
			$membershipTable->addColumnAttribute($key, ['style' => 'text-align:center;']);
			}

		foreach ($this->stats->getPerYear() as $row)
			{
			$membershipTable->addRow($row);
			}
		$output .= $membershipTable;

		return $output;
		}
	}
