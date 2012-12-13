APP_ROOT=framework-standard-edition
PHPUNIT_OPTS="--testdox"

test:
	@phpunit.phar $(PHPUNIT_OPTS) -c $(APP_ROOT)/app/phpunit.xml.dist

test-component:
	@phpunit.phar $(PHPUNIT_OPTS) -c $(APP_ROOT)/app/phpunit.xml.dist --filter $(COMPONENT)

install:
	composer.phar install
	if [ ! -d "framework-standard-edition" ]; then composer.phar create-project 'symfony/framework-standard-edition'; fi;
	make init

init:
	cp src/Rouffj/Bundle/LearningBundle/Resources/skeletons/phpunit.xml.dist framework-standard-edition/app
