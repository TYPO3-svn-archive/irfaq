<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitry Dulepov <dmitry@typo3.org>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
require_once(PATH_t3lib . 'class.t3lib_tcemain.php');
require_once(PATH_t3lib . 'class.t3lib_flexformtools.php');

/**
 * Updates the extension from older versions to this one.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 */
class ext_update {

	/** @var language Language support */
	var	$lang;

	/** Defines new sheets and fields inside them */
	var $fieldSet = array(
		'sCATEGORIES' => array('categoryMode', 'categorySelection'),
		'sSEARCH' => array('searchPid', 'emptySearchAtStart'),
	);

	/**
	 * Shows form and/or runs the update process.
	 *
	 * @return	string	Output
	 */
	function main() {
		$this->lang = t3lib_div::makeInstance('language');
		$this->lang->init($GLOBALS['BE_USER']->uc['lang']);
		$this->lang->includeLLFile('EXT:irfaq/lang/locallang_update.xml');
		return ($_POST['run'] ? $this->runConversion() : $this->showForm());
	}

	/**
	 * Checks if "UPDATE!" should be shown at all. In out case it will be shown
	 * only if there are irfaq records in the system.
	 *
	 * @return	boolean	true if script should be displayed
	 */
	function access() {
		// Check if there are any instances of irfaq in the system
		list($row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t',
						'tt_content', 'list_type=\'irfaq_pi1\'' .
						t3lib_BEfunc::BEenableFields('tt_content') .
						t3lib_BEfunc::deleteClause('tt_content'));
		return ($row['t'] > 0);
	}

	/**
	 * Shows form to update irfaq
	 *
	 * @return	string	Generated form
	 */
	function showForm() {
		$content = '<p>' . $this->lang->getLL('form_intro', true) . '</p>' .
			'<form action="' . t3lib_div::getIndpEnv('REQUEST_URI') .
			'" method="post">' .
			'<input type="checkbox" name="replaceEmpty" value="1" />' .
			$this->lang->getLL('replace_empty') . '<br />' .
			'<input type="submit" name="run" value="' .
			$this->lang->getLL('submit_button', true) . '" /></form>';
		return $content;
	}

	/**
	 * Runs conversion procedure.
	 *
	 * @return	string	Generated content
	 */
	function runConversion() {
		$content = '';
		// Select all instances
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,pi_flexform',
						'tt_content', 'list_type=\'irfaq_pi1\'' .
						t3lib_BEfunc::BEenableFields('tt_content') .
						t3lib_BEfunc::deleteClause('tt_content'));
		$results = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		$converted = 0;
		$data = array();
		$replaceEmpty = intval(t3lib_div::_GP('replaceEmpty'));

		$flexformtools = t3lib_div::makeInstance('t3lib_flexformtools');
		/* @var $flexformtools t3lib_flexformtools */
		$GLOBALS['TYPO3_CONF_VARS']['BE']['compactFlexFormXML'] = true;
		$GLOBALS['TYPO3_CONF_VARS']['BE']['niceFlexFormXMLtags'] = true;

		// Walk all rows
		while (false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			$ffArray = t3lib_div::xml2array($row['pi_flexform']);
			$modified = false;
			if (is_array($ffArray) && isset($ffArray['data']['sDEF'])) {
				foreach ($ffArray['data']['sDEF'] as $sLang => $sLdata) {
					foreach ($this->fieldSet as $sheet => $fieldList) {
						foreach ($fieldList as $field) {
							if (isset($ffArray['data']['sDEF'][$sLang][$field]) &&
									isset($ffArray['data']['sDEF'][$sLang][$field]['vDEF']) &&
									strlen($ffArray['data']['sDEF'][$sLang][$field]['vDEF']) > 0 &&
									(!isset($ffArray['data'][$sheet][$sLang][$field]) ||
									!isset($ffArray['data'][$sheet][$sLang][$field]['vDEF']) ||
									($replaceEmpty && strlen($ffArray['data'][$sheet][$sLang][$field]['vDEF']) == 0))) {
								$ffArray['data'][$sheet][$sLang][$field]['vDEF'] =
									$ffArray['data']['sDEF'][$sLang][$field]['vDEF'];
								$modified = true;
							}
						}
					}
				}
			}
			if ($modified) {
				// Assemble data back
				$data['tt_content'][$row['uid']] = array(
					'pi_flexform' => $flexformtools->flexArray2Xml($ffArray),
				);
				$converted++;
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		if ($converted > 0) {
			// Update data
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			/* @var $tce t3lib_TCEmain */
			$tce->start($data, null);
			$tce->process_datamap();
			if (count($tce->errorLog) > 0) {
				$content .= '<p>' . $this->lang->getLL('errors') . '</p><ul><li>' .
					implode('</li><li>', $tce->errorLog) . '</li></ul>';
			}
		}
		$content .= '<p>' . sprintf($this->lang->getLL('result'), $results, $converted) .
					'</p>';
		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.ext_update.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.ext_update.php']);
}

?>