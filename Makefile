# https://docs.docker.com/compose/reference/overview

.PHONY: start
start: build
    # starts the entire runtime infrastructure
	docker compose up -d ssl

.PHONY: dev
dev: start
	docker compose up -d ui-builder

.PHONY: playwright-tests-ci
playwright-tests-ci:
	npm ci
	$(MAKE) playwright-app
	npx playwright install chromium && npx playwright test -c ./test/e2e/playwright.config.ts

.PHONY: playwright-tests
playwright-tests:
	npm install
	$(MAKE) playwright-app
	docker compose up -d ui-builder
	npx playwright install chromium && npx playwright test -c ./test/e2e/playwright.config.ts $(params)

.PHONY: playwright-app
playwright-app:
    # delete any cached session storage state files if the service isn't running
	docker compose ps app-for-playwright > /dev/null 2>&1 || rm -f *-storageState.json
	docker compose up -d app-for-playwright
    # wait until the app-for-playwright service is serving up HTTP before continuing
	until curl localhost:3238 > /dev/null 2>&1; do sleep 1; done

.PHONY: e2e-tests
e2e-tests:
	docker compose build app-for-e2e test-e2e
	docker compose restart app-for-e2e || docker compose up -d app-for-e2e
	docker compose run -e TEST_SPECS=$(TEST_SPECS) test-e2e

.PHONY: e2e-tests-ci
e2e-tests-ci:
	docker compose build app-for-e2e test-e2e
	docker compose run -e GITHUB_ACTIONS=1 --name e2etests test-e2e
	docker cp e2etests:/data/e2e-output/junitresults.xml e2e-results.xml
	docker rm e2etests

.PHONY: unit-tests
unit-tests:
	docker compose build test-php
	docker compose run test-php

.PHONY: unit-tests-ci
unit-tests-ci:
	docker compose run --name unittests test-php
	docker cp unittests:/var/www/PhpUnitTests.xml .
	docker rm unittests

.PHONY: build
build:
	npm install
	docker compose build mail app lfmerge ld-api next-proxy next-app

.PHONY: scan
# https://docs.docker.com/engine/scan
scan:
	docker build -t lf-app:prod -f docker/app/Dockerfile --platform linux/amd64 .
	docker login
	-docker scan --accept-license lf-app:prod > docker-scan-results.txt

.PHONY: next-dev
next-dev: build
	docker compose up -d next-proxy-dev

.PHONY: build-next
build-next:
	docker compose build next-proxy next-app

.PHONY: build-base-php
build-base-php:
	docker build -t sillsdev/web-languageforge:base-php -f docker/base-php/Dockerfile .

.PHONY: clean
clean:
	docker compose down
	docker system prune -f

.PHONY: clean-powerwash
clean-powerwash: clean
	npx rimraf *storageState.json test-results
	docker system prune -f --volumes
	- docker rmi -f `docker images -q "lf-*"` sillsdev/web-languageforge:base-php
	docker builder prune -f
