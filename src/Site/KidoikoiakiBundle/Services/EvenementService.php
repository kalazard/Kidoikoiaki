<?php namespace Site\KidoikoiakiBundle\Services;

use Site\KidoikoiakiBundle\Entity\Evenement;

class EvenementService
{
	protected $entityManager;
    
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->entityManager = $em;
    }
	
	public function creerevenement($title)
	{
		$new_token = $this->random_string();
	
		$event = new Evenement;
		$event->setTitre($title);
		$event->setToken($new_token);
		
		$this->entityManager->persist($event);
		$this->entityManager->flush();
		
		return json_encode(array("token" => $new_token));
	}
	
	//public function creerevenementwithparticipants($title, $participants)
	//{
	//	$new_token = $this->random_string();
	//
	//	$event = new Evenement;
	//	$event->setTitre($title);
	//	$event->setToken($new_token);
	//	$this->entityManager->persist($event);
	//	
	//	// Créer les participants
	//	foreach($participants as $participant)
	//	{
	//		$person = new Personne;
	//		$person->setNom($participant['nom']);
	//		$person->setPrenom($participant['prenom']);
	//		$person->setEmail($participant['email']);
	//		$person->setPartdefaut(1);
	//		$person->setEvenement($event);
	//		$this->entityManager->persist($person);
	//	}
	//	
	//	$this->entityManager->flush();
	//	
	//	return json_encode(array("token" => $new_token));
	//}
	
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
 
