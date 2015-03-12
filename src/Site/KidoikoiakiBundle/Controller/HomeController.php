<?php namespace Site\KidoikoiakiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Site\KidoikoiakiBundle\Entity\Evenement;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', 'text', array('label'  => 'Titre', 'attr' => array('class' => 'form-control')))
            ->add('valider', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
			
		$form->handleRequest($request);

		if ($form->isValid())
		{
			$manager = $this->getDoctrine()->getManager();
			
			$new_token = $this->random_string();
		
			$event = new Evenement;
			$event->setTitre($form->get('name')->getData());
			$event->setToken($new_token);
			
			$manager->persist($event);
			$manager->flush();
			
			return $this->redirect($this->generateUrl('site_kidoikoiaki_event', array('token' => $new_token)));
		}	

        return $this->render('SiteKidoikoiakiBundle:Home:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
	
	public function random_string($length = 40) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
