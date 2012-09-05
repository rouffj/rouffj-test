<?php

namespace Rouffj\Tests\Doctrine;

use Doctrine\ORM\UnitOfWork;

use Rouffj\Tests\TestCase;
use Rouffj\Bundle\LearningBundle\Entity\Employee;

class DoctrineTest extends TestCase
{
    private $em;

    public function doSetUp()
    {
        $this->em = $this->container->get('doctrine')->getEntityManager();
        $this->em->getConnection()->beginTransaction();
        $this->createEmployees(array(
            new Employee('Smith'),
            new Employee('John')
        ));
    }

    public function tearDown()
    {
        $this->em->getConnection()->rollback();
    }

    public function testHowToPersistEntity()
    {
        $e1 = new Employee('Smith');
        $e2 = new Employee('John');
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->em->getUnitOfWork()->getEntityState($e2));
        $this->assertEquals(false, $this->em->getUnitOfWork()->isInIdentityMap($e2));
        $this->em->persist($e2);
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->em->getUnitOfWork()->getEntityState($e2));
        $this->assertEquals(false, $this->em->getUnitOfWork()->isInIdentityMap($e2));
        $this->em->flush();
        $this->assertEquals(true, $this->em->getUnitOfWork()->isInIdentityMap($e2));
    }

    public function testHowIdentityMapWorks()
    {
        $o1 = $this->em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $o2 = $this->em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $o3 = $this->em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('John');

        $this->assertEquals(2, $this->em->getUnitOfWork()->size(),
            'identityMap should have ONLY 2 entities because $o1 and $o2 have the same index in it (entityname+id).');
        $this->assertSame($o1, $o2,
            '$o2 is not retrieve from DB but from identityMap because its classname+id matches with $o1');
    }

    public function testHowIdentityMapIsStructured()
    {
        $this->assertEquals(array(), $this->em->getUnitOfWork()->getIdentityMap());
        $employeeRepository = $this->em->getRepository('RouffjLearningBundle:Employee');
        $emps = $employeeRepository->findAll();
        $expectedIdentityMap = array(
            'Rouffj\Bundle\LearningBundle\Entity\Employee' => array(
                $emps[0]->id => $emps[0],
                $emps[1]->id => $emps[1]
            )
        );
        $this->assertEquals($expectedIdentityMap, $this->em->getUnitOfWork()->getIdentityMap());

        //$this->em->getUnitOfWork()->clear();
        //$emps = $employeeRepository->findByLastname('Smith');
        //$this->assertEquals(array(), $this->em->getUnitOfWork()->getIdentityMap());
    }

    /**
     * This HowTo is usefull if you have the same entity root BUT with different filtered relations
     */
    public function testHowToByPassIdentityMap()
    {
        $o1 = $this->em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $this->em->clear('Rouffj\Bundle\LearningBundle\Entity\Employee');
        $o2 = $this->em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $this->assertNotSame($o1, $o2, '->clear() allow to retrieve $o1 and $o2 from DB');
    }

    private function createEmployees($employees)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        foreach ($employees as $emp) {
            $this->em->persist($emp);
        }
        $this->em->flush();
        $this->em->clear();
    }
}
