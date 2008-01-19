<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov (dmitry@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* class.tx_irfaq_eID.php
*
* Comment management script.
*
* $Id: class.tx_irfaq_eID.php 7093 2007-10-24 12:39:55Z liels_bugs $
*
* @author Dmitry Dulepov <dmitry@typo3.org>
*/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   60: class tx_irfaq_eID
 *   64:     function init()
 *  100:     function main()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
//require_once(PATH_site . 't3lib/class.t3lib_tcemain.php');

/**
 * Comment management script.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage tx_comments
 */
class tx_irfaq_eID {
	var $uid;
	var $pid;
	var $rating;
	var $command;

	function init() {
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init('default');
		$GLOBALS['LANG']->includeLLFile('EXT:irfaq/lang/locallang_eID.xml');

		tslib_eidtools::connectDB();

		// Sanity check
		$this->command = t3lib_div::_GET('cmd');
		if ($this->command != 'vote') {
			echo $GLOBALS['LANG']->getLL('wrong_cmd');
			exit;
		}
		$this->uid = t3lib_div::_GET('uid');
		if (!t3lib_div::testInt($this->uid)) {
			echo $GLOBALS['LANG']->getLL('bad_uid_value');
			exit;
		}
		$this->pid = t3lib_div::_GET('pid');
		if (!t3lib_div::testInt($this->pid)) {
			echo $GLOBALS['LANG']->getLL('bad_pid_value');
			exit;
		}
		$this->rating = t3lib_div::_GET('rating');
		if (!t3lib_div::testInt($this->rating)) {
			echo $GLOBALS['LANG']->getLL('bad_rating_value');
			exit;
		}
		$check = t3lib_div::_GET('chk');
		if (md5($this->uid . $this->pid . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']) != $check) {
			echo $GLOBALS['LANG']->getLL('wrong_check_value');
			exit;
		}
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'tx_irfaq_q', 'uid=' . $this->uid);
		if ($rows[0]['t'] == 0) {
			echo $GLOBALS['LANG']->getLL('faq_does_not_exist');
			exit;
		}
	}

	/**
	 * Main processing function of eID script
	 *
	 * @return	void
	 */
	function main() {
		switch ($this->command) {
			case 'vote':
				$this->updateVote();
				break;
		}
		// Clear cache. TCEmain requires $TCA for this, so we just do it ourselves.
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', 'page_id=' . $this->pid);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pagesection', 'page_id=' . $this->pid);
	}

	function updateVote() {
		// Update in the database
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_irfaq_rating', array(
				'crdate' => time(),
				'tstamp' => time(),
				'rating' => $this->rating,
				'faq' => $this->uid,
			));
		// Get new values
		list($row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('SUM(rating)/COUNT(rating) AS rating, COUNT(rating) as count', 'tx_irfaq_rating',
				'faq=' . $this->uid . ' AND deleted=0');
		// set new values to parent page
		echo '<html><head><title></title></head><body>';
		echo '<script type="text/javascript">';
		echo 'window.opener.tx_irfaq_setRating(' . $this->uid . ',' . $row['rating'] .
				',' . $row['count'] . ');window.close()';
		echo '</script></body></html>';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_eID.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_eID.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_irfaq_eID');
$SOBE->init();
$SOBE->main();
?>