<div>
  <h3> Indicii de rezolvare</h3>

  <a id="show_algorithm_tags" href="#">
    Arată {$tagTree|count} categorii
  </a>

  <ul class="hidden">
    {foreach $tagTree as $rec}
      {$colorScheme=$rec.method->id % 6}
      <li>
        <span class="tag_name color_scheme_{$colorScheme}">
          {include "bits/tagLink.tpl" tag=$rec.method class="tag_search_anchor"}
        </span>

        <a class="show_tag_anchor" href="#">
          {$rec.algorithms|count} etichete
        </a>

        <span class="hidden">
          {foreach $rec.algorithms as $alg}
            <div class="sub_tag_name">
              {include "bits/tagLink.tpl" tag=$alg class="sub_tag_search_anchor"}
            </div>
          {/foreach}
        </span>
      </li>
    {/foreach}
  </ul>
</div>
