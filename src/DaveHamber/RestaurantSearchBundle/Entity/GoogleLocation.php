<?php
/**
 * Created by PhpStorm.
 * User: Dave
 * Date: 08/03/2016
 * Time: 14:48
 */

namespace DaveHamber\RestaurantSearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="google_locations", uniqueConstraints={@ORM\UniqueConstraint(name="location", columns={"latitude", "longitude"})})
 */
class GoogleLocation
{
    /**
     * @ORM\Column(type="integer",name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="float", name="latitude")
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", name="longitude")
     */
    protected $longitude;

    protected $distance;

    /**
     * @ORM\ManyToMany(targetEntity="GooglePlace", inversedBy="googleLocations")
     * @ORM\JoinTable(name="place_results",
     *      joinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="place_id", referencedColumnName="id")}
     * )
     *
     * @var \Doctrine\Common\Collections\Collection|GooglePlace[]
     */
    protected $googlePlaces;

    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->googlePlaces = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set latitude
     *
     * @param float $latitude
     *
     * @return GoogleLocation
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return GoogleLocation
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param GooglePlace $googlePlace
     */
    public function addGooglePlace(GooglePlace $googlePlace)
    {
        if ($this->googlePlaces->contains($googlePlace)) {
            return;
        }
        //$this->googlePlaces->add($googlePlace);
        $this->googlePlaces[$googlePlace->getId()] = $googlePlace;
        $googlePlace->addGoogleLocation($this);
    }

    /**
     * @param GooglePlace $googlePlace
     */
    public function removeGooglePlace(GooglePlace $googlePlace)
    {
        if (!$this->googlePlaces->contains($googlePlace)) {
            return;
        }
        $this->googlePlaces->removeElement($googlePlace);
        $googlePlace->removeGoogleLocation($this);
    }

    /**
     * Get googlePlaces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGooglePlaces()
    {
        return $this->googlePlaces;
    }

    public function getDistance()
    {
        return $this->distance;
    }

    public function calculateDistance(GoogleLocation $googleLocation)
    {
        $earthRadius = 6371000;

        // convert from degrees to radians
        $latFrom = deg2rad($googleLocation->getLatitude());
        $lonFrom = deg2rad($googleLocation->getLongitude());
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);

        $this->distance = (int)round($angle * $earthRadius);

        return $this->distance;
    }
}
