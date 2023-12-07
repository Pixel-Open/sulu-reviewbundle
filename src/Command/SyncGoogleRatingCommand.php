<?php

namespace Pixel\ReviewBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Entity\Review;
use Pixel\ReviewBundle\Entity\Setting;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SyncGoogleRatingCommand extends Command
{
    protected static $defaultName = "sync:google:rating";
    protected static $defaultDescription = "Synchronizes the rating with the ones from Google";
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $client;

    public function __construct(
        EntityManagerInterface $entityManager,
        HttpClientInterface $client,
        string $name = null
    ) {
        $this->entityManager = $entityManager;
        $this->client = $client;
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setHelp("Synchronizes the rating with the ones from Google");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Synchronization...");
        $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);

        if ($setting !== null && $setting->getUseGoogleRating()) {
            $placeId = $setting->getPlaceId();
            $apiKey = $setting->getApiKey();

            if ($placeId) {
                $response = $this->client->request("GET", "https://maps.googleapis.com/maps/api/place/details/json", [
                    'query' => [
                        'placeid' => $placeId,
                        'key' => $apiKey,
                        'fields' => "user_ratings_total,rating,reviews",
                        'reviews_sort' => "newest",
                        'reviews_no_translations' => "true",
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $content = $response->toArray();

                    if ($content['status'] !== "OK") {
                        if ($content['status'] === "INVALID_REQUEST") {
                            throw new \Exception("The place ID might not be correct. Please check it in the administration interface");
                        }

                        if ($content['status'] === "REQUEST_DENIED") {
                            throw new \Exception("The API key might not be correct or is missing. Please check it in the administration interface");
                        }
                    } else {
                        $setting->setTotalRating($content['result']['user_ratings_total']);
                        $setting->setAverageRating($content['result']['rating']);

                        if ($setting->getRetrieveReviews()) {
                            $retrievedReviews = $content['result']['reviews'];
                            $reviewsDB = $this->entityManager->getRepository(Review::class)->findBy([
                                'isActive' => true,
                                'isFromGoogle' => true,
                            ]);
                            $time = [];
                            foreach ($reviewsDB as $reviewDB) {
                                $time[] = $reviewDB->getDate()->getTimestamp();
                            }

                            foreach ($retrievedReviews as $retrievedReview) {
                                if (!in_array($retrievedReview['time'], $time)) {
                                    $review = new Review();
                                    $review->setName($retrievedReview['author_name']);
                                    $review->setRating($retrievedReview['rating']);
                                    $review->setDate((new \DateTimeImmutable())->setTimestamp($retrievedReview['time']));
                                    $review->setIsFromGoogle(true);
                                    $review->setIsActive(true);
                                    $review->setDefaultLocale($retrievedReview['original_language']);
                                    if (isset($retrievedReview['text'])) {
                                        $review->setMessage($retrievedReview['text']);
                                    }

                                    $this->entityManager->persist($review);
                                }
                            }
                        }

                        $this->entityManager->persist($setting);
                        $this->entityManager->flush();

                        $output->writeln("<info>Synchronization ended successfully!</info>");
                        return Command::SUCCESS;
                    }
                } else {
                    $output->writeln("<error>An error occurs during the synchronization</error>");
                    return Command::FAILURE;
                }
            }

            $output->writeln("<error>There is no 'place ID' registered in the review settings</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>No synchronization is required</info>");
        return Command::SUCCESS;
    }
}
