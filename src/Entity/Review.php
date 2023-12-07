<?php

namespace Pixel\ReviewBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="review")
 * @ORM\Entity(repositoryClass="Pixel\ReviewBundle\Repository\ReviewRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Review
{
    public const RESOURCE_KEY = "reviews";
    public const LIST_KEY = "reviews";
    public const FORM_KEY = "review_details";
    public const SECURITY_CONTEXT = "review.reviews";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     */
    private string $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Serializer\Expose()
     */
    private \DateTimeImmutable $date;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private int $rating;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     */
    private ?bool $isFromGoogle = null;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @Serializer\Expose()
     */
    private ?MediaInterface $clientImage = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     */
    private ?bool $isActive;

    /**
     * @var Collection<string, ReviewTranslation>
     * @ORM\OneToMany(targetEntity="Pixel\ReviewBundle\Entity\ReviewTranslation", mappedBy="review", cascade={"ALL"}, indexBy="locale")
     * @Serializer\Exclude()
     */
    private $translations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $defaultLocale;

    private string $locale = "fr";

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }
    public function isFromGoogle(): ?bool
    {
        return $this->isFromGoogle;
    }
    public function setIsFromGoogle(?bool $isFromGoogle): void
    {
        $this->isFromGoogle = $isFromGoogle;
    }

    public function getClientImage(): ?MediaInterface
    {
        return $this->clientImage;
    }

    public function setClientImage(?MediaInterface $clientImage): void
    {
        $this->clientImage = $clientImage;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    protected function createTranslation(string $locale): ReviewTranslation
    {
        $translation = new ReviewTranslation($this, $this->locale);
        $this->translations->set($locale, $translation);
        return $translation;
    }

    protected function getTranslation(string $locale): ?ReviewTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }
        return $this->translations->get($locale);
    }

    /**
     * @return array<ReviewTranslation>
     */
    protected function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @Serializer\VirtualProperty(name="message")
     */
    public function getMessage(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getMessage();
    }

    public function setMessage(string $message): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setMessage($message);
        return $this;
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(?string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
    }
}
