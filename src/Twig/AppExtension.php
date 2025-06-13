<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('age', [$this, 'calculateAge']),
        ];
    }

    public function calculateAge($birthDate): string
    {
        if (is_string($birthDate)) {
            $birthDate = new \DateTime($birthDate);
        }
        
        $today = new \DateTime();
        $interval = $today->diff($birthDate);
        
        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
        } else {
            $months = ($interval->y * 12) + $interval->m;
            return $months . ' month' . ($months > 1 ? 's' : '');
        }
    }
} 