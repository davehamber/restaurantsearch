<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

class NearBySearchResults implements \IteratorAggregate
{
    /**
     * @var array
     *
     * Array which is used to store NearBySearchResult objects
     */
    protected $googlePlacesResults = array();

    /**
     * @var array
     *
     * html_attributions contain a set of attributions about this listing which must be displayed to the user.
     */
    protected $htmlAttributions = array();

    /**
     * @var string
     *
     * Contains metadata on the request.
     */
    protected $status = null;

    /**
     * @var string
     *
     * Your application's API key. This key identifies your application for purposes of quota management and so that
     * places added from your application are made immediately available to your app.
     */
    protected $googleAPIKey;

    /**
     * @var GooglePlacesViaAddress
     *
     * Stores the GooglePlacesViaAddress object that was the source of this query
     */
    protected $googlePlacesViaAddress;

    /**
     * NearBySearchResults constructor.
     * @param $googleAPIKey
     * @param GooglePlacesViaAddress $googlePlacesViaAddress
     */
    public function __construct($googleAPIKey, GooglePlacesViaAddress $googlePlacesViaAddress)
    {
        $this->googleAPIKey = $googleAPIKey;
        $this->googlePlacesViaAddress = $googlePlacesViaAddress;
    }

    /**
     * performNearBySearch
     * Executes the near by search query and stores result as NearBySearchResult objects
     */
    public function performNearBySearch()
    {
        $resultData = $this->googlePlacesViaAddress->getGooglePlaces()->nearBySearch();

        if (!isset($resultData['status'])) {
            return;
        }

        $this->htmlAttributions = $resultData['html_attributions'];
        $this->status = $resultData['status'];

        if (StatusCode::OK == $resultData['status']) {

            foreach ($resultData['results'] as $result) {
                $this->googlePlacesResults[] = new NearBySearchResult($result, $this->googlePlacesViaAddress);
            }
        }
    }

    /**
     * @return \ArrayIterator
     * Allows object to be iterated over using the internal array of NearBySearchResult objects
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->googlePlacesResults);
    }
}