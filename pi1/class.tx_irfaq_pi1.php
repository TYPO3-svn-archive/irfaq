<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Ingo Renner (typo3@ingo-renner.com)
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
 * Plugin 'Simple FAQ' for the 'irfaq' extension.
 *
 * @author	Ingo Renner <typo3@ingo-renner.com>
 * @package TYPO3
 * @subpackage irfaq
 */


require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * class.tx_irfaq_pi1.php
 *
 * Creates a faq list.
 * $Id$
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */
class tx_irfaq_pi1 extends tslib_pibase {
	var $cObj;

	var $prefixId 		= 'tx_irfaq_pi1';		// Same as class name
	var $scriptRelPath 	= 'pi1/class.tx_irfaq_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'irfaq';	// The extension key.

	var $config 		= array();
	var $categories 	= array();
	var $experts		= array();
	var $pageArray 		= array();

	/**
	 * main function, which is called at startup
	 * calls init() and determines view
	 *
	 * @param	string		$content: output string of page
	 * @param	array		$conf: configuration array from TS
	 * @return	string		$content: output of faq plugin
	 */
	function main($content,$conf)	{

		$this->init($conf);

		switch($this->config['code']) {
			case 'DYNAMIC':
				$content .= $this->dynamicView();
				break;
			case 'STATIC':
				$content .= $this->staticView();
				break;
			default:
				$content .= 'unknown view!';
		}

		return $content;
	}

	/**
	 * initializes configuration variables
	 *
	 * @param	array		$conf: configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		$this->config = $conf;

		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		$this->enableFields = $this->cObj->enableFields('tx_irfaq_q');

		// "CODE" decides what is rendered: code can be added by TS or FF with priority on FF
		$ffCode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display');
		$this->config['code'] = $ffCode ? $ffCode : strtoupper($conf['code']);

		// categoryModes are: 0=display all categories, 1=display selected categories, -1=display deselected categories
		$ffCategoryMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoryMode');
		$this->config['categoryMode'] = $ffCategoryMode ? $ffCategoryMode : $this->conf['categoryMode'];

		$ffCatSelection = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categorySelection');
		$this->config['catSelection'] = $ffCatSelection ? $ffCatSelection : trim($this->conf['categorySelection']);

		// ignore category selection if categoryMode isn't set
		if($this->config['categoryMode'] != 0) {
			$this->config['catExclusive'] = $this->config['catSelection'];
		}
		else {
			$this->config['catExclusive'] = 0;
		}

		// pidList is the pid/list of pids from where to fetch the faq items.
		$ffPidList = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages');
		$pidList   = $ffPidList ? $ffPidList : trim($this->cObj->stdWrap($this->conf['pid_list'], $this->conf['pid_list.']));
		$this->config['pidList'] = $pidList ? implode(t3lib_div::intExplode(',', $pidList), ',') : $GLOBALS['TSFE']->id;


		//get items recursive
		$recursive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive');
		$recursive = is_numeric($recursive) ? $recursive : $this->cObj->stdWrap($conf['recursive'], $conf['recursive.']);
		// extend the pid_list by recursive levels
		$this->config['pidList'] = $this->pi_getPidList($this->config['pidList'], $recursive);
		// generate array of page titles
		//$this->generatePageArray();

		// max items per page
		$TSLimit = t3lib_div::intInRange($conf['limit'], 0, 1000);
		$this->config['limit'] = $TSLimit ? $TSLimit : 50;

		$this->config['showPBrowserText'] = $this->conf['showPBrowserText']; // display text like 'page' in pagebrowser
		$this->config['pageBrowser.']     = $this->conf['pageBrowser.']; // get pageBrowser configuration

		// read template file
		$this->templateCode = $this->cObj->fileResource($this->config['templateFile']);

		$this->initCategories(); // initialize category-array
		$this->initExperts(); // initialize experts-array
	}

	/**
	 * Getting all tx_irfaq_cat categories into internal array
	 * partly taken from tt_news - thx Rupert!!!
	 *
	 * @return	void
	 */
	function initCategories() {
		$storagePid = $GLOBALS['TSFE']->getStorageSiterootPids();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_irfaq_cat LEFT JOIN tx_irfaq_q_cat_mm ON tx_irfaq_q_cat_mm.uid_foreign = tx_irfaq_cat.uid', 'tx_irfaq_cat.pid IN (' . $storagePid['_STORAGE_PID'] . ')' . $this->cObj->enableFields('tx_irfaq_cat'));

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			$catTitle = '';
			$catTitle = $row['title'];

			if (isset($row['uid_local'])) {
				$this->categories[$row['uid_local']][] = array('title' => $catTitle, 'catid' => $row['uid_foreign']);
			} else {
				$this->categories['0'][$row['uid']] = $catTitle;
			}
		}
	}
	
	/**
	 * Getting all experts into internal array
	 * 
	 * @return void
	 */
	function initExperts() {
		// Fetching experts
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_irfaq_expert', '1=1'.$this->cObj->enableFields('tx_irfaq_expert'));
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))   {
			$this->experts[$row['uid']]['name']  = $row['name'];
			$this->experts[$row['uid']]['url']   = $row['url'];
			$this->experts[$row['uid']]['email'] = $row['email'];
		}
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

		$header  = '<script type="text/javascript" language="javascript">'.chr(10);
		$header .= '<!--'.chr(10);
		$header .= 'var tx_irfaq_pi1_iconPlus = "'.$this->config['iconPlus'].'";'.chr(10);
		$header .= 'var tx_irfaq_pi1_iconMinus = "'.$this->config['iconMinus'].'";'.chr(10);
		$header .= '// -->'.chr(10);
		$header .= '</script>'.chr(10);
		$header .= '<script type="text/javascript" language="javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/toggleFaq.js"></script>';
		
		$GLOBALS['TSFE']->additionalHeaderData['tx_irfaq'] = $header;
		
		$template['total']   = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_DYNAMIC###');
		$template['content'] = $this->cObj->getSubPart($template['total'], '###CONTENT###');

		$subpartArray['###CONTENT###'] = $this->fillMarkers($template['content']);
		$content = $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray);

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
		$template['total'] = $this->cObj->getSubpart($this->templateCode, '###'.$templateName.'###');

		$temp = $this->cObj->getSubPart($template['total'], '###QUESTIONS###');
		$subpartArray['###QUESTIONS###'] = $this->fillMarkers($temp);

		$temp = $this->cObj->getSubPart($template['total'], '###ANSWERS###');
		$subpartArray['###ANSWERS###'] = $this->fillMarkers($temp);

		$content = $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray);

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
		$where = 'pid = '.$this->config['pidList'].$this->cObj->enableFields('tx_irfaq_q');	
		$selectConf = $this->getSelectConf($where);
		$selectConf['selectFields'] = 'DISTINCT tx_irfaq_q.uid, tx_irfaq_q.q, tx_irfaq_q.a, tx_irfaq_q.cat, tx_irfaq_q.expert';
		
		$res = $this->cObj->exec_getQuery('tx_irfaq_q', $selectConf);
		
		
		#$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_irfaq_q', 'pid = '.$this->config['pidList'].$this->cObj->enableFields('tx_irfaq_q'), '', 'sorting');

		$markerArray = array();


		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray['###FAQ_ID###']		   = $row['uid'];
			$markerArray['###FAQ_Q###']			   = $row['q'];
			$markerArray['###FAQ_A###']			   = $row['a'];

			$markerArray['###FAQ_CATEGORY###']     = $this->getCatMarkerArray($markerArray, $row);
			$markerArray['###FAQ_EXPERT###']	   = $this->experts[$row['expert']]['name'];
			$markerArray['###TEXT_EXPERT###']	   = $this->pi_getLL('text_expert');
			
			$markerArray['###FAQ_PM_IMG###']	   = '<img src="'.$this->config['iconPlus'].'" id="irfaq_pm_'.$row['uid'].'" alt="'.$this->pi_getLL('fold_faq').'" />';
			
			$subpart  = $this->cObj->getSubPart($template, '###FAQ###');
			$content .= $this->cObj->substituteMarkerArrayCached($subpart, $markerArray);
		}

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

		if(isset($this->categories[$row['uid']])) {
			reset($this->categories[$row['uid']]);
			$faq_category = array();

			while(list($key, $val) = each($this->categories[$row['uid']])) {

				// find categories, wrap them with links and collect them in the array $news_category.
				$faq_category[] = $this->categories[$row['uid']][$key]['title'];
			}
		}
		
		//check for empty categories
		if(!is_array($faq_category)) {
			$faq_category = array();
		}
		
		$markerArray['###FAQ_CATEGORY###'] = implode(', ', array_slice($faq_category, 0));

		return $markerArray['###FAQ_CATEGORY###'];
	}
	
	/**
	 * build the selectconf (array of query-parameters) to get the news items from the db
	 *
	 * @param	string		$where : where-part of the query
	 * @return	array		the selectconf for the display of a news item
	 */
	function getSelectConf($where) {
		$selectConf = array();
		$selectConf['pidInList'] = $this->config['pidList'];
		$selectConf['where'] = $where;
		
		//build SQL on condition of categoryMode
		if($this->config['categoryMode'] == 1) {
			$selectConf['leftjoin'] = 'tx_irfaq_q_cat_mm ON tx_irfaq_q.uid = tx_irfaq_q_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tx_irfaq_q_cat_mm.uid_foreign,0) IN (' .$this->config['catExclusive']. '))';
		}
		elseif ($this->config['categoryMode'] == -1) {
			$selectConf['leftjoin'] = 'tx_irfaq_q_cat_mm ON (tx_irfaq_q.uid = tx_irfaq_q_cat_mm.uid_local AND (tx_irfaq_q_cat_mm.uid_foreign=';
			
			//multiple categories selected?
			if(strpos($this->config['catExclusive'], ',')) {
				//yes
				$selectConf['leftjoin'] .= ereg_replace(',', ' OR tx_irfaq_q_cat_mm.uid_foreign=', $this->config['catExclusive']);
			}
			else {
				//no
				$selectConf['leftjoin'] .= $this->config['catExclusive'];
			}
			$selectConf['leftjoin'] .= '))';
			$selectConf['where'] .= ' AND (tx_irfaq_q_cat_mm.uid_foreign IS NULL)';
		}
		
		/*
		if ($this->config['categoryMode'] == -1) {
			$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,0) NOT IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
		} elseif ($this->catExclusive) {
			if ($this->config['categoryMode'] == 1) {
				$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
				$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,0) IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
			}
			if ($this->config['categoryMode'] == -1) {
				$selectConf['leftjoin'] = 'tt_news_cat_mm ON (tt_news.uid = tt_news_cat_mm.uid_local AND (tt_news_cat_mm.uid_foreign=';
				if (strstr($this->catExclusive, ',')) {
					$selectConf['leftjoin'] .= ereg_replace(',', ' OR tt_news_cat_mm.uid_foreign=', $this->catExclusive);
				} else {
					$selectConf['leftjoin'] .= $this->catExclusive?$this->catExclusive:0;
				}
				$selectConf['leftjoin'] .= '))';
				$selectConf['where'] .= ' AND (tt_news_cat_mm.uid_foreign IS NULL)';
			}
		} elseif ($this->config['categoryMode']) {
			$selectConf['leftjoin'] = 'tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tt_news_cat_mm.uid_foreign,\'nocat\') ' . ($this->config['categoryMode'] > 0?'':'!') . '=\'nocat\')';
		}
		*/
		
		return $selectConf;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/pi1/class.tx_irfaq_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/pi1/class.tx_irfaq_pi1.php']);
}

?>