/**
 * toggle FAQ Items
 *
 * @param id 		the id of the FAQ item to hide or show
 * @param single	true to show only one item at a time, false the open as many as you want
 */
function toggleFaq(id, single, hash) {	
		
	if(single) {
		//show only one Q+A at a time
		toggleAll(false, hash);		
		showHideFaq(id, true, hash);
	}
	else {
		//open as many Q+A as you like		
		if(document.getElementById('irfaq_a_'+id+'_'+hash).style.display == 'none') {
			showHideFaq(id, true, hash);
		}
		else {
			showHideFaq(id, false, hash);
		}			
	}	
}

/**
 * shows or hides a FAQ item at a time depending on the given status
 *
 * @param id 		the id of the FAQ item to hide or show
 * @param status	true to show the item, false to hide it
 */
function showHideFaq(id, status, hash) {
	var faq_id = 'irfaq_a_'+id+'_'+hash; //answer
	var pm_id  = 'irfaq_pm_'+id+'_'+hash; // plus/minus icon
	
	if(status) {
		document.getElementById(faq_id).style.display = 'inline';
		document.getElementById(pm_id).src = tx_irfaq_pi1_iconMinus;
	}
	else {
		document.getElementById(faq_id).style.display = 'none';	
		document.getElementById(pm_id).src = tx_irfaq_pi1_iconPlus;
	}
}

/**
 * shows or hides all FAQ items with one click
 *
 * @param mode	true to show the items, false to hide them
 */
function toggleAll(mode, hash, count) {
	for(i = 0; i < count; i++) {
		showHideFaq(i+1, mode, hash);
	}				
}