<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IsNumericTwigExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return array(
            new TwigFunction('is_numeric', array($this, 'isNumeric')),
        );
    }

    public function isNumeric($value)
    {
        return  is_numeric($value);
    }
}
?>