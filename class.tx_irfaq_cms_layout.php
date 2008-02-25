<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov <dmitry@typo3.org>
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
 *
 *
 *   46: class tx_irfaq_cms_layout
 *   54:     function getExtensionSummary($params, &$pObj)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Hook to display verbose information about pi1 plugin in Web>Page module
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_irfaq
 */
class tx_irfaq_cms_layout {
	/**
 * Returns information about this extension's pi1 plugin
 *
 * @param	array		$params	Parameters to the hook
 * @param	object		$pObj	A reference to calling object
 * @return	string		Information about pi1 plugin
 */
	function getExtensionSummary($params, &$pObj) {
		global $LANG;

		if ($params['row']['list_type'] == 'irfaq_pi1') {
			$data = t3lib_div::xml2array($params['row']['pi_flexform']);
			if (is_array($data)) {
				$modes = array();
				foreach(t3lib_div::trimExplode(',', $data['data']['sDEF']['lDEF']['what_to_display']['vDEF'], true) as $mode) {
					switch ($mode) {
						case 'DYNAMIC':
							$modes[] = $LANG->sL('LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.pi_flexform.view_dynamic');
							break;
						case 'STATIC':
							$modes[] = $LANG->sL('LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.pi_flexform.view_static');
							break;
						case 'STATIC_SEPARATE':
							$modes[] = $LANG->sL('LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.pi_flexform.view_static_separate');
							break;
						case 'SEARCH':
							$modes[] = $LANG->sL('LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.pi_flexform.view_search');
							break;
					}
				}
				$result = implode(', ', $modes);
			}
			if (!$result) {
				$result = $LANG->sL('LLL:EXT:irfaq/lang/locallang_db.xml:tx_irfaq.pi_flexform.no_view');
			}
		}
		return $result;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_cms_layout.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/class.tx_irfaq_cms_layout.php']);
}

?>