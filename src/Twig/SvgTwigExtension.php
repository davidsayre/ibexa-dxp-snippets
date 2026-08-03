<?php
declare(strict_types=1);

namespace App\Twig;

use Exception;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SvgTwigExtension extends AbstractExtension
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('ibexa_svg_link', [$this, 'generateLink']),
        ];
    }

    public function generateLink(int $contentId, string $fieldIdentifier, string $filename): string
    {
        $url = ""; // safe
        $filename = str_replace(" ", "-", $filename);
        $filename = str_replace("%20", "-", $filename);
        try {
            $url = $this->router->generate('app.svg_download', [
                'contentId' => $contentId,
                'fieldIdentifier' => $fieldIdentifier,
                'filename' => $filename,
            ]);
        } catch (Exception $e) {
            // TODO error log
        }

        return $url;
    }
}
