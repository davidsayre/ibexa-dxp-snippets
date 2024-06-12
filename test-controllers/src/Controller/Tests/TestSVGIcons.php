<?php

declare(strict_types=1);

namespace App\Controller\Tests;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
  # config/routes/test_svgicons_routes.yaml (manual include)

    TestSVGIcons:
        path: /_test/svg_icons
        defaults:
            _controller: App\Controller\Tests\TestSVGIcons::svgIcons
 */
class TestSVGIcons extends AbstractController
{
    public function svgIcons(Request $request): Response
    {
        return $this->render('@standard/test/svg_icons.html.twig');
    }

}