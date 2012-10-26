<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;

class PDependTest extends TestCase
{
    private $logger;
    private $pdepend;

    public function doSetUp()
    {
        $config = new \stdClass();
        $config->cache = new \stdClass();
        $config->parser = new \stdClass();
        $config->parser->nesting = 100;
        $config->cache->driver = new \stdClass();
        $config->cache->driver = 'memory';

        $pdepend = new \PHP_Depend(new \PHP_Depend_Util_Configuration($config));
        $logger = new MyLogger;
        $pdepend->addLogger($logger);

        $this->logger = $logger;
        $this->pdepend = $pdepend;
    }

   // AnalyzerClassFileSystemLocator ne fonctionne qu'avec un include_path vers le debut de la lib.
   // autrement, aucun analyzer n'est chargé.
    public function testHowToInterpretNodeLocAnalyzer()
    {
        $this->pdepend->addFile(__DIR__.'/Fixtures/Test.php');
        $this->pdepend->analyze();
        $metrics = $this->logger->getAnalyzer('PHP_Depend_Metrics_NodeLoc_Analyzer')->getProjectMetrics();

        $this->assertEquals(12, $metrics['loc'], 'loc is the number of lines (code line, empty lines, comments...) in a project');
        $this->assertEquals(3, $metrics['cloc'], 'cloc is the number of lines with comments in a project');
        $this->assertEquals(6, $metrics['eloc'], 'eloc is the number of lines with php code');
    }

    public function testHowToCreateAProjectAwareAnalyzer()
    {
        $this->pdepend->addFile(__DIR__.'/Fixtures/test2.php');
        $this->pdepend->analyze();
        $metrics = $this->logger->getAnalyzer('PHP_Depend_Metrics_Analyzer1_Analyzer')->getProjectMetrics();

        $this->assertEquals(2, $metrics['nbm']);
    }

    public function testHowToRetrieveParentClasses()
    {
        $this->pdepend->addFile(__DIR__.'/Fixtures/test3.php');
        $this->pdepend->analyze();
        $klassA = $this->logger->getAnalyzer('PHP_Depend_Metrics_Analyzer1_Analyzer')->getNode('+global\ClassA');

        $klassAParents = $klassA->getParentClasses();
        $this->assertEquals(2, count($klassAParents), 'ClassA has 2 ancestor classes');
        $this->assertEquals('ClassB', $klassAParents[0]->getName(), 'the first parent is the closest one (ClassB)');
        $this->assertEquals('ClassC', $klassAParents[1]->getName());

        $klassBParents = $klassAParents[0]->getParentClasses();
        $this->assertEquals(1, count($klassBParents));
        $this->assertEquals('ClassC', $klassBParents[0]->getName());

        $klassCParents = $klassAParents[1]->getParentClasses();
        $this->assertEquals(0, count($klassCParents), 'ClassC has no parent so an empty array is given');
    }

    public function testHowToRetrieveCompleteCall()
    {
        $this->pdepend->addfile(__DIR__.'/Fixtures/test4.php');
        $this->pdepend->analyze();
        $klassA = $this->logger->getAnalyzer('PHP_Depend_Metrics_Analyzer1_Analyzer')->getNode('Test\ClassA');
        $methods = $klassA->getMethods();

        // I'm looking for all methods calls within the __construct method (test(), test()).
        $invocations = $methods[0]->findChildrenOfType(
            \PHP_Depend_Code_ASTInvocation::CLAZZ
        );

        // For the first method call (test()), i retrieve all before calls
        $parents = $invocations[0]->getParentsOfType(
            \PHP_Depend_Code_ASTMemberPrimaryPrefix::CLAZZ
        );

        $this->assertEquals('$this', $parents[0]->getChild(0)->getImage());
        $this->assertEquals('dep1', $parents[1]->getChild(0)->getImage());
        $this->assertEquals('test', $invocations[0]->getImage());
    }

    //public function testDoesPDependSupportsNamespaces()
    //{
    //    $this->pdepend->addfile(__DIR__.'/Fixtures/test4.php');
    //    $this->pdepend->analyze();
    //    $packages = $this->logger->getAnalyzer('PHP_Depend_Metrics_Analyzer1_Analyzer')->getPackages();
    //    $klasses = $packages[0]->getClasses();
    //    $methods = $klasses[0]->getAllMethods();
    //    foreach ($methods as $method) {
    //        //$child = $method->findChildrenOfType(\PHP_Depend_Code_ASTComment::CLAZZ);
    //        //$child = $method->findChildrenOfType(\PHP_Depend_Code_ASTClassReference::CLAZZ);
    //        //$child = $method->findChildrenOfType(\PHP_Depend_Code_ASTInvocation::CLAZZ);
    //        //$type = \PHP_Depend_Code_ASTInvocation::CLAZZ;
    //        //$node = $child[0];
    //        //$res = $child[0]->getParentsOfType(\PHP_Depend_Code_ASTMethodPostfix::CLAZZ);
    //        ////echo $res[0]->getImage();
    //        //$res2 = $method->getDependencies();
    //        //echo '<pre>'; var_dump($res2[1]->getName()); echo '</pre>';
    //        //while ($node instanceof \PHP_Depend_Code_ASTInvocation) {
    //        //    echo $node->getImage();
    //        //    $node = $node->getParent();
    //        //    echo get_class($node);
    //        //}
    //    }
    //    //$props = $klasses[0]->getProperties();
    //    //foreach ($props as $prop) {
    //    //    echo '<pre>'; var_dump($prop->getDefaultValue()); echo '</pre>';
    //    //}
    //}

    //public function testHowToRetrieveImplementedInterfaces()
    //{
    //    $this->pdepend->addfile(__dir__.'/fixtures/test3.php');
    //    $this->pdepend->analyze();
    //    $klassa = $this->logger->getanalyzer('php_depend_metrics_analyzer1_analyzer')->getnode('classAWithInterfaces');
    //}
}

class MyLogger implements \PHP_Depend_Log_LoggerI
{
    private $analyzers = array();

    public function log(\PHP_Depend_Metrics_AnalyzerI $analyzer)
    {
        $this->analyzers[get_class($analyzer)] = $analyzer;

        return true;
    }

    public function close()
    {

    }

    public function getAcceptedAnalyzers()
    {
        return array(
            'PHP_Depend_Metrics_ProjectAwareI'
        );
    }

    public function getAnalyzer($name)
    {
        return isset($this->analyzers[$name]) ? $this->analyzers[$name] : null;
    }
}
