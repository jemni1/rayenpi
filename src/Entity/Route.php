<?php

namespace App\Entity;

use App\Repository\RouteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RouteRepository::class)]
class Route
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le lieu de départ ne peut pas être vide.')]
    #[Assert\Length(max: 255, maxMessage: 'Le lieu de départ ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $startLocation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le lieu d\'arrivée ne peut pas être vide.')]
    #[Assert\Length(max: 255, maxMessage: 'Le lieu d\'arrivée ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $endLocation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: 'Les points de passage ne peuvent pas dépasser {{ limit }} caractères.')]
    private ?string $waypoints = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La durée estimée ne peut pas être vide.')]
    #[Assert\GreaterThan(value: 0, message: 'La durée estimée doit être supérieure à zéro.')]
    private ?int $estimationDuration = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le type de véhicule ne peut pas être vide.')]
    #[Assert\Length(max: 255, maxMessage: 'Le type de véhicule ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $vehiculeType = null;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'route')]
    private Collection $relation;

    public function __construct()
    {
        $this->relation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartLocation(): ?string
    {
        return $this->startLocation;
    }

    public function setStartLocation(string $startLocation): static
    {
        $this->startLocation = $startLocation;

        return $this;
    }

    public function getEndLocation(): ?string
    {
        return $this->endLocation;
    }

    public function setEndLocation(string $endLocation): static
    {
        $this->endLocation = $endLocation;

        return $this;
    }

    public function getWaypoints(): ?string
    {
        return $this->waypoints;
    }

    public function setWaypoints(?string $waypoints): static
    {
        $this->waypoints = $waypoints;

        return $this;
    }

    public function getEstimationDuration(): ?int
    {
        return $this->estimationDuration;
    }

    public function setEstimationDuration(int $estimationDuration): static
    {
        $this->estimationDuration = $estimationDuration;

        return $this;
    }

    public function getVehiculeType(): ?string
    {
        return $this->vehiculeType;
    }

    public function setVehiculeType(string $vehiculeType): static
    {
        $this->vehiculeType = $vehiculeType;

        return $this;
    }

    /**
     * @return Collection<int, Events>
     */
    public function getRelation(): Collection
    {
        return $this->relation;
    }

    public function addRelation(Events $relation): static
    {
        if (!$this->relation->contains($relation)) {
            $this->relation->add($relation);
            $relation->setRoute($this);
        }

        return $this;
    }

    public function removeRelation(Events $relation): static
    {
        if ($this->relation->removeElement($relation)) {
            // set the owning side to null (unless already changed)
            if ($relation->getRoute() === $this) {
                $relation->setRoute(null);
            }
        }

        return $this;
    }
}
