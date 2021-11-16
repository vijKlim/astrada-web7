<?php


namespace App\Controller;


use App\Controller\Utils\UserTrait;
use App\Entity\Listing;
use App\Entity\ListingReview;
use App\Form\ListingReviewType;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Review\Factory\ReviewFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ListingReviewController extends AbstractController
{
    use UserTrait;

    private $listingReviewFactory;
    private $entityManager;
    private $slugify;

    public function __construct(ReviewFactoryInterface $listingReviewFactory,
                                EntityManagerInterface $entityManager,
                                SlugifyInterface $slugify)
    {
        $this->listingReviewFactory = $listingReviewFactory;
        $this->entityManager = $entityManager;
        $this->slugify = $slugify;
    }

    /**
     * Creates a new ListingReview form.
     *
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listingReviewFormAction(Listing $listing)
    {

        $listingReview = $this->listingReviewFactory->createNew();
        $form = $this->createReviewForm($listing, $listingReview);

        return $this->render(
            'form/frontend/listing_review.html.twig',
            array(
                'form' => $form->createView(),
                'listing' => $listing
            )
        );
    }

    /**
     * Get Booking
     *
     * @Route("/{listing_id}", name="listing_review_new", requirements={"listing_id" = "\d+"})
     *
     * @Security("is_granted('ROLE_USER')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function newListingReviewAction($listing_id, Request $request)
    {
        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)->find($listing_id);

        if (!$listing) {
            throw new NotFoundHttpException();
        }


        $listingReview = $this->listingReviewFactory->createForSubjectWithReviewer($listing,$this->getUser()->getCustomer());
        $form = $this->createReviewForm($listing, $listingReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ListingReview $listingReview */
            $listingReview = $form->getData();

            $this->entityManager->persist($listingReview);
            $this->entityManager->flush();
        }

        $urlDefaultParameters = [
            'id' => $listing->getId(),
            'slug' => $this->slugify->slugify($listing->getTitle())
        ];
        return $this->redirect(
            $this->generateUrl(
                'listing',
                $urlDefaultParameters
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
        $form = $this->createForm(
            ListingReviewType::class,
            $listingReview,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'listing_review_new',
                    array(
                        'listing_id' => $listing->getId()
                    )
                )
            )
        );

        return $form;
    }
}