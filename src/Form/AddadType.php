<?php

namespace App\Form;

use App\Entity\Products;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class AddadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => "This field cannot be blank"
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'The name must be {{limit}} chars minimal',
                        'max' => 100,
                        'maxMessage' => 'The name must not exceed {{limit}} chars'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'The price must be provided'
                    ])
                ]
            ])
            ->add('mainImage', FileType::class, [
                'label' => "Main image",
                'mapped' => false, // Très important
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(
                        ['message' => 'This field must be filled']
                    ),
                    new Length([
                        'min' => 20,
                        'minMessage' => 'You must at least write {{ limit }} chars',
                        'max' => 2000,
                        'maxMessage' => 'You cannot write more than {{ limit }} chars'
                    ])
                ]
            ])
            ->add('category', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => "Please Specify a category"])
                ]
            ])
            ->add("manyAds", CheckboxType::class, [
                "mapped" => false,
                'label' => "I want to add another ad",
                'required' => false
            ])
            ->add("Post_my_ad", SubmitType::class, [
                'label' => "Post my ad"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
