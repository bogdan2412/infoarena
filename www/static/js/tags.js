function show_tag_list(parent_tag_id) {
    var tag_lists = $$(".tag_list");
    var i = 0;

    document.getElementById('tag_list_' + parent_tag_id).style.display = "inline";
    document.getElementById('tag_anchor_' + parent_tag_id).style.display = "none";
}

function show_tags() {
    var show_tag_anchors = $$(".tags_list_item");

    for (i = 0; i < show_tag_anchors.length; i++) {
        show_tag_anchors[i].style.display = "list-item";
    }

    document.getElementById('show_tags').style.display = "none";
}
