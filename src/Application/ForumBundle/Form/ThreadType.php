<?php

namespace Application\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ThreadType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('body')
            ->add('forum_id')
            //->add('user_id')
            //->add('visits')
            //->add('threads')
            //->add('slug')
            //->add('date');
        ;
    }

    public function getName()
    {
        return 'application_forumbundle_threadtype';
    }
}
