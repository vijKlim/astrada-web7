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
use App\Entity\Post;
use App\Entity\Topic;

use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;

use Sylius\Component\Resource\Factory\FactoryInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostController extends AbstractController
{

    /**
     * Creates a new Topic Post form.
     *
     * @param  Topic $topic
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getForTopicFormAction(Topic $topic,FactoryInterface $postFactory)
    {
        $post = $postFactory->createNew();
        $form = $this->createPostForm($post, $topic);

        return $this->render(
            'post/form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    private function createPostForm(Post $post, Topic $topic)
    {
        $form = $this->createForm(
            PostType::class,
            $post,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'post_create_for_topic',
                    array(
                        'id' => $topic->getId()
                    )
                )
            )
        );

        return $form;
    }

    /**
     * @Route("/post/create_for_topic/{id}", name="post_create_for_topic")
     */
    public function createForTopicAction($id, Request $request,FactoryInterface $postFactory,
                                     EntityManagerInterface $entityManager)
    {
        $repository = $this->getDoctrine()->getRepository(Topic::class);

        $topic = $repository->findOneBy(['id'=>$id, 'author' => $this->getUser()->getCustomer()]);

        $this->topicIsGrantedOr403($topic);

        $post = $postFactory->createForTopic($topic);

        $form = $this->createPostForm($post, $topic);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post = $form->getData();

            $entityManager->persist($post);
            $entityManager->flush();
        }else{
            foreach ($form->getErrors() as $error) {
                var_dump($error);
            }
            var_dump($form->getData()->getBody());
            die(count($form->getErrors()));
        }
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
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
        if (null === $booking = $topic->getBooking()) {
            return;
        }
        $allowed = $booking->getUser()->getId() == $this->getUser()->getId();

        if(!$allowed){
            $listingBusiness = $booking->getListing()->getBusiness();
            foreach ($this->getUser()->getBusinesses() as $business){
                if($listingBusiness->getId() == $business->getId()){
                    $allowed = true;
                    break;
                }
            }
        }

        if(!$allowed){
            throw new AccessDeniedException();
        }

    }

    /**
     * @return AuthorizationChecker
     */
    protected function getAuthorizationChecker()
    {
        return $this->get('security.authorization_checker');
    }


}
