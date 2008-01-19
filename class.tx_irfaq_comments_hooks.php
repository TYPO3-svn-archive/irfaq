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
* class.tx_irfaq_comments_hooks.php
*
* Commenting system hooks.
*
* $Id: $
*
* @author Dmitry Dulepov <dmitry@typo3.org>
*/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

/**
 * Commenting system hook. Hook receives the following in <code>$params</code>:
 * <ul>
 * 	<li><code>uid</code> - uid of the item</li>
 * </ul>
 * Hook returns time when commenting should be stopped. Thus 0 disables commenting and
 * <code>PHP_INT_MAX</code> means there is no limit.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 */
class tx_irfaq_comments_hooks {
	/**
	 * Provides comment closing date to comments extension for tt_news items
	 *
	 * @param	array	$params	Parameters to the function
	 * @param	tx_comments_pi1	$pObj	Parent object
	 */
	function irfaqHook(&$params, &$pObj) {
		return $this->getCloseTime('tx_irfaq_q', $params['uid'], $pObj->cObj);
	}

	/**
	 * Gets closing time from a record
	 *
	 * @param	string	$table	Table name
	 * @param	int	$uid	UID of the record
	 * @param	tslib_cObj	$cObj	COBJECT
	 * @return	int	Closing timestamp
	 */
	function getCloseTime($table, $uid, &$cObj) {
		$result = 0;
		$recs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('disable_comments,comments_closetime',
					$table, 'uid=' . intval($uid) . $cObj->enableFields($table));
		if (count($recs)) {
			$result = $recs[0]['disable_comments'] ? 0 :
							($recs[0]['comments_closetime'] ? $recs[0]['comments_closetime'] : PHP_INT_MAX);
		}
		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_comments_hooks.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_comments_hooks.php']);
}

?>