<?php

namespace Rouffj\Tests\Symfony\Form;

use Rouffj\Tests\TestCase;

class FormTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testHowToBuildInlinedFormWithoutEntityAsDataBag()
    {
        // Array databag instead of Object data bag (eg. Entity)
        $defaultData = array('message' => 'Type your message here');

        // build inlined form.
        $form = $this->container->get('form.factory')->createBuilder('form', $defaultData, array())
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('message', 'textarea')
            ->getForm();

        $expectedData = array(
            'name' => 'Joseph Rouff',
            'email' => 'foo@bar.com',
            'message' => 'hello'
        );

        $form->bind($expectedData);
        $this->assertEquals($expectedData, $form->getData(),
            'As $defaultData given to createBuilder() is an array, $form->getData() returns an array'
        );
    }

    /**
     * By default the Form component set/get data in field name, if you say a field
     * name is "title" it will use setTitle/getTitle.
     */
    public function testHowToHandleCustomizedGetterSetter()
    {
        $form = $this->container->get('form.factory')->createBuilder('form', array('full_name' => 'Joseph Rouff'), array())
            ->add('name', null, array('property_path' => '[full_name]')) // [...] notation is only for array, if it is an object "fullName" is enough.
            ->getForm();
        ;

        $this->assertEquals('Joseph Rouff', $form->get('name')->getData());
    }
}
