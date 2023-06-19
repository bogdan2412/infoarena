var Monitor_Url;
var Monitor_Timeout; // dictated by checkbox data-interval
var Monitor_AutoRefresh; // dictated by checkbox checked state
var Monitor_RefreshTimeout = null;

function SkipJobs() {
    var list = new Array;
    $(".skip_job:checked").each(function() {
        list.push($(this).val());
    });
    if (list.length == 0) {
        alert("Atenție! Selectează cel puțin un job.");
        return false;
    }

    if (submit = confirm(
        "Ești sigur că vrei să ignori " + list.length + " job-uri?")) {
        $("#skipped-jobs").val(list.join());
        return;
    }
    return false;
}

function Monitor_Refresh() {
    Monitor_RefreshTimeout = null;
    if (Monitor_AutoRefresh) { // could have been turned off after it was enqueued
        $("#monitor-table").load(Monitor_Url, {},
            function(responseText, statusText, req) {
                if (Monitor_AutoRefresh && Monitor_RefreshTimeout === null) {
                    Monitor_RefreshTimeout =
                        setTimeout(Monitor_Refresh, Monitor_Timeout);
                }
            });
    }
}

function Monitor_Init() {
    Monitor_AutoRefresh = $('#autorefresh')[0].checked;
    Monitor_Timeout = $('#autorefresh').data('interval');
    $(".skip_job").live('click', function() {
        Monitor_AutoRefresh = false;
        clearTimeout(Monitor_RefreshTimeout);
        Monitor_RefreshTimeout = null;
        $('#autorefresh').prop('checked', false);
    });
    $("#skip-jobs-form").live('submit', SkipJobs);

    $(".skip-job-link").live("click", function() {
        var job_id = $(this).prev().val();
        $.ajax({url:BASE_HREF + 'json/job-skip?job_id=' + escape(job_id),
            type:'POST', dataType: 'json', success:
            function(data, textStatus, req) {
                if (textStatus == 'error') {
                    alert('Eroare! Nu se poate ignora submisia.');
                } else {
                    Monitor_Refresh();
                }
                return;
            }});
        return false;
    });

    $("#skip-all-checkbox").live("click", function() {
        $(".skip_job").prop('checked', $(this).prop('checked'));
    })

    Monitor_ToggleRefresh(Monitor_AutoRefresh);
}



function Monitor_ToggleRefresh(selected) {
    Monitor_AutoRefresh = selected;
    if (Monitor_AutoRefresh) {
        Monitor_Refresh();
    }
}

$(document).ready(Monitor_Init);
