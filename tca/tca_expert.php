<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_irfaq_expert'] = Array (
	'ctrl' => $TCA['tx_irfaq_expert']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'name,email,url'
	),
	'feInterface' => $TCA['tx_irfaq_expert']['feInterface'],
	'columns' => Array (
		'name' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_expert.name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'email' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'checkbox' => '',
				'eval' => 'nospace',
			)
		),
		'url' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_expert.url',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'checkbox' => '',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'name;;;;1-1-1, email, url')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);
?>