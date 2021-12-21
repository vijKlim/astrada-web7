<?php


namespace App\Controller;


use App\Form\ListingFlow\CreateListing;
use App\Form\ListingFlow\CreateListingFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ListingFlowController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig) {
        $this->twig = $twig;
    }

    /**
     * @Route("/create-listing/", name="_FormFlow_createListing")
     */
    public function createListingAction(CreateListingFlow $flow) {
        return $this->processFlow(new CreateListing(), $flow,
            'listingFlow/createListing.html.twig');
    }

    protected function processFlow($formData, FormFlowInterface $flow, $template) {
        $flow->bind($formData);

        $form = $submittedForm = $flow->createForm();

        if ($flow->isValid($submittedForm)) {
            $flow->saveCurrentStepData($submittedForm);

            if ($flow->nextStep()) {
                // create form for next step
                $form = $flow->createForm();
            } else {
                // flow finished
                // ...

                $flow->reset();

                return $this->redirect($this->generateUrl('_FormFlow_start'));
            }
        }


        if ($flow->redirectAfterSubmit($submittedForm)) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $params = $this->get('craue_formflow_util')->addRouteParameters(array_merge($request->query->all(),
                $request->attributes->get('_route_params')), $flow);

            return $this->redirect($this->generateUrl($request->attributes->get('_route'), $params));
        }

        return new Response($this->twig->render($template, [
            'form' => $form->createView(),
            'flow' => $flow,
            'formData' => $formData,
        ]));
    }
}