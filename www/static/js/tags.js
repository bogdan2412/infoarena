$(function() {

  $('#show_algorithm_tags').on('click', function() {
    $(this).hide();
    $(this).next('ul').slideDown();
    return false;
  });

  $('.show_tag_anchor').on('click', function() {
    $(this).hide();
    $(this).next('span').fadeIn();
    return false;
  });

});
