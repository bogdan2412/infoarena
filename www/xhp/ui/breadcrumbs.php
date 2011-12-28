<?php

require_once(IA_ROOT_DIR . 'www/xhp/ui/base.php');

class :ui:breadcrumbs extends :ui:element {
    attribute
        array entries,
        string current,
        string title = "Pagini recente";
    children empty;

    protected function render() {
        $entries = $this->getAttribute('entries');
        $current = $this->getAttribute('current');

        if (count($entries) <= 1) {
            return <x:frag />;
        }

        $elem =
          <div class="breadcrumbs">
            {$this->getAttribute('title')} &raquo;
          </div>;
        $first = true;
        foreach ($entries as $key => $entry) {
            list($url, $title) = $entry;

            if (!$first) {
                $elem->appendChild(<span class="separator"> | </span>);
            } else {
                $first = false;
            }

            if ($current == $key) {
                $elem->appendChild(<strong>{$title}</strong>);
            } else {
                $elem->appendChild(<a href={$url}>{$title}</a>);
            }
        }
        return $elem;
    }
}
