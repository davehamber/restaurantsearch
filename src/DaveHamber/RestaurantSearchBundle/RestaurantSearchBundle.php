<?php

namespace DaveHamber\RestaurantSearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RestaurantSearchBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
