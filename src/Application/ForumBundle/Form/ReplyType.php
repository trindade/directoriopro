<?php

namespace Application\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ReplyType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('body')
            ->add('thread_id')
            //->add('user_id')
            //->add('votes')
            //->add('spam')
            //->add('date')
        ;
    }

    public function getName()
    {
        return 'application_forumbundle_replytype';
    }
}
