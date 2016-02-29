<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class GoogleStreetView
 * @package DaveHamber\RestaurantSearchBundle\Model
 *
 * Downloads a google street view image from google using either a location or panorama id.
 */
class GoogleStreetView
{
    /**
     * @var string
     */
    const BASE_URL = 'https://maps.googleapis.com/maps/api/streetview';

    /**
     * @var string
     *
     * The full path and file name to download the image to
     */
    protected $pathAndFileName;

    /**
     * @var string
     *
     * location can be either a text string (such as Chagrin Falls, OH) or a lat/lng value (40.457375,-80.009353).
     * The Google Street View Image API will snap to the panorama photographed closest to this location.
     * Because Street View imagery is periodically refreshed, and photographs may be taken from slightly different
     * positions each time, it's possible that your location may snap to a different panorama when imagery is updated.
     */
    protected $location = null;

    /**
     * @var string
     *
     * pano is a specific panorama ID. These are generally stable.
     */
    protected $pano = null;

    /**
     * @var string
     *
     * size specifies the output size of the image in pixels. Size is specified as {width}x{height}
     * - for example, size=600x400 returns an image 600 pixels wide, and 400 high.
     */
    protected $size = "250x200";

    /**
     * @var string
     *
     * key allows you to monitor your application's API usage in the Google Developers Console; enables per-key
     * instead of per-IP-address quota limits; and ensures that Google can contact you about your application
     * if necessary. For more information, see Get a Key and Signature.
     *
     * Note: Google Maps APIs Premium Plan customers may use either an API key and digital signature, or a valid
     * client ID and digital signature, in your Street View requests. Get more information on authentication
     * parameters for Premium Plan customers.
     */
    protected $key;

    /**
     * @var string
     *
     * signature (recommended) is a digital signature used to verify that any site generating requests using your
     * API key is authorized to do so. For more information, see Get a Key and Signature.
     */
    protected $signature = null;

    /**
     * @var float
     * heading indicates the compass heading of the camera. Accepted values are from 0 to 360
     * (both values indicating North, with 90 indicating East, and 180 South). If no heading is specified, a value
     * will be calculated that directs the camera towards the specified location, from the point at which the closest
     * photograph was taken.
     */
    protected $heading = null;

    /**
     * @var int
     *
     * fov (default is 90) determines the horizontal field of view of the image. The field of view is expressed in
     * degrees, with a maximum allowed value of 120. When dealing with a fixed-size viewport, as with a Street View
     * image of a set size, field of view in essence represents zoom, with smaller numbers indicating a higher level
     * of zoom. (Left: fov=120; Right: fov=20)
     */
    protected $fov = null;

    /**
     * @var float
     *
     * pitch (default is 0) specifies the up or down angle of the camera relative to the Street View vehicle.
     * This is often, but not always, flat horizontal. Positive values angle the camera up
     * (with 90 degrees indicating straight up); negative values angle the camera down
     * (with -90 indicating straight down).
     */
    protected $pitch = null;

    /**
     * GoogleStreetView constructor.
     * @param $key string
     * @param $pathAndFileName string
     */
    public function __construct($key, $pathAndFileName)
    {
        $this->key = $key;
        $this->pathAndFileName = $pathAndFileName;
    }

    /**
     * @param $longitude float|string
     * @param $latitude float|string
     */
    public function setLocation($longitude, $latitude)
    {
        $this->location = (string)($longitude.','.$latitude);
    }

    /**
     * @param $pano
     */
    public function setPano($pano)
    {
        $this->pano = $pano;
    }

    /**
     * @param $width int
     * @param $height int
     */
    public function setSize($width, $height)
    {
        $this->size = (string)($width.'x'.$height);
    }

    /**
     * @param $heading float
     */
    public function setHeading($heading)
    {
        if ($heading < 0.0) {
            $heading = 0.0;
        } elseif ($heading >= 360.0) {
            $heading = 359.0;
        }

        $this->heading = (float)$heading;
    }

    /**
     * @param $fov int
     *
     * Field of view
     */
    public function setFov($fov)
    {
        if ($fov > 120) {
            $fov = 120;
        } elseif ($fov < 20) {
            $fov = 20;
        }

        $this->fov = (int)$fov;
    }

    /**
     * @param $signature string
     */
    public function setSignature($signature)
    {
        $this->signature = (string)$signature;
    }

    /**
     * @param $pitch float
     */
    public function setPitch($pitch)
    {
        if ($pitch > 90.0) {
            $pitch = 90.0;
        } elseif ($pitch < -90.0) {
            $pitch = -90.0;
        }

        $this->pitch = (float)$pitch;
    }

    /**
     * @return bool
     * @throws \Exception
     *
     * Builds the url query string and then downloads from the url request.
     */
    public function getStreetView()
    {
        $queryString = 'size='.$this->size;

        if ($this->location !== null) {
            $queryString .= '&location='.$this->location;
        } elseif ($this->pano !== null) {
            $queryString .= '&pano='.$this->pano;
        } else {
            //error
            throw new \Exception("Parameters not set: location or pano missing");
        }

        if ($this->heading !== null) {
            $queryString .= '&heading='.$this->heading;
        }

        if ($this->pitch !== null) {
            $queryString .= '&pitch='.$this->pitch;
        }

        if ($this->fov !== null) {
            $queryString .= '&fov='.$this->fov;
        }

        if ($this->signature !== null) {
            $queryString .= '&signature='.$this->signature;
        }

        $queryString .= '&key='.$this->key;

        $this->downloadImage(self::BASE_URL.'?'.$queryString);

        $fs = new Filesystem();

        if ($fs->exists($this->pathAndFileName)) {
            return true;
        }

        return false;
    }

    /**
     * @param $imageUrl
     *
     * curl call to download street view image from google.
     */
    protected function downloadImage($imageUrl)
    {
        $fp = fopen($this->pathAndFileName, 'w+');

        $ch = curl_init($imageUrl);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        curl_exec($ch);

        curl_close($ch);
        fclose($fp);
    }
}
