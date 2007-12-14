<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_irfaq_q'] = Array (
	'ctrl' => $TCA['tx_irfaq_q']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,fe_group,q,cat,a,related,faq_files'
	),
	'feInterface' => $TCA['tx_irfaq_q']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'fe_group' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'q' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.q',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => 'required,trim',
			)
		),
		'q_from' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.q_from',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'cat' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.category',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_irfaq_cat',
				'size' => 3,
				'minitems' => 0,
				'maxitems' => 5,
				'MM' => 'tx_irfaq_q_cat_mm',
			)
		),
		'a' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.a',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_ttc.php:bodytext.W.RTE',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			),
			'softref' => 'typolink_tag,images,email[subst],url'
		),
		'expert' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.expert',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0)
				),
				'foreign_table' => 'tx_irfaq_expert'
			)
		),
		/*
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => '1000',
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),*/
		'related' => array(
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.related',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_irfaq_q',
				'prepand_tname' => false,	// specify explicitly because we depend on it!
				'size' => 3,
				'autoSizeMax' => 10,
				'multiple' => false,
				'maxitems' => 1000,	// looks reasonable
				'wizards' => array(
					'_PADDING' => 2,
					'edit' => array(
						'type' => 'popup',
						'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.related_edit',
						'script' => 'wizard_edit.php',
						'icon' => 'edit2.gif',
						'popup_onlyOpenIfSelected' => 1,
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					),
					'add' => array(
						'type' => 'script',
						'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.related_new',
						'icon' => 'add.gif',
						'params' => array(
							'table'=>'tx_irfaq_q',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'append'
						),
						'script' => 'wizard_add.php',
					),
				),
			),
		),
		'related_links' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.related_links',
			'config' => array(
				'type' => 'text',
				'wrap' => 'off',
			),
		),
		/*'faq_files' => array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:cms/locallang_ttc.php:media',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	// Must be empty for disallowed to work.
				'disallowed' => 'php,php3',
				'max_size' => '10000',
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '3',
				'autoSizeMax' => '10',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),*/
		'enable_ratings' => array(
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.enable_ratings',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'items'    => array(
					array('', '')
				),
				'default'  => '1'
			)
		),
		'disable_comments' => array(
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.disable_comments',
			'exclude' => 1,
			'displayCond' => 'EXT:comments:LOADED:true',
			'config' => array(
				'type' => 'check',
				'items' => array(
					array('', '')
				),
				'default'  => '0'
			)
		),
		'comments_closetime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.comments_closetime',
			'displayCond' => 'EXT:comments:LOADED:true',
			'config' => array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, q, a;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css];,--div--;Details, q_from, expert, related, related_links, faq_files, cat, --div--;LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q.extra_tab, enable_ratings;;;;2-2-2, disable_comments, comments_closetime')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'fe_group')
	)
);
?>