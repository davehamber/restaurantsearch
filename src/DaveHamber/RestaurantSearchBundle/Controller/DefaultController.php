<?php

namespace DaveHamber\RestaurantSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RestaurantSearchBundle:Default:index.html.twig');
    }
}
