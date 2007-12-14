<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_irfaq_q=1');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_irfaq_cat=1');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_irfaq_expert=1');

//listing FAQ in Web->Page view
$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_irfaq_q'][0] = array(
	'fList' => 'q,a,q_from,expert',
	'icon' => TRUE
);

// TCEmain hooks for managing related entries
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['irfaq'] = 'EXT:irfaq/class.tx_irfaq_tcemain.php:tx_irfaq_tcemain';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['irfaq'] = 'EXT:irfaq/class.tx_irfaq_tcemain.php:tx_irfaq_tcemain';

// Hook to comments for comments closing
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments/pi1/class.tx_comments_pi1.php']['tx_irfaq_q'] = 'EXT:irfaq/class.tx_irfaq_comments_hooks.php:tx_irfaq_comments_hooks->irfaqHook';

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['irfaq'] = 'EXT:irfaq/class.tx_irfaq_eID.php';
?>