APP_ROOT=../framework-standard-edition
PHPUNIT_OPTS="--testdox"
test:
	@phpunit $(PHPUNIT_OPTS) -c $(APP_ROOT)/app/phpunit.xml.dist
