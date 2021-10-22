<?php


namespace App\Controller;


use App\Controller\Utils\UserTrait;
use App\Entity\Address;
use App\Entity\Listing;
use Cocur\Slugify\SlugifyInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ListingController extends AbstractController
{
    use UserTrait;

    private $serializer;

    public function __construct(
        SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $type
     * @param int $id
     * @param string $slug
     * @param Request $request
     * @param SlugifyInterface $slugify
     * @param Address|null $address
     *
     * @Route("/listing/{id}-{slug}", name="listing",
     *   requirements={
     *     "id"="(\d+|__LISTING_ID__)",
     *     "slug"="([a-z0-9-]+)"
     *   },
     *   defaults={
     *     "slug"=""
     *   }
     * )
     */
    public function indexAction( $id, $slug, Request $request,
                                SlugifyInterface $slugify,
                                Address $address = null)
    {
        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)->find($id);

        if (!$listing) {
            throw new NotFoundHttpException();
        }

//        $this->denyAccessUnlessGranted('view', $listing);

        $listingNormalized = $this->get('serializer')->normalize($listing, 'jsonld', [
            'resource_class' => Listing::class,
            'operation_type' => 'item',
            'item_operation_name' => 'get',
            'groups' => ['listing_public']
        ]);

        return $this->render('listing/index.html.twig', array(
            'listing' => $listing,
            'listingNormalized' => $listingNormalized,
            'addresses_normalized' => $this->getUserAddresses(),
        ));
    }
}