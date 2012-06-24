function show_tag_list(parent_tag_id) {
    var tag_lists = $(".tag_list");
    var i = 0;

    document.getElementById('tag_list_' + parent_tag_id).style.display = "inline";
    document.getElementById('tag_anchor_' + parent_tag_id).style.display = "none";
}

function show_tags() {
    $(".tags_list_item").css('display', 'list-item');

    $('#show_tags').hide();
}
