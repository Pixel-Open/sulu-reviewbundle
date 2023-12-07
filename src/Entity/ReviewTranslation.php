<?php

namespace Pixel\ReviewBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="review_translation")
 * @ORM\Entity(repositoryClass="Pixel\ReviewBundle\Repository\ReviewRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class ReviewTranslation implements AuditableInterface
{
    use AuditableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @var Review
     * @ORM\ManyToOne(targetEntity="Pixel\ReviewBundle\Entity\Review", inversedBy="translations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $review;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private string $locale;

    /**
     * @ORM\Column(type="text")
     * @Serializer\Expose()
     */
    private string $message;

    public function __construct(Review $review, string $locale)
    {
        $this->review = $review;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReview(): Review
    {
        return $this->review;
    }

    public function setReview(Review $review): void
    {
        $this->review = $review;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
