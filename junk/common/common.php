// Unroll iterator, stores result in array
function unroll(Iterator $iterator) {
    $collect = array();
    foreach ($iterator as $entry) {
        $collect[] = $entry;
    }

    return $collect;
}
