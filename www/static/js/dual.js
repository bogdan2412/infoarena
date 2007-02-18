/*
* dual.js
* Cristian George Strat,	updated 2005-08-28
*
* Transparently transforms multiple-select-boxes (<select multiple="multiple" .. >)
* into interactive dual-list-boxes. Form submission events are automatically handled.
*
* Simply link this script in your page and call DlbInit() for each multiple-select-box
* you wish to transform. Additionally you may wish to apply visual formatting rules
* through CSS. Controls are enclosed by a layout table (style class "dualList").
*
* This code is public domain.
*/

var DlbHash = {
};

/*
* Transforms a multiple-select-box into an interactive dual-list-box.
* Attaches an onsubmit event to the enclosing form. The old onsubmit event
* is still delegated and properly handled.
*/
function DlbInit(selectId) {
	var i, l, o;

	// ## reference to select-box
	var select = document.getElementById(selectId);
	if (!select || !select.multiple) return;

	// ## create widget structure
	// layout table
	var table = document.createElement('table');
	var tbody, tr, td1, td2, td3;
	table.className = 'dualList';
	table.appendChild(tbody = document.createElement('tbody'));
	tbody.appendChild(tr = document.createElement('tr'));
	tr.appendChild(td1 = document.createElement('td'));
	tr.appendChild(td2 = document.createElement('td'));
	tr.appendChild(td3 = document.createElement('td'));

	// let select-box
	var select1 = document.createElement('select');
	select1.multiple = true;
	select1.disabled = select.disabled;
	select1.size = select.size;
	// select1.style.width = '10em';
	select1.id = '_dlb1_' + selectId;
	select1.ondblclick = function() {
		DlbMove(this.id, DlbHash[this.id], false);
	}
	td1.appendChild(select1);

	// right select-box
	var select2 = document.createElement('select');
	select2.multiple = true;
	select2.disabled = select.disabled;
	select2.size = select.size;
	select2.width = select.width;
	// select2.style.width = '10em';
	select2.name = select.name;
	select2.id = '_dlb2_' + selectId;
	select2.ondblclick = function() {
		DlbMove(this.id, DlbHash[this.id], false);
	}
	td3.appendChild(select2);

	// link select-boxes
	DlbHash[select1.id] = select2.id;
	DlbHash[select2.id] = select1.id;

	// ## create and distribute options
	for (l = select.childNodes.length, i = 0; i < l; i++) {
		if ('OPTION' != select.childNodes[i].nodeName) continue;
		o = new Option(select.childNodes[i].text, select.childNodes[i].value, false, false); 

		if (select.childNodes[i].selected) {
			select2.options[select2.options.length] = o;
		}
		else {
			select1.options[select1.options.length] = o;
		}
	}

	// ## action buttons
	// > (move selected items to right)
	var b;
	b = document.createElement('input');
	b.disabled = select.disabled;
	b.setAttribute('type', 'button');
	b.setAttribute('value', '>');
	b.setAttribute('id', '_dlb3_' + selectId);
	DlbHash[b.id] = Array(select1.id, select2.id);
	b.onclick = function() {
		DlbMove(DlbHash[this.id][0], DlbHash[this.id][1], false);
	}
	td2.appendChild(b);

	// < (move selected items to left)
	b = document.createElement('input');
	b.disabled = select.disabled;
	b.setAttribute('type', 'button');
	b.setAttribute('value', '<');
	b.setAttribute('id', '_dlb4_' + selectId);
	DlbHash[b.id] = Array(select1.id, select2.id);
	b.onclick = function() {
		DlbMove(DlbHash[this.id][1], DlbHash[this.id][0], false);
	}
	td2.appendChild(b);

	// >>> (move all items to right)
	b = document.createElement('input');
	b.disabled = select.disabled;
	b.setAttribute('type', 'button');
	b.setAttribute('value', '>>>');
	b.setAttribute('id', '_dlb5_' + selectId);
	DlbHash[b.id] = Array(select1.id, select2.id);
	b.onclick = function() {
		DlbMove(DlbHash[this.id][0], DlbHash[this.id][1], true);
	}
	td2.appendChild(b);

	// <<< (move all items to left)
	b = document.createElement('input');
	b.disabled = select.disabled;
	b.setAttribute('type', 'button');
	b.setAttribute('value', '<<<');
	b.setAttribute('id', '_dlb6_' + selectId);
	DlbHash[b.id] = Array(select1.id, select2.id);
	b.onclick = function() {
		DlbMove(DlbHash[this.id][1], DlbHash[this.id][0], true);
	}
	td2.appendChild(b);

	// ## attach onsubmit event
	var form;

	// get enclosing form
	for (form = select; form && (form.nodeName != "FORM"); form = form.parentNode);
	
	if (form) {
		// the enclosing form has to have an id
		if (!form.id) {
			form.id = "_dlb0_" + selectId;
		}

		// has this form already been attached a dual-list-box event?
		// (occurs when multiple dual-list-boxes are enclosed in a single form)
		if (DlbHash[form.id]) {
			// add to this form's current list of dual-list-boxes  
			DlbHash[form.id][0].push(select2.id);
		}
		else {
			// create and attach event
			DlbHash[form.id] = Array(Array(select2.id), form.onsubmit);
		
			form.onsubmit = function() {
				// call initial onsubmit event
				var result = DlbHash[this.id][1] ? DlbHash[this.id][1]() : true;
				var l, i;

				if (result) {
					// select options to be submitted
					for (l = DlbHash[this.id][0].length, i = 0; i < l; i++) {
						DlbSelectAll(DlbHash[this.id][0][i]);
					}
					return true;
				}
				else {
					// initial onsubmit event returned false. submission failed,
					// so no selections this time
					return false;
				}
			}
		}
	}
	
	// ## replace old select-box with the new dual-list-box
	select.parentNode.insertBefore(table, select);
	select.parentNode.removeChild(select);
}

/*
* Moves elements from one select-box to another.
*/
function DlbMove(from, to, all) {
	// reference select-boxes
	var select1 = document.getElementById(from);
	var select2 = document.getElementById(to);
	if (!select1 || !select2) return;
	
	var l, i, o;

	// iterate through select options and act accordingly
	for (l = select1.options.length, i = 0; i < l; i++) {
		if ('OPTION' != select1.childNodes[i].nodeName) continue;

		if (all || select1.childNodes[i].selected) {
			o = new Option(select1.childNodes[i].text, select1.childNodes[i].value, false, false); 
			select2.options[select2.options.length] = o;
			
			select1.removeChild(select1.childNodes[i]);
			i--;	// freeze index
			l--;	// update length
		}
	}
}

/*
* Selects all options in a given select-box.
*/
function DlbSelectAll(selectId) {
	var i;
	
	var select = document.getElementById(selectId);
	if (!select) return;
	
	for (i = select.length - 1; 0 <= i; i--) {
		select[i].selected = true;
	}
}
