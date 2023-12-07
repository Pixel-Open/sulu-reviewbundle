<?php

namespace Pixel\ReviewBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="review_settings")
 * @Serializer\ExclusionPolicy("all")
 */
class Setting implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = "review_settings";
    public const FORM_KEY = "review_settings";
    public const SECURITY_CONTEXT = "review_settings.settings";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private int $totalRating;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose()
     */
    private float $averageRating;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $placeId = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $apiKey = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     */
    private ?bool $useGoogleRating = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     */
    private ?bool $retrieveReviews;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalRating(): int
    {
        return $this->totalRating;
    }

    public function setTotalRating(int $totalRating): void
    {
        $this->totalRating = $totalRating;
    }

    public function getAverageRating(): float
    {
        return $this->averageRating;
    }

    public function setAverageRating(float $averageRating): void
    {
        $this->averageRating = $averageRating;
    }
    public function getPlaceId(): ?string
    {
        return $this->placeId;
    }
    public function setPlaceId(?string $placeId): void
    {
        $this->placeId = $placeId;
    }
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }
    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getUseGoogleRating(): ?bool
    {
        return $this->useGoogleRating;
    }

    public function setUseGoogleRating(?bool $useGoogleRating): void
    {
        $this->useGoogleRating = $useGoogleRating;
    }

    public function getRetrieveReviews(): ?bool
    {
        return $this->retrieveReviews;
    }

    public function setRetrieveReviews(?bool $retrieveReviews): void
    {
        $this->retrieveReviews = $retrieveReviews;
    }
}
