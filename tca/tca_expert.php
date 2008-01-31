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
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_irfaq_expert',
				'foreign_table_where' => 'AND tx_irfaq_expert.uid=###REC_FIELD_l18n_parent### AND tx_irfaq_expert.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
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