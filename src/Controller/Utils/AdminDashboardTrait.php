<?php

namespace App\Controller\Utils;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\Query\Expr;
use Nucleos\UserBundle\Model\UserInterface;
use Nucleos\UserBundle\Model\UserManagerInterface;
use Hashids\Hashids;
use League\Flysystem\Filesystem;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use phpcent\Client as CentrifugoClient;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vich\UploaderBundle\Storage\StorageInterface;

trait AdminDashboardTrait
{
    protected function redirectToDashboard(Request $request, array $params = [])
    {
        $nav = $request->query->getBoolean('nav', true);

        $defaultParams = [
            'date' => $request->get('date', (new \DateTime())->format('Y-m-d')),
        ];

        if (!$nav) {
            $defaultParams['nav'] = 'off';
        }

        return $this->redirectToRoute('admin_dashboard_fullscreen', array_merge($defaultParams, $params));
    }

    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function dashboardAction(Request $request,
        JWTManagerInterface $jwtManager,
        CentrifugoClient $centrifugoClient,
        Redis $tile38,
        IriConverterInterface $iriConverter)
    {
        return $this->dashboardFullscreenAction((new \DateTime())->format('Y-m-d'),
            $request,  $jwtManager, $centrifugoClient, $tile38, $iriConverter);
    }

    /**
     * @Route("/admin/dashboard/fullscreen/{date}", name="admin_dashboard_fullscreen",
     *   requirements={"date"="[0-9]{4}-[0-9]{2}-[0-9]{2}"})
     */
    public function dashboardFullscreenAction($date, Request $request,

        JWTManagerInterface $jwtManager,
        CentrifugoClient $centrifugoClient,
        Redis $tile38,
        IriConverterInterface $iriConverter)
    {
        die('soon...');
    }


}
