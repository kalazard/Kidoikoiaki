<?php

namespace Site\KidoikoiakiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Achat
 *
 * @ORM\Table(name="achat", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_achat_evenement1_idx", columns={"evenement"}), @ORM\Index(name="fk_achat_acheteur1_idx", columns={"acheteur"}), @ORM\Index(name="fk_achat_categorie1_idx", columns={"categorie"})})
 * @ORM\Entity
 */
class Achat
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="objet", type="string", length=100, nullable=false)
     */
    private $objet;

    /**
     * @var float
     *
     * @ORM\Column(name="prix", type="float", precision=10, scale=0, nullable=false)
     */
    private $prix;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var \Categorie
     *
     * @ORM\ManyToOne(targetEntity="Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="categorie", referencedColumnName="id")
     * })
     */
    private $categorie;

    /**
     * @var \Personne
     *
     * @ORM\ManyToOne(targetEntity="Personne")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="acheteur", referencedColumnName="id")
     * })
     */
    private $acheteur;

    /**
     * @var \Evenement
     *
     * @ORM\ManyToOne(targetEntity="Evenement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="evenement", referencedColumnName="id")
     * })
     */
    private $evenement;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Personne", mappedBy="achat")
     */
    private $personne;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->personne = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objet
     *
     * @param string $objet
     * @return Achat
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string 
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set prix
     *
     * @param float $prix
     * @return Achat
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return float 
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Achat
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set categorie
     *
     * @param \Site\KidoikoiakiBundle\Entity\Categorie $categorie
     * @return Achat
     */
    public function setCategorie(\Site\KidoikoiakiBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \Site\KidoikoiakiBundle\Entity\Categorie 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set acheteur
     *
     * @param \Site\KidoikoiakiBundle\Entity\Personne $acheteur
     * @return Achat
     */
    public function setAcheteur(\Site\KidoikoiakiBundle\Entity\Personne $acheteur = null)
    {
        $this->acheteur = $acheteur;

        return $this;
    }

    /**
     * Get acheteur
     *
     * @return \Site\KidoikoiakiBundle\Entity\Personne 
     */
    public function getAcheteur()
    {
        return $this->acheteur;
    }

    /**
     * Set evenement
     *
     * @param \Site\KidoikoiakiBundle\Entity\Evenement $evenement
     * @return Achat
     */
    public function setEvenement(\Site\KidoikoiakiBundle\Entity\Evenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement
     *
     * @return \Site\KidoikoiakiBundle\Entity\Evenement 
     */
    public function getEvenement()
    {
        return $this->evenement;
    }

    /**
     * Add personne
     *
     * @param \Site\KidoikoiakiBundle\Entity\Personne $personne
     * @return Achat
     */
    public function addPersonne(\Site\KidoikoiakiBundle\Entity\Personne $personne)
    {
        $this->personne[] = $personne;

        return $this;
    }

    /**
     * Remove personne
     *
     * @param \Site\KidoikoiakiBundle\Entity\Personne $personne
     */
    public function removePersonne(\Site\KidoikoiakiBundle\Entity\Personne $personne)
    {
        $this->personne->removeElement($personne);
    }

    /**
     * Get personne
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPersonne()
    {
        return $this->personne;
    }
}
