<?php
/**
 * Created by PhpStorm.
 * User: Dave
 * Date: 17/02/2016
 * Time: 17:14
 */

namespace DaveHamber\RestaurantSearchBundle\Model;


class DetailedResult extends SearchResult
{
    /**
     * @var array
     */
    protected $htmlAttributions = array();

    /**
     * @var array
     * 	address_components
     *       "long_name" : "48",
     *       "short_name" : "48",
     *       "types" : [ "street_number" ],
     *
     *       "long_name" : "Pirrama Road",
     *       "short_name" : "Pirrama Road",
     *       "types" : [ "route" ]
     *
     *       "long_name" : "Pyrmont",
     *       "short_name" : "Pyrmont",
     *       "types" : [ "locality", "political" ]
     *
     *       "long_name" : "NSW",
     *       "short_name" : "NSW",
     *       "types" : [ "administrative_area_level_1", "political" ]
     *
     *       "long_name" : "AU",
     *       "short_name" : "AU",
     *       "types" : [ "country", "political" ]
     *
     *       "long_name" : "2009",
     *       "short_name" : "2009",
     *       "types" : [ "postal_code" ]
     */
    protected $addressComponents = array();

    /*
     * There is an adr_address column but I can not see it in the Google Places API documentation. Deprecated?
     */


    /**
     * @var string
     * formatted_address : "48 Pirrama Road, Pyrmont NSW, Australia",
     */
    protected $formattedAddress = null;

    /**
     * @var string
     * formatted_phone_number : "(02) 9374 4000",
     */
    protected $formattedPhoneNumber = null;

    /**
     * @var array
     * geometry
     *      location
     *          "lat" : -33.8669710,
     *          "lng" : 151.1958750
     */
    protected $geometry = null;

    /**
     * @var string
     * icon : "http://maps.gstatic.com/mapfiles/place_api/icons/generic_business-71.png",
     */
    protected $icon = null;

    /**
     * @var string
     * @deprecated
     * id" : "4f89212bf76dde31f092cfc14d7506555d85b5c7",
     */
    protected $id = null;

    /**
     * @var string
     * international_phone_number : "+61 2 9374 4000",
     */
    protected $internationalPhoneNumber;

    /**
     * @var string
     * name : "Google Sydney",
     */
    protected $name;

    /**
     * @var array
     * 'opening_hours' =>  array (
    'open_now' => false,
    'periods' => array (
    0 => array (
    'close' => array (
    'day' => 1,
    'time' => '0000',
    ),
    'open' => array (
    'day' => 0,
    'time' => '1800',
    ),
    ),
    1 => array (
    'close' => array (
    'day' => 3,
    'time' => '0000',
    ),
    'open' => array (
    'day' => 2,
    'time' => '1800',
    ),
    ),
    2 => array (
    'close' => array (
    'day' => 4,
    'time' => '0000',
    ),
    'open' => array (
    'day' => 3,
    'time' => '1800',
    ),
    ),
    3 => array (
    'close' => array (
    'day' => 5,
    'time' => '0000',
    ),
    'open' => array (
    'day' => 4,
    'time' => '1800',
    ),
    ),
    4 => array (
    'close' => array (
    'day' => 6,
    'time' => '0000',
    ),
    'open' => array (
    'day' => 5,
    'time' => '1800',
    ),
    ),
    5 => array (
    'close' => array (
    'day' => 0,
    'time' => '0000',
    ),
    'open' => array (
    'day' => 6,
    'time' => '1800',
    ),
    ),
    ),
    'weekday_text' => array (
    0 => 'Monday: Closed',
    1 => 'Tuesday: 6:00 PM – 12:00 AM',
    2 => 'Wednesday: 6:00 PM – 12:00 AM',
    3 => 'Thursday: 6:00 PM – 12:00 AM',
    4 => 'Friday: 6:00 PM – 12:00 AM',
    5 => 'Saturday: 6:00 PM – 12:00 AM',
    6 => 'Sunday: 6:00 PM – 12:00 AM',
    ),
    ),
     */
    protected $openingHours;

    /**
     * @var array
     * 'photos' => array (
    0 => array (
    'height' => 768,
    'html_attributions' => array (
    0 => 'L'Origine Du Monde',
    ),
    'photo_reference' => 'CmRdAAAAKmD15yoCj1yPr7YLWJoePmnw5h3uZcAg2w-aKXa9tnxgmC1sMDrP3k6K337w0X8T4utN5MdDNZem1M5uEbkm58td2ohBPDi7sg3VdmhHoi_x3S9UihsV7uWVEz-POTV4EhAuZVktACpqZh5abitTGnlgGhSL5Og2GSU2SiqGJpjgmNr-uTFPHQ',
    'width' => 1024,
    ),
     *
     */
    protected $photos;

    /**
     * @var string
     * place_id : "ChIJN1t_tDeuEmsRUsoyG83frY4",
     */
    protected $placeId;

    /**
     * @var float
     * rating : 4.70
     */
    protected $rating;

    /**
     * @var string
     * reference: "CnRsAAAA98C4wD-VFvzGq-KHVEFhlHuy1TD1W6UYZw7KjuvfVsKMRZkbCVBVDxXFOOCM108n9PuJMJxeAxix3WB6B16c1p2bY1ZQyOrcu1d9247xQhUmPgYjN37JMo5QBsWipTsnoIZA9yAzA-0pnxFM6yAcDhIQbU0z05f3xD3m9NQnhEDjvBoUw-BdcocVpXzKFcnMXUpf-nkyF1w",
     * @deprecated
     */
    protected $reference;

    /**
     * @var array
     * 	reviews
     *      aspects
     *      rating
     *      type : "quality"
     *      author_name "Simon Bengtsson",
     *      author_url : "https://plus.google.com/104675092887960962573",
     *      language : "en",
     *      rating : 5,
     *      text : "Just went inside to have a look at Google. Amazing.",
     *      time : 1338440552869
     */
    protected $reviews;

    /**
     * @var string
     * scope" : "GOOGLE",
     */
    protected $scope;

    /**
     * @var array
     * alt_ids
     *      place_id : "D9iJyWEHuEmuEmsRm9hTkapTCrk",
     *      scope : "APP"
     */
    protected $altIds;

    /**
     * @var array
     * types : [ "establishment" ]
     */
    protected $types;

    /**
     * @var string
     * url : "http://maps.google.com/maps/place?cid=10281119596374313554",
     */
    protected $url;

    /**
     * @var int
     */
    protected $userRatingsTotal;

    /**
     * @var int
     * 'utc_offset' => 60,
     */
    protected $utcOffset;
    /**
     * @var string
     * vicinity : "48 Pirrama Road, Pyrmont",
     */
    protected $vicinity;

    /**
     * @var string
     * website : "http://www.google.com.au/"
     */
    protected $website;

    /**
     * @var string
     * status "OK"
     */
    protected $status;

    public function __construct(array $resultData)
    {
        if (isset($resultData['status'])) {
            $this->status = $resultData['status'];
            if (StatusCode::OK != $resultData['status']) {
                return;
            }
        }

        if (isset($resultData['html_attributions'])) {
            $this->htmlAttributions = (array)$resultData['html_attributions'];
        }

        if (isset($resultData['result'])) {
            $resultData = $resultData['result'];
        } else {
            return;
        }

        if (isset($resultData['address_components'])) {
            $this->addressComponents = (array)$resultData['address_components'];
        }

        if (isset($resultData['formatted_address'])) {
            $this->formattedAddress = (string)$resultData['formatted_address'];
        }

        if (isset($resultData['formatted_phone_number'])) {
            $this->formattedPhoneNumber = (string)$resultData['formatted_phone_number'];
        }

        if (isset($resultData['geometry']['location']) && is_array($resultData['geometry']['location']) && 2 == count(
                $resultData['geometry']['location']
            )
        ) {
            $this->geometry = array_values($resultData['geometry']['location']);
        }

        if (isset($resultData['icon'])) {
            $this->icon = (string)$resultData['icon'];
        }

        if (isset($resultData['international_phone_number'])) {
            $this->internationalPhoneNumber = (string)$resultData['international_phone_number'];
        }

        if (isset($resultData['name'])) {
            $this->name = (string)$resultData['name'];
        }

        if (isset($resultData['opening_hours'])) {
            $this->openingHours = (array)$resultData['opening_hours'];
        }

        if (isset($resultData['photos'])) {
            $this->photos = (array)$resultData['photos'];
        }

        if (isset($resultData['place_id'])) {
            $this->placeId = (string)$resultData['place_id'];
        }

        if (isset($resultData['rating'])) {
            $this->rating = (float)$resultData['rating'];
        }

        if (isset($resultData['reviews'])) {
            $this->reviews = (array)$resultData['reviews'];
        }

        if (isset($resultData['scope'])) {
            $this->scope = (string)$resultData['scope'];
        }

        if (isset($resultData['alt_ids'])) {
            $this->altIds = (array)$resultData['alt_ids'];
        }

        if (isset($resultData['types'])) {
            $this->types = (array)$resultData['types'];
        }

        if (isset($resultData['url'])) {
            $this->url = (string)$resultData['url'];
        }

        if (isset($resultData['user_ratings_total'])) {
            $this->userRatingsTotal = (int)$resultData['user_ratings_total'];
        }

        if (isset($resultData['utc_offset'])) {
            $this->utcOffset = (int)$resultData['utc_offset'];
        }

        if (isset($resultData['vicinity'])) {
            $this->vicinity = (string)$resultData['vicinity'];
        }

        if (isset($resultData['website'])) {
            $this->website = (string)$resultData['website'];
        }
    }
}