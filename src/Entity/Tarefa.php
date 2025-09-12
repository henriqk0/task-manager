<?php

namespace App\Entity;

use App\Repository\TarefaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: TarefaRepository::class)]
#[UniqueEntity(fields: ["nomeDaTarefa"])]
class Tarefa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    private ?string $nomeDaTarefa = null;

    #[ORM\Column]
    private ?float $custo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dataLimite = null;

    #[ORM\Column]
    private ?int $ordemDaApresentacao = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomeDaTarefa(): ?string
    {
        return $this->nomeDaTarefa;
    }

    public function setNomeDaTarefa(string $nomeDaTarefa): static
    {
        $this->nomeDaTarefa = $nomeDaTarefa;

        return $this;
    }

    public function getCusto(): ?float
    {
        return $this->custo;
    }

    public function setCusto(float $custo): static
    {
        $this->custo = $custo;

        return $this;
    }

    public function getDataLimite(): ?\DateTime
    {
        return $this->dataLimite;
    }

    public function setDataLimite(\DateTime $dataLimite): static
    {
        $this->dataLimite = $dataLimite;

        return $this;
    }

    public function getOrdemDaApresentacao(): ?int
    {
        return $this->ordemDaApresentacao;
    }

    public function setOrdemDaApresentacao(int $ordemDaApresentacao): static
    {
        $this->ordemDaApresentacao = $ordemDaApresentacao;

        return $this;
    }
}
