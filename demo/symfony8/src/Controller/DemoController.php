<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Demo controllers for Fragment Kit Bundle.
 */
final class DemoController extends AbstractController
{
    #[Route(path: '/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('demo/home.html.twig');
    }

    /**
     * Sub-request that returns HTTP 403 (no exception).
     * Without FragmentKit, ignore_errors:true still yields a parent 500.
     */
    #[Route(path: '/_fragment/forbidden', name: 'app_fragment_forbidden', methods: ['GET'])]
    public function forbiddenFragment(): Response
    {
        return new Response('Forbidden fragment body', Response::HTTP_FORBIDDEN);
    }

    /**
     * Sub-request that returns HTTP 404 (no exception).
     */
    #[Route(path: '/_fragment/missing', name: 'app_fragment_missing', methods: ['GET'])]
    public function missingFragment(): Response
    {
        return new Response('Missing fragment body', Response::HTTP_NOT_FOUND);
    }

    /**
     * Healthy fragment used as a control case.
     */
    #[Route(path: '/_fragment/ok', name: 'app_fragment_ok', methods: ['GET'])]
    public function okFragment(): Response
    {
        return new Response('<p class="fragment-ok">Healthy fragment rendered OK.</p>');
    }
}
