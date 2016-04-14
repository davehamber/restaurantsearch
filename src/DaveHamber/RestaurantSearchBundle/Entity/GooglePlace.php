<?php
/**
 * Created by PhpStorm.
 * User: Dave
 * Date: 15/03/2016
 * Time: 17:00
 */

namespace DaveHamber\RestaurantSearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use DaveHamber\RestaurantSearchBundle\Model\GoogleStreetView;
use DaveHamber\RestaurantSearchBundle\Model\GooglePlacePhoto;

/**
 * @ORM\Entity
 * @ORM\Table(name="google_places")
 */
class GooglePlace
{
    /**
     * @ORM\Column(type="string", name="id", length=255)
     * @ORM\Id
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="GoogleLocation")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     *
     * @var GoogleLocation
     */
    protected $googleLocation = null;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, name="name")
     *
     * Contains the human-readable name for the returned result. For establishment results,
     * this is usually the business name.
     */
    protected $name = null;

    /**
     * @ORM\Column(type="string", length=255, name="address")
     * @var string
     *
     * Contains a feature name of a nearby location. Often this feature refers to a street or neighborhood within the
     * given results. The vicinity property is only returned for a Nearby Search.
     */
    protected $vicinity = null;

    /**
     * @var string
     * @ORM\Column(type="float", name="rating")
     *
     * Contains the place's rating, from 1.0 to 5.0, based on aggregated user reviews.
     */
    protected $rating = null;

    /**
     * @ORM\ManyToMany(targetEntity="GoogleLocation", mappedBy="googlePlaces")
     * @ORM\JoinTable(name="place_results",
     *      joinColumns={@ORM\JoinColumn(name="place_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")}
     * )
     *
     * @var \Doctrine\Common\Collections\Collection|GoogleLocation[]
     */
    protected $googleLocations;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true, name="photo_id")
     *
     * Contains the first photo id of any photo ids returned from the place search result.
     */
    protected $photoId = null;

    protected static $streetViewPath;
    protected static $rootPath;
    protected static $apiKey;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return GooglePlace
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return GooglePlace
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set vicinity
     *
     * @param string $vicinity
     *
     * @return GooglePlace
     */
    public function setVicinity($vicinity)
    {
        $this->vicinity = $vicinity;

        return $this;
    }

    /**
     * Get vicinity
     *
     * @return string
     */
    public function getVicinity()
    {
        return $this->vicinity;
    }

    /**
     * Set rating
     *
     * @param float $rating
     *
     * @return GooglePlace
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRatingText()
    {
        if (0 == $this->rating) {
            return 'Unrated';
        }

        return (string)$this->rating;
    }
    /**
     * Set googleLocation
     *
     * @param \DaveHamber\RestaurantSearchBundle\Entity\GoogleLocation $googleLocation
     *
     * @return GooglePlace
     */
    public function setGoogleLocation(GoogleLocation $googleLocation = null)
    {
        $this->googleLocation = $googleLocation;

        return $this;
    }

    /**
     * Get googleLocation
     *
     * @return \DaveHamber\RestaurantSearchBundle\Entity\GoogleLocation
     */
    public function getGoogleLocation()
    {
        return $this->googleLocation;
    }

    /**
     * @param GoogleLocation $googleLocation
     */
    public function addGoogleLocation(GoogleLocation $googleLocation)
    {
        if ($this->googleLocations->contains($googleLocation)) {
            return;
        }

        //$this->googleLocations->add($googleLocation);
        $this->googleLocations[$googleLocation->getId()] = $googleLocation;
        $googleLocation->addGooglePlace($this);
    }

    /**
     * @param GoogleLocation $googleLocation
     */
    public function removeGoogleLocation(GoogleLocation $googleLocation)
    {
        if (!$this->googleLocations->contains($googleLocation)) {
            return;
        }
        $this->googleLocations->removeElement($googleLocation);
        $googleLocation->removeGooglePlace($this);
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->googleLocations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get googleLocations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGoogleLocations()
    {
        return $this->googleLocations;
    }

    public static function setPathsAndApiKey($rootPath, $streetViewPath, $apiKey)
    {
        static::$streetViewPath = $streetViewPath;
        static::$rootPath = $rootPath;
        static::$apiKey = $apiKey;
    }

    public function getStreetViewPath()
    {
        $this->fetchImages();
        return '/'.basename(static::$streetViewPath).'/'.$this->id.'.jpg';
    }

    public function getAbsoluteStreetViewPath()
    {
        return realpath(static::$rootPath.'/'.static::$streetViewPath).'/'.$this->id.'.jpg';

    }

    public function setPhotoId($photoId)
    {
        $this->photoId = $photoId;
    }

    public function getPhotoId()
    {
        return $this->photoId;
    }

    public function fetchImages()
    {
        $fileName = $this->getAbsoluteStreetViewPath();

        $fs = new Filesystem();

        if (!$fs->exists($fileName)) {
            if (null != $this->photoId) {
                $placePhoto = new GooglePlacePhoto(
                    static::$apiKey,
                    $fileName,
                    $this->photoId
                );
                $placePhoto->getPlacePhoto(250, 200);
            } else {
                $streetView = new GoogleStreetView(static::$apiKey, $fileName);
                $streetView->setLocation($this->googleLocation->getLongitude(), $this->googleLocation->getLatitude());
                $streetView->getStreetView();
            }
        }
    }
}
