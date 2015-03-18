<?php namespace Site\KidoikoiakiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Site\KidoikoiakiBundle\Entity\Evenement;
use Site\KidoikoiakiBundle\Entity\Personne;
use Site\KidoikoiakiBundle\Entity\Achat;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    public function indexAction($token, Request $request)
    {
		// Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des evenements
        $repository_evenement = $manager->getRepository("SiteKidoikoiakiBundle:Evenement");
		$event = $repository_evenement->findOneBy(array('token' => $token));
		
		// Si l'event est null, erreur 404 !
		if ($event == NULL) {
			throw $this->createNotFoundException("L'evenement n'existe pas");
		}

		// Récupère le dossier des personnes
        $repository_personne = $manager->getRepository("SiteKidoikoiakiBundle:Personne");
		$persons = $repository_personne->findBy(array('evenement' => $event->getId()));
		
		// Créer le formulaire
		$form = $this->createFormBuilder()
            ->add('name', 'text', array('label'  => 'Nom', 'attr' => array('class' => 'form-control')))
			->add('firstname', 'text', array('label'  => 'Prenom', 'attr' => array('class' => 'form-control')))
			->add('email', 'email', array('label'  => 'Email', 'attr' => array('class' => 'form-control')))
			->add('part', 'integer', array('label'  => 'Nombre de parts', 'attr' => array('class' => 'form-control')))
            ->add('valider', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
			
		$form->handleRequest($request);

		// Si le formulaire a été posté correctement
		if ($form->isValid())
		{
			// Récupère le manager de doctrine
			$manager = $this->getDoctrine()->getManager();
			
			// Créer le participant avec les informations récupérés
			$person = new Personne;
			$person->setNom($form->get('name')->getData());
			$person->setPrenom($form->get('firstname')->getData());
			$person->setEmail($form->get('email')->getData());
			$person->setPartdefaut($form->get('part')->getData());
			$person->setEvenement($event);
			
			// Enregistre le participant
			$manager->persist($person);
			$manager->flush();
			
			// Envoi un mail
			$message = \Swift_Message::newInstance()
			->setSubject('Ajout a un evenement')
			->setFrom('noreply.kidoikoiaki@gmail.com')
			->setTo($form->get('email')->getData())
			->setBody($this->renderView('SiteKidoikoiakiBundle:Email:nouveauparticipant.html.twig', array(
					'eventtitle' => $event->getTitre(),
					'eventurl' => $this->generateUrl('site_kidoikoiaki_event', array('token' => $event->getToken()))
				)));
			$this->get('mailer')->send($message);

			return $this->redirect($this->generateUrl('site_kidoikoiaki_event', array('token' => $event->getToken())));
		}	

		return $this->render('SiteKidoikoiakiBundle:Event:index.html.twig', array(
			'persons' => $persons,
            'event' => $event,
			'form' => $form->createView(),
        ));
    }
	
	public function depensesAction($token, Request $request)
    {
		// Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des evenements
        $repository_evenement = $manager->getRepository("SiteKidoikoiakiBundle:Evenement");
		$event = $repository_evenement->findOneBy(array('token' => $token));
		
		// Si l'event est null, erreur 404 !
		if ($event == NULL) {
			throw $this->createNotFoundException("L'evenement n'existe pas");
		}

		// Récupère le dossier des dépenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		$spending = $repository_depenses->findBy(array('evenement' => $event->getId()));
		
		// Récupère le dossier des personnes
        $repository_personne = $manager->getRepository("SiteKidoikoiakiBundle:Personne");
		$persons = $repository_personne->findBy(array('evenement' => $event->getId()));
		
		$persons_table = array();
		foreach($persons as $person)
		{
			$persons_table[$person->getId()] = $person->getPrenom();
		}
		
		// Créer le formulaire
		$form = $this->createFormBuilder()
            ->add('objet', 'text', array('label'  => 'Objet', 'attr' => array('class' => 'form-control')))
			->add('prix', 'text', array('label'  => 'Prix', 'attr' => array('class' => 'form-control')))
			->add('acheteur', 'choice', array('label'  => 'Acheteur', 'attr' => array('class' => 'form-control'), 'choices' => $persons_table))
            ->add('valider', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
			
		$form->handleRequest($request);

		// Si le formulaire a été posté correctement
		if ($form->isValid())
		{
			// Récupère le manager de doctrine
			$manager = $this->getDoctrine()->getManager();
			
			// Créer la dépense avec les informations récupérés
			$spending = new Achat;
			$spending->setObjet($form->get('objet')->getData());
			$spending->setAcheteur($repository_personne->findOneBy(array('id' => $form->get('acheteur')->getData()))->getPrenom());
			$spending->setPrix($form->get('prix')->getData());
			$spending->setEvenement($event);
			
			// Enregistre le participant
			$manager->persist($spending);
			$manager->flush();

			return $this->redirect($this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())));
		}	

		return $this->render('SiteKidoikoiakiBundle:Event:spending.html.twig', array(
			'spending' => $spending,
            'event' => $event,
			'form' => $form->createView(),
        ));
    }
}
