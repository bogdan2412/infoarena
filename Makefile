.PHONY: hphp-build clean-cache clean-hphp clean lint

hphp-build:
	mkdir -p hphp/build/
	find common/ www/ eval/ hphp/ config.php -name "*.php" > hphp/build/filelist
	hphp --input-list=hphp/build/filelist -o hphp/build/ --program infoarena -l 3 --cluster-count 8

clean-cache:
	find cache/ -type f -exec rm {} +
	rm -rf www/static/images/{latex,tmp}/*

clean-hphp:
	rm -rf hphp/build/*

clean-sessions:
	find /var/infoarena/sessions/ -name sess_\* -exec rm {} +

clean: clean-cache clean-hphp clean-sessions

arcanist:
	git clone git://github.com/facebook/arcanist.git

libphutil:
	git clone git://github.com/facebook/libphutil.git
	libphutil/scripts/build_xhpast.sh

lint: arcanist libphutil
	arcanist/bin/arc lint --apply-patches

lint-all: arcanist libphutil
	arcanist/bin/arc lint --apply-patches --lintall

lint-repo: arcanist libphutil
	find . -name \*.php | xargs arcanist/bin/arc lint --lintall --never-apply-patches
