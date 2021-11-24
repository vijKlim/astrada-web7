<?php


namespace App\Controller;

use App\Entity\Model\UserAddressRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


class UserAddressController extends AbstractController
{
    private $userAddressRequest;

    public function __construct(UserAddressRequest $userAddressRequest)
    {
        $this->userAddressRequest = $userAddressRequest;
    }

    /**
     * Save current user address.
     *
     * @Route("/user-address", name="user_address")
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request)
    {

        $this->get('session')->set('user_address_request', $this->userAddressRequest);
        return new JsonResponse(['status'=>$this->get('session')->get('user_address_request')]);
    }
}