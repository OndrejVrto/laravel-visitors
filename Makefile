pint:
	vendor\bin\pint

test:
	vendor\bin\pest

test-coverage:
	vendor\bin\pest --coverage

test-help:
	vendor\bin\pest --help


stan:
	vendor\bin\phpstan analyze --configuration phpstan.neon --memory-limit=4G

stan-debug:
	vendor\bin\phpstan analyze --configuration phpstan.neon --memory-limit=4G --debug

stan-base:
	vendor\bin\phpstan analyze --configuration phpstan.neon --memory-limit=4G --debug --generate-baseline

stan-help:
	vendor\bin\phpstan --help



rector:
	vendor\bin\rector --clear-cache

rector-dry:
	vendor\bin\rector --dry-run

rector-help:
	vendor\bin\rector --help


all:
	vendor\bin\pint
	vendor\bin\pest
	vendor\bin\phpstan analyze --configuration phpstan.neon --memory-limit=4G --debug
	vendor\bin\rector
