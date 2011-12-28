<?php
// Contains the base ia:page XHP object that is used as a platform for
// rendering all pages on the main website.

require_once(IA_ROOT_DIR . 'www/xhp/ia/sitewide.php');

class :ia:page extends :x:element {
    attribute
        array user,
        string title = "infoarena",
        string charset = "UTF-8";
    /*
     * The ia:page XHP object can have meta, link and script children that are
     * added to the <head> of the rendered page in addition to the default one.
     *
     * The website's header, top navigation bar, left column and footer are all
     * optional and can be omitted. The main content of the website should be
     * enclosed in a <ia:content> tag.
     */
    children ((:meta | :link | :script)*,
              :ia:header?, :ia:top-navbar?, :ia:left-col?,
              :ui:breadcrumbs?, :ui:flash-message?, :ia:footer?,
              :ia:content);

    protected function render() {
        $default_css = array(
            'sitewide.css', 'screen.css',
            'iconize.css', 'sh/shCore.css', 'sh/shThemeDefault.css',
            <link type="text/css" rel="stylesheet"
              href={url_static('css/print.css')} media="print" />,
        );
        // FIXME: infoarena's javascript is a horrible mess.
        $default_js = array(
            'config.js.php',
            IA_DEVELOPMENT_MODE ?
                'jquery-1.7.2.js' :
                '//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
            'default.js',
            'submit.js',
            'remotebox.js',
            'postdata.js',
            'sh/shCore.js',
            'sh/shBrushCpp.js',
            'sh/shBrushDelphi.js',
            'sh/shBrushJava.js',
            'sh/shBrushPython.js',
            'sh/shInit.js',
            'tags.js',
            'roundtimer.js',
            'restoreparity.js',
            'foreach.js',
            'sorttable.js',
            'tablednd.js',
            <script type="text/javascript">
              {"Sh_Init('" . url_static('swf/clipboard.swf') . "')"}
            </script>,
        );

        // Add the default CSS and JS files to the page's children.
        foreach ($default_css as $css_element) {
            if (is_string($css_element)) {
                $this->appendChild(
                  <link type="text/css" rel="stylesheet"
                    href={url_static('css/' . $css_element)} />);
            } else {
                $this->appendChild($css_element);
            }
        }
        foreach ($default_js as $js_element) {
            if (is_string($js_element)) {
                $this->appendChild(
                  <script type="text/javascript"
                    src={url_static('js/' . $js_element)} />);
            } else {
                $this->appendChild($js_element);
            }
        }

        // Forward the user attribute down to the children that require it.
        $user = $this->getAttribute('user');
        foreach ($this->getChildren() as $child) {
            $child_attributes = $child->__xhpAttributeDeclaration();
            if (array_key_exists('user', $child_attributes)) {
                $child->setAttribute('user', $user);
            }
        }

        // Render the document
        $content_type = 'text/html; charset=' . $this->getAttribute('charset');
        $head =
          <head>
            <meta http-equiv="Content-Type" content={$content_type} />
            {$this->getChildren('meta')}

            <title>{$this->getAttribute('title')}</title>

            {$this->getChildren('link')}
            <link rel="icon" href={IA_URL . 'favicon.ico'}
              type="image/vnd.microsoft.icon" />

            {$this->getChildren('script')}
          </head>;

        $body =
          <body id="infoarena">
            <div id="page">
              {$this->getChildren('ia:header')}
              {$this->getChildren('ia:top-navbar')}
              <div id="content_small" class="clear">
                {$this->getChildren('ia:left-col')}
                <div id="main">
                  {$this->getChildren('ui:breadcrumbs')}
                  {$this->getChildren('ui:flash-message')}
                  {$this->getChildren('ia:content')}
                </div>
              </div>
            </div>
            {$this->getChildren('ia:footer')}
          </body>;

        return
          <x:doctype>
            <html>
              {$head}
              {$body}
            </html>
          </x:doctype>;
    }
}

class :ia:content extends :x:element {
    protected function render() {
        return <x:frag>{$this->getChildren()}</x:frag>;
    }
}
