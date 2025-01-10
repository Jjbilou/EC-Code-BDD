<?php

namespace App\Form;

use App\Entity\BookRead;
use App\Entity\Book;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddReadBookForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('book', ChoiceType::class, [
                'choices' => $this->getBookChoices($options['books'])
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le nombre limite de caractère est de 255',
                    ]),
                ],
            ])
            ->add('rating', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
                'required' => true,
            ])
            ->add('is_read', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    private function getBookChoices($books)
    {
        $choices = [];
        foreach ($books as $book) {
            $choices[$book->getName()] = $book;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookRead::class,
            'books' => []
        ]);
    }
}
