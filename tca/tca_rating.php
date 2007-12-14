<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_irfaq_rating'] = array(
	'ctrl' => $TCA['tx_irfaq_rating']['ctrl'],
	'columns' => array(
		'faq' => array(
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_rating.faq',
			'exclude' => 1,
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_irfaq_q',
				'prepend_tname' => false,
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'rating' => array(
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_rating.rating',
			'exclude' => 1,
			'config' => Array (
				'type' => 'input',
				'eval' => 'int,trim',
				'size' => 5,
				'default' => 0,
			)

		),
	),
	'types' => array(
		'0' => array('showitem' => 'faq;;;;1-1-1, rating')
	)
);
?>