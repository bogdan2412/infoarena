/**
 * DHTML for task solution submission
 * (c) 2006 info-arena
 */ 

var Submit_CompilerDisplay;

function Submit_Init() {
    Submit_CompilerDisplay = $('field_compiler').style.display;
    
    connect($('form_solution'), 'onchange', Submit_UpdateSolution);
    connect($('form_task'), 'onchange', Submit_UpdateTask);

    Submit_UpdateTask();
}

function Submit_HasCompiler(taskId) {
    var o = $('output_only');
    return !o || (0 > o.value.indexOf(':' + taskId + ':'));
}

function Submit_UpdateSolution() {
    var f = $('form_solution');
    var compiler = $('form_compiler');

    var k = -1;
    for (var i = f.value.length - 1; 0 <= i; i--) {
        if ('.' == f.value[i]) {
            k = i;
            break;
        }
    }
    var ext = f.value.substring(k + 1).toLowerCase();

    if ('c' == ext || 'cpp' == ext || 'pas' == ext) {
        compiler.value = ext;
    }
    else {
        alert('Atentie! Pentru fisierul selectat nu am putut alege automat ' 
              + 'un compilator.');
        compiler.value = '';
    }
}

function Submit_UpdateTask() {
    var t = $('form_task');
    if (Submit_HasCompiler(t.value)) {
        $('field_compiler').style.display = Submit_CompilerDisplay;
    }
    else {
        $('field_compiler').style.display = 'none';
    }
}

connect(window, 'onload', Submit_Init);

