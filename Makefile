all:
	@echo "Supported commands:"
	@echo
	@echo "    \033[1mmake deploy\033[0m  - deploy to production"
	@echo "    \033[1mmake static\033[0m  - build static locally"
	@echo

deploy:
	./deploy/deploy.sh

static: build-js build-css static-config

build-js:
	./deploy/build_js.sh -i ./htdocs/js -o ./htdocs/dist-js

build-css:
	./deploy/build_css.sh -i ./htdocs/scss -o ./htdocs/dist-css

static-config:
	./deploy/gen_static_config.php -i ./htdocs > ./config-static.php

.PHONY: all deploy static