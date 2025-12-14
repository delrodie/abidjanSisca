<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['denomination', 'lieu', 'dateDebut'],
    message: 'Cette activité existe déjà.'
)]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $denomination = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectif = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(nullable: true)]
    private ?array $cible = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $responsable = null;

    #[ORM\Column(nullable: true)]
    private ?array $partiePrenante = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $statut = 'brouillon';

    #[ORM\ManyToOne]
    private ?Utilisateur $auteur = null;

    #[ORM\ManyToOne]
    private ?Instance $instance = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $approbateurDistrict = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $approbateurRegion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $approbationDistrictAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $approbationRegionAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $niveauCreation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $archive = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireValidation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motifRejet = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(?string $denomination): static
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(?string $objectif): static
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCible(): ?array
    {
        return $this->cible;
    }

    public function setCible(?array $cible): static
    {
        $this->cible = $cible;

        return $this;
    }

    public function getResponsable(): ?string
    {
        return $this->responsable;
    }

    public function setResponsable(?string $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getPartiePrenante(): ?array
    {
        return $this->partiePrenante;
    }

    public function setPartiePrenante(?array $partiePrenante): static
    {
        $this->partiePrenante = $partiePrenante;

        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getInstance(): ?Instance
    {
        return $this->instance;
    }

    public function setInstance(?Instance $instance): static
    {
        $this->instance = $instance;

        return $this;
    }

    public function getSlug(): ?Uuid
    {
        return $this->slug;
    }

    public function setSlug(?Uuid $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getApprobateurDistrict(): ?Utilisateur
    {
        return $this->approbateurDistrict;
    }

    public function setApprobateurDistrict(?Utilisateur $approbateurDistrict): static
    {
        $this->approbateurDistrict = $approbateurDistrict;

        return $this;
    }

    public function getApprobateurRegion(): ?Utilisateur
    {
        return $this->approbateurRegion;
    }

    public function setApprobateurRegion(?Utilisateur $approbateurRegion): static
    {
        $this->approbateurRegion = $approbateurRegion;

        return $this;
    }

    public function getApprobationDistrictAt(): ?\DateTimeImmutable
    {
        return $this->approbationDistrictAt;
    }

    public function setApprobationDistrictAt(?\DateTimeImmutable $approbationDistrictAt): static
    {
        $this->approbationDistrictAt = $approbationDistrictAt;

        return $this;
    }

    public function getApprobationRegionAt(): ?\DateTimeImmutable
    {
        return $this->approbationRegionAt;
    }

    public function setApprobationRegionAt(?\DateTimeImmutable $approbationRegionAt): static
    {
        $this->approbationRegionAt = $approbationRegionAt;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNiveauCreation(): ?string
    {
        return $this->niveauCreation;
    }

    public function setNiveauCreation(?string $niveauCreation): static
    {
        $this->niveauCreation = $niveauCreation;

        return $this;
    }

    public function isArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(?bool $archive): static
    {
        $this->archive = $archive;

        return $this;
    }

    public function getCommentaireValidation(): ?string
    {
        return $this->commentaireValidation;
    }

    public function setCommentaireValidation(?string $commentaireValidation): static
    {
        $this->commentaireValidation = $commentaireValidation;

        return $this;
    }

    public function getMotifRejet(): ?string
    {
        return $this->motifRejet;
    }

    public function setMotifRejet(?string $motifRejet): static
    {
        $this->motifRejet = $motifRejet;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updateAt = new \DateTimeImmutable();

        if (empty($this->slug)){
            $this->slug = Uuid::v4();
        }
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updateAt = new \DateTimeImmutable();
    }
}
