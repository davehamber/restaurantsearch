<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class GooglePlacePhoto
 * @package DaveHamber\RestaurantSearchBundle\Model
 *
 * Downloads a google place search photo from google using the photo reference provided from place search result data.
 */
class GooglePlacePhoto
{
    const BASE_URL = 'https://maps.googleapis.com/maps/api/place/photo';

    /**
     * @var string
     *
     * The full path and file name to download the image to
     */
    protected $pathAndFileName;

    /**
     * @var string
     *
     * Your application's API key. This key identifies your application for purposes of quota management.
     */
    protected $key;

    /**
     * @var int
     *
     * The height of the image available
     */
    protected $height = 0;

    /**
     * @var array
     * array of strings
     *
     * Contains any required attributions. This field will always be present, but may be empty.
     */
    protected $htmlAttributions = array();

    /**
     * @var string
     *
     * A string used to identify the photo when you perform a Photo request.
     */
    protected $photoReference = "";

    /**
     * @var int
     *
     * The width of the image available
     */
    protected $width = 0;

    /**
     * GooglePlacePhoto constructor.
     * @param $key
     * @param $pathAndFileName
     * @param array $photoData
     *
     * Data is populated from result data from google place search
     */
    public function __construct($key, $pathAndFileName, array $photoData)
    {

        $this->key = $key;

        $this->pathAndFileName = $pathAndFileName;

        if (isset($photoData['height'])) {
            $this->height = (int)$photoData['height'];
        }

        if (isset($photoData['html_attributions'])) {
            $this->htmlAttributions = (array)$photoData['html_attributions'];
        }

        if (isset($photoData['photo_reference'])) {
            $this->photoReference = (string)$photoData['photo_reference'];
        }

        if (isset($photoData['width'])) {
            $this->width = (int)$photoData['width'];
        }
    }

    /**
     * @param int $height
     * @param int $width
     *
     * When the photo object is made by the data returned from the google place search, the photo's dimensions are
     * given. When we want to fetch the photo
     */
    public function setHeightAndWidth($height = 200, $width = 250)
    {
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * @param int $height
     * @param int $width
     * @return bool
     *
     * Builds the url query string and then downloads from the url request. If height and width are specified, the
     * original image size (as stored per photo on google) will be over ridden.
     */
    public function getPlacePhoto($height = 0, $width = 0)
    {
        if (0 == $height || 0 == $width) {
            $height = $this->height;
            $width = $this->width;
        }

        $queryString = "?maxwidth=".$width."&maxheight=".$height;
        $queryString .= "&photoreference=".$this->photoReference;
        $queryString .= "&key=".$this->key;

        $this->downloadPhoto(self::BASE_URL.'?'.$queryString);

        $fs = new Filesystem();

        if ($fs->exists($this->pathAndFileName)) {
            return true;
        }

        return false;
    }

    /**
     * @param $photoUrl
     *
     * curl call to download place photo from google place search.
     */
    protected function downloadPhoto($photoUrl)
    {
        $fp = fopen($this->pathAndFileName, 'w+');

        $ch = curl_init($photoUrl);

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