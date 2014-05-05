/**
 * DHTML for task solution submission
 * (c) 2006 infoarena
 */

var Submit_CompilerDisplay;

function Submit_HasCompiler(taskId) {
    var o = $('#output_only');
    return taskId && (o.length == 0);
}

function Submit_AutoCompiler() {
    var f = $('#form_solution');
    var compiler = $('#form_compiler');

    // we simply map file extensions to hard-coded compiler IDs
    var k = -1;
    for (var i = f.val().length - 1; 0 <= i; i--) {
        if ('.' == f.val().charAt(i)) {
            k = i;
            break;
        }
    }
    var ext = f.val().substring(k + 1).toLowerCase();

    if ('c' == ext || 'cc' == ext || 'cpp' == ext || 'pas' == ext || 'py' == ext || 'java' == ext) {
        if ('pas' == ext) {
            // choose FreePascal compiler
            compiler.val('fpc');
        }
        else if ('cc' == ext) {
            // choose GNU C++ compiler
            compiler.val('cpp');
        }
        else {
            compiler.val(ext);
        }
    }
    else {
        alert('Atentie! Pentru fisierul selectat nu am putut alege automat ' +
            'un compilator.');
        compiler.val('-');
    }
}

function Submit_UpdateSolution() {
    if (!Submit_HasCompiler($('#form_task').val())) {
        return;
    }

    // auto-choose compiler
    Submit_AutoCompiler();
}

function Submit_UpdateTask() {
    var t = $('#form_task');

    // toggle displaying compiler select box
    if (Submit_HasCompiler(t.val())) {
        $('#field_compiler').css('display', Submit_CompilerDisplay);
    } else {
        $('#field_compiler').css('display', 'none');
    }

    if (t.val()) {
        $('#field_round').css('display', Submit_RoundDisplay);

        $.ajax({
            url:BASE_HREF + 'json/task-get-rounds?task_id=' + escape(t.val()),
            dataType: 'json', type: 'POST', success:
            function(response, postStatus, xhr) {
                if (postStatus == 'error') {
                    alert('Eroare! Nu pot determina rundele. Incercati din nou.');
                    return;
                }
                var data = response;//$.parseJSON(response);
                var rounds = data["rounds"];
                var default_round = data["default"];

                $('#form_round').html('');
                warning_container = $('#field_round_warning');
                if (warning_container.length > 0) {
                    if (rounds.length != 1) {
                        warning_container.html(
                            '<p class="submit-warning">Această problemă face p'
                          + 'arte din mai multe concursuri. Selectează-l pe ce'
                          + 'l la care participi!</p>');
                    } else {
                        warning_container.html('');
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
                    $('#form_round').append(option);
                }
            }
        }});
    } else {
        $('#field_round').hide();
    }

    // auto-choose compiler
    if ($('#form_solution').val()) {
        Submit_AutoCompiler();
    }
}

function Submit_Init() {
    if ($('#task_submit').length == 0) {
        // no such form on this page
        return;
    }

    var fSolution = $('#form_solution');
    var fTask = $('#form_task');

    Submit_CompilerDisplay = $('#field_compiler').css('display');
    Submit_RoundDisplay = $('#field_round').css('display');

    fSolution.on("change", Submit_UpdateSolution);

    if ('hidden' != fTask.type) {
        fTask.on("change", Submit_UpdateTask);
    }

    Submit_UpdateTask();
}

$(document).ready(Submit_Init);
//connect(window, 'onload', Submit_Init);
