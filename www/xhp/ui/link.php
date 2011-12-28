<?php

require_once(IA_ROOT_DIR . 'www/xhp/ui/base.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');

class :ui:link extends :ui:element {
    attribute
        bool highlight_accesskey = true,    // Whether or not to highlight the
                                            // link's accesskey if specified.
        :a;

    protected function render() {
        $elem = <a>{$this->getChildren()}</a>;

        $accesskey = $this->getAttribute('accesskey');
        if ($accesskey && $this->getAttribute('highlight_accesskey')) {
            // This assumes that no HTML tags are present inside the link.
            // If these are present, they will be escaped.
            $content = (string)<x:frag>{$this->getChildren()}</x:frag>;
            $elem = <a>{format_highlight_access_key($content, $accesskey)}</a>;
        }

        foreach ($this->getAttributes() as $key => $value) {
            $elem->setAttribute($key, $value);
        }
        return $elem;
    }
}
