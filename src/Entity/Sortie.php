<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Type(type:'string',
        message:'Le nom doit être une chaine de caractères.')]
    #[Assert\NotBlank([],
        message: 'Merci de renseigner un nom de sortie.')]
    #[Assert\Length(min: 3, max: 30,
        minMessage: 'Le nom doit faire entre 3 et 30 caractères.',
        maxMessage: 'Le nom doit faire entre 3 et 30 caractères.')]
    #[Assert\Regex(
        pattern: '/^[^@%$?#=]+$/i',
        message: ('Le nom ne doit pas contenir de caractères spéciaux.'),
        match: true)]
    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[Assert\Type(type:\DateTimeInterface::class,
        message:'La date de début de sortie doit être une date-heure.')]
    #[Assert\GreaterThan('today',
        message: 'La sortie ne peut débuter avant demain.')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeureDeb = null;

    #[Assert\Type(type: 'integer',
        message: 'la durée {{ value }} n\'est pas de type {{ type }}.')]
    #[Assert\GreaterThanOrEqual(0 ,
        message: 'La durée ne peut pas être négative.')]
    #[ORM\Column(nullable: true)]
    private ?int $duree = null;

    #[Assert\Type(type:\DateTimeInterface::class,
        message:'La date de clôture des inscriptions doit être une date-heure.')]
    #[Assert\LessThan([],
        propertyPath: "dateHeureDeb",
        message: 'La clôture des inscriptions doit avoir lieu avant le début de la sortie.')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCloture = null;

    #[Assert\Type(type: 'integer',
        message: 'Le nombre d\'inscriptions maximum {{ value }} n\'est pas de type {{ type }}.')]
    #[Assert\GreaterThanOrEqual(0 , message: 'Le nombre d\'inscriptions ne peut pas être négatif.')]
    #[ORM\Column]
    private ?int $nbInscriptionsMax = null;

    #[Assert\Type(type:'string',
        message: 'La description doit être une chaîne de caractères.')]
    #[Assert\Regex(
        pattern: '/^[^@%$?#=]+$/i',
        message: ('La description ne doit pas contenir de caractères spéciaux.'),
        match: true)]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infosSortie = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisateur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'sortiesParticipant')]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDeb(): ?\DateTimeInterface
    {
        return $this->dateHeureDeb;
    }

    public function setDateHeureDeb(\DateTimeInterface $dateHeureDeb): self
    {
        $this->dateHeureDeb = $dateHeureDeb;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateCloture(): ?\DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(\DateTimeInterface $dateCloture): self
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }
}
