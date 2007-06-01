<?php
require_once(IA_ROOT_DIR.'common/db/tags.php');

function tag_form_event() {
    return 'onsubmit="return checkForm();"';
}

function tag_input_box($size = 50, $name = "tags", $id = "form_tags") {
    $output = "<input class=\"wickEnabled\" type=\"text\" size=\"".$size."\" name='".$name."' id='".$id."'/>";
    $output .= "<script type=\"text/javascript\" language=\"JavaScript\" src=\"static/js/wick.js\" />";
    return $output;
}
?>

<link rel="stylesheet" type="text/css" href="static/css/wick.css">
<script type="text/javascript" language="JavaScript">
function checkForm() {
    answer = true;
    if (siw && siw.selectingSomething)
        answer = false;
    return answer;
}
<?php
$tag_names = tag_get_all_names();
echo  "collection = [\n";
foreach ($tag_names as $name) {
    echo "'".$name."',\n";
}
echo "];\n";
?>
</script>
