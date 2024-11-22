<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;//Agregamos esta linea

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;//Agregamos esta linea

use Symfony\Component\Validator\Constraints\Length;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => [
                        new Length([
                            'min' => 3,
                            'max' => 128,
                            'minMessage' => 'Minimo 3 caracteres!!',
                            'maxMessage' => 'Maximo 128 caracteres!!',
                        ]),
                    ],
                    'required' => true
            ))
            ->add('type', ChoiceType::class, [
                'choices' => Post::TYPES
            ])
            ->add('description')
            ->add('file', FileType::Class, [
                'label' => 'photo',
                'required' => false
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
