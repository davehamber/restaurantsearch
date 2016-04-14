<?php

namespace DaveHamber\RestaurantSearchBundle\Entity;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use \DaveHamber\RestaurantSearchBundle\Entity\GoogleLocation;

/**
 * @ORM\Entity
 * @ORM\Table(name="query_strings")
 */
class QueryString
{
    /**
     * @ORM\Column(type="integer",name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="query_string", unique=true)
     */
    protected $queryString;

    /**
     * @ORM\ManyToOne(targetEntity="GoogleLocation")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    protected $googleLocation;

    public function __construct($queryString)
    {
        $this->queryString = $queryString;
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
     * Set queryString
     *
     * @param string $queryString
     *
     * @return QueryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;

        return $this;
    }

    /**
     * Get queryString
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Set googleLocation
     *
     * @param \DaveHamber\RestaurantSearchBundle\Entity\GoogleLocation $googleLocation
     *
     * @return QueryString
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
}
