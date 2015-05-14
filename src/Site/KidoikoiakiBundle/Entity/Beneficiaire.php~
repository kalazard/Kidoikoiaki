<?php

namespace Site\KidoikoiakiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Beneficiaire
 *
 * @ORM\Table(name="beneficiaire", indexes={@ORM\Index(name="fk_beneficiaire_2_idx", columns={"achat"}), @ORM\Index(name="IDX_B140D802FCEC9EF", columns={"personne"})})
 * @ORM\Entity
 */
class Beneficiaire
{
    /**
     * @var float
     *
     * @ORM\Column(name="part", type="float", precision=10, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $part;

    /**
     * @var \Personne
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Personne")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="personne", referencedColumnName="id")
     * })
     */
    private $personne;

    /**
     * @var \Achat
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Achat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="achat", referencedColumnName="id")
     * })
     */
    private $achat;



    /**
     * Set part
     *
     * @param float $part
     * @return Beneficiaire
     */
    public function setPart($part)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return float 
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Set personne
     *
     * @param \Site\KidoikoiakiBundle\Entity\Personne $personne
     * @return Beneficiaire
     */
    public function setPersonne(\Site\KidoikoiakiBundle\Entity\Personne $personne)
    {
        $this->personne = $personne;

        return $this;
    }

    /**
     * Get personne
     *
     * @return \Site\KidoikoiakiBundle\Entity\Personne 
     */
    public function getPersonne()
    {
        return $this->personne;
    }

    /**
     * Set achat
     *
     * @param \Site\KidoikoiakiBundle\Entity\Achat $achat
     * @return Beneficiaire
     */
    public function setAchat(\Site\KidoikoiakiBundle\Entity\Achat $achat)
    {
        $this->achat = $achat;

        return $this;
    }

    /**
     * Get achat
     *
     * @return \Site\KidoikoiakiBundle\Entity\Achat 
     */
    public function getAchat()
    {
        return $this->achat;
    }
}
