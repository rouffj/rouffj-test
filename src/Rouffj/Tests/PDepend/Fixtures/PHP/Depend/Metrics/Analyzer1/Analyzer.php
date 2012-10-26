<?php

class PHP_Depend_Metrics_Analyzer1_Analyzer
extends \PHP_Depend_Metrics_AbstractAnalyzer
implements \PHP_Depend_Metrics_ProjectAwareI
{
     private $nbMethods = 0;
     private $packages;

     public function analyze(\PHP_Depend_Code_NodeIterator $packages)
     {
        $this->packages = $packages;

        foreach ($packages as $package) {
            foreach ($package->getClasses() as $file) {
                foreach ($file->getMethods() as $method) {
                    $this->nbMethods += 1;
                }
            }
        }
     }

     public function getProjectMetrics()
     {
         return array('nbm' => $this->nbMethods);
     }

    /**
     * ONLY FOR TEST
     */
    public function getNode($nodeName)
    {
        foreach ($this->packages as $package) {
            foreach ($package->getClasses() as $klass) {
                if ($nodeName === $klass->getPackageName().'\\'.$klass->getName()) {
                    return $klass;
                }
            }
        }
    }

    /**
     * ONLY FOR TEST
     */
    public function getPackages()
    {
        return $this->packages;
    }
}
