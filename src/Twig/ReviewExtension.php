<?php

namespace Pixel\ReviewBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Entity\Review;
use Pixel\ReviewBundle\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReviewExtension extends AbstractExtension
{
    private ReviewRepository $reviewRepository;
    private Environment $environment;
    private RequestStack $request;

    public function __construct(ReviewRepository $reviewRepository, Environment $environment, RequestStack $request)
    {
        $this->reviewRepository = $reviewRepository;
        $this->environment = $environment;
        $this->request = $request;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction("get_latest_reviews_html", [$this, "getLatestReviewsHtml"], [
                "is_safe" => ["html"],
            ]),
            new TwigFunction("get_latest_reviews", [$this, "getLatestReviews"], [
                "is_safe" => ["html"],
            ]),
        ];
    }

    public function getLatestReviewsHtml(int $limit = 3): string
    {
        $reviews = $this->reviewRepository->getLatestReviews($limit, $this->request->getMainRequest()->getLocale());
        return $this->environment->render("@Review/twig/reviews.html.twig", [
            "reviews" => $reviews,
        ]);
    }

    /**
     * @return array<Review>
     */
    public function getLatestReviews(int $limit = 3): array
    {
        return $this->reviewRepository->getLatestReviews($limit, $this->request->getMainRequest()->getLocale());
    }
}
