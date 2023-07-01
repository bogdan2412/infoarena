.PHONY: clean-cache clean

clean-cache:
	find cache/ -type f -exec rm {} +
	rm -rf www/static/images/tmp/*

clean-sessions:
	find /var/infoarena/sessions/ -name sess_\* -exec rm {} +

clean: clean-cache clean-sessions

distclean: clean
