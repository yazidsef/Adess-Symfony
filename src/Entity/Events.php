<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $salle = null;

    #[ORM\Column]
    private ?bool $paying = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column]
    private ?int $numberofregistrants = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'salle')]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeInterface $date_start): static
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getSalle(): ?Room
    {
        return $this->salle;
    }

    public function setSalle(?Room $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function isPaying(): ?bool
    {
        return $this->paying;
    }

    public function setPaying(bool $paying): static
    {
        $this->paying = $paying;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getNumberofregistrants(): ?int
    {
        return $this->numberofregistrants;
    }

    public function setNumberofregistrants(int $numberofregistrants): static
    {
        $this->numberofregistrants = $numberofregistrants;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setSalle($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getSalle() === $this) {
                $appointment->setSalle(null);
            }
        }

        return $this;
    }
}
