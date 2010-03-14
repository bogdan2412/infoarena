/**
 * DHTML for task solution submission
 * (c) 2006 infoarena
 */

var Submit_CompilerDisplay;

function Submit_HasCompiler(taskId) {
    var o = $('output_only');
    return taskId && (!o ||  (0 > o.value.indexOf(':' + taskId + ':')));
}

function Submit_AutoCompiler() {
    var f = $('form_solution');
    var compiler = $('form_compiler');

    // we simply map file extensions to hard-coded compiler IDs
    var k = -1;
    for (var i = f.value.length - 1; 0 <= i; i--) {
        if ('.' == f.value.charAt(i)) {
            k = i;
            break;
        }
    }
    var ext = f.value.substring(k + 1).toLowerCase();
    if ('c' == ext || 'cpp' == ext || 'pas' == ext || 'py' == ext) {
        if ('pas' == ext) {
            // choose FreePascal compiler
            compiler.value = 'fpc';
        }
        else {
            compiler.value = ext;
        }
    }
    else {
        alert('Atentie! Pentru fisierul selectat nu am putut alege automat ' +
            'un compilator.');
        compiler.value = '-';
    }
}

function Submit_UpdateSolution() {
    if (!Submit_HasCompiler($('form_task').value)) {
        return;
    }

    // auto-choose compiler
    Submit_AutoCompiler();
}

function Submit_UpdateTask() {
    var t = $('form_task');

    // toggle displaying compiler select box
    if (Submit_HasCompiler(t.value)) {
        $('field_compiler').style.display = Submit_CompilerDisplay;
    } else {
        $('field_compiler').style.display = 'none';
    }

    if (t.value) {
        $('field_round').style.display = Submit_RoundDisplay;
        var d = doXHR(BASE_HREF + 'json/task-get-rounds?task_id=' + escape(t.value), {method: 'POST'});

        var ready = function(xhr) {
            var data = evalJSONRequest(xhr);
            var rounds = data["rounds"];
            var default_round = data["default"];

            $('form_round').innerHTML = '';
            warning_container = $('field_round_warning');
            if (warning_container) {
                if (rounds.length != 1) {
                    warning_container.innerHTML = '<p class="submit-warning">Această problemă face parte din mai multe concursuri. Selectează-l pe cel la care participi!</p>';
                } else {
                    warning_container.innerHTML = '';
                }
            }
            for (var key in rounds) {
                if (rounds.hasOwnProperty(key)) {
                    var option = document.createElement('option');
                    option.value = rounds[key]["id"];
                    if (rounds[key]["id"] == default_round) {
                        option.selected = 'selected';
                    }
                    var text = document.createTextNode(rounds[key]["title"]);
                    option.appendChild(text);
                    $('form_round').appendChild(option);
                }
            }
        };

        var error = function(error) {
            window.alert('Eroare! Nu pot determina rundele. Incercati din nou.');
        };

        d.addCallbacks(ready, error);
    } else {
        $('field_round').style.display = 'none';
    }

    // auto-choose compiler
    if (0 < $('form_solution').value.length) {
        Submit_AutoCompiler();
    }
}

function Submit_Init() {
    if (!$('task_submit')) {
        // no such form on this page
        return;
    }

    var fSolution = $('form_solution');
    var fTask = $('form_task');

    Submit_CompilerDisplay = $('field_compiler').style.display;
    Submit_RoundDisplay = $('field_round').style.display;

    connect(fSolution, 'onchange', Submit_UpdateSolution);
    if ('hidden' != fTask.type) {
        connect(fTask, 'onchange', Submit_UpdateTask);
    }

    Submit_UpdateTask();
}

connect(window, 'onload', Submit_Init);

