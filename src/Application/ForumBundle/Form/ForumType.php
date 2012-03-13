<?php

namespace Application\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ForumType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('body')
            //->add('visits')
            //->add('threads')
            //->add('slug')
        ;
    }

    public function getName()
    {
        return 'application_forumbundle_forumtype';
    }
}
