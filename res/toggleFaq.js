/**
 * toggle FAQ Items
 *
 * @param index   the index of the FAQ item to hide or show
 * @param single  true to show only one item at a time, false the open as many as you want
 */
function tx_irfaq_toggleFaq(index, totalcount, single, hash) {

	if (single) {
		// show only one Q+A at a time
		tx_irfaq_toggleAll(false, hash, totalcount);
		tx_irfaq_showHideFaq(index, true, hash);
	}
	else {
		// open as many Q+A as you like
		var hidden = (document.getElementById('irfaq_a_'+index+'_'+hash).className == 'tx-irfaq-dynans-hidden');
		tx_irfaq_showHideFaq(index, hidden, hash);
	}
}

/**
 * shows or hides a FAQ item at a time depending on the given status
 *
 * @param index  the index of the FAQ item to hide or show
 * @param show   true to show the item, false to hide it
 */

function tx_irfaq_showHideFaq(index, show, hash) {
	var faq_id = 'irfaq_a_'+index+'_'+hash; //answer
	var pm_id  = 'irfaq_pm_'+index+'_'+hash; // plus/minus icon

	if (show) {
		document.getElementById(faq_id).className = 'tx-irfaq-dynans-visible';
		document.getElementById(pm_id).src = tx_irfaq_pi1_iconMinus;
	}
	else {
		document.getElementById(faq_id).className = 'tx-irfaq-dynans-hidden';
		document.getElementById(pm_id).src = tx_irfaq_pi1_iconPlus;
	}
}

/**
 * shows or hides all FAQ items with one click
 *
 * @param mode	true to show the items, false to hide them
 */
function tx_irfaq_toggleAll(mode, hash, index) {
	for (i = 0; i < index; i++) {
		tx_irfaq_showHideFaq(i+1, mode, hash);
	}
}
