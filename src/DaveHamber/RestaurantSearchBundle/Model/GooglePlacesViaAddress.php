<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

use DaveHamber\RestaurantSearchBundle\Entity\GooglePlace;
use joshtronic\GooglePlaces;
use \GoogleGeocode;
use Psr\Log\LoggerInterface;
use DaveHamber\RestaurantSearchBundle\Entity\QueryString;
use DaveHamber\RestaurantSearchBundle\Entity\GoogleLocation;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Filesystem\Filesystem;

class GooglePlacesViaAddress
{
    /**
     * @var string
     */
    private $googleAPIKey;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $streetViewPath;

    /**
     * @var GooglePlaces
     */
    private $googlePlaces;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $queryStringRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $locationRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $placeRepository;

    /**
     * GooglePlacesViaAddress constructor.
     * @param $googleAPIKey
     * @param $rootDir
     * @param $streetViewPath
     * @param $entityManager
     * @param $logger
     */
    public function __construct($googleAPIKey, $rootDir, $streetViewPath, $entityManager, $logger)
    {
        $this->googleAPIKey = $googleAPIKey;
        $this->rootDir = $rootDir;

        $this->entityManager = $entityManager;
        $this->logger = $logger;

        GooglePlace::setPaths($rootDir, $streetViewPath);

        $this->queryStringRepository = $this->entityManager->getRepository('RestaurantSearchBundle:QueryString');
        $this->locationRepository = $this->entityManager->getRepository('RestaurantSearchBundle:GoogleLocation');
        $this->placeRepository = $this->entityManager->getRepository('RestaurantSearchBundle:GooglePlace');
    }


    /**
     * @param $searchAddress
     * @return array|\Doctrine\Common\Collections\Collection
     * @throws \Exception
     */
    public function getPlacesData($searchAddress)
    {
        $queryString = $this->queryStringRepository->findOneByQueryString($searchAddress);

        if ($queryString) {
            $searchLocation = $queryString->getGoogleLocation();
        } else {
            $queryString = new QueryString($searchAddress);
            $this->entityManager->persist($queryString);

            // Prevent against any race condition on the unique query string constraint
            try {
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->checkEntityManager();
                $queryString = $this->queryStringRepository->findOneByQueryString($searchAddress);
                $this->logger->info("Exception: " . $e->getMessage());
            }

            // converts an address string inputted by the user into a geographical longitude and latitude
            $geo = new GoogleGeocode();
            $geometryData = $geo->geocode($searchAddress);

            if ($geometryData['Response']['Status'] != 'OK') {
                return array();
            }

            $latitude = $geometryData['Geometry']['Latitude'];
            $longitude = $geometryData['Geometry']['Longitude'];

            $searchLocation = $this->fetchLocationEntity($longitude, $latitude);

            $queryString->setGoogleLocation($searchLocation);
            $this->entityManager->flush();
        }

        //$googlePlaces = $searchLocation->getGooglePlaces();
        $googlePlacesNearSearchLocation = $searchLocation->getGooglePlaces();

        if (count($googlePlacesNearSearchLocation) == 0) {
            $queryGooglePlaces = new GooglePlaces($this->googleAPIKey);

            $queryGooglePlaces->location = array($searchLocation->getLatitude(), $searchLocation->getLongitude());
            $queryGooglePlaces->types = "restaurant";
            $queryGooglePlaces->rankby = 'distance';

            $resultData = $queryGooglePlaces->nearBySearch();

            if (!isset($resultData['status'])) {
                return array();
            }

            if (StatusCode::OK != $resultData['status']) {
                return array();
            }

            foreach ($resultData['results'] as $result) {

                if (!isset($result['place_id'])) {
                    continue;
                }

                $googlePlace = $this->placeRepository->findOneBy(array('id' => $result['place_id']));

                if (!$googlePlace) {
                    $googlePlace = new GooglePlace();

                    if (isset($result['rating'])) {
                        $rating = (float)$result['rating'];
                    } else {
                        $rating = 0.0;
                    }

                    $googlePlace->setId($result['place_id']);
                    $googlePlace->setName($result['name']);
                    $googlePlace->setVicinity($result['vicinity']);
                    $googlePlace->setRating($rating);



                    if (isset($result['geometry']['location']) && is_array($result['geometry']['location']) && 2 == count(
                            $result['geometry']['location']
                        )
                    ) {
                        $longitude = (float)$result['geometry']['location']['lng'];
                        $latitude = (float)$result['geometry']['location']['lat'];
                    } else {
                        continue;
                    }

                    $fileName = $googlePlace->getAbsoluteStreetViewPath();

                    $fs = new Filesystem();

                    if (!$fs->exists($fileName)) {
                        if (isset($result['photos'])) {
                            $placePhoto = new GooglePlacePhoto(
                                $this->googleAPIKey,
                                $fileName,
                                $result['photos'][0]
                            );
                            $placePhoto->getPlacePhoto(250, 200);
                        } else {
                            $streetView = new GoogleStreetView($this->googleAPIKey, $fileName);
                            $streetView->setLocation($longitude, $latitude);
                            $streetView->getStreetView();
                        }
                    }

                    //try {
                        $this->entityManager->persist($googlePlace);
                        $this->entityManager->flush();
                    //} catch (UniqueConstraintViolationException $e) {
                    //    $this->checkEntityManager();
                    //    $googlePlace = $this->placeRepository->findOneById($result['place_id']);
                    //    $this->logger->info("Exception: " . $e->getMessage());
                    //}

                    $newGoogleLocation = $this->fetchLocationEntity($longitude, $latitude);

                    $googlePlace->setGoogleLocation($newGoogleLocation);

                    $this->entityManager->persist($googlePlace);
                    $this->entityManager->flush();

                    $searchLocation->addGooglePlace($googlePlace);
                    $this->entityManager->flush();
                }

                $searchLocation->addGooglePlace($googlePlace);
                $this->entityManager->flush();
            }

            $googlePlacesNearSearchLocation = $searchLocation->getGooglePlaces();
        }

        /* @var $currentGooglePlace GooglePlace */
        foreach($googlePlacesNearSearchLocation as $currentGooglePlace) {
            $currentGooglePlace->getGoogleLocation()->calculateDistance($searchLocation);
        }

        $iterator = $googlePlacesNearSearchLocation->getIterator();

        $iterator->uasort(function ($a, $b) {
            return ($a->getGoogleLocation()->getDistance() < $b->getGoogleLocation()->getDistance()) ? -1 : 1;
        });

        $googlePlacesNearSearchLocation = iterator_to_array($iterator);

        return $googlePlacesNearSearchLocation;
    }

    public function fetchLocationEntity($longitude, $latitude)
    {
        $location = $this->locationRepository->findOneBy(array('latitude' => $latitude, 'longitude' => $longitude));

        if (!$location) {
            $location = new GoogleLocation($latitude, $longitude);

            // Prevent against any race condition on the unique location constraint
            try {
                $this->entityManager->persist($location);
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->checkEntityManager();
                $location = $this->locationRepository->findOneBy(
                    array(
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    )
                );
            }
        }

        return $location;
    }

    /**
     * @return GooglePlaces
     */
    public function getGooglePlaces()
    {
        return $this->googlePlaces;
    }

    /**
     * @return string
     */
    public function getGoogleAPIKey()
    {
        return $this->googleAPIKey;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * @return string
     */
    public function getStreetViewPath()
    {
        return $this->streetViewPath;
    }

    public function checkEntityManager()
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }
}