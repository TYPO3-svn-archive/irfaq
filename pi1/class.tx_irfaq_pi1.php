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
	var $local_cObj;

	// Same as class name
	var $prefixId 		= 'tx_irfaq_pi1';		
	// Path to this script relative to the extension dir.
	var $scriptRelPath 	= 'pi1/class.tx_irfaq_pi1.php';	
	var $extKey 		= 'irfaq';	

	var $config 		= array();
	var $categories 	= array();
	var $experts		= array();
	var $pageArray 		= array();
	var $faqCount			= 0;
	var $hash			= ''; //a random hash to use multiple pi on one page

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

		return $this->pi_wrapInBaseClass($content);
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
		$ffCode = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'what_to_display');
		$this->config['code'] = $ffCode ?
			$ffCode : 
			strtoupper($conf['code']);

		// categoryModes are: 0=display all categories, 1=display selected categories, -1=display deselected categories
		$ffCategoryMode = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'categoryMode');
		$this->config['categoryMode'] = $ffCategoryMode ? 
			$ffCategoryMode : 
			$this->conf['categoryMode'];

		$ffCatSelection = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'categorySelection');
		$this->config['catSelection'] = $ffCatSelection ? 
			$ffCatSelection : 
			trim($this->conf['categorySelection']);

		// ignore category selection if categoryMode isn't set
		if($this->config['categoryMode'] != 0) {
			$this->config['catExclusive'] = $this->config['catSelection'];
		}
		else {
			$this->config['catExclusive'] = 0;
		}

		//set category by $_GET
		if (is_numeric($this->piVars['cat'])) {
			$this->config['catExclusive'] = $this->piVars['cat'];
			$this->config['categoryMode'] = 1;
		}

		// pidList is the pid/list of pids from where to fetch the faq items.
		$ffPidList = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages');
		$pidList   = $ffPidList ? 
			$ffPidList : 
			trim($this->cObj->stdWrap(
				$this->conf['pid_list'], $this->conf['pid_list.']
			));
		$this->config['pidList'] = $pidList ? 
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
		$this->config['pidList'] = $this->pi_getPidList(
			$this->config['pidList'], 
			$recursive
		);

		// max items per page
		$TSLimit = t3lib_div::intInRange($conf['limit'], 0, 1000);
		$this->config['limit'] = $TSLimit ? $TSLimit : 50;

		// display text like 'page' in pagebrowser
		$this->config['showPBrowserText'] = $this->conf['showPBrowserText']; 
		// get pageBrowser configuration
		$this->config['pageBrowser.']     = $this->conf['pageBrowser.']; 

		// read template file
		$this->templateCode = $this->cObj->fileResource(
			$this->config['templateFile']
		);

		$this->initCategories(); // initialize category-array
		$this->initExperts(); // initialize experts-array
		
		mt_srand(time());
		$this->hash = substr(md5(mt_rand()), 0, 5);
	}

	/**
	 * Getting all tx_irfaq_cat categories into internal array
	 * partly taken from tt_news - thx Rupert!!!
	 *
	 * @return	void
	 */
	function initCategories() {
		$storagePid = $GLOBALS['TSFE']->getStorageSiterootPids();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',
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

		$template['total']   = $this->cObj->getSubpart(
			$this->templateCode, '###TEMPLATE_DYNAMIC###'
		);
		$template['content'] = $this->cObj->getSubPart(
			$template['total'], '###CONTENT###'
		);

		$subpartArray['###CONTENT###'] = $this->fillMarkers($template['content']);

		//after calling fillMarkers we know count and can fill the corresponding js var
		$header  = '<script type="text/javascript" language="javascript">'.chr(10);
		$header .= '<!--'.chr(10);
		$header .= 'var tx_irfaq_pi1_iconPlus = "'.$this->config['iconPlus'].'";'.chr(10);
		$header .= 'var tx_irfaq_pi1_iconMinus = "'.$this->config['iconMinus'].'";'.chr(10);
		$header .= '// -->'.chr(10);
		$header .= '</script>'.chr(10);
		$header .= '<script type="text/javascript" src="'.
			t3lib_extMgm::siteRelPath($this->extKey).'res/toggleFaq.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['tx_irfaq'] = $header;

		$markerArray = array(
			'###HASH###' => $this->hash,
			'###COUNT###' => $this->faqCount,
		);
		$content = $this->cObj->substituteMarkerArrayCached(
			$template['total'], 
			$markerArray, 
			$subpartArray
		);

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
		$where 		= 'pid = '.$this->config['pidList'].$this->cObj->enableFields('tx_irfaq_q');
		$selectConf = $this->getSelectConf($where);
		$selectConf['selectFields'] = 'DISTINCT tx_irfaq_q.uid, tx_irfaq_q.q, tx_irfaq_q.q_from, tx_irfaq_q.a, tx_irfaq_q.cat, tx_irfaq_q.expert';
		$selectConf['orderBy'] 		= 'tx_irfaq_q.sorting';

		$res = $this->cObj->exec_getQuery('tx_irfaq_q', $selectConf);
		$this->faqCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		$markerArray = array();
		$i = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray['###FAQ_ID###'] = ++$i;
			$markerArray['###FAQ_Q###']	 = $this->formatStr(
				$this->local_cObj->stdWrap(
					$row['q'], 
					$this->config['question_stdWrap.']
				)
			);
			$markerArray['###FAQ_A###']	 = $this->formatStr(
				$this->local_cObj->stdWrap(
					$row['a'], 
					$this->config['answer_stdWrap.']
				)
			);
			//categories
			$markerArray    			 = $this->getCatMarkerArray(
				$markerArray, 
				$row
			);

			if($this->config['singleOpen']) {
				$markerArray['###SINGLE_OPEN###'] = 'true';
			}
			else {
				$markerArray['###SINGLE_OPEN###'] = 'false';
			}

			if($row['expert']) {
				$this->local_cObj->LOAD_REGISTER(
				array(
					'faqExpertEmail' => $this->experts[$row['expert']]['email'],
					'faqExpertUrl'   => $this->experts[$row['expert']]['url']
				),
				'');
				$markerArray['###FAQ_EXPERT###']	   = $this->local_cObj->stdWrap(
					$this->experts[$row['expert']]['name'], 
					$this->config['expert_stdWrap.']
				);
				
				$markerArray['###TEXT_EXPERT###']	   = $this->local_cObj->stdWrap(
					$this->pi_getLL('text_expert'), 
					$this->config['text_expert_stdWrap.']
				);
				
				$markerArray['###FAQ_EXPERT_EMAIL###'] = $this->local_cObj->stdWrap(
					$this->experts[$row['expert']]['email'], 
					$this->config['expertemail_stdWrap.']
				);
								
				if($this->experts[$row['expert']]['url']) {
					$markerArray['###FAQ_EXPERT_URL###'] = $this->local_cObj->stdWrap(
						$this->experts[$row['expert']]['url'], 
						$this->config['experturl_stdWrap.']
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
					$this->config['text_asked_by_stdWrap.']
				);
				$markerArray['###ASKED_BY###'] = $this->local_cObj->stdWrap(
					$row['q_from'], 
					$this->config['asked_by_stdWrap.']
				);
			}
			else {
				$markerArray['###TEXT_ASKED_BY###'] = '';
				$markerArray['###ASKED_BY###'] = '';
			}

			$markerArray['###FAQ_PM_IMG###'] = '<img src="'.
				$this->config['iconPlus'].'" id="irfaq_pm_'.$i.'_'.$this->hash.'" alt="'.
				$this->pi_getLL('fold_faq').'" />';

			$markerArray['###HASH###'] = $this->hash;
			
			$subpart  = $this->cObj->getSubPart($template, '###FAQ###');
			$content .= $this->cObj->substituteMarkerArrayCached($subpart, 
																 $markerArray);
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
		$markerArray['###TEXT_CATEGORY###'] = '';

		if(isset($this->categories[$row['uid']])) {
			reset($this->categories[$row['uid']]);
			$faq_category = array();

			while(list($key, $val) = each($this->categories[$row['uid']])) {
				// find categories, wrap them with links and collect them in the array $news_category.
				if ($this->config['catTextMode'] == 1) {
					// link to category shortcut page
					$faq_category[] = $this->pi_linkToPage(
						$this->categories[$row['uid']][$key]['title'], 
						$this->categories[$row['uid']][$key]['shortcut']
					);
				}
				else if($this->config['catTextMode'] == 2) {
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
				$this->config['category_stdWrap.']
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
		$selectConf['pidInList'] = $this->config['pidList'];
		$selectConf['where'] = $where;

		//build SQL on condition of categoryMode
		if($this->config['categoryMode'] == 1) {
			$selectConf['leftjoin'] = 'tx_irfaq_q_cat_mm ON tx_irfaq_q.uid = tx_irfaq_q_cat_mm.uid_local';
			$selectConf['where']   .= ' AND (IFNULL(tx_irfaq_q_cat_mm.uid_foreign,0) IN (' .$this->config['catExclusive']. '))';
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
		return $selectConf;
	}

	/**
	 * Format string with general_stdWrap from configuration
	 *
	 * @param	string		string to wrap
	 * @return	string		wrapped string
	 */
	function formatStr($str) {
		if (is_array($this->config['general_stdWrap.'])) {
			$str = $this->local_cObj->stdWrap(
				$str, 
				$this->config['general_stdWrap.']
			);
		}
		return $str;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/pi1/class.tx_irfaq_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/irfaq/pi1/class.tx_irfaq_pi1.php']);
}

?>