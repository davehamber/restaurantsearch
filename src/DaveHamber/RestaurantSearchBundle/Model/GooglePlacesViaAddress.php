<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

use joshtronic\GooglePlaces;
use \GoogleGeocode;

class GooglePlacesViaAddress {
    
    private $googleAPIKey;

    private $logger;
    
    /**
     * Constructor.
     *
     */
    public function __construct($googleAPIKey, $logger)
    {
        $this->googleAPIKey = $googleAPIKey;
        $this->logger = $logger;
    }
    
    public function givePlacesData($searchAddress)
    {
        $searchData = null;
        
        $geo = new GoogleGeocode();
        $geometryData = $geo->geocode($searchAddress);
        
        if ($geometryData['Response']['Status'] == 'OK')
        {
        
            $latitude = $geometryData['Geometry']['Latitude'];
            $longitude = $geometryData['Geometry']['Longitude'];
        
            $googlePlaces = new GooglePlaces($this->googleAPIKey);
        
            $googlePlaces->query = "restaurant";
            $googlePlaces->location = array($latitude, $longitude);
        
            //$googlePlaces->keyword = "Some address";
            $googlePlaces->types = "restaurant";
            $googlePlaces->radius = 500;
        
            $searchData = $googlePlaces->textSearch();
            //searchData("html_attributions", "next_page_token", "results", "status");
            //searchData["results"](0 - 19)
            // The id and reference fields are deprecated as of June 24, 2014. They are replaced by the new place ID
            // Place Search request: 
            //searchData["results"][n]("formatted_address", "geometry", "icon", "id", "name", "opening_hours", "place_id", "price_level", "reference", "types" )
            // can perform a Place Details request
            
            //$this->logger->info("PLACES DATA: " . implode(", ", array_keys($searchData["results"][0])));
            $searchData = nl2br(var_export($searchData, true));
            $searchData = str_replace(' ','&nbsp;', $searchData);

        }

        return $searchData;
    }
}