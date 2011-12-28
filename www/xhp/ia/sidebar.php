<?php
// Contains XHP objects for the site's left side bar.

require_once(IA_ROOT_DIR . 'www/xhp/ia/login_form.php');
require_once(IA_ROOT_DIR . 'www/wiki/wiki.php');

class :ia:left-col extends :x:element {
    attribute
        array user;

    protected function render() {
        $user = $this->getAttribute('user');
        foreach ($this->getChildren('ia:left-navbar') as $child) {
            $child->setAttribute('user', $user);
        }
        return
          <div id="sidebar">
            {$this->getChildren()}
          </div>;
    }
}

class :ia:left-navbar extends :x:element {
    attribute
        array user;
    children empty;

    protected function render() {
        $user = $this->getAttribute('user');
        $list =
          <ul id="nav" class="clear">
            <li><a href={url_home()}>Home</a></li>
            <li>
              <ui:link href={url_textblock('arhiva')} accesskey="a">
                Arhiva de probleme
              </ui:link>
            </li>
            <li>
              <a href={url_textblock('arhiva-educationala')}>
                Arhiva educatională
              </a>
            </li>
            <li>
              <a href={url_textblock('arhiva-monthly')}>
                Arhiva monthly
              </a>
            </li>
            <li><a href={url_textblock('concursuri')}>Concursuri</a></li>
            <li>
              <a href={url_textblock('concursuri-virtuale')}>
                Concursuri virtuale
              </a>
            </li>
            <li><a href={url_textblock('clasament-rating')}>Clasament</a></li>
            <li><a href={url_textblock('articole')}>Articole</a></li>
            <li><a href={url_textblock('downloads')}>Downloads</a></li>
            <li><a href={url_textblock('links')}>Links</a></li>
            <li><a href={url_textblock('documentatie')}>Documentaţie</a></li>
            <li>
              <a href={url_textblock('despre-infoarena')}>Despre infoarena</a>
            </li>
            <li class="separator"><hr/></li>
          </ul>;

        if ($user) {
            $list->appendChild(
              <li>
                <ui:link
                  href={url_monitor(array('user' => $user['username']))}
                  accesskey="m">
                  Monitorul de evaluare
                </ui:link>
              </li>);
            $list->appendChild(
              <li>
                <a href={url_submit()}>
                  <strong>Trimite soluţii</strong>
                </a>
              </li>);
            $list->appendChild(
              <li>
                <ui:link href={url_account()} accesskey="c">
                  Contul meu
                </ui:link>
              </li>);
        } else {
            $list->appendChild(
              <li>
                <ui:link href={url_monitor()} accesskey="m">
                  Monitorul de evaluare
                </ui:link>
              </li>);
        }

        return $list;
    }
}

class :ia:sidebar-ad extends :x:element {
    children empty;

    protected function render() {
        $sidebar = textblock_get_revision(IA_SIDEBAR_PAGE);
        if (!$sidebar) {
            return <x:frag />;
        }

        return
          <div class="ad">
            <div class="wiki_text_block">
              {HTML(wiki_process_textblock($sidebar))}
            </div>
          </div>;
    }
}

class :ia:sidebar-login extends :x:element {
    attribute
        bool show_login_form = true;
    children empty;

    protected function render() {
        if ($this->getAttribute('show_login_form')) {
            $form = <ia:login-form />;
        } else {
            $form = <x:frag />;
        }

        $extra =
          <p>
            <ui:link href={url_register()}>Mă înregistrez!</ui:link>
            <br />
            <ui:link href={url_resetpass()}>Mi-am uitat parola...</ui:link>
          </p>;

        return
          <div id="login">
            {$form}
            {$extra}
          </div>;
    }
}
