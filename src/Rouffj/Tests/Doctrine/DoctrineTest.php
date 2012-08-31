<?php

namespace Rouffj\Tests\Doctrine;

use Doctrine\ORM\UnitOfWork;

use Rouffj\Tests\TestCase;
use Rouffj\Bundle\LearningBundle\Entity\Employee;

class DoctrineTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testHowToPersistEntity()
    {
        $e1 = new Employee('Smith');
        $e2 = new Employee('John');
        $em = $this->container->get('doctrine')->getEntityManager();

        $this->assertEquals(UnitOfWork::STATE_NEW, $em->getUnitOfWork()->getEntityState($e2));
        $this->assertEquals(false, $em->getUnitOfWork()->isInIdentityMap($e2));
        $em->persist($e2);
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $em->getUnitOfWork()->getEntityState($e2));
        $this->assertEquals(false, $em->getUnitOfWork()->isInIdentityMap($e2));
        $em->flush();
        $this->assertEquals(true, $em->getUnitOfWork()->isInIdentityMap($e2));
    }

    public function testHowIdentityMapWorks()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $this->createEmployees(array(
            new Employee('Smith'),
            new Employee('John')
        ));

        $o1 = $em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $o2 = $em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $o3 = $em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('John');

        $this->assertEquals(2, $em->getUnitOfWork()->size(),
            'identityMap should have ONLY 2 entities because $o1 and $o2 have the same index in it (entityname+id).');
        $this->assertSame($o1, $o2,
            '$o2 is not retrieve from DB but from identityMap because its classname+id matches with $o1');

        $em->getConnection()->rollback();
    }

    /**
     * This HowTo is usefull if you have the same entity root BUT with different filtered relations
     */
    public function testHowToByPassIdentityMap()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $this->createEmployees(array(
            new Employee('Smith'),
            new Employee('John')
        ));

        $o1 = $em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $em->clear('Rouffj\Bundle\LearningBundle\Entity\Employee');
        $o2 = $em->getRepository('RouffjLearningBundle:Employee')->findOneByLastname('Smith');
        $this->assertNotSame($o1, $o2, '->clear() allow to retrieve $o1 and $o2 from DB');

        $em->getConnection()->rollback();
    }

    private function createEmployees($employees)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $em->getConnection()->beginTransaction();
        foreach ($employees as $emp) {
            $em->persist($emp);
        }
        $em->flush();
        $em->clear();
    }
}
