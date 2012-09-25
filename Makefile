.PHONY: hphp-build hphp-install hphp-redeploy clean-cache clean-hphp clean setup lint-repo

setup: arcanist libphutil
	rm -f arc
	ln -s arcanist/bin/arc .
	sudo scripts/setup

hphp-build:
	mkdir -p hphp/build/
	find common/ www/ eval/ hphp/ config.php -name "*.php" > hphp/build/filelist
	hphp --input-list=hphp/build/filelist -o hphp/build/ --program infoarena -l 3 --cluster-count 8

hphp-install:
	prod/ia_stop.sh
	cp -a hphp/build/infoarena .
	prod/ia_start.sh

hphp-redeploy: hphp-build hphp-install

clean-cache:
	find cache/ -type f -exec rm {} +
	rm -rf www/static/images/{latex,tmp}/*

clean-hphp:
	rm -rf hphp/build/*

clean-sessions:
	find /var/infoarena/sessions/ -name sess_\* -exec rm {} +

clean-tools:
	rm -rf arc arcanist libphutil

clean: clean-cache clean-hphp clean-sessions

distclean: clean-tools clean

arcanist:
	git clone git://github.com/facebook/arcanist.git
	ln -s arcanist/bin/arc .

libphutil:
	git clone git://github.com/facebook/libphutil.git
	libphutil/scripts/build_xhpast.sh || true
	libphutil/scripts/build_xhpast.sh

lint-repo: arcanist libphutil
	find . -name \*.php | xargs arcanist/bin/arc lint --lintall --never-apply-patches
