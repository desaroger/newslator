<?php

namespace AppBundle\Form;

use AppBundle\Utils\Scraper2;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FeedType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach(Scraper2::$staticPublishers as $publisher) {
            $choices[$publisher['printable']] = $publisher['code'];
        }
        $builder->add('title')->add('body')->add('image')->add('source')
            ->add('publisher', ChoiceType::class, [
               'choices' => $choices
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Feed'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_feed';
    }


}
