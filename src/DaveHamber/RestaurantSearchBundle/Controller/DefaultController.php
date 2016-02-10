<?php

namespace DaveHamber\RestaurantSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DaveHamber\RestaurantSearchBundle\Form\Type\RestaurantSearchType;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $form = $this->createForm(new RestaurantSearchType());
            $output = "";
            if ($request->isMethod('POST')) {
                $form->bind($request);

                if ($form->isValid()) {

                    $searchAddress = $form->get('address')->getData();

                    $googlePlacesViaAddress = $this->get('google_places_via_address');
                    $output = $googlePlacesViaAddress->givePlacesData($searchAddress);

                }
            }

            return $this->render('RestaurantSearchBundle:Default:index.html.twig', array('form' => $form->createView(), 'output' => $output));


        }
        else {
            return $this->render('RestaurantSearchBundle:Default:index.html.twig');

        }
    }
}
