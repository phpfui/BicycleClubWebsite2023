<?php

namespace App\Migration;

class Migration_75 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Abandon Cart Setup';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$settingTable = new \App\Table\Setting();
		$settingTable->save('abandonCartDaysBack', '1,4');
		$clubAbbrev = $settingTable->value('clubAbbrev');
		$settingTable->save('abandonCartTitle', "Did you forget to pay for your recent {$clubAbbrev} order?");
		$settingTable->save('abandonCart', "Dear ~firstName~,<p>We noticed you did not pay for your recent order (see attached). Don't worry, we can help you pay with PayPal." .
					'<p>PayPal accepts major credit cards and does not require you to join or give them anything other than the normal credit card info you ' .
					"do when you buy anything on the web. PayPal takes security extremely seriously and has never had any issues, plus we don't see your credit card info.<p>" .
					"<a href='~paymentURL~'>Click on this link to pay with PayPal</a>. ~paymentMessage~<p>Of course, if you have any questions, please feel " .
					'free to reply to this email and we will be happy to answer any questions.<p>And thanks for your order!<p>' .
					'~storeManager_firstName~ ~storeManager_lastName~');

		return true;
		}
	}
