<?php
require_once(IA_ROOT_DIR.'common/tags.php');

function tag_form_event() {
    return 'onsubmit="return checkForm();"';
}

function tag_input_box($value = "", $size = 50, $name = "tags", $id = "form_tags") {
    $output = "<input class=\"wickEnabled\" type=\"text\" size=\"".htmlentities($size).
              "\" name='".htmlentities($name)."' id='".htmlentities($id)."'/ value=\"".
              htmlentities($value)."\">";
    $output .= "<script type=\"text/javascript\" language=\"JavaScript\" src=\"".
               htmlentities(url_static("js/wick.js"))."\" />";
    return $output;
}
?>

<link rel="stylesheet" type="text/css" href="<?= htmlentities(url_static("css/wick.css")) ?>">
<script type="text/javascript" language="JavaScript">
function checkForm() {
    answer = true;
    if (siw && siw.selectingSomething) {
        answer = false;
    }
    return answer;
}
<?php
$tag_names = tag_get_all_names();
echo  "collection = [\n";
foreach ($tag_names as $tag) {
    echo "'".htmlentities($tag['name'])."',\n";
}
echo "];\n";
?>
</script>
