This PHP repository experiment the "Learning By Automatic Test" concept.

Instead of creating fake project to test libraries, technologies, frameworks etc. we use
here Automatic test to experiment things.

INSTALL
-------

* git clone https://github.com/rouffj/rouffj-test.git
* make install
* remplace ligne `"psr-0": { "": "src/" }' by '"psr-0": { "": ["../src", "src/"] }` in `framework-standard-edition/composer.json`
* add `new Rouffj\Bundle\LearningBundle\RouffjLearningBundle()` to `framework-standard-edition/app/AppKernel.php`
* update composer autoloader : `cd framework-standard-edition/ && composer.phar install`
