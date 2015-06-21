<?php namespace Site\KidoikoiakiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Site\KidoikoiakiBundle\Entity\Evenement;
use Symfony\Component\HttpFoundation\Request;

class EvenementController extends Controller
{
	/**
     * Fonction SOAP
     * 
	 **/
    public function indexAction()
    {
        $server = new \SoapServer(null, array('uri' => $this->container->getParameter("base_url") . '/kidoikoiaki/web/app_dev.php/evenement'));
        $server->setObject($this->get('evenement_service'));

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        ob_start();
        $server->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }
}