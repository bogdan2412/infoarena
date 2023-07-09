<div id="footer">
  <ul class="clear">
    <li class="copyright">
      © {Config::COPYRIGHT_FIRST_YEAR}-{$currentYear}
      {Config::COPYRIGHT_OWNER}
    </li>

    <li class="separate">
      <a href="{url_textblock(Config::ABOUT_PAGE)}">
        Despre {Config::SITE_NAME}
      </a>
    </li>

    <li>
      <a href="{url_textblock('termeni-si-conditii')}">
        Termeni și condiții
      </a>
    </li>

    <li>
      <a href="{url_textblock('contact')}">
        Contact
      </a>
    </li>

    <li class="top">
      <a href="#header">Sari la începutul paginii ↑</a>
    </li>
  </ul>

  {if Config::DEVELOPMENT_MODE}
    <textarea id="log" rows="50" cols="80">
      {get_execution_stats_log()}
    </textarea>
  {else}
    <p class="cc">
      <a
        class="badge"
        href="https://creativecommons.org/licenses/by-nc-sa/4.0/"
        rel="license">
        <img
          alt="Creative Commons License"
          src="{url_static('images/creative-commons.png')}">
      </a>

      Cu excepția cazurilor în care se specifică altfel, conținutul site-ului
      {Config::SITE_NAME}<br>
      este publicat sub licența

      <a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">
        Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International
      </a>.
    </p>
  {/if}
</div>
