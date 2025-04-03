<?php

$config = new PhpCsFixer\Config();

return $config
	->setParallelConfig(new PhpCsFixer\Runner\Parallel\ParallelConfig(4, 20))
	->setRiskyAllowed(true)
	->setRules([
		'nullable_type_declaration_for_default_null_value' => true,
	]);
