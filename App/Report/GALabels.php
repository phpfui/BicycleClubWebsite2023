<?php

namespace App\Report;

class GALabels
	{
	/**
	 * @var array<int> $parameters
	 */
	private array $parameters;

	/**
	 * @param array<int,bool> $eventBools
	 */
	public function __construct(array $eventBools, private ?string $labelStock = '5960')
		{
		foreach ($eventBools as $gaEventId => $active)
			{
			if (! $active)
				{
				unset($this->parameters[$gaEventId]);
				}
			}
		$this->parameters = \array_keys($this->parameters);
		}

	public function generate() : void
		{
		$gaRiderTable = new \App\Table\GaRider();
		$riders = $gaRiderTable->getForEvents($this->parameters);
		$pdf = new \PDF_Label($this->labelStock ?? '5960');
		$count = 0;

		foreach ($riders as $rider)
			{
			if ($rider->zip && $rider->address)
				{
				++$count;
				$label = "\n" . $rider->firstName . ' ' . $rider->lastName . "\n";
				$label .= $rider->address . "\n";
				$label .= $rider->town . ', ' . $rider->state . ' ' . $rider->zip;
				$label = \App\Tools\TextHelper::unhtmlentities($label);
				$pdf->Add_PDF_Label($label);
				}
			}
		$pdf->Add_PDF_Label("{$count} labels printed\nOn " . \gmdate('m/d/Y'));
		$now = \date('Y-m-d');
		$pdf->Output("GALabels-{$now}.pdf", 'I');
		}

	/**
	 * @return array<int>
	 */
	public function getEvents() : array
		{
		return $this->parameters;
		}
	}
