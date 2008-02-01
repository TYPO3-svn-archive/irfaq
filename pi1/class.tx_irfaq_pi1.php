<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 - 2006 Ingo Renner (typo3@ingo-renner.com)
*  (c) 2006        Netcreators (extensions@netcreators.com)
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
 *   72: class tx_irfaq_pi1 extends tslib_pibase
 *   97:     function main($content,$conf)
 *  131:     function init($conf)
 *  257:     function initCategories()
 *  291:     function initExperts()
 *  310:     function searchView()
 *  328:     function dynamicView()
 *  376:     function staticView()
 *  407:     function fillMarkers($template)
 *  441:     function getCatMarkerArray($markerArray, $row)
 *  500:     function getSelectConf($where)
 *  543:     function searchWhere($searchWords)
 *  554:     function formatStr($str)
 *  570:     function validateFields($fieldlist)
 *  588:     function getRelatedEntries($list)
 *  619:     function getRelatedLinks($list)
 *  644:     function fillMarkerArrayForRow(&$row)
 *  752:     function singleView()
 *
 * TOTAL FUNCTIONS: 17
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
/**
 * Plugin 'Simple FAQ' for the 'irfaq' extension.
 *
 * @author	Netcreators <extensions@netcreators.com>
 * @package TYPO3
 * @subpackage irfaq
 */


require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * class.tx_irfaq_pi1.php
 *
 * Creates a faq list.
 *
 * @author Netcreators <extensions@netcreators.com>
 */
class tx_irfaq_pi1 extends tslib_pibase {
	var $local_cObj;

	// Same as class name
	var $prefixId 		= 'tx_irfaq_pi1';
	// Path to this script relative to the extension dir.
	var $scriptRelPath 	= 'pi1/class.tx_irfaq_pi1.php';
	var $extKey 		= 'irfaq';
	var $searchFieldList = 'q, a';
	var $categories 	= array();
	var $experts		= array();
	var $pageArray 		= array();
	var $faqCount		= 0;
	var $hash			= ''; //a random hash to use multiple pi on one page
	var $showUid		= 0;
	var $pi_checkCHash	= true;

	/**
	 * main function, which is called at startup
	 * calls init() and determines view
	 *
	 * @param	string		$content: output string of page
	 * @param	array		$conf: configuration array from TS
	 * @return	string		$content: output of faq plugin
	 */
	function main($content,$conf)	{
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->init($conf);

		if (!isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_irfaq_pi1.'])) {
			$content = $this->pi_getLL('no_ts_template');
		}
		else {
			$content = '';
			foreach($this->conf['code'] as $code) {
				switch($code) {
					case 'SINGLE':
						$content .= $this->singleView();
						break;
					case 'SEARCH':
						$content .= $this->searchView();
						break;
					case 'DYNAMIC':
						$content .= $this->dynamicView();
						break;
					case 'STATIC':
						$content .= $this->staticView();
						break;
					case 'STATIC_SEPARATE':
						$content .= $this->staticSeparateView();
						break;
					default:
						$content .= 'unknown view!';
						break;
				}
			}
		}

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * initializes configuration variables
	 *
	 * @param	array		$conf: configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		$this->conf = $conf;

		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		$this->enableFields = $this->cObj->enableFields('tx_irfaq_q');

		// "CODE" decides what is rendered: code can be added by TS or FF with priority on FF
		$this->showUid = intval($this->piVars['showUid']);
		if ($this->showUid) {
			$this->conf['code'] = array('SINGLE');
			$this->conf['categoryMode'] = 0;
			$this->conf['catExclusive'] = 0;
		} else {
			$ffCode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display');
			$this->conf['code'] = $ffCode ? $ffCode : strtoupper($conf['code']);
			if(empty($this->conf['code'])) {
				$this->conf['code'] = strtoupper($conf['defaultCode']);
			}
			$this->conf['code'] = explode(',', $this->conf['code']);

			// categoryModes are: 0=display all categories, 1=display selected categories, -1=display deselected categories
			$ffCategoryMode = $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'], 'categoryMode', 'sCATEGORIES');
			$this->conf['categoryMode'] = $ffCategoryMode ?
				$ffCategoryMode :
				$this->conf['categoryMode'];

			$ffCatSelection = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categorySelection', 'sCATEGORIES');
			$this->conf['catSelection'] = $ffCatSelection ?
				$ffCatSelection :
				trim($this->conf['categorySelection']);

			// ignore category selection if categoryMode isn't set
			if($this->conf['categoryMode'] != 0) {
				$this->conf['catExclusive'] = $this->conf['catSelection'];
			}
			else {
				$this->conf['catExclusive'] = 0;
			}

			//set category by $_GET
			if (is_numeric($this->piVars['cat'])) {
				$this->conf['catExclusive'] = intval($this->piVars['cat']);
				$this->conf['categoryMode'] = 1;
			}

			$ffSearchPid = $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'], 'searchPid', 'sSEARCH');
			$this->conf['searchPid'] = $ffSearchPid ?
				$ffSearchPid :
				trim($this->conf['searchPid']);

			$ffEmptySearchAtStart = $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'], 'emptySearchAtStart', 'sSEARCH');
			$this->conf['emptySearchAtStart'] = $ffEmptySearchAtStart != '' ?
				$ffEmptySearchAtStart :
				trim($this->conf['emptySearchAtStart']);

				// get fieldnames from the tx_irfaq_q db-table
			$this->fieldNames = array_keys($GLOBALS['TYPO3_DB']->admin_get_fields('tx_irfaq_q'));

			if ($this->conf['searchFieldList']) {
				$searchFieldList = $this->validateFields($this->conf['searchFieldList']);
				if ($searchFieldList) {
					$this->searchFieldList = $searchFieldList;
				}
			}

			// pidList is the pid/list of pids from where to fetch the faq items.
			$ffPidList = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages');
			$pidList   = $ffPidList ?
				$ffPidList :
				trim($this->cObj->stdWrap(
					$this->conf['pid_list'], $this->conf['pid_list.']
				));
			$this->conf['pidList'] = $pidList ?
				implode(t3lib_div::intExplode(',', $pidList), ',') :
				$GLOBALS['TSFE']->id;


			//get items recursive
			$recursive = $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'],
				'recursive'
			);
			$recursive = is_numeric($recursive) ?
				$recursive :
				$this->cObj->stdWrap($conf['recursive'], $conf['recursive.']);
			// extend the pid_list by recursive levels
			$this->conf['pidList'] = $this->pi_getPidList(
				$this->conf['pidList'],
				$recursive
			);

			// max items per page
			$TSLimit = t3lib_div::intInRange($conf['limit'], 0, 1000);
			$this->conf['limit'] = $TSLimit ? $TSLimit : 50;

			// display text like 'page' in pagebrowser
			$this->conf['showPBrowserText'] = $this->conf['showPBrowserText'];
			// get pageBrowser configuration
			$this->conf['pageBrowser.']     = $this->conf['pageBrowser.'];
		}

		// ratings
		if (!t3lib_extMgm::isLoaded('ratings')) {
			$this->conf['enableRatings'] = false;
		}
		else {
			$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'enableRatings', 'sDEF');
			if ($val != '') {
				$this->conf['enableRatings'] = $val;
			}
		}

		// read template file
		$this->templateCode = $this->cObj->fileResource(
			$this->conf['templateFile']
		);

		$this->initCategories(); // initialize category-array
		$this->initExperts(); // initialize experts-array

		mt_srand((double) microtime() * 1000000);
		$this->hash = substr(md5(mt_rand()), 0, 5);

		$this->addHeaderParts();
	}

	/**
	 * Getting all tx_irfaq_cat categories into internal array
	 * partly taken from tt_news - thx Rupert!!!
	 *
	 * @return	void
	 */
	function initCategories() {
		$storagePid = $GLOBALS['TSFE']->getStorageSiterootPids();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_irfaq_cat LEFT JOIN tx_irfaq_q_cat_mm ON tx_irfaq_q_cat_mm.uid_foreign = tx_irfaq_cat.uid',
			'tx_irfaq_cat.pid IN (' . $storagePid['_STORAGE_PID'] . ')' . $this->cObj->enableFields('tx_irfaq_cat'),
			'',
			'tx_irfaq_cat.sorting');

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			$catTitle    = '';
			$catTitle    = $row['title'];
			$catShortcut = $row['shortcut'];

			if (isset($row['uid_local'])) {
				$this->categories[$row['uid_local']][] = array(
					'title' => $catTitle,
					'catid' => $row['uid_foreign'],
					'shortcut' => $catShortcut
				);
			} else {
				$this->categories['0'][$row['uid']] = $catTitle;
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
	}

	/**
	 * Getting all experts into internal array
	 *
	 * @return	void
	 */
	function initExperts() {
		// Fetching experts
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',
													  'tx_irfaq_expert',
													  '1=1'.$this->cObj->enableFields('tx_irfaq_expert'));

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))   {
			if (($row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_irfaq_expert', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL))) {
				$this->experts[$row['uid']]['name']  = $row['name'];
				$this->experts[$row['uid']]['url']   = $row['url'];
				$this->experts[$row['uid']]['email'] = $row['email'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
	}

	/**
	 * Shows search.
	 *
	 * @return	string		Generated content
	 */
	function searchView() {
		$template['total'] = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_SEARCH###');

		$formURL = $this->pi_linkTP_keepPIvars_url(array('cat' => null), 0, 1, $this->conf['searchPid']) ;

		$content = $this->cObj->substituteMarker($template['total'], '###FORM_URL###', $formURL);
		$content = $this->cObj->substituteMarker($content, '###SWORDS###', htmlspecialchars($this->piVars['swords']));
		$content = $this->cObj->substituteMarker($content, '###SEARCH_BUTTON###', $this->pi_getLL('searchButtonLabel'));

		return $content;
	}

	/**
	 * creates the dynamic view with dhtml and adds javascript to the page
	 * header
	 *
	 * @return	string		faq list
	 */
	function dynamicView() {
		$header		  = '';
		$content      = '';
		$template     = array();
		$subpartArray = array();

		$template['total']   = $this->cObj->getSubpart(
			$this->templateCode, '###TEMPLATE_DYNAMIC###'
		);
		$template['content'] = $this->cObj->getSubPart(
			$template['total'], '###CONTENT###'
		);

		$subpartArray['###CONTENT###'] = $this->fillMarkers($template['content']);

		if(!empty($this->faqCount)) {
			//after calling fillMarkers we know count and can fill the corresponding js var
			$header  = '<script type="text/javascript">'.chr(10);
			$header .= '<!--'.chr(10);
			$header .= 'var tx_irfaq_pi1_iconPlus = "'.$this->conf['iconPlus'].'";'.chr(10);
			$header .= 'var tx_irfaq_pi1_iconMinus = "'.$this->conf['iconMinus'].'";'.chr(10);
			$header .= '// -->'.chr(10);
			$header .= '</script>'.chr(10);
			$header .= '<script type="text/javascript" src="'.
				t3lib_extMgm::siteRelPath($this->extKey).'res/toggleFaq.js"></script>';
			$GLOBALS['TSFE']->additionalHeaderData['tx_irfaq'] = $header;

			$markerArray = array(
				'###HASH###' => $this->hash,
				'###COUNT###' => $this->faqCount,
				'###TEXT_SHOW###' => $this->pi_getLL('text_show'),
				'###TEXT_HIDE###' => $this->pi_getLL('text_hide'),
			);
			$content = $this->cObj->substituteMarkerArrayCached(
				$template['total'],
				$markerArray,
				$subpartArray
			);
		}

		return $content;
	}

	/**
	 * creates the static view without dhtml
	 *
	 * @return	string		faq list
	 */
	function staticView() {
		$templateName = 'TEMPLATE_STATIC';
		$content = '';

		$template = array();
		$template['total'] = $this->cObj->getSubpart(
			$this->templateCode,
			'###'.$templateName.'###'
		);

		$temp = $this->cObj->getSubPart($template['total'], '###QUESTIONS###');
		$subpartArray['###QUESTIONS###'] = $this->fillMarkers($temp);

		$temp = $this->cObj->getSubPart($template['total'], '###ANSWERS###');
		$subpartArray['###ANSWERS###'] = $this->fillMarkers($temp);

		$content = $this->cObj->substituteMarkerArrayCached(
			$template['total'],
			array(),
			$subpartArray
		);

		return $content;
	}

	/**
	 * replaces markers with content
	 *
	 * @param	string		$template: the html with markers to substitude
	 * @return	string		template with substituted markers
	 */
	function fillMarkers($template) {

		$content = '';

		$selectConf = array();
		$where = '1 = 1'.$this->cObj->enableFields('tx_irfaq_q');
		$selectConf = $this->getSelectConf($where);
		$selectConf['selectFields'] = 'DISTINCT tx_irfaq_q.uid, tx_irfaq_q.pid, tx_irfaq_q.q, tx_irfaq_q.q_from, tx_irfaq_q.a, tx_irfaq_q.cat, tx_irfaq_q.expert, tx_irfaq_q.related, tx_irfaq_q.related_links, tx_irfaq_q.enable_ratings, tx_irfaq_q.sys_language_uid, tx_irfaq_q.l18n_parent, tx_irfaq_q.l18n_diffsource';
		$selectConf['orderBy'] 		= 'tx_irfaq_q.sorting';

		$res = $this->cObj->exec_getQuery('tx_irfaq_q', $selectConf);
		$this->faqCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		$markerArray = array(); $i = 1;
		while (false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			if (($row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_irfaq_q', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL))) {
				$markerArray = $this->fillMarkerArrayForRow($row, $i);
				$markerArray['###FAQ_ID###'] = $i++;

				$subpart  = $this->cObj->getSubPart($template, '###FAQ###');
				$content .= $this->cObj->substituteMarkerArrayCached($subpart, $markerArray);
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $content;
	}

	/**
	 * Fills in the Category markerArray with data
	 * also taken from tt_news ;-)
	 *
	 * @param	array		$markerArray : partly filled marker array
	 * @param	array		$row : result row for a news item
	 * @return	array		$markerArray: filled markerarray
	 */
	function getCatMarkerArray($markerArray, $row) {
		// clear the category text marker if the FAQ item has no categories
		$markerArray['###FAQ_CATEGORY###'] = '';
		$markerArray['###TEXT_CATEGORY###'] = '';

		if(isset($this->categories[$row['uid']])) {
			reset($this->categories[$row['uid']]);
			$faq_category = array();

			while(list($key, $val) = each($this->categories[$row['uid']])) {
				// find categories, wrap them with links and collect them in the array $faq_category.
				if ($this->conf['catTextMode'] == 1) {
					// link to category shortcut page
					$faq_category[] = $this->pi_linkToPage(
						$this->categories[$row['uid']][$key]['title'],
						$this->categories[$row['uid']][$key]['shortcut']
					);
				}
				else if($this->conf['catTextMode'] == 2) {
					// act as category selector
					$faq_category[] = $this->pi_linkToPage(
						$this->categories[$row['uid']][$key]['title'],
						$GLOBALS['TSFE']->page['uid'],
						'',
						array('tx_irfaq_pi1[cat]' => $this->categories[$row['uid']][$key]['catid'])
					);
				}
				else {
					//no link
					$faq_category[] = $this->categories[$row['uid']][$key]['title'];
				}
			}
		}

		//check for empty categories
		if(!is_array($faq_category)) {
			$faq_category = array();
		}

		$markerArray['###FAQ_CATEGORY###'] = implode(', ', array_slice($faq_category, 0));

		//apply the wraps if there are categories
		if(count($faq_category)) {
			$markerArray['###FAQ_CATEGORY###']= $this->local_cObj->stdWrap(
				$markerArray['###FAQ_CATEGORY###'],
				$this->conf['category_stdWrap.']
			);
			$markerArray['###TEXT_CATEGORY###'] = $this->pi_getLL('text_category');
		}

		return $markerArray;
	}

	/**
	 * build the selectconf (array of query-parameters) to get the faq items from the db
	 *
	 * @param	string		$where : where-part of the query
	 * @return	array		the selectconf for the display of a news item
	 */
	function getSelectConf($where) {
		$selectConf = array();
		$selectConf['pidInList'] = $this->conf['pidList'];
		$selectConf['where'] = $where;
		$selectConf['where'] .= ' AND sys_language_uid=0';

		//build SQL on condition of categoryMode
		if($this->conf['categoryMode'] == 1) {
			$selectConf['leftjoin'] = 'tx_irfaq_q_cat_mm ON tx_irfaq_q.uid = tx_irfaq_q_cat_mm.uid_local';
			$selectConf['where']   .= ' AND (IFNULL(tx_irfaq_q_cat_mm.uid_foreign,0) IN (' .$this->conf['catExclusive']. '))';
		}
		elseif ($this->conf['categoryMode'] == -1) {
			$selectConf['leftjoin'] = 'tx_irfaq_q_cat_mm ON (tx_irfaq_q.uid = tx_irfaq_q_cat_mm.uid_local AND (tx_irfaq_q_cat_mm.uid_foreign=';

			//multiple categories selected?
			if(strpos($this->conf['catExclusive'], ',')) {
				//yes
				$selectConf['leftjoin'] .= ereg_replace(',', ' OR tx_irfaq_q_cat_mm.uid_foreign=', $this->conf['catExclusive']);
			}
			else {
				//no
				$selectConf['leftjoin'] .= $this->conf['catExclusive'];
			}
			$selectConf['leftjoin'] .= '))';
			$selectConf['where'] .= ' AND (tx_irfaq_q_cat_mm.uid_foreign IS NULL)';
		}

		// do the search and add the result to the $where string
		if ($this->piVars['swords']) {
			$selectConf['where'] .= $this->searchWhere(trim($this->piVars['swords']));
		} elseif(in_array('SEARCH', $this->conf['code'])) {
			// display an empty list, if 'emptySearchAtStart' is set.
			$selectConf['where'] .= ($this->conf['emptySearchAtStart'] ? 'AND 1=0' : '');
		}

		return $selectConf;
	}

	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($searchWords) {
		$where = $this->cObj->searchWhere($searchWords, $this->searchFieldList, 'tx_irfaq_q');
		return $where;
	}

	/**
	 * Format string with general_stdWrap from configuration
	 *
	 * @param	string		string to wrap
	 * @return	string		wrapped string
	 */
	function formatStr($str) {
		if (is_array($this->conf['general_stdWrap.'])) {
			$str = $this->local_cObj->stdWrap(
				$str,
				$this->conf['general_stdWrap.']
			);
		}
		return $str;
	}

	/**
	 * checks for each field of a list of items if it exists in the tx_irfaq_q table ($this->fieldNames) and returns the validated fields
	 *
	 * @param	string		$fieldlist: a list of fields to ckeck
	 * @return	string		the list of validated fields
	 */
	function validateFields($fieldlist) {
		$checkedFields = array();
		$fArr = t3lib_div::trimExplode(',',$fieldlist,1);
		while (list(,$fN) = each($fArr)) {
			if (in_array($fN,$this->fieldNames)) {
				$checkedFields[] = $fN;
			}
		}
		$checkedFieldlist = implode($checkedFields,',');
		return $checkedFieldlist;
	}

	/**
	 * Makes formatted list of related FAQ entries
	 *
	 * @param	string		$list	Comma-separated list of related-entires
	 * @return	string		Generated HTML or empty string if no related entries
	 */
	function getRelatedEntries($list) {
		$content = '';
		$list = t3lib_div::trimExplode(',', $list, true);	// Have to do that because there can be empty elements!
		if (count($list)) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_irfaq_q', 'uid IN (' . implode(',', $list) . ')' .
						$this->cObj->enableFields('tx_irfaq_q'));
			if (is_array($rows)) {
				$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RELATED_FAQ###');
				$templateInner = $this->cObj->getSubpart($template, '###RELATED_FAQ_ENTRY###');
				// We use base64_encode to encode return url because it looks better in the link and mod_security-like utilities do not throw false errors
				$returnUrl = base64_encode(t3lib_div::getIndpEnv('TYPO3_SITE_SCRIPT'));
				foreach ($rows as $row) {
					// TODO Anchor is customizable in template!
					if (($row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_irfaq_q', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL))) {
						$markers = array(
							'###RELATED_FAQ_ENTRY_TITLE###' => $this->formatStr($this->local_cObj->stdWrap(htmlspecialchars($row['q']), $this->conf['question_stdWrap.'])),
							'###RELATED_FAQ_ENTRY_HREF###' => $this->pi_list_linkSingle('', $row['uid'], true, array('back' => $returnUrl), true),
						);
						$content .= $this->cObj->substituteMarkerArrayCached($templateInner, $markers);
					}
				}
				$content = $this->cObj->substituteMarkerArrayCached($template, array('###TEXT_RELATED_FAQ###' => $this->pi_getLL('text_related_faq')), array('###RELATED_FAQ_ENTRY###' => $content));
			}
		}
		return $content;
	}

	/**
	 * Makes formatted list of related links
	 *
	 * @param	string		$list	NL-separated list of related links
	 * @return	string		Generated HTML or empty string if no related links
	 */
	function getRelatedLinks($list) {
		$content = '';
		$list = t3lib_div::trimExplode(chr(10), $list, true);	// Have to do that because there can be empty elements!
		if (count($list)) {
			$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_RELATED_LINKS###');
			$templateInner = $this->cObj->getSubpart($template, '###RELATED_LINK_ENTRY###');
			foreach ($list as $link) {
				$markers = array(
					'###RELATED_LINK_ENTRY_TITLE###' => htmlspecialchars($link),
					'###RELATED_LINK_ENTRY_HREF###' => $link,
				);
				$content .= $this->cObj->substituteMarkerArrayCached($templateInner, $markers);
			}
			$content = $this->cObj->substituteMarkerArrayCached($template, array('###TEXT_RELATED_LINKS###' => $this->pi_getLL('text_related_links')), array('###RELATED_LINK_ENTRY###' => $content));
		}
		return $content;
	}

	/**
	 * Creates marker array for a single FAQ row
	 *
	 * @param	array		$row	A row from tx_irfaq_q. Passed by reference to save memory.
	 * @return	array		Generated marker array
	 * @see	fillMarkers()
	 */
	function fillMarkerArrayForRow(&$row, $i) {
		$markerArray = array();

		$markerArray['###FAQ_Q###']	 = $this->formatStr(
			$this->local_cObj->stdWrap(
				htmlspecialchars($row['q']),
				$this->conf['question_stdWrap.']
			)
		);
		$markerArray['###FAQ_A###']	 = $this->formatStr(
			$this->local_cObj->stdWrap(
				$this->pi_RTEcssText($row['a']),
				$this->conf['answer_stdWrap.']
			)
		);

		// categories
		$markerArray = $this->getCatMarkerArray($markerArray, $row);

		$markerArray['###SINGLE_OPEN###'] = ($this->conf['singleOpen'] ? 'true' : 'false');

		if($row['expert']) {
			$this->local_cObj->LOAD_REGISTER(
				array(
					'faqExpertEmail' => $this->experts[$row['expert']]['email'],
					'faqExpertUrl'   => $this->experts[$row['expert']]['url']
				),
				'');
			$markerArray['###FAQ_EXPERT###'] = $this->local_cObj->stdWrap(
					$this->experts[$row['expert']]['name'],
					$this->conf['expert_stdWrap.']
				);

			$markerArray['###TEXT_EXPERT###'] = $this->local_cObj->stdWrap(
				$this->pi_getLL('text_expert'),
				$this->conf['text_expert_stdWrap.']
			);

			$markerArray['###FAQ_EXPERT_EMAIL###'] = $this->local_cObj->stdWrap(
				$this->experts[$row['expert']]['email'],
				$this->conf['expertemail_stdWrap.']
			);

			if($this->experts[$row['expert']]['url']) {
				$markerArray['###FAQ_EXPERT_URL###'] = $this->local_cObj->stdWrap(
					$this->experts[$row['expert']]['url'],
					$this->conf['experturl_stdWrap.']
				);
			}
			else {
				$markerArray['###FAQ_EXPERT_URL###']   = '';
			}
		}
		else {
			//leave everything empty if no expert assigned
			$markerArray['###FAQ_EXPERT###']	   = '';
			$markerArray['###TEXT_EXPERT###']	   = '';
			$markerArray['###FAQ_EXPERT_EMAIL###'] = '';
			$markerArray['###FAQ_EXPERT_URL###']   = '';
			$this->local_cObj->LOAD_REGISTER(
				array('faqExpertEmail' => '','faqExpertUrl' => ''), '');
		}

		if($row['q_from']) {
			$markerArray['###TEXT_ASKED_BY###'] = $this->local_cObj->stdWrap(
				$this->pi_getLL('text_asked_by'),
				$this->conf['text_asked_by_stdWrap.']
			);
			$markerArray['###ASKED_BY###'] = $this->local_cObj->stdWrap(
				$row['q_from'],
				$this->conf['asked_by_stdWrap.']
			);
		}
		else {
			$markerArray['###TEXT_ASKED_BY###'] = '';
			$markerArray['###ASKED_BY###'] = '';
		}

		$markerArray['###RELATED_FAQ###'] = '';
		if ($row['related']) {
			$related = $this->getRelatedEntries($row['related']);
			if ($related) {
				$markerArray['###RELATED_FAQ###'] = $this->local_cObj->stdWrap($related, $this->conf['related_entries_stdWrap.']);
			}
		}

		$markerArray['###RELATED_LINKS###'] = '';
		if ($row['related_links']) {
			$related_links = $this->getRelatedLinks($row['related_links']);
			if ($related_links) {
				$markerArray['###RELATED_LINKS###'] = $this->local_cObj->stdWrap($related_links, $this->conf['related_links_stdWrap.']);
			}
		}

		$markerArray['###FAQ_PM_IMG###'] = '<img src="'.
			$this->conf['iconPlus'].'" id="irfaq_pm_'.$i.'_'.$this->hash.'" alt="'.
			$this->pi_getLL('fold_faq').'" />';

		$markerArray['###HASH###'] = $this->hash;

		$returnUrl = base64_encode(t3lib_div::getIndpEnv('TYPO3_SITE_SCRIPT'));
		$markerArray['###SINGLEVIEW_LINK###'] = $this->pi_list_linkSingle('', $row['uid'], true, array('back' => $returnUrl), true);

		$markerArray['###RATING###'] = $this->getRatingForRow($row);

		return $markerArray;
	}

	/**
	 * Shows single FAQ item
	 *
	 * @return	string		Generated content
	 */
	function singleView() {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_irfaq_q', 'uid=' . intval($this->showUid) .
					$this->cObj->enableFields('tx_irfaq_q'));
		if (count($rows)) {
			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_irfaq_q', $rows[0], $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
			if ($row) {
				$rows[0] = $row;
			}
		}
		if (count($rows) == 0) {
			$content = $this->pi_getLL('noSuchEntry');
		}
		else {
			$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_SINGLE_VIEW###');
			$markers = $this->fillMarkerArrayForRow($rows[0], 1);
			unset($this->piVars['showUid']);
			$markers['###BACK_HREF###'] = base64_decode($this->piVars['back']);
			$markers['###BACK_TEXT###'] = $this->pi_getLL('back');
			$content = $this->cObj->substituteMarkerArrayCached($template, $markers);
		}
		return $content;
	}

	/**
	 * Creates a set of links to a separate page with answer. This mode is suitable if you want comments for FAQ entries
	 *
	 * @return Generated FAQ list
	 */
	function staticSeparateView() {
		$template_sub = $this->cObj->getSubPart($this->templateCode, '###TEMPLATE_STATIC_SEPARATE###');
		$template = $this->cObj->getSubPart($template_sub, '###QUESTIONS###');
		$subpartArray['###QUESTIONS###'] = $this->fillMarkers($template);
		return $this->cObj->substituteMarkerArrayCached($template_sub, array(), $subpartArray);
	}

	/**
	 * Obtains rating HTML for row if enabled.
	 *
	 * @param	array	$row	Database row from tx_irfaq_q table
	 * @return	string	Generated ratings
	 */
	function getRatingForRow($row) {
		$result = '';
			if ($row['enable_ratings'] && $this->conf['enableRatings']) {
			require_once(t3lib_extMgm::extPath('ratings', 'class.tx_ratings_api.php'));

			$apiObj = t3lib_div::makeInstance('tx_ratings_api');
			/* @var $apiObj tx_ratings_api */
			$result = $apiObj->getRatingDisplay('tx_irfaq_q_' . ($row['l18n_parent'] ? $row['l18n_parent'] : $row['uid']));
		}
		return $result;
	}

	/**
	 * Adds header parts from the template to the TSFE.
	 * It fetches subpart identified by $subpart and replaces ###SITE_REL_PATH### with site-relative part to the extension.
	 *
	 * @param	string	$subpart	Subpart from template to add.
	 */
	function addHeaderParts() {
		$subpart = '###HEADER_PARTS###';
		if (!isset($GLOBALS['TSFE']->additionalHeaderData['tx_irfaq_pi1_' . $subpart])) {
			$template = $this->cObj->getSubpart($this->templateCode, $subpart);
			$GLOBALS['TSFE']->additionalHeaderData['tx_irfaq_pi1_' . $subpart] =
				$this->cObj->substituteMarker($template, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath('irfaq'));
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/pi1/class.tx_irfaq_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/pi1/class.tx_irfaq_pi1.php']);
}
?>