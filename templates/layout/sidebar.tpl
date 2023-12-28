<div id="sidebar">
  <ul id="nav" class="clear">
    <li>
      <a href="{url_home()}">Acasă</a>
    </li>
    <li>
      <a href="{url_textblock('concursuri')}">Concursuri</a>
    </li>
    <li>
      <a href="{url_textblock('concursuri-virtuale')}">Concursuri virtuale</a>
    </li>
    <li>
      <a href="{url_textblock('clasament-rating')}">Clasament</a>
    </li>

    <li>
      <a
        accesskey="m"
        href="{User::getCurrentUserMonitorUrl()}">
        <span class="access-key">M</span>onitorul de evaluare
      </a>
    </li>

    <li class="separator"><hr></li>

    <li>
      <a href="{url_task_search([])}">Categorii probleme</a>
    </li>

    {if Config::GOOGLE_CSE_TOKEN && !Config::DEVELOPMENT_MODE}
      <li>
        <a href="{url_google_search()}">Căutare probleme</a>
      </li>
      {include "layout/googleSearch.tpl"}
    {/if}

    {if $identity}
      <li>
        <a href="{url_submit()}"><strong>Trimite soluții</strong></a>
      </li>
      <li>
        <a
          accesskey="m"
          href="{$identity->getAccountUrl()}">
          <span class="access-key">C</span>ontul meu
        </a>
      </li>
      <li>
        <a
          accesskey="p"
          href="{$identity->getProfileUrl()}">
          <span class="access-key">P</span>rofilul meu
        </a>
      </li>
    {/if}

    {if Identity::isAdmin()}
		  <li class="separator"><hr></li>
		  <li>
        <a
          accesskey="a"
          href="{url_admin()}">
          <span class="access-key">A</span>dministrativ
        </a>
      </li>
    {/if}

    {if $numReports && Identity::mayViewReports()}
      <li>
        <a href="{Config::URL_PREFIX}report/list">Rapoarte</a>
        ({$numReports})
      </li>
    {/if}
  </ul>

  {include "layout/sidebarLogin.tpl"}

  <p class="user-count">
    {user_count()} membri înregistrați
  </p>

  <div class="user-count" id="srv_time"></div>
  <script>loadTime({format_date(null, 'HH, mm, ss')});</script>

</div>
