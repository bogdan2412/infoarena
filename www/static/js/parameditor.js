/**
 * DHTML for parameter editor
 * (c) 2006 infoarena
 */

// Note: this code sucks, everything is hardcoded.

// Initialize paramter editor
function ParamEditor_Init() {
    connect($('form_type'), 'onchange', ParamEditor_TypeChange);
    ParamEditor_TypeChange();
}

// Called when form_type is changed.
function ParamEditor_TypeChange() {
    cval = $('form_type').value;
    ParamEditor_Switch(cval);
}

// Horrible horrible hack.
// Whatever, I don't care about JavaScript
function ParamEditor_Switch(new_type) {
    tables = document.getElementsByTagName('table');
    for (i = 0; i < tables.length; ++i) {
        table = tables[i];
        if (table.className == 'parameters') {
            if ("params_" + new_type == table.id) {
                table.style.display = 'block';
            } else {
                table.style.display = 'none';
            }
        }
    }
    lists = document.getElementsByTagName('ul');
    for (i = 0; i < lists.length; ++i) {
        list = lists[i];
        if (list.className == 'form parameters') {
            if ("params_" + new_type == list.id) {
                list.style.display = 'block';
            } else {
                list.style.display = 'none';
            }
        }
    }
}

connect(window, 'onload', ParamEditor_Init);

