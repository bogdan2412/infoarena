/**
 * (c) 2012 Asociatia Infoarena
 */

/**
 * Binds the Toggle Button to the form element
 * On click hide the original element(if it exists)
 * and display the form instead
 */
function bindToggleToForm(toggleElement, formElement, originalElement) {
    // We backup the original text
    var initialText = toggleElement.text();
    formElement.hide();

    toggleElement.click(function(e) {
        e.preventDefault();
        if (formElement.css('display') == 'none') {
            // we display the form, hide the original element and
            // change the label of the Toggle Button
            formElement.show();
            toggleElement.text("Anulează");
            if (typeof(originalElement) != "undefined") {
                originalElement.hide();
            }
        } else {
            // we hide the form, display the original element and
            // change the label of the Toggle Button to the oriinal value
            formElement.hide();
            toggleElement.text(initialText);
            if (typeof(originalElement) != "undefined") {
                originalElement.show();
            }
        }
    });
}

$(document).ready(function() {
    bindToggleToForm($("#add_category > a"), $("#add_category > form"));
    bindToggleToForm($("#add_author > a"), $("#add_author > form"));

    // Adding new tags
    var addForms = $(".algorithm_tag_add form");
    var addLinks = $(".algorithm_tag_add a.toggle_add");
    for (var i = 0; i < addForms.length; ++i)
        bindToggleToForm($(addLinks[i]), $(addForms[i]));

    // Renaming tags
    var renameForms = $("table.category form");
    var renameLinks = $("table.category a.toggle_rename");
    var renameOriginals = $("table.category a.algorithm_tag");
    for (var i = 0; i < renameForms.length; ++i)
        bindToggleToForm($(renameLinks[i]), $(renameForms[i]), $(renameOriginals[i]));

    // Renaming categories
    var renameCategoryForms = $(".rename_method form");
    var renameCategoryLinks = $(".rename_method a.toggle_rename");
    for (var i = 0; i < renameCategoryForms.length; ++i)
        bindToggleToForm($(renameCategoryLinks[i]), $(renameCategoryForms[i]));
})
