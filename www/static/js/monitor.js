$(function() {

  var autorefreshTimeout = null;
  var autorefreshInterval = null;

  init();

  function init() {
    autorefreshInterval = $('#autorefresh').data('interval');
    checkCheckboxIfHashExists();
    if (autorefreshIsChecked()) {
      createTimeout();
    }
    $('#autorefresh').change(onAutorefreshChange);
    $('.skip_job').change(onSkipChange);
    $('.skip-job-link').click(onSkipLinkClick)
    $("#skip-all-checkbox").change(onSkipAllChange);
    $('#skip-jobs-form').submit(onSkipFormSubmit);
  }

  function checkCheckboxIfHashExists() {
    var hash = window.location.hash;
    if (hash == '#auto') {
      $('#autorefresh').prop('checked', true);
    }
  }

  function autorefreshIsChecked() {
    return $('#autorefresh').prop('checked');
  }

  function createTimeout() {
    autorefreshTimeout = setTimeout(refreshIfCheckboxChecked, autorefreshInterval);
    appendHashIfNeeded();
  }

  function deleteTimeout() {
    if (autorefreshTimeout) {
      clearTimeout(autorefreshTimeout);
      autorefreshTimeout = null;
    }
  }

  function refreshIfCheckboxChecked() {
    if (autorefreshIsChecked()) {
      location.reload();
    }
  }

  function appendHashIfNeeded() {
    var autorefreshInServerConfig = $('#autorefresh').data('config');
    if (!autorefreshInServerConfig) {
      window.location.hash = 'auto';
    }
  }

  function onAutorefreshChange() {
    if (autorefreshIsChecked()) {
      createTimeout();
    } else {
      deleteTimeout();
    }
  }

  function onSkipChange() {
    $('#autorefresh').prop('checked', false);
  }

  function onSkipLinkClick() {
    var jobId = $(this).prev().val();

    if (!confirm('Confirmi ignorarea jobului #' + jobId + '?')) {
      return false;
    }

    var url = BASE_HREF + 'json/job-skip?job_id=' + jobId;
    var success = function(data, textStatus, req) {
      if (textStatus == 'error') {
        alert('Eroare! Nu am putut ignora jobul.');
      } else {
        location.reload();
      }
    }

    $.post(url, success);
    return false;
  }

  function onSkipAllChange() {
    var val = $(this).prop('checked');
    $(".skip_job").prop('checked', val);
  }

  function onSkipFormSubmit() {
    var list = [];
    $('.skip_job:checked').each(function() {
      list.push($(this).val());
    });

    if (list.length == 0) {
      alert('Trebuie să selectezi cel puțin un job.');
      return false;
    }

    $('#skipped-jobs').val(list.join());

    var msg = 'Confirmi ignorarea a ' + list.length + ' joburi?'
    return confirm(msg);
  }

});
