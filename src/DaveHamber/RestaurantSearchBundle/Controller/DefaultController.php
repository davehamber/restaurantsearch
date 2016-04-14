<?php

namespace DaveHamber\RestaurantSearchBundle\Controller;

use DaveHamber\RestaurantSearchBundle\Model\NearBySearchResults;
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
            $googlePlacesViaAddress = $this->get('google_places_via_address');
            if ($request->isMethod('POST')) {
                $form->bind($request);

                if ($form->isValid()) {

                    $searchAddress = $form->get('address')->getData();
                    $searchResults = $googlePlacesViaAddress->getPlacesData($searchAddress);

                } else {
                    $searchResults = array();
                }
            } else {
                $searchResults = array();
            }

            return $this->render(
                'RestaurantSearchBundle:Default:index.html.twig',
                array('form' => $form->createView(), 'results' => $searchResults)
            );
        } else {
            return $this->render('RestaurantSearchBundle:Default:index.html.twig');

        }
    }
}
