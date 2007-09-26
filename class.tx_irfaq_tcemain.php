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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Handling of related records for IrFAQ.
 *
 * $Id: $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_irfaq_tcemain
 *   67:     function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, &$pObj)
 *   91:     function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, &$pObj)
 *  123:     function processCmdmap_preProcess($command, $table, $id, $value, &$pObj)
 *  144:     function processCmdmap_postProcess($command, $table, $id, $value, &$pObj)
 *  167:     function process_relatedItems($id, $oldItemList, $newItemList)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * A hook to TCEmain that processes related records.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage irfaq
 */
class tx_irfaq_tcemain {

	/** Saves original 'related' field before record update */
	var $saved_related_items = false;

	/**
	 * Saves original record's 'related' field
	 *
	 * @param	array		$incomingFieldArray	Field array
	 * @param	string		$table	Table
	 * @param	integer		$id	UID of the record or 'NEWxxx' string
	 * @param	t3lib_TCEmain		$pObj	Reference to TCEmain
	 * @return	void		Nothing
	 */
	function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, &$pObj) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_irfaq']['insideTCEmain']) {
			// If we were the source of this call, ignore it
			return;
		}
		// Process only if:
		//	- correct table
		//	- we are in update operation (=$id is integer)
		if ($table == 'tx_irfaq_q' && t3lib_div::testInt($id) && isset($incomingFieldArray['related'])) {
			$rec = t3lib_BEfunc::getRecord($table, $id, 'related');
			$this->saved_related_items = $rec['related'];
		}
	}

	/**
	 * Processes related records in tx_irfaq_q
	 *
	 * @param	string		$status	Status of the record ('new' or 'update'). Unused.
	 * @param	string		$table	Table name
	 * @param	mixed		$id	UID of the record or 'NEWxxx' string
	 * @param	array		$fieldArray	Added or updated fields
	 * @param	t3lib_TCEmain		$pObj	Reference to TCEmain
	 * @return	void		Nothing
	 */
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, &$pObj) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_irfaq']['insideTCEmain']) {
			// If we were the source of this call, ignore it
			return;
		}
		if ($table == 'tx_irfaq_q') {
			if ($status == 'new') {
				if ($fieldArray['related']) {
					$id = ($id{0} == '-' ? substr($id, 1) : $id);
					/* @var $pObj t3lib_TCEmain */
					$id = $pObj->substNEWwithIDs[$id];
					$this->process_relatedItems($id, '', $fieldArray['related']);
				}
			}
			elseif (isset($fieldArray['related']) && $this->saved_related_items !== false) {
				// Processing updates only if 'related' field was changed
				$this->process_relatedItems($id, $this->saved_related_items, $fieldArray['related']);
				$this->saved_related_items = false;
			}
		}
	}

	/**
	 * Saves related items for current record
	 *
	 * @param	string		$command	Command. We are interested only in 'delete'
	 * @param	string		$table	Table name. We work only if 'tx_irfaq_q'
	 * @param	int		$id	Record uid
	 * @param	mixed		$value	Unused
	 * @param	t3lib_TCEmain		$pObj	Reference to parent object
	 * @return	void		Nothing
	 */
	function processCmdmap_preProcess($command, $table, $id, $value, &$pObj) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_irfaq']['insideTCEmain']) {
			// If we were the source of this call, ignore it
			return;
		}
		if ($table == 'tx_irfaq_q' && $command == 'delete') {
			$rec = t3lib_BEfunc::getRecord($table, $id, 'related');
			$this->saved_related_items = $rec['related'];
		}
	}

	/**
	 * Removes all references to deleted FAQ record
	 *
	 * @param	string		$command	Command. We are interested only in 'delete'
	 * @param	string		$table	Table name. We work only if 'tx_irfaq_q'
	 * @param	int		$id	Record uid
	 * @param	mixed		$value	Unused
	 * @param	t3lib_TCEmain		$pObj	Reference to parent object
	 * @return	void		Nothing
	 */
	function processCmdmap_postProcess($command, $table, $id, $value, &$pObj) {
		if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_irfaq']['insideTCEmain']) {
			// If we were the source of this call, ignore it
			return;
		}
		/* @var $pObj t3lib_TCEmain */
		if ($table == 'tx_irfaq_q' && $command == 'delete') {
			if (count($pObj->errorLog) == 0) {
				// Remove all references to this item from other items
				$this->process_relatedItems($id, $this->saved_related_items, '');
			}
			$this->saved_related_items = false;
		}
	}

	/**
	 * Processes related items for current item.
	 *
	 * @param	integer		$id	UID of current record in tx_irfaq_q
	 * @param	string		$oldItemList	Comma-separated list of items (previous)
	 * @param	string		$newItemList	Comma-separated list of items (new)
	 * @return	void		Nothing
	 */
	function process_relatedItems($id, $oldItemList, $newItemList) {
		$oldItemList = t3lib_div::trimExplode(',', $oldItemList, true); sort($oldItemList);
		$newItemList = t3lib_div::trimExplode(',', $newItemList, true); sort($newItemList);
		$diff = array_unique(array_merge(array_diff($oldItemList, $newItemList), array_diff($newItemList, $oldItemList)));
		$list = array();
		foreach ($diff as $uid) {
			if (!isset($list[$uid])) {
				$rec = t3lib_BEfunc::getRecord('tx_irfaq_q', $uid, 'related');
				if ($rec) {
					$list[$uid] = t3lib_div::trimExplode(',', $rec['related'], true);
				}
				else {
					// No such record - dead link!
					continue;
				}
			}
			if (in_array($uid, $oldItemList)) {
				// removed
				$key = array_search($id, $list[$uid]);
				if ($key !== false) {
					unset($list[$uid][$key]);
				}
			}
			else {
				// added
				$list[$uid][] = $id;
			}
		}
		if (count($list)) {
			// Create datamap and update records using TCEmain
			$datamap = array('tx_irfaq_q' => array());
			foreach ($list as $uid => $values) {
				$datamap['tx_irfaq_q'][$uid] = array(
					'related' => implode(',', $values)
				);
			}
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_irfaq']['insideTCEmain'] = true;
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			/* @var $tce t3lib_TCEmain */
			$tce->start($datamap, null, $pObj->BE_USER);
			$tce->process_datamap();
			if (count($tce->errorLog)) {
				/* @var $pObj t3lib_TCEmain */
				$pObj->errorLog = array_merge($pObj->errorLog, $tce->errorLog);
			}
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_irfaq']['insideTCEmain'] = false;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_tcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_tcemain.php']);
}

?>