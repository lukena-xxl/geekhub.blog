<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DayCheckExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('isDay', [$this, 'isDay']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isDay', [$this, 'isDay']),
        ];
    }

    public function isDay($value)
    {
        $result = false;
        $now = new \DateTime();
        if ($now->format('l') == $value) {
            $result = true;
        }
        return $result;
    }
}
