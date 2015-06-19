<?php namespace Site\KidoikoiakiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Site\KidoikoiakiBundle\Entity\Evenement;
use Site\KidoikoiakiBundle\Entity\Personne;
use Site\KidoikoiakiBundle\Entity\Categorie;
use Site\KidoikoiakiBundle\Entity\Achat;
use Site\KidoikoiakiBundle\Entity\Beneficiaire;
use \DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
			
			return $this->redirect($this->generateUrl('site_kidoikoiaki_participants', array('token' => $new_token)));
		}	

        return $this->render('SiteKidoikoiakiBundle:Home:index.html.twig', array(
		'kidurl' => $this->generateUrl('site_kidoikoiaki_homepage'),
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
	
	public function participantsAction($token, Request $request)
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
		
		// Récupère le dossier des dépenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		
		foreach($persons as & $person)
		{
			$is_in_depenses = $repository_depenses->findBy(array('acheteur' => $person->getPrenom()));
			$is_beneficiaire = $repository_beneficiaire->findBy(array('personne' => $person->getId()));
			
			$can_be_deleted = TRUE;
			if($is_in_depenses != '' || $is_beneficiaire != '')
			{
				$can_be_deleted = FALSE;
			}
			
			$person->can_be_deleted = $can_be_deleted;
		}
		
		// Récupère le dossier des dépenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		$spending = $repository_depenses->findBy(array('evenement' => $event->getId()));
		
		// Créer le formulaire
		$form = $this->createFormBuilder()
            ->add('name', 'text', array('label'  => 'Nom', 'attr' => array('class' => 'form-control')))
			->add('firstname', 'text', array('label'  => 'Prenom', 'attr' => array('class' => 'form-control')))
			->add('email', 'email', array('label'  => 'Email', 'attr' => array('class' => 'form-control')))
			->add('part', 'integer', array('label'  => 'Nombre de parts', 'attr' => array('class' => 'form-control'), 'data' => '1',))
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
					'eventurl' => $this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken()))
				)));
			$this->get('mailer')->send($message);

			return $this->redirect($this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())));
		}	

		$view = array(
			'persons' => $persons,
			'token' => $token,
			'kidurl' => $this->generateUrl('site_kidoikoiaki_homepage'),
			'participantsurl' => $this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())),
            'event' => $event,
			'form' => $form->createView(),
        );
		if($persons != '')
		{
			$view['depensesurl'] = $this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken()));
			$view['categoriesurl'] = $this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken()));
		}
		if($spending != '')
		{
			$view['bilansurl'] = $this->generateUrl('site_kidoikoiaki_assessment', array('token' => $event->getToken()));
		}
		return $this->render('SiteKidoikoiakiBundle:Home:participants.html.twig', $view);
    }
	
	public function suppressionParticipantAction($token, $participant_id)
    {
        // Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des dépenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		
		// Récupère le dossier des evenements
        $repository_evenement = $manager->getRepository("SiteKidoikoiakiBundle:Evenement");
		// Puis l'événement précise
		$event = $repository_evenement->findOneBy(array('token' => $token));
		
		// Récupère le dossier des personnes
        $repository_personne = $manager->getRepository("SiteKidoikoiakiBundle:Personne");
		// Puis la personne précise
		$persons = $repository_personne->findBy(array('id' => $participant_id, 'evenement' => $event->getId()));
		
		// Si la personne existe
		if ($persons != '') {
			if(Count($persons) == 1)
			{
				foreach($persons as $person)
				{
					$is_in_depenses = $repository_depenses->findBy(array('acheteur' => $person->getId()));
					$is_beneficiaire = $repository_beneficiaire->findBy(array('personne' => $person->getId()));
					$can_be_deleted = TRUE;
					if($is_in_depenses != '' || $is_beneficiaire != '')
					{
						$can_be_deleted = FALSE;
					}
					
					if($can_be_deleted)
					{
						// La supprimer
						$manager->remove($person);
						$manager->flush();
					}
				}
			}
		}

		return $this->redirect($this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())));
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
		
		// Récupère le dossier des personnes
        $repository_personne = $manager->getRepository("SiteKidoikoiakiBundle:Personne");
		$persons = $repository_personne->findBy(array('evenement' => $event->getId()));
		
		// S'il n'y a pas de personnes à l'événement, on ne peut pas accéder aux dépenses
		if ($persons == '') {
			return $this->redirect($this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())));
		}

		// Récupère le dossier des dépenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		$spending = $repository_depenses->findBy(array('evenement' => $event->getId()));
		
		// Récupère le dossier des catégories
        $repository_category = $manager->getRepository("SiteKidoikoiakiBundle:Categorie");
		$categories = $repository_category->findBy(array('evenement' => array(0, $event->getId())));
		
		// Variable du total des dépenses
		$total_price = 0;
		
		// Créer un tableau de personne : id=>prenom
		$persons_table = array();
		foreach($persons as $person)
		{
			$persons_table[$person->getId()] = $person->getPrenom();
		}
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		// Ajoute les bénéficiaires aux dépenses
		foreach($spending as & $s)
		{
			$b = '';
			$beneficiaries = $repository_beneficiaire->findBy(array('achat' => $s->getId()));
			for($i = 0; $i < Count($beneficiaries); $i++)
			{
				if($i > 0)
				{
					$b = $b . ', ';
				}
				$b = $b . $beneficiaries[$i]->getPersonne()->getPrenom();
			}
			$s->beneficiaires = $b;
			
			// Modifie le total des dÃ©penses
			$total_price = $total_price + $s->getPrix();
		}
		
		// Quand on post le formulaire
		if ($request->isMethod('post'))
		{
			// Si un des champs obligatoire est vide
			if($request->get('objet') == '' ||  $request->get('prix') == '' ||  $request->get('beneficiaire') == '')
			{
				$this->get('session')->getFlashBag()->add('message-error', 'Aucun champs ne peut Ãªtre vide');
				return $this->redirect($this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())));
			}
			
			$dateTime = new DateTime('NOW');
			
			// CrÃ©er la dÃ©pense avec les informations rÃ©cupÃ©rÃ©s
			$spending = new Achat;
			$spending->setObjet($request->get('objet'));
			$spending->setAcheteur($repository_personne->findOneBy(array('id' => $request->get('acheteur'))));
			$spending->setPrix($request->get('prix'));
			$spending->setCategorie($repository_category->findOneBy(array('id' => $request->get('categorie'))));
			$spending->setDate($dateTime);
			$spending->setEvenement($event);
			
			// Enregistre la depense
			$manager->persist($spending);
			$manager->flush();
			
			// Récupère les beneficiaires et les ajoutes
			$array_beneficiary = $request->get('beneficiaire');
			
			if(Count($array_beneficiary) > 0)
			{		
				foreach($array_beneficiary as $key => $value)
				{
					$beneficiary = new Beneficiaire;
					$beneficiary->setPart($request->get('partbeneficiaire')[$key]);
					$beneficiary->setPersonne($repository_personne->findOneBy(array('id' => $value)));
					$beneficiary->setAchat($spending);
					$manager->persist($beneficiary);
					$manager->flush();
				}
			}

			return $this->redirect($this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())));
		}

		$view = array(
			'spending' => $spending,
			'token' => $token,
			'kidurl' => $this->generateUrl('site_kidoikoiaki_homepage'),
			'participantsurl' => $this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())),
			'depensesurl' => $this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())),
            'categoriesurl' => $this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken())),
			'event' => $event,
			'persons_table' => $persons_table,
			'categories' => $categories,
			'total_price' => $total_price,
			'persons' => $persons,
        );
		if($spending != '')
		{
			$view['bilansurl'] = $this->generateUrl('site_kidoikoiaki_assessment', array('token' => $event->getToken()));
		}
		return $this->render('SiteKidoikoiakiBundle:Home:spending.html.twig', $view);
    }
	
	public function suppressionDepenseAction($token, $spending_id)
    {
        // Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des evenements
        $repository_evenement = $manager->getRepository("SiteKidoikoiakiBundle:Evenement");
		// Puis l'événement précise
		$event = $repository_evenement->findOneBy(array('token' => $token));
		
		// Récupère le dossier des depenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		// Puis la depense précise
		$depense = $repository_depenses->findOneBy(array('id' => $spending_id, 'evenement' => $event->getId()));
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		
		// Si la depense existe
		if ($depense != '') {
			$beneficiaries = $repository_beneficiaire->findBy(array('achat' => $depense->getId()));
			foreach($beneficiaries as $beneficiary)
			{
				// Supprimer les beneficiaires
				$manager->remove($beneficiary);
				$manager->flush();
			}
	
			// La supprimer
			$manager->remove($depense);
			$manager->flush();
		}

		return $this->redirect($this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())));
    }
	
	public function recuperationDepenseAction()
    {
		$request = $this->getRequest();
        // Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des depenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		// Puis la depense précise
		$depense = $repository_depenses->findOneBy(array('id' => $request->request->get('spending_id')));
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		// Puis les bénéficiaires précis
		$beneficiaires = $repository_beneficiaire->findBy(array('achat' => $depense));
		// Pour créer un tableau : id=>part
		$beneficiaire_in_the_spending = array();
		foreach($beneficiaires as $beneficiaire)
		{
			$beneficiaire_in_the_spending[$beneficiaire->getPersonne()->getId()] = $beneficiaire->getPart();
		}
		
		if($depense != '')
		{
			$return = array(
				'success' => TRUE,
				'id' => $depense->getId(),
				'objet' => $depense->getObjet(),
				'prix' => $depense->getPrix(),
				'acheteur' => $depense->getAcheteur()->getId(),
				'beneficiaires' => $beneficiaire_in_the_spending
			);
		}
		else
		{
			$return = array(
				'success' => FALSE
			);
		}
		$response = new Response(json_encode($return));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
    }
	
	public function sauvegarderDepenseAction()
    {	
		$request = $this->getRequest();
		parse_str($request->request->get('form'), $output);
		
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des personnes
        $repository_personne = $manager->getRepository("SiteKidoikoiakiBundle:Personne");
		
		// Récupère le dossier des depenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		// Puis la depense précise
		$spending = $repository_depenses->findOneBy(array('id' => $output['update-id']));
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		
		$dateTime = new DateTime('NOW');
		
		// Modifie avec les informations récupérés
		$spending->setObjet($output['update-objet']);
		$spending->setAcheteur($repository_personne->findOneBy(array('id' => $output['update-acheteur'])));
		$spending->setPrix($output['update-prix']);
		
		// Enregistre
		$manager->flush();
		
		// Supprime les bénéficiaires
		$manager->createQuery('DELETE FROM
				SiteKidoikoiakiBundle:Beneficiaire Beneficiaire
				WHERE Beneficiaire.achat = :achat')
			->setParameters([ 'achat' => $spending ])
			->execute();
		
		// Et les remplacent
		foreach($output['update-beneficiaire'] as $update)
		{		
			// Récupère le bénéficiaire précis
			$beneficiary = new Beneficiaire;
			$beneficiary->setPart($output['update-partbeneficiaire'][$update]);
			$beneficiary->setPersonne($repository_personne->findOneBy(array('id' => $update)));
			$beneficiary->setAchat($spending);
			$manager->persist($beneficiary);
			$manager->flush();
		}
		
		$response = new Response(json_encode($spending));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function categoriesAction($token, Request $request)
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
		
		// Récupère le dossier des catégories
        $repository_category = $manager->getRepository("SiteKidoikoiakiBundle:Categorie");
		$default_categories = $repository_category->findBy(array('evenement' => array(0)));
		$categories = $repository_category->findBy(array('evenement' => array($event->getId())));
		
		// Variable du total des categories
		$total_categ = 0;
		
		// Ajoute les statistiques des categories
		foreach($spending as & $s)
		{	
			foreach($default_categories as & $category)
			{
				if($s->getCategorie()->getId() == $category->getId())
				{		
					if(isset($category->part))
					{
						$category->part = $category->part + 1;
					}
					else
					{
						$category->part = 1;
					}
					$total_categ = $total_categ + 1;
					
					if(isset($category->prix))
					{
						$category->prix = $category->prix + $s->getPrix();
					}
					else
					{
						$category->prix = $s->getPrix();
					}
				}
			}
			
			foreach($categories as & $category)
			{
				if($s->getCategorie()->getId() == $category->getId())
				{		
					if(isset($category->part))
					{
						$category->part = $category->part + 1;
					}
					else
					{
						$category->part = 1;
					}
					$total_categ = $total_categ + 1;
					
					if(isset($category->prix))
					{
						$category->prix = $category->prix + $s->getPrix();
					}
					else
					{
						$category->prix = $s->getPrix();
					}
				}
			}
		}
		
		// Quand on post le formulaire
		if ($request->isMethod('post'))
		{
			// Si un des champs obligatoire est vide
			if($request->get('nom') == '')
			{
				$this->get('session')->getFlashBag()->add('message-error', 'Le champ ne peut Ãªtre vide');
				return $this->redirect($this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken())));
			}
			
			// Créer la catégorie avec les informations récupérés
			$category = new Categorie;
			$category->setNom($request->get('nom'));
			$category->setEvenement($event->getId());
			
			// Enregistre la depense
			$manager->persist($category);
			$manager->flush();

			return $this->redirect($this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken())));
		}
		
		$view = array(
			'token' => $token,
			'kidurl' => $this->generateUrl('site_kidoikoiaki_homepage'),
			'participantsurl' => $this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())),
			'depensesurl' => $this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())),
            'categoriesurl' => $this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken())),
			'event' => $event,
			'default_categories' => $default_categories,
			'categories' => $categories,
			'total_categ' => $total_categ,
        );
		if($spending != '')
		{
			$view['bilansurl'] = $this->generateUrl('site_kidoikoiaki_assessment', array('token' => $event->getToken()));
		}
		return $this->render('SiteKidoikoiakiBundle:Home:categories.html.twig', $view);
	}
	
	public function suppressionCategoryAction($token, $category_id)
	{
		// Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des evenements
        $repository_evenement = $manager->getRepository("SiteKidoikoiakiBundle:Evenement");
		// Puis l'événement précise
		$event = $repository_evenement->findOneBy(array('token' => $token));
		
		// Récupère le dossier des catégories
        $repository_categories = $manager->getRepository("SiteKidoikoiakiBundle:Categorie");
		// Puis la catégorie précise
		$category = $repository_categories->findOneBy(array('id' => $category_id, 'evenement' => $event->getId()));
		
		// Si la catégorie existe
		if ($category != '') {	
			// La supprimer
			$manager->remove($category);
			$manager->flush();
		}

		return $this->redirect($this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken())));
	}
	
	public function recuperationCategoryAction()
    {
		$request = $this->getRequest();
        // Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des catégories
        $repository_category = $manager->getRepository("SiteKidoikoiakiBundle:Categorie");
		// Puis la catégorie précise
		$category = $repository_category->findOneBy(array('id' => $request->request->get('category_id')));
		
		if($category != '')
		{
			$return = array(
				'success' => TRUE,
				'id' => $category->getId(),
				'nom' => $category->getNom(),
			);
		}
		else
		{
			$return = array(
				'success' => FALSE
			);
		}
		$response = new Response(json_encode($return));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
    }
	
	public function sauvegarderCategoryAction()
    {	
		$request = $this->getRequest();
		parse_str($request->request->get('form'), $output);
		
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des depenses
        $repository_categories = $manager->getRepository("SiteKidoikoiakiBundle:Categorie");
		// Puis la depense précise
		$category = $repository_categories->findOneBy(array('id' => $output['update-id']));
		
		// Modifie avec les informations récupérés
		$category->setNom($output['update-nom']);
		
		// Enregistre
		$manager->flush();
		
		$response = new Response(json_encode($category));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function bilanAction($token, Request $request)
    {
		// Récupère le manager de doctrine
		$manager = $this->getDoctrine()->getManager();
		
		// Récupère le dossier des evenements
        $repository_evenement = $manager->getRepository("SiteKidoikoiakiBundle:Evenement");
		// Puis l'événement précise
		$event = $repository_evenement->findOneBy(array('token' => $token));
		
		// Récupère le dossier des personnes
        $repository_personne = $manager->getRepository("SiteKidoikoiakiBundle:Personne");
		$persons = $repository_personne->findBy(array('evenement' => $event->getId()));
		
		// Récupère le dossier des dépenses
        $repository_depenses = $manager->getRepository("SiteKidoikoiakiBundle:Achat");
		$spendings = $repository_depenses->findBy(array('evenement' => $event->getId()));
		
		// Récupère le dossier des beneficiaires
        $repository_beneficiaire = $manager->getRepository("SiteKidoikoiakiBundle:Beneficiaire");
		
		$comptes = array();
		foreach($persons as $person)
		{
			$comptes[$person->getId()] = 0;
		}
		
		foreach($spendings as $spending)
		{
			$comptes[$spending->getAcheteur()->getId()] += $spending->getPrix();
			
			$beneficiaires = $repository_beneficiaire->findBy(array('achat' => $spending));
			
			$total_part = 0;
			foreach($beneficiaires as $beneficiaire)
			{
				$total_part = $total_part + $beneficiaire->getPart();
			}
			
			foreach($beneficiaires as $beneficiaire)
			{
				$comptes[$beneficiaire->getPersonne()->getId()] -= ( $spending->getPrix() / $total_part ) * $beneficiaire->getPart();
			}
		}
		
		$comptes_finaux = array();
		$comptes_moins = array();
		$comptes_plus = array();
		foreach($comptes as $key => $value)
		{
			$person = $repository_personne->findOneBy(array('id' => $key));
			$comptes_finaux[$person->getPrenom()] = $value;
			
			if($value >= 0)
			{
				$comptes_plus[$person->getPrenom()] = $value;
			}
			else
			{
				$comptes_moins[$person->getPrenom()] = $value;
			}
		}
		
		arsort($comptes_plus);
		asort($comptes_moins);
		$transactions = array();
		foreach($comptes_plus as $keycp => & $valuecp)
		{
			foreach($comptes_moins as $keycm => & $valuecm)
			{
				if($valuecm == 0)
				{
					break;
				}
				
				if (abs($valuecp) > abs($valuecm))
				{
					$valuecp = $valuecp - abs($valuecm);

					$value = number_format(abs($valuecm), 2, ',', '');
					
					if($value <= 1)
					{
						array_push($transactions, $keycm . ' doit ' . $value . ' euro a ' . $keycp);
					}
					else
					{
						array_push($transactions, $keycm . ' doit ' . $value . ' euros a ' . $keycp);
					}
					
					$valuecm = $valuecm + abs($valuecm);
				}
				else
				{
					$valuecm = $valuecm + abs($valuecp);
					
					$value = number_format(abs($valuecp), 2, ',', '');
					
					if($value <= 1)
					{
						array_push($transactions, $keycm . ' doit ' . $value . ' euro a ' . $keycp);
					}
					else
					{
						array_push($transactions, $keycm . ' doit ' . $value . ' euros a ' . $keycp);
					}
					
					$valuecp = $valuecp - abs($valuecp);
				}
				
				arsort($comptes_plus);
				asort($comptes_moins);
				
				if($valuecp == 0)
				{
					break;
				}
			}
		}
		
		$view = array(
			'comptes_finaux' => $comptes_finaux,
			'transactions' => $transactions,
			'token' => $token,
			'kidurl' => $this->generateUrl('site_kidoikoiaki_homepage'),
			'participantsurl' => $this->generateUrl('site_kidoikoiaki_participants', array('token' => $event->getToken())),
			'depensesurl' => $this->generateUrl('site_kidoikoiaki_spending', array('token' => $event->getToken())),
			'categoriesurl' => $this->generateUrl('site_kidoikoiaki_categories', array('token' => $event->getToken())),
            'bilansurl' => $this->generateUrl('site_kidoikoiaki_assessment', array('token' => $event->getToken())),
			'event' => $event
        );
		return $this->render('SiteKidoikoiakiBundle:Home:assessment.html.twig', $view);
	}
}