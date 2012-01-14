.PHONY: hphp-build clean-cache clean-hphp clean

hphp-build:
	mkdir -p hphp/build/
	find common/ www/ eval/ hphp/ config.php -name "*.php" > hphp/build/filelist
	hphp --input-list=hphp/build/filelist -o hphp/build/ --program infoarena -l 3 --cluster-count 8

clean-cache:
	find cache/ -type f -exec rm {} +
	rm -rf www/static/images/{latex,tmp}/*

clean-hphp:
	rm -rf hphp/build/*

clean: clean-cache clean-hphp
