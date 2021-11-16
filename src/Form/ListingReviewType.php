<?php


namespace App\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingReviewType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('rating', ChoiceType::class, [
                'choices' => $this->createRatingList($options['rating_steps']),
                'label' => 'sylius.form.review.rating',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('title', HiddenType::class, [
                'label' => 'sylius.form.review.title',
                'data' => 'Awesome Title',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'sylius.ui.comment',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'rating_steps' => 5,
        ]);
    }

    /**
     * @param int $maxRate
     *
     * @return array
     */
    protected function createRatingList($maxRate)
    {
        $ratings = [];
        for ($i = 1; $i <= $maxRate; ++$i) {
            $noStar = str_repeat('☆',$maxRate - $i);
            $yesStar = str_repeat('★', $i);
            $ratings[$noStar.$yesStar.' ('.$i.'/'.$maxRate.')'] = $i;
        }

        return $ratings;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sylius_listing_review';
    }
}