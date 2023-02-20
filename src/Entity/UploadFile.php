<?php

namespace App\Entity;

use App\Repository\UploadFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadFileRepository::class)]
class UploadFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $fileAddUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileAddUser(): ?string
    {
        return $this->fileAddUser;
    }

    public function setFileAddUser(string $fileAddUser): self
    {
        $this->fileAddUser = $fileAddUser;

        return $this;
    }
}
