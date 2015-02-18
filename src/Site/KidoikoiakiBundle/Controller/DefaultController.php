<?php

namespace Site\KidoikoiakiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SiteKidoikoiakiBundle:Default:index.html.twig', array('name' => $name));
    }
}
