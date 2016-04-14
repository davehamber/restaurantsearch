<?php

namespace DaveHamber\RestaurantSearchBundle\Controller;

use DaveHamber\RestaurantSearchBundle\Model\NearBySearchResults;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DaveHamber\RestaurantSearchBundle\Form\Type\RestaurantSearchType;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{
    protected $activeText = ' class="active"';
    protected $active = array('', '');

    public function indexAction(Request $request)
    {
        $this->active[0] = $this->activeText;
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
                array('form' => $form->createView(), 'results' => $searchResults, 'active' => $this->active)
            );
        } else {
            return $this->render('RestaurantSearchBundle:Default:index.html.twig', array('active' => $this->active));

        }
    }

    public function aboutAction()
    {
        $this->active[1] = $this->activeText;
        return $this->render('RestaurantSearchBundle:About:index.html.twig', array('active' => $this->active));
    }

}
