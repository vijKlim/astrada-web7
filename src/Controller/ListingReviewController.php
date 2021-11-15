<?php


namespace App\Controller;


use App\Entity\Listing;
use App\Entity\ListingReview;
use App\Form\ListingReviewType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ListingReviewController extends AbstractController
{

    /**
     * Creates a new ListingReview form.
     *
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listingReviewFormAction(Listing $listing)
    {

//        $listingReview = $this->container->get('sylius.factory.listing_review')->createNew();
//        $form = $this->createReviewForm($listing, $listingReview);

        return $this->render(
            'form/frontend/listing_review.html.twig',
            array(

                'listing' => $listing
            )
        );
    }

    /**
     * Creates a form for Booking Price.
     *
     * @param Listing $listing The entity
     * @param ListingReview $listingReview The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createReviewForm(Listing $listing, ListingReview $listingReview)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingReviewType::class,
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'listing_review',
                    array(
                        'listing_id' => $listing->getId()
                    )
                )
            )
        );

        return $form;
    }
}