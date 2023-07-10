<?php

namespace App\UI;

class PaginatedTable extends \PHPFUI\SortableTable
	{
	private bool $alwaysShowPaginator = true;

	private bool $continuousScroll = false;

	private string $csvDownloadName = '';

	private ?\PHPFUI\ORM\ArrayCursor $cursor = null;

	private array $customColumns = [];

	private array $fieldTable = [];

	private bool $filled = false;

	private int $limitNumber = 25;

	private array $operators = [];

	private int $pageNumber = 0;

	private array $parameters = [];

	private array $searchColumns = [];

	private bool $showLimitSelect = true;

	private string $sort = '';

	private string $sortColumn = '';

	public function __construct(protected ?\PHPFUI\Interfaces\Page $page, private readonly \PHPFUI\ORM\Table $dataTable)
		{
		parent::__construct();

		$this->parameters = $this->getParsedParameters();
		$this->pageNumber = (int)($this->parameters['p'] ?? $this->dataTable->getPage());
		$this->limitNumber = (int)($this->parameters['l'] ?? $this->dataTable->getLimit() ?? 20);

		// load up appropriate operators

		$this->operators[] = new \PHPFUI\ORM\Operator\GreaterThanEqual();
		$this->operators[] = new \PHPFUI\ORM\Operator\LessThanEqual();
		$this->operators[] = new \PHPFUI\ORM\Operator\NotEqual();
		$this->operators[] = new \PHPFUI\ORM\Operator\Equal();
		$this->operators[] = new \PHPFUI\ORM\Operator\GreaterThan();
		$this->operators[] = new \PHPFUI\ORM\Operator\LessThan();

		// make fields in this table explicit for searching
		$table = $this->dataTable->getTableName();

		foreach (\array_keys($this->dataTable->getFields()) as $field)
			{
			$this->fieldTable[$field] = $table;
			}
		}

	public function addCustomColumn(string $field, callable $callback, array $additionalData = []) : static
		{
		$this->customColumns[$field] = [$callback, $additionalData];

		return $this;
		}

	public function addRow(array $row, array $colspans = []) : static
		{
		if ($this->searchColumns)
			{
			parent::addRow($this->searchColumns, $colspans);
			$this->searchColumns = [];
			}

		parent::addRow($row, $colspans);

		return $this;
		}

	public function alwaysShowPaginator(bool $alwaysShowPaginator = true) : static
		{
		$this->alwaysShowPaginator = $alwaysShowPaginator;

		return $this;
		}

	/**
	 * @return \PHPFUI\ORM\ArrayCursor a cached cursor with limits and ordering applied
	 */
	public function getArrayCursor(bool $limited = true) : \PHPFUI\ORM\ArrayCursor
		{
		if ($this->cursor)
			{
			return $this->cursor;
			}

		$this->sortColumn = $this->parameters['c'] ?? $this->sortColumn;
		$this->sort = $this->parameters['s'] ?? $this->sort;

		if ($limited)
			{
			$this->dataTable->setLimit($this->limitNumber, $this->pageNumber);
			}

// possible pagination optimization
//SELECT * FROM people
//    INNER JOIN (
//      -- Paginate the narrow subquery instead of the entire table
//      SELECT id FROM people ORDER BY first_name, id LIMIT 10 OFFSET 450000
//    ) AS tmp USING (id)
//ORDER BY
//  first_name, id

		if ($this->sortColumn)
			{
			$this->setSortedColumnOrder($this->sortColumn, $this->sort);
			$this->dataTable->setOrderBy($this->sortColumn, $this->sort);

			foreach ($this->dataTable->getPrimaryKeys() as $field)
				{
				$this->dataTable->addOrderBy($field, $this->sort);
				}
			}

		$this->cursor = $this->getRawArrayCursor();

		return $this->cursor;
		}

	public function getDownloadButtonToolTip() : \PHPFUI\ToolTip
		{
		$this->parameters['downloadCSV'] = 1;
		$download = new \PHPFUI\Button('CSV', $this->getUrl());
		$download->addClass('small');

		return new \PHPFUI\ToolTip($download, 'Download currently selected records in CSV format');
		}

	public function getPage() : \PHPFUI\Interfaces\Page
		{
		return $this->page;
		}

	/**
	 * @return \PHPFUI\ORM\ArrayCursor an uncached cursor without any limits or ordering
	 */
	public function getRawArrayCursor() : \PHPFUI\ORM\ArrayCursor
		{
		$searchCondition = new \PHPFUI\ORM\Condition();

		foreach ($_GET as $name => $value)
			{
			if (\strlen((string)$value) && \str_starts_with($name, 's_'))
				{
				$fieldName = \substr($name, 2);

				if (isset($this->fieldTable[$fieldName]))
					{
					$fieldName = "{$this->fieldTable[$fieldName]}.{$fieldName}";
					}
				$parts = \explode('|', (string)$value);

				foreach ($parts as $part)
					{
					$operator = $this->getOperator($part);
					$searchCondition->and($fieldName, $part, $operator);
					}
				}
			}

		if (\count($searchCondition))
			{
			$whereCondition = $this->dataTable->getWhereCondition();

			if (\count($whereCondition))
				{
				$searchCondition->and($whereCondition);
				}
			$this->dataTable->setWhere($searchCondition);
			}

		return $this->dataTable->getArrayCursor();
		}

	public function getUrl(?array $parameters = null) : string
		{
		return $this->getBaseUrl() . '?' . \http_build_query($parameters ?? $this->parameters);
		}

	public function setContinuousScroll(bool $continuousScroll = true) : static
		{
		$this->continuousScroll = $continuousScroll;

		return $this;
		}

	public function setDownloadName(string $csvDownloadName) : static
		{
		$this->csvDownloadName = $csvDownloadName;

		return $this;
		}

	/**
	 * Specify column headers
	 *
	 * @param array $headers if the key is a string, then use it as a column name, and use the value for the title. Otherwise value is the field name and the title is capitalSplit from the value.
	 */
	public function setHeaders(array $headers) : static
		{

		$newHeaders = [];

		foreach ($headers as $key => $value)
			{
			if (\is_int($key))
				{
				$key = $value;
				$value = $this->dataTable->translate($value);
				}
			elseif (\is_string($key) && $value instanceof \PHPFUI\Input\Input)
				{
				$value = $this->dataTable->translate($key);
				}
			$newHeaders[$key] = $value;
			}

		parent::setHeaders($newHeaders);

		return $this;
		}

	/**
	 * Specify which columns are sortable.
	 *
	 * @param array $inputs if the key is a string, then value must be a PHPFUI\Input. If value is a string, then it is assumed a field name.
	 */
	public function setSearchColumns(array $inputs) : static
		{
		$this->searchColumns = [];

		foreach ($inputs as $key => $value)
			{
			if (\is_string($key) && $value instanceof \PHPFUI\Input\Input)
				{
				$this->searchColumns[$key] = $value;
				}
			elseif (\is_string($key))
				{
				$this->searchColumns[$key] = new \App\UI\SearchField('s_' . $key, $this->parameters['s_' . $key] ?? '');
				}
			else
				{
				$this->searchColumns[$value] = new \App\UI\SearchField('s_' . $value, $this->parameters['s_' . $value] ?? '');
				}
			}

		return $this;
		}

	public function setSortColumn(string $column) : static
		{
		$this->sortColumn = $column;

		return $this;
		}

	public function setSortDirection(string $direction) : static
		{
		$this->sort = $direction;

		return $this;
		}

	public function showLimitSelect(bool $showLimitSelect = true) : static
		{
		$this->showLimitSelect = $showLimitSelect;

		return $this;
		}

	protected function getEnd() : string
		{
		return parent::getEnd() . $this->getPagination();
		}

	protected function getOperator(string &$value) : \PHPFUI\ORM\Operator
		{
		foreach ($this->operators as $operator)
			{
			$operatorString = $operator->getOperatorString();

			if (\str_starts_with($value, (string)$operatorString))
				{
				$value = \substr($value, \strlen((string)$operatorString));

				return $operator;
				}
			}

		// did not find anything, do a like or not like
		if ('!' === $value[0])
			{
			$value = \substr($value, 1);
			$operator = new \PHPFUI\ORM\Operator\NotLike();
			}
		else
			{
			$operator = new \PHPFUI\ORM\Operator\Like();
			}

		$value = "%{$value}%";

		return $operator;
		}

	protected function getPagination() : string
		{
		if ($this->continuousScroll)
			{
			return '';
			}

		$numberFound = $this->cursor->total();
		// set page to magic value for replacement
		$this->parameters['p'] = 'PAGE';

		$lastPage = (int)(($numberFound - 1) / $this->limitNumber) + 1;

		if ($this->pageNumber > $lastPage)
			{
			$this->pageNumber = $lastPage - 1;

			if (! $this->pageNumber)
				{
				unset($this->parameters['p']);
				}

			\header('location: ' . $this->getUrl());

			exit;
			}

		$paginator = new \PHPFUI\Pagination($this->pageNumber, $lastPage, $this->getUrl());

		// @phpstan-ignore-next-line
		if (! $this->continuousScroll)
			{
			$paginator->alwaysShow($this->alwaysShowPaginator);
			}
		$paginator->center();
		$limitSelect = new \PHPFUI\Input\LimitSelect($this->page, $this->limitNumber);
		$limitSelect->addClass('float-right');

		$div = new \PHPFUI\GridX();

		if ($this->allowCSVDownload())
			{
			$downloadCell = new \PHPFUI\Cell();
			$downloadCell->add($this->getDownloadButtonToolTip());
			$downloadCell->addClass('auto');
			$div->add($downloadCell);
			}

		$paginatorCell = new \PHPFUI\Cell();
		$paginatorCell->add($paginator);
		$div->add($paginatorCell);

		$limitSelectCell = new \PHPFUI\Cell();
		$limitSelectCell->addClass('auto');

		// @phpstan-ignore-next-line
		if (! $this->continuousScroll && $this->showLimitSelect)
			{
			$limitSelectCell->add($limitSelect);
			}
		$div->add($limitSelectCell);

		return $div;
		}

	protected function getStart() : string
		{
		if (! empty($this->parameters['downloadCSV']) && $this->allowCSVDownload())
			{
			unset($this->parameters['downloadCSV']);

			if ($this->csvDownloadName)
				{
				$fileName = $this->csvDownloadName;
				}
			else
				{
				$parts = \explode('\\', $this->dataTable::class);
				$fileName = \array_pop($parts) . '_' . \App\Tools\Date::todayString() . '.csv';
				}

			$csvWriter = new \App\Tools\CSVWriter($fileName);
			$csvWriter->addHeaderRow();

			$this->dataTable->setLimit(0);

			foreach ($this->getArrayCursor(false) as $row)
				{
				unset($row['password'], $row['loginAttempts']);
				$csvWriter->outputRow($row);
				}
			unset($csvWriter);
			\header('location: ' . $this->getUrl());

			exit;
			}

		if (isset($this->parameters['cs']))
			{
			$this->searchColumns = [];
			}

		$this->fillTable();

		if ($this->continuousScroll)
			{
			$last = $this->dataTable->getOffset() + $this->dataTable->getLimit();

			$cursor = $this->getArrayCursor();

			if ($cursor->total() <= $last)
				{
				$query = '';
				$footerText = 'End of Data, ' . $cursor->total() . ' Records';
				}
			else
				{
				// local copy of parameters so we can change things
				$parameters = $this->parameters;
				$parameters['cs'] = 1;
				$parameters['p'] = $this->pageNumber + 1;	// we want the next page
				$query = $this->getUrl($parameters);
				$footerText = 'Loading ...';
				}

			$temp = new \PHPFUI\HTML5Element('div');
			$footerId = $temp->getId();

			$footerSpan = new \PHPFUI\HTML5Element('span');
			$footerSpan->add($footerText);
			$footerSpanId = $footerSpan->getId();

			$hidden = new \PHPFUI\Input\Hidden('query', $query);
			$hiddenId = $hidden->getId();
			$this->page->addJavaScript('var urls=new Set();let options={root:null,threshold:0.1}');
			$javaScript = "var intersectionObserver=new IntersectionObserver(entries=>{if(entries.some(entry=>entry.intersectionRatio>0)){let hidden=$('#{$hiddenId}');
if(hidden.length==0)return;let url=hidden.val();if(urls.has(url))return;if(url.length){urls.add(url);$.ajax(url,{success:(function(data,status,arg3){
var footer=$('#{$footerId}').parent();footer.prev().append(data.rows);hidden.val(data.query);$('#{$footerSpanId}').html(data.footerText);})});}}},options);
intersectionObserver.observe(document.querySelector('#{$footerId}'));";
			$this->page->addJavaScript($javaScript);

			$this->addFooterAttribute('id', [$footerId]);

			$this->addFooter(\array_key_first($this->headers), $footerSpan . $hidden);

			if ($this->allowCSVDownload())
				{
				$this->addFooter(\array_key_last($this->headers), $this->getDownloadButtonToolTip());
				}

			if (isset($this->parameters['cs']))
				{
				$jsonData = ['rows' => $this->outputBodyRows(), 'footerText' => $footerText, 'query' => $query];
				$this->page->setRawResponse(\json_encode($jsonData));
				}
			}

		return parent::getStart();
		}

	private function allowCSVDownload() : bool
		{
		$parts = \explode('\\', $this->dataTable::class);
		$name = \array_pop($parts);
		$permission = "Download {$name} CSV File";

		// @phpstan-ignore-next-line
		return $this->page->isAuthorized($permission);
		}

	private function fillTable() : void
		{
		if ($this->filled)
			{
			return;
			}
		$this->filled = true;

		$count = 0;

		foreach ($this->getArrayCursor() as $row)
			{
			++$count;
			$displayRow = $row;

			foreach ($row as $field => $value)
				{
				$displayRow[$field] = $this->dataTable->displayTransform($field, $value);
				}

			foreach ($this->customColumns as $field => $callbackInfo)
				{
				$displayRow[$field] = $callbackInfo[0]($row, $callbackInfo[1]);
				}
			$this->addRow($displayRow);
			}

		if (! $count)
			{
			$this->addRow([]);
			}
		}
	}
