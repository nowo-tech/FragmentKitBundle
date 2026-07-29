.PHONY: help up-symfony8 down-symfony8 install-symfony8 shell-symfony8 release-check demo-smoke release-verify update-bundle-symfony8

help:
	@echo "Fragment Kit Bundle — Demos"
	@echo ""
	@echo "Symfony 8 (port 8050):"
	@echo "  up-symfony8 down-symfony8 install-symfony8 shell-symfony8"
	@echo ""
	@echo "  release-check          Verify demo health (HTTP 200 + fallback text)"
	@echo "  demo-smoke             REQ-TEST-011: up -> HTTP 200 -> down"
	@echo "  release-verify         Alias of demo-smoke"

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

# REQ-TEST-011: boot demo stack and expect HTTP 200
demo-smoke:
	@PORT=$$(grep "^PORT=" symfony8/.env.example 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
	[ -z "$$PORT" ] && PORT=8050; \
	echo "Demo smoke: symfony8 (http://127.0.0.1:$$PORT)"; \
	$(MAKE) up-symfony8; sleep 8; \
	code=$$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:$$PORT" 2>/dev/null || echo "000"); \
	$(MAKE) down-symfony8 2>/dev/null || true; \
	if [ "$$code" != "200" ]; then echo "symfony8 failed with HTTP $$code"; exit 1; fi; \
	echo "symfony8 OK (HTTP $$code)"

release-verify: demo-smoke

DEMOS := symfony8
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
# Optional: monorepo helper absent on standalone GitHub Actions checkout (REQ-MAKE-009).
-include $(BUNDLE_ROOT)/../.scripts/Makefile.demo-aggregate-update-deps.mk
