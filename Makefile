help: ## Shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.PHONY: phpstan phpstan-baseline phpunit 

phpstan: ## run phpstan
	vendor/bin/phpstan -vvv

phpstan-baseline: ## update baseline for phpstan
	vendor/bin/phpstan -vvv analyze -c phpstan.neon --generate-baseline=phpstan.baseline.neon

phpunit: ## run phpunit
	vendor/bin/phpunit

