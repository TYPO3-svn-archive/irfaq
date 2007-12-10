<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_irfaq_q');

$TCA['tx_irfaq_q'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q',
		'label' => 'q',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dividers2tabs' => true,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_irfaq_q.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, fe_group, q, cat, a, related',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_irfaq_cat');

$TCA['tx_irfaq_cat'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_cat',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_irfaq_cat.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, fe_group, title',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_irfaq_expert');

$TCA['tx_irfaq_expert'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_expert',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_irfaq_expert.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'name, email, url',
	)
);

$TCA['tx_irfaq_rating'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_rating',
		'label' => 'faq',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'faq',
		'delete' => 'deleted',
		'hideTable' => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
//		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_irfaq_q.gif',
	),
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

//adding sysfolder icon
t3lib_div::loadTCA('pages');
$TCA['pages']['columns']['module']['config']['items'][$_EXTKEY]['0'] = 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.sysfolder';
$TCA['pages']['columns']['module']['config']['items'][$_EXTKEY]['1'] = $_EXTKEY;

t3lib_extMgm::addPlugin(Array('LLL:EXT:irfaq/lang/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:irfaq/flexform_ds.xml');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/css/','Default CSS-styles');


if (TYPO3_MODE=='BE')
{
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_irfaq_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_irfaq_pi1_wizicon.php';
	//adding sysfolder icon
	$ICON_TYPES[$_EXTKEY] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_irfaq_sysfolder.gif');
}
?>