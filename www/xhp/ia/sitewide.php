<?php
// Contains important XHP objects that are needed to render the website's
// frame, such as the website header, footer and navigation bar.

require_once(IA_ROOT_DIR . 'www/xhp/ui/link.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');

class :ia:header:userbox extends :x:element {
    attribute
        array user      @required;
    children empty;

    protected function render() {
        $user = $this->getAttribute('user');
        $username = $user['username'];

        return
          <div id="userbox">
            <ui:link href={url_user_profile($username, true)}>
              {HTML(format_user_avatar($username, "normal", true))}
            </ui:link>
            <div class="user">
              <strong>{$user['full_name']}</strong><br />
              {HTML(format_user_ratingbadge($username, $user['rating_cache']))}&nbsp;<ui:link href={url_user_profile($username, true)} accesskey="p">{$username}</ui:link><br />
              {HTML(format_post_link(url_logout(), "logout", array(), true, array('class' => 'logout')))} |
              <ui:link href={url_account()} accesskey="c">contul meu</ui:link>
            </div>
          </div>;
    }
}

class :ia:header extends :x:element {
    attribute
        array user;
    children empty;

    protected function render() {
        $header = <div id="header" class="clear" />;

        $user = $this->getAttribute('user');
        if ($user) {
            $header->appendChild(<ia:header:userbox user={$user} />);
        }

        if (IA_DEVELOPMENT_MODE) {
            $header->appendChild(
              <div id="dev_warning">
                Bravely working in development mode&hellip;<br/>Keep it up!
              </div>);
        }

        $header->appendChild(
          <h1>
            <ui:link href={url_home()}>
              infoarena informatica de performanta
            </ui:link>
          </h1>);

        return $header;
    }
}

class :ia:top-navbar extends :x:element {
    attribute
        array user,
        int user_pm_count = 0,
        string selected = "infoarena";
    children empty;

    protected function render() {
        $fields = array(
          'infoarena' =>
            <ui:link href={url_home()}>info<em>arena</em></ui:link>,
          'blog' => <ui:link href={url_blog()} accesskey="b">blog</ui:link>,
          'forum' => <ui:link href={url_forum()} accesskey="f">forum</ui:link>,
          'calendar' =>
            <ui:link href={url_forum() . '?action=calendar'}>
              calendar
            </ui:link>,
        );

        $user = $this->getAttribute('user');
        if (!$user) {
            $fields['login'] =
              <ui:link href={url_login()}>autentificare</ui:link>;
            $fields['register'] =
              <ui:link href={url_login()}>inregistrare</ui:link>;
        } else {
            $fields['profile'] =
              <ui:link href={url_user_profile($user['username'])}
                accesskey="p">profilul meu</ui:link>;

            $user_pm_count = $this->getAttribute('user_pm_count');
            if ($user_pm_count) {
                $pm_content = <strong>mesaje ({$user_pm_count})</strong>;
            } else {
                $pm_content = 'mesaje';
            }
            $fields['pm'] =
              <ui:link href={url_forum() . '?action=pm'}>
                {$pm_content}
              </ui:link>;

            if ($user['security_level'] === 'admin') {
                $fields['admin'] = <ui:link href={url_admin()}>admin</ui:link>;
                $fields['smf_admin'] =
                  <ui:link href={url_forum() . '?action=admin'}>
                    forum admin
                  </ui:link>;
            }
        }

        $selected = $this->getAttribute('selected');
        $list = <ul />;
        foreach ($fields as $name => $content) {
            if ($selected === $name) {
                $content = <strong>{$content}</strong>;
            }
            $list->appendChild(<li>{$content}</li>);
        }

        return
          <div id="topnav">
            {$list}
          </div>;
    }
}

class :ia:footer extends :x:element {
    children empty;

    protected function render() {
        $elem =
          <div id="footer">
            <ul class="clear">
              <li class="copyright">&copy;&nbsp;2004-{date("Y")}&nbsp;<ui:link href={url_textblock('Asociatia-infoarena')}>Asociatia infoarena</ui:link></li>
              <li class="separate">
                <ui:link href={url_home()}>Prima pagina</ui:link>
              </li>
              <li>
                <ui:link href={url_textblock('despre-infoarena')}>
                  Despre infoarena
                </ui:link>
              </li>
              <li>
                <ui:link href={url_textblock('termeni-si-conditii')}>
                  Termeni si conditii
                </ui:link>
              </li>
              <li>
                <ui:link href={url_textblock('contact')}>Contact</ui:link>
              </li>
              <li class="top">
                <a href="#header">Sari la inceputul paginii &uarr;</a>
              </li>
            </ul>
          </div>;

        if (!IA_DEVELOPMENT_MODE) {
            $elem->appendChild(
            <p class="cc">
              {HTML('<!--Creative Commons License-->')}
              <a class="badge" rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/"><img alt="Creative Commons License" src={url_static('images/CreativeCommonsBadge.png')} /></a>
              Cu exceptia cazurilor in care se specifica altfel, continutul site-ului infoarena<br/>este publicat sub licenta <a rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/">Creative Commons Attribution-NonCommercial 2.5</a>.
              {HTML('<!--/Creative Commons License-->
                <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
                  <Work rdf:about="">
                    <license rdf:resource="http://creativecommons.org/licenses/by-nc/2.5/" />
                  </Work>
                  <License rdf:about="http://creativecommons.org/licenses/by-nc/2.5/">
                    <permits rdf:resource="http://web.resource.org/cc/Reproduction"/>
                    <permits rdf:resource="http://web.resource.org/cc/Distribution"/>
                    <requires rdf:resource="http://web.resource.org/cc/Notice"/>
                    <requires rdf:resource="http://web.resource.org/cc/Attribution"/>
                    <prohibits rdf:resource="http://web.resource.org/cc/CommercialUse"/>
                    <permits rdf:resource="http://web.resource.org/cc/DerivativeWorks"/>
                  </License>
                </rdf:RDF>')}
            </p>);

            $elem->appendChild(
              <script src="http://www.google-analytics.com/urchin.js" type="text/javascript" />);
            $elem->appendChild(
              <script type="text/javascript">
                _uacct = 'UA-113289-8';
                _udn = 'infoarena.ro';
                urchinTracker();
              </script>);
        }

        return $elem;
    }
}
