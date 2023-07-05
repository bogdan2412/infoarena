/**
 * sitewide JS
 * (c) infoarena
 */

function Page_Init() {
  // fade away flash messages except for errors
  setTimeout(function() {
    $('.flash:not(.flash-error)').hide('slow');
  }, 10000);

  // page log (used in development mode)
  var log = $('#log');
  if (log.length) {
    // scroll down
    log.scrollTop(log.prop('scrollHeight') - log.height());

    // maximize on click
    var callback = function(event) {
      log.height(log.prop('scrollHeight'));
      log.prop('id', 'log_active');
    }
    log.one('click', callback);
  }
}

$(document).ready(Page_Init);
