<?php

class :ia:google-search extends :x:element {
    children empty;

    protected function render() {
        return
          <div id="google-search">
            <form id="searchbox_010130381492294265836:kw-fmmvpxco" action="http://infoarena.ro/search">
              <fieldset>
                <legend><img src={url_static('images/icons/search.png')} alt="!" /> Cautare</legend>
                <input type="hidden" name="cx" value="010130381492294265836:kw-fmmvpxco" />
                <input type="hidden" name="cof" value="FORID:9" />
                <input name="q" type="text" size="22" />
                <input type="submit" name="sa" value="Cauta" class="button important"/>
              </fieldset>
            </form>
            <script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=searchbox_010130381492294265836%3Akw-fmmvpxco"></script>
          </div>;
    }
}
