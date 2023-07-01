.PHONY: clean-cache clean setup lint-repo

setup: arcanist libphutil
	rm -f arc
	ln -s arcanist/bin/arc .

clean-cache:
	find cache/ -type f -exec rm {} +
	rm -rf www/static/images/tmp/*

clean-sessions:
	find /var/infoarena/sessions/ -name sess_\* -exec rm {} +

clean-tools:
	rm -rf arc arcanist libphutil

clean: clean-cache clean-sessions

distclean: clean-tools clean

arcanist:
	git clone git://github.com/facebook/arcanist.git
	rm -f arc
	ln -s arcanist/bin/arc .

libphutil:
	git clone git://github.com/facebook/libphutil.git
	libphutil/scripts/build_xhpast.php || true

lint-repo: arcanist libphutil
	find . -name \*.php | xargs arcanist/bin/arc lint --lintall --never-apply-patches
