<?php

########################################################################
# Extension Manager/Repository config file for ext: "irfaq"
#
# Auto generated 01-02-2008 12:51
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Modern FAQ',
	'description' => 'FAQ frontend plugin with dynamic or static view which will merge and improve functionality of EXT:faq and EXT:faq_plus into a modern look',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.0.1',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Netcreators',
	'author_email' => 'extensions@netcreators.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.6.2-4.2.0',
			'php' => '4.1.2-6.0.2',
			'cms' => '',
		),
		'conflicts' => array(
			'comments' => '0.0.0-1.2.999'
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:30:{s:9:"ChangeLog";s:4:"b3ce";s:33:"class.tx_irfaq_comments_hooks.php";s:4:"3628";s:26:"class.tx_irfaq_tcemain.php";s:4:"9f71";s:12:"ext_icon.gif";s:4:"fc45";s:17:"ext_localconf.php";s:4:"33b2";s:14:"ext_tables.php";s:4:"8243";s:14:"ext_tables.sql";s:4:"4df4";s:14:"doc/manual.sxw";s:4:"8b8f";s:24:"flexform/flexform_ds.xml";s:4:"9bd9";s:18:"lang/locallang.xml";s:4:"50d8";s:21:"lang/locallang_db.xml";s:4:"5cf9";s:26:"pi1/class.tx_irfaq_pi1.php";s:4:"f942";s:34:"pi1/class.tx_irfaq_pi1_wizicon.php";s:4:"3ea6";s:17:"pi1/locallang.xml";s:4:"6847";s:14:"res/ce_wiz.gif";s:4:"02b6";s:25:"res/icon_tx_irfaq_cat.gif";s:4:"cce6";s:28:"res/icon_tx_irfaq_expert.gif";s:4:"cdfb";s:23:"res/icon_tx_irfaq_q.gif";s:4:"fc45";s:31:"res/icon_tx_irfaq_sysfolder.gif";s:4:"8f2c";s:14:"res/irfaq.tmpl";s:4:"5a3d";s:13:"res/minus.gif";s:4:"769b";s:12:"res/plus.gif";s:4:"29d0";s:14:"res/styles.css";s:4:"8131";s:16:"res/toggleFaq.js";s:4:"8a40";s:20:"static/css/setup.txt";s:4:"9800";s:23:"static/ts/constants.txt";s:4:"080e";s:19:"static/ts/setup.txt";s:4:"261d";s:15:"tca/tca_cat.php";s:4:"26da";s:18:"tca/tca_expert.php";s:4:"f4a8";s:13:"tca/tca_q.php";s:4:"f88f";}',
	'suggests' => array(
	),
);

?>