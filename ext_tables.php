<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_irfaq_q');

$TCA['tx_irfaq_q'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_q',
		'label' => 'q',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dividers2tabs' => true,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca/tca_q.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icon_tx_irfaq_q.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'hidden, fe_group, q, cat, a, related',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_irfaq_cat');

$TCA['tx_irfaq_cat'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_cat',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca/tca_cat.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icon_tx_irfaq_cat.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'hidden, fe_group, title',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_irfaq_expert');

$TCA['tx_irfaq_expert'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq_expert',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca/tca_expert.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icon_tx_irfaq_expert.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'name, email, url',
	)
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';

//adding sysfolder icon
t3lib_div::loadTCA('pages');
$TCA['pages']['columns']['module']['config']['items'][$_EXTKEY]['0'] = 'LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.sysfolder';
$TCA['pages']['columns']['module']['config']['items'][$_EXTKEY]['1'] = $_EXTKEY;

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/ts/', 'IRFAQ default TS');
//t3lib_extMgm::addStaticFile($_EXTKEY, 'static/css/', 'Default CSS-styles (obsolete)');
t3lib_extMgm::addPlugin(array('LLL:EXT:irfaq/lang/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1'), 'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:irfaq/flexform/flexform_ds.xml');

if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_irfaq_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY) . 'pi1/class.tx_irfaq_pi1_wizicon.php';
		//adding sysfolder icon
	if(version_compare(TYPO3_version, '4.5.0', '<')) {
		$ICON_TYPES[$_EXTKEY] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icon_tx_irfaq_sysfolder.gif');
	}
	else {
		t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-irfaq', t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icon_tx_irfaq_sysfolder.gif');
	}
}
?>
