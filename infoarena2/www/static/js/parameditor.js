/**
 * DHTML for parameter editor
 * (c) 2006 infoarena
 */

function ParamEditor_Init() {
    connect($('form_type'), 'onchange', ParamEditor_TypeChange);
    ParamEditor_TypeChange();
}

// Called when form_type is changed.
function ParamEditor_TypeChange() {
    cval = $('form_type').value;
    ParamEditor_Switch(cval);
}

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
}

connect(window, 'onload', ParamEditor_Init);

