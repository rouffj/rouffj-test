APP_ROOT=../framework-standard-edition

test:
	@phpunit -c $(APP_ROOT)/app/phpunit.xml.dist
