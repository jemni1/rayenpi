<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Event name is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Event name cannot be longer than {{ limit }} characters."
    )]
    private ?string $eventName = null;


    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Event description is required.")]
    #[Assert\Length(
        min: 10,
        minMessage: "Event description must be at least {{ limit }} characters long."
    )]
    private ?string $eventDescription = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "Start date is required.")]
    #[Assert\Type("\DateTimeInterface", message: "Start date must be a valid date.")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "End date is required.")]
    #[Assert\Type("\DateTimeInterface", message: "End date must be a valid date.")]
    #[Assert\Expression(
        "this.getEndDate() >= this.getStartDate()",
        message: "End date must be after or equal to the start date."
    )]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Location is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Location cannot be longer than {{ limit }} characters."
    )]
    private ?string $location = null;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $latitude = null;
    
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $longitude = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Category is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Category cannot be longer than {{ limit }} characters."
    )]
    private ?string $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): static
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getEventDescription(): ?string
    {
        return $this->eventDescription;
    }

    public function setEventDescription(string $eventDescription): static
    {
        $this->eventDescription = $eventDescription;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

public function getLatitude(): ?string
{
    return $this->latitude;
}

public function setLatitude(?string $latitude): self
{
    $this->latitude = $latitude;
    return $this;
}

public function getLongitude(): ?string
{
    return $this->longitude;
}

public function setLongitude(?string $longitude): self
{
    $this->longitude = $longitude;
    return $this;
}
    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }
}
