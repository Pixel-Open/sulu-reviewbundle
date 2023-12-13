<?php

namespace Pixel\ReviewBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Entity\Setting;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SettingsExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('reviews_settings', [$this, 'reviewsSettings']),
        ];
    }

    public function reviewsSettings(): Setting
    {
        return $this->entityManager->getRepository(Setting::class)->findOneBy([]);
    }
}
