.PHONY: help up-symfony8 down-symfony8 install-symfony8 shell-symfony8 release-check update-bundle-symfony8

help:
	@echo "Fragment Kit Bundle — Demos"
	@echo ""
	@echo "Symfony 8 (port 8050):"
	@echo "  up-symfony8 down-symfony8 install-symfony8 shell-symfony8"
	@echo ""
	@echo "  release-check          Verify demo health (HTTP 200 + fallback text)"

up-symfony8:
	@$(MAKE) -C symfony8 up

down-symfony8:
	@$(MAKE) -C symfony8 down

install-symfony8:
	@$(MAKE) -C symfony8 install

shell-symfony8:
	@$(MAKE) -C symfony8 shell

update-bundle-symfony8:
	@$(MAKE) -C symfony8 update-bundle

release-check:
	@$(MAKE) up-symfony8
	@$(MAKE) update-bundle-symfony8
	@PORT=$$(grep "^PORT=" symfony8/.env 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
	[ -z "$$PORT" ] && PORT=$$(grep "^PORT=" symfony8/.env.example 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
	[ -z "$$PORT" ] && PORT=8050; \
	BODY=$$(curl -sf "http://localhost:$$PORT/"); \
	echo "$$BODY" | grep -q 'Fragment Kit Demo' && \
	echo "$$BODY" | grep -q 'Healthy fragment rendered OK' && \
	echo "$$BODY" | grep -q 'Fragment unavailable' && \
	echo "$$BODY" | grep -q 'HTTP 403' && \
	echo "Symfony 8 Fragment Kit demo healthcheck OK (port $$PORT)" || \
	(echo "Symfony 8 Fragment Kit demo healthcheck failed" && exit 1)

DEMOS := symfony8
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
include $(BUNDLE_ROOT)/../.scripts/Makefile.demo-aggregate-update-deps.mk
