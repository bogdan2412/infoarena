<table class="task-header">
  <tbody>

    <tr>
      <th>
        Fișierul intrare/ieșire
      </th>
      <td>
        {$task->id}.in, {$task->id}.out
      </td>
      <th>
        Sursă
      </th>
      <td>
        {$task->source}
      </td>
    </tr>

    <tr>
      <th>
        Autor
      </th>
      <td>
        {foreach $authors as $i => $author}
          {if $i}•{/if}
          {include "bits/tagLink.tpl" tag=$author}
        {/foreach}
      </td>
      <th>
        Adăugată de
      </th>
      <td>
        {include "bits/userTiny.tpl" user=$owner showRating=true}
      </td>
    </tr>

    <tr>
      <th>
        Timp de execuție pe test
      </th>
      <td>
        {$task->getTimeLimit()} sec
      </td>
      <th>
        Limită de memorie
      </th>
      <td>
        {$task->getMemoryLimit()} KB
      </td>
    </tr>

    <tr>
      <th>
        Scorul tău
      </th>
      <td>
        {if $score !== null}
          {$score} puncte
        {else}
          N/A
        {/if}
      </td>
      <th>
        Dificultate
      </th>
      <td>
        {$task->getDifficulty()}
      </td>
    </tr>

  </tbody>
</table>

{if $task->open_tests}
  <div class="open-task-banner">
    <div>
      <img
        alt="open book"
        src="{Config::URL_PREFIX}static/images/open_big.png">
      Poți vedea testele pentru această problemă accesând
      <a href="{$task->getAttachmentUrl()}">
        atașamentele
      </a>.
    </div>
  </div>
{/if}

<p></p>

<p class="task-title-links">
  <a href="{Config::URL_PREFIX}monitor?task={$task->id}">
    Vezi soluțiile trimise
  </a>
  |
  <a href="{Config::URL_PREFIX}statistici_problema?task={$task->id}">
    Statistici
  </a>
</p>

<h1>{$task->title}</h1>
