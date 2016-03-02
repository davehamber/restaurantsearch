<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

use joshtronic\GooglePlaces;
use \GoogleGeocode;
use Psr\Log\LoggerInterface;

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
     * GooglePlacesViaAddress constructor.
     * @param $googleAPIKey
     * @param $rootDir
     * @param $logger
     */
    public function __construct($googleAPIKey, $rootDir, $streetViewPath, $logger)
    {
        $this->googleAPIKey = $googleAPIKey;
        $this->rootDir = $rootDir;
        $this->streetViewPath = $streetViewPath;
        $this->logger = $logger;
    }

    /**
     * @param $searchAddress
     * @return NearBySearchResults
     */
    public function getPlacesData($searchAddress)
    {
        // converts an address string inputted by the user into a geographical longitude and latitude
        $geo = new GoogleGeocode();
        $geometryData = $geo->geocode($searchAddress);

        if ($geometryData['Response']['Status'] != 'OK') {
            return new NearBySearchResults($this->googleAPIKey, $this);
        }

        $latitude = $geometryData['Geometry']['Latitude'];
        $longitude = $geometryData['Geometry']['Longitude'];

        // create and prepare the parameters of a GooglePlaces object
        $this->googlePlaces = new GooglePlaces($this->googleAPIKey);

        $this->googlePlaces->location = array($latitude, $longitude);
        $this->googlePlaces->types = "restaurant";
        $this->googlePlaces->rankby = 'distance';

        // create the search result object and then execute search to populate it
        $searchResults = new NearBySearchResults($this->googleAPIKey, $this);
        $searchResults->performNearBySearch();

        return $searchResults;
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
}