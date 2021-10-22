<?php


namespace App\Controller;

use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}", requirements={ "_locale": "%locale_regex%" })
 */
class ContentController extends AbstractController
{
    /**
     * @Route("/legal", name="legal")
     */
    public function legalAction(Request $request, Filesystem $assetsFilesystem)
    {
        if ($assetsFilesystem->has('custom_legal.md')) {
            $text = $assetsFilesystem->read('custom_legal.md');
        } else {
            $text = $this->localizeRemoteFile($request, 'legal');
        }

        return $this->render('content/markdown.html.twig', [
            'text' => $text
        ]);
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction(Request $request, Filesystem $assetsFilesystem)
    {
        if ($assetsFilesystem->has('custom_terms.md')) {
            $text = $assetsFilesystem->read('custom_terms.md');
        } else {
            $text = $this->localizeRemoteFile($request, 'terms');
        }

        return $this->render('content/markdown.html.twig', [
            'text' => $text
        ]);
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction(Request $request, Filesystem $assetsFilesystem)
    {
        if ($assetsFilesystem->has('custom_privacy.md')) {
            $text = $assetsFilesystem->read('custom_privacy.md');
        } else {
            $text = $this->localizeRemoteFile($request, 'privacy');
        }

        return $this->render('content/markdown.html.twig', [
            'text' => $text
        ]);
    }

    private function localizeRemoteFile(Request $request, $type)
    {
        $locale = $request->getLocale();
        $files = [
//            'fr' => sprintf('http://coopcycle.org/%s/fr.md', $type),
//            'en' => sprintf('http://coopcycle.org/%s/en.md', $type),
        ];

        $key = array_key_exists($locale, $files) ? $locale : 'en';

        return file_get_contents($files[$key]);
    }
}