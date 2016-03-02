<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class NearBySearchResult
 * @package DaveHamber\RestaurantSearchBundle\Model
 *
 * Class ignores deprecated id and reference data from google places api
 */
class NearBySearchResult
{
    /**
     * @var float
     *
     * Google result stores long / lat as 'geometry' => array('location' => array ('lat' => 0.0, 'lng' => 0.0))
     * Here we just forgo 'location' and store longitude and latitude directly
     */
    protected $longitude = null;

    /**
     * @var float
     *
     * Google result stores long / lat as 'geometry' => array('location' => array ('lat' => 0.0, 'lng' => 0.0))
     * Here we just forgo 'location' and store longitude and latitude directly
     */
    protected $latitude = null;

    /**
     * @var string
     *
     * Icon contains the URL of a recommended icon which may be displayed to the user when indicating this result.
     */
    protected $icon = null;

    /**
     * @var string
     *
     * Contains the human-readable name for the returned result. For establishment results,
     * this is usually the business name.
     */
    protected $name = null;

    /**
     * @var array
     *
     * opening_hours may contain the following information:
     * open_now is a boolean value indicating if the place is open at the current time.
     */
    protected $openingHours = array();

    /**
     * @var array
     *
     * An array of photo objects, each containing a reference to an image. A Place Search will return at most one
     * photo object. Performing a Place Details request on the place may return up to ten photos. More information
     * about Place Photos and how you can use the images in your application can be found in the Place Photos
     * documentation. A photo object is described as:
     * photo_reference — a string used to identify the photo when you perform a Photo request.
     * height — the maximum height of the image.
     * width — the maximum width of the image.
     * html_attributions[] — contains any required attributions. This field will always be present, but may be empty.
     */
    protected $photos = array();

    /**
     * @var string
     *
     * A textual identifier that uniquely identifies a place. To retrieve information about the place, pass this
     * identifier in the placeId field of a Places API request. For more information about place IDs,
     * see the place ID overview.
     */
    protected $placeId = null;

    /**
     * @var string
     *
     * Contains the place's rating, from 1.0 to 5.0, based on aggregated user reviews.
     */
    protected $rating = null;

    /**
     * @var string
     *
     * The scope of an alternative place ID will always be APP, indicating that the alternative place ID is recognised
     * by your application only.
     */
    protected $scope = null;

    /**
     * @var array
     *
     * Contains an array of feature types describing the given result. See the list of supported types for more
     * information. XML responses include multiple <type> elements if more than one type is assigned to the result.
     *
     * example array(0 => 'restaurant', 1 => 'food', 2 => 'point_of_interest', 3 => 'establishment')
     */
    protected $types = null;

    /**
     * @var string
     *
     * Contains a feature name of a nearby location. Often this feature refers to a street or neighborhood within the
     * given results. The vicinity property is only returned for a Nearby Search.
     */
    protected $vicinity = null;

    /**
     * @var GooglePlacesViaAddress
     *
     * Stores the GooglePlacesViaAddress object that was the source of this query
     */
    protected $googlePlacesViaAddress;

    /**
     * NearBySearchResult constructor.
     * @param array $resultData
     * @param GooglePlacesViaAddress $googlePlacesViaAddress
     *
     * Populates search result object with data from array data returned from near by search query on google.
     */
    public function __construct(array $resultData, GooglePlacesViaAddress $googlePlacesViaAddress)
    {
        $this->googlePlacesViaAddress = $googlePlacesViaAddress;

        if (isset($resultData['geometry']['location']) && is_array($resultData['geometry']['location']) && 2 == count(
                $resultData['geometry']['location']
            )
        ) {
            $this->longitude = (float)$resultData['geometry']['location']['lng'];
            $this->latitude = (float)$resultData['geometry']['location']['lat'];
        }

        if (isset($resultData['icon'])) {
            $this->icon = $resultData['icon'];
        }

        if (isset($resultData['name'])) {
            $this->name = $resultData['name'];
        }

        if (isset($resultData['opening_hours'])) {
            $this->openingHours = $resultData['opening_hours'];
        }

        if (isset($resultData['photos']) && is_array($resultData['photos'])) {
            $this->photos = $resultData['photos'];
        }

        if (isset($resultData['place_id'])) {
            $this->placeId = $resultData['place_id'];
        }

        if (isset($resultData['rating'])) {
            $this->rating = $resultData['rating'];
        }

        if (isset($resultData['scope'])) {
            $this->scope = $resultData['scope'];
        }

        if (isset($resultData['types'])) {
            $this->types = $resultData['types'];
        }

        if (isset($resultData['vicinity'])) {
            $this->vicinity = $resultData['vicinity'];
        }

        $fileName = realpath(
                $googlePlacesViaAddress->getRootDir().'/'.$googlePlacesViaAddress->getStreetViewPath()
            ).'/'.
            $this->placeId.'.jpg';

        $fs = new Filesystem();

        if (!$fs->exists($fileName)) {
            if (isset($this->photos[0])) {
                $placePhoto = new GooglePlacePhoto(
                    $googlePlacesViaAddress->getGoogleAPIKey(),
                    $fileName,
                    $this->photos[0]
                );
                $placePhoto->getPlacePhoto(250, 200);
            } else {
                $streetView = new GoogleStreetView($googlePlacesViaAddress->getGoogleAPIKey(), $fileName);
                $streetView->setLocation($this->longitude, $this->latitude);
                $streetView->getStreetView();
            }
        }
    }

    public function getGeometry()
    {
        return $this->longitude." ".$this->latitude;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVicinity()
    {
        return $this->vicinity;
    }

    public function getRating()
    {
        if ($this->rating === null) {
            return 'Unrated';
        }

        return $this->rating;
    }

    public function getPlaceId()
    {
        return $this->placeId;
    }

    public function getImagePathAndName()
    {
        return '/'.basename($this->googlePlacesViaAddress->getStreetViewPath()).'/'.$this->placeId.'.jpg';
    }

    /**
     * @param int $earthRadius
     * @return int
     *
     * Vincenty Great Circle Distance.
     *
     * Used for getting the distance between two longitude and latitude points in metres.
     */
    public function getDistance($earthRadius = 6371000)
    {
        list($srcLatitude, $srcLongitude) = $this->googlePlacesViaAddress->getGooglePlaces()->location;

        // convert from degrees to radians
        $latFrom = deg2rad($srcLatitude);
        $lonFrom = deg2rad($srcLongitude);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);

        return (int)round($angle * $earthRadius);
    }
}