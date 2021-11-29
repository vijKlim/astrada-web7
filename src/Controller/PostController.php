<?php

/*
 * This file is part of jedisjeux project.
 *
 * (c) Loïc Frémont
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Topic;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostController extends ResourceController
{

    /**
     * @Route("/post/create_for_booking/{id}", name="post_create_for_booking")
     */
    public function createForBooking($id, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Booking::class);

        $booking = $repository->findOneBy(['id'=>$id, 'user' => $this->getUser()]);

        if(!$booking){
            /** @var Booking $booking */
            $booking = $repository->find(['id'=>$id]);
            $listingBusiness = $booking->getListing()->getBusiness();
            $user = $this->getUser();
            $allowed = false;
            foreach ($user->getBusinesses() as $business){
                if($listingBusiness->getId() == $business->getId()){
                    $allowed = true;
                }
            }
        }
    }

    public function indexByTopicAction(Request $request)
    {
//        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
//
//        $this->isGrantedOr403($configuration, ResourceActions::INDEX);
//
//        /** @var TaxonTranslationInterface $rootTaxon */
//        $rootTaxon = $this->getTaxonRepository()->findOneBy(['code' => Taxon::CODE_FORUM]);
//
//        $criteria = $configuration->getCriteria();
//        /** @var Topic $topic */
//        $topic = $this->get('app.repository.topic')->find($criteria['topic']);
//        $this->topicIsGrantedOr403($topic);
//
//        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);
//
//        $view = View::create($resources);
//
//        if ($configuration->isHtmlRequest()) {
//            $view
//                ->setTemplate($configuration->getTemplate(ResourceActions::INDEX))
//                ->setTemplateVar($this->metadata->getPluralName())
//                ->setData([
//                    'metadata' => $this->metadata,
//                    'resources' => $resources,
//                    'posts' => $resources,
//                    'topic' => $topic,
//                    'taxons' => $rootTaxon->getChildren(),
//                    $this->metadata->getPluralName() => $resources,
//                ])
//            ;
//        }
//
//        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * @param Topic $topic
     *
     * @throws AccessDeniedException
     */
    protected function topicIsGrantedOr403(Topic $topic)
    {
//        if (null === $mainTaxon = $topic->getMainTaxon()) {
//            return;
//        }
//
//        $onlyPublic = $this->getAuthorizationChecker()->isGranted('ROLE_STAFF') ? false : true;
//
//        if (!$mainTaxon->isPublic() and $onlyPublic) {
//            throw new AccessDeniedException();
//        }
    }

    /**
     * @return AuthorizationChecker
     */
    protected function getAuthorizationChecker()
    {
        return $this->get('security.authorization_checker');
    }


}
