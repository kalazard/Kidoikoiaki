<?php

namespace OC\KidoikoiakiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function indexAction()
    {
        $content = $this->get('templating')->render('OCKidoikoiakiBundle:Home:index.html.twig', array('nom' => 'envy'));
		return new Response($content);
    }
}