# Makefile for Fragment Kit Bundle
# Simplifies Docker commands for development.
# All dev targets use the root docker-compose.yml (single file).

COMPOSE_FILE := docker-compose.yml
# Prefer Compose V2 plugin (GitHub Actions / modern Docker Desktop); fall back to docker-compose V1 (REQ-MAKE-010).
COMPOSE_BIN := $(shell docker compose version >/dev/null 2>&1 && echo "docker compose" || echo "docker-compose")
COMPOSE     := $(COMPOSE_BIN) -f $(COMPOSE_FILE)
SERVICE_PHP := php

.PHONY: help ensure-up up down down-dev build shell install test test-coverage coverage-php-percent coverage-check test-coverage-100 cs-check cs-fix rector rector-dry phpstan qa release-check release-check-demos demo-smoke composer-sync clean update validate validate-translations assets setup-hooks check-no-cursor-coauthor check-open-prs strip-cursor-coauthor-from-history update-deps

# Default target
help:
	@echo "Fragment Kit Bundle - Development Commands"
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  ensure-up     Start container if not running"
	@echo "  up            Start Docker container"
	@echo "  down          Stop Docker container"
	@echo "  down-dev      Stop containers (keep volumes; REQ-MAKE-007)"
	@echo "  build         Rebuild Docker image (no cache)"
	@echo "  shell         Open shell in container"
	@echo "  install       Install Composer dependencies (starts container if needed)"
	@echo "  test          Run PHPUnit tests"
	@echo "  test-coverage Run tests with code coverage"
	@echo "  coverage-php-percent  Print Lines coverage from coverage-php.txt"
	@echo "  cs-check      Check code style"
	@echo "  cs-fix        Fix code style"
	@echo "  rector        Apply Rector refactoring"
	@echo "  rector-dry    Run Rector in dry-run mode"
	@echo "  phpstan       Run PHPStan static analysis"
	@echo "  qa            Run all QA checks (cs-check + test)"
	@echo "  release-check Pre-release: co-author audit, open PRs, cs-fix, cs-check, rector-dry, phpstan, test-coverage, demo healthchecks"
	@echo "  check-open-prs Fail if unresolved open GitHub PRs remain (REQ-REL-003)"
	@echo "  demo-smoke    REQ-TEST-011: boot demo + HTTP 200 (make -C demo demo-smoke)"
	@echo "  coverage-check Fail if PHP line coverage is below 100% (REQ-TEST-006)"
	@echo "  composer-sync Validate composer.json and align composer.lock (no install)"
	@echo "  clean         Remove vendor and cache"
	@echo "  update-deps   Update composer dependencies (REQ-MAKE-008)"
	@echo "  validate      Run composer validate --strict"
	@echo "  validate-translations Validate translation YAML files"
	@echo "  assets        No-op (no frontend assets in this bundle)"
	@echo "  setup-hooks   Install git hooks"
	@echo ""
	@echo "Demos: use make -C demo or make -C demo/symfony8"
	@echo ""

build:
	$(COMPOSE) build --no-cache

up:
	$(COMPOSE) build
	$(COMPOSE) up -d
	@echo "Waiting for container to be ready..."
	@sleep 2
	@echo "Installing dependencies..."
	$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction
	@echo "✅ Container ready!"

down:
	$(COMPOSE) down

down-dev:
	$(COMPOSE) down --remove-orphans

shell: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) sh

ensure-up:
	@if ! $(COMPOSE) exec -T $(SERVICE_PHP) true 2>/dev/null; then \
		echo "Starting container..."; \
		$(COMPOSE) up -d; \
		sleep 3; \
		$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction; \
	fi

install: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer install

test: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) composer test

test-coverage: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) composer test-coverage | tee coverage-php.txt
	./.scripts/php-coverage-percent.sh coverage-php.txt

coverage-php-percent:
	./.scripts/php-coverage-percent.sh coverage-php.txt

coverage-check: test-coverage
	@VALUE=$$(sed 's/\x1B\[[0-9;]*[A-Za-z]//g' coverage-php.txt | awk '/^[[:space:]]*Lines:[[:space:]]+/ { gsub(/%/, "", $$2); print $$2; exit }'); \
	awk -v p="$${VALUE:-0}" 'BEGIN { if ((p+0) < 100) { printf "ERROR: PHP line coverage %s%% < 100%%\n", p; exit 1 } printf "OK: PHP line coverage %s%%\n", p }'

test-coverage-100: coverage-check

cs-check: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer cs-check

cs-fix: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer cs-fix

rector: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer rector

rector-dry: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer rector-dry

phpstan: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer phpstan

qa: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer qa

release-check: ensure-up check-no-cursor-coauthor check-open-prs composer-sync cs-fix cs-check rector-dry phpstan test-coverage coverage-check release-check-demos

check-no-cursor-coauthor:
	@chmod +x .scripts/check-no-cursor-coauthor.sh
	@./.scripts/check-no-cursor-coauthor.sh HEAD

check-open-prs:
	@chmod +x .scripts/check-open-prs.sh
	@bash .scripts/check-open-prs.sh

release-check-demos:
	@if [ -f demo/Makefile ]; then $(MAKE) -C demo release-check; else echo "No demo/Makefile — skip release-check-demos"; fi

demo-smoke:
	@if [ -f demo/Makefile ]; then $(MAKE) -C demo demo-smoke; else echo "No demo/Makefile — skip demo-smoke"; fi

composer-sync: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer validate --strict
	$(COMPOSE) exec -T $(SERVICE_PHP) composer update --lock --no-install

clean:
	rm -rf vendor
	rm -rf .phpunit.cache
	rm -rf coverage
	rm -f coverage.xml
	rm -f .php-cs-fixer.cache

update: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer update --no-interaction

validate: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer validate --strict

validate-translations:
	@echo "No translation files in this bundle."

assets:
	@echo "No frontend assets in this bundle."

setup-hooks:
	@chmod +x .githooks/pre-commit 2>/dev/null || true
	@chmod +x .githooks/commit-msg 2>/dev/null || true
	@git config core.hooksPath .githooks
	@echo "Git hooks installed (.githooks — includes commit-msg for REQ-GIT-001)."

strip-cursor-coauthor-from-history:
	@chmod +x .scripts/strip-cursor-coauthor-from-history.sh
	@./.scripts/strip-cursor-coauthor-from-history.sh main

# REQ-MAKE-008: update-deps (REQ-MAKE-008)
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))
# Optional: monorepo helper absent on standalone GitHub Actions checkout (REQ-MAKE-009).
-include $(BUNDLE_ROOT)/../.scripts/Makefile.update-deps.mk
