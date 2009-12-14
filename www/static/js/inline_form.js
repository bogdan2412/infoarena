/**
 * (c) 2009 Asociatia Infoarena
 */

function bindToggleLinkToForm(toggleElement, formElement, originalElement) {
    var initialText = toggleElement.innerHTML;
    formElement.style.display = "none";
    connect(toggleElement, "onclick", function() {
        if (formElement.style.display == "none") {
            // Display inline form and replace toggleElement with "Anuleaza"
            formElement.style.display = "inline";
            toggleElement.innerHTML = "Anuleaza";
            // Hide original content if specified
            if (typeof(originalElement) != "undefined") {
                originalElement.style.display = "none";
            }
            // Focus input in form
            findChildElements(formElement, ["input[type=text]"])[0].focus();
        } else {
            // Hide inline form and replace toggleElement with original text
            formElement.style.display = "none";
            toggleElement.innerHTML = initialText;
            // Show original content if specified
            if (typeof(originalElement) != "undefined") {
                originalElement.style.display = "block";
            }
        }
    });
}
