<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;

class PDependTest extends TestCase
{
    public function doSetUp()
    {
    }

   // AnalyzerClassFileSystemLocator ne fonctionne qu'avec un include_path vers le debut de la lib.
   // autrement, aucun analyzer n'est chargé.
    public function testHowToAddProjectMetric()
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
        $pdepend->addFile(__DIR__.'/../PHP/PHPTest.php');
        $pdepend->analyze();
        $metrics = $logger->getAnalyzer('PHP_Depend_Metrics_NodeLoc_Analyzer')->getProjectMetrics();

        $this->assertEquals(52, $metrics['loc']);
    }
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
