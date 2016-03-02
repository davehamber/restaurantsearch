<?php

namespace DaveHamber\RestaurantSearchBundle\Model;

/**
 * Class StatusCode
 * @package DaveHamber\RestaurantSearchBundle\Model
 *
 * An enumeration representing the status code responses from google place search queries
 */
class StatusCode
{
    /**
     * indicates that no errors occurred; the place was successfully detected and at least one result was returned.
     */
    const OK = "OK";

    /**
     * indicates that the search was successful but returned no results. This may occur if the search was passed a
     * latlng in a remote location.
     */
    const ZERO_RESULTS = "ZERO_RESULTS";

    /**
     * indicates that you are over your quota.
     */
    const OVER_QUERY_LIMIT = "OVER_QUERY_LIMIT";

    /**
     * indicates that your request was denied, generally because of lack of an invalid key parameter.
     */
    const REQUEST_DENIED = "REQUEST_DENIED";

    /**
     * generally indicates that a required query parameter (location or radius) is missing.
     */
    const INVALID_REQUEST = "INVALID_REQUEST";
}