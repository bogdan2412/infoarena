/**
 * DHTML for parameter editor
 * (c) 2006 infoarena
 */

// cata: This kicks in when changing the task type (between classic and interactive)

// Initialize paramter editor
function ParamEditor_Init() {
  $('#form_type').on("change", ParamEditor_TypeChange);
  ParamEditor_TypeChange();
}

// Called when form_type is changed.
function ParamEditor_TypeChange() {
  ParamEditor_Switch($('#form_type').val());
}

// Horrible horrible hack.
// Whatever, I don't care about JavaScript
function ParamEditor_Switch(new_type) {
  $('table.parameters').hide();
  $('ul.form.parameters').hide();

  $('#params_' + new_type).show();
}

$(document).ready(ParamEditor_Init);
