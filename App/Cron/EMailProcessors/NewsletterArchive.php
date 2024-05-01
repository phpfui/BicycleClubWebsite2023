<?php

namespace App\Cron\EMailProcessors;

include PROJECT_ROOT . '/DI\functions.php';

class NewsletterArchive
	{
	private readonly \App\Model\NewsletterFiles $model;

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->model = new \App\Model\NewsletterFiles(new \App\Record\Newsletter());
		$this->settingTable = new \App\Table\Setting();
		}

	public function process(\ZBateson\MailMimeParser\Message $message) : bool
		{
		// if sent to class name, then archive as newsletter
		if ($this->settingTable->value('newsletterEmail') != \App\Model\Member::cleanEmail($message->getHeaderValue('from')))
			{
			return false;
			}

		$html = $message->getHtmlContent();
		$offset = 0;
		$sizes = [];

		while ($offset = \strpos($html, 'font-size:', $offset))
			{
			$semiOffset = \stripos($html, 'px', $offset);
			$size = \substr($html, $offset, $semiOffset - $offset);
			$parts = \explode(':', $size);
			$sizes[$parts[1]] = 1;
			$offset = $semiOffset;
			}

		// double font sizes
		\krsort($sizes);
		$targets = [];
		$newSizes = [];

		foreach ($sizes as $size => $set)
			{
			$targets[] = 'font-size:' . $size . 'px';
			$newSizes[] = 'font-size:' . ((int)$size * 2) . 'px';
			}
		$newHtml = \str_replace($targets, $newSizes, $html);

		// set image widths to 100%
		$dom = new \voku\helper\HtmlDomParser($newHtml);
		$images = $dom->find('img');

		foreach ($images as $image)
			{
			$image->removeAttribute('width');
			$image->setAttribute('max-width', '100%');
			}

		$mpdf = new \Mpdf\Mpdf();

		$date = $dateAdded = \App\Tools\Date::todayString();

		$mpdf->SetTitle($this->settingTable->value('newsletterName') . ' ' . $dateAdded);
		$editor = new \App\Model\MemberPicker('Newsletter Editor');
		$member = $editor->getSavedMember();
		$mpdf->SetAuthor($member['firstName'] . ' ' . $member['lastName']);
		$mpdf->SetCreator('webmaster@' . \emailServerName());

		$mpdf->WriteHTML("{$dom}");

		$tempFile = new \App\Tools\TempFile();
		$mpdf->Output($tempFile, 'F');

		$newsletter = new \App\Record\Newsletter();
		$newsletter->date = $date;
		$newsletter->dateAdded = $dateAdded;
		$newsletter->size = \filesize($tempFile);
		$newsletter->html = $html;
		$newsletterId = $newsletter->insert();
		\copy($tempFile, $this->model->get($newsletterId . '.pdf'));

		return true;
		}
	}
