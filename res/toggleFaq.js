function toggleFaq(id) {
	var faq_id = 'irfaq_a_'+id; //answer
	var pm_id = 'irfaq_pm_'+id; // plus/minus icon
	
	if(document.getElementById(faq_id).style.display == 'none') {
		document.getElementById(faq_id).style.display = 'inline';
		document.getElementById(pm_id).src = tx_irfaq_pi1_iconMinus;
	}
	else {
		document.getElementById(faq_id).style.display = 'none';	
		document.getElementById(pm_id).src = tx_irfaq_pi1_iconPlus;
	}	
		
	
			
}