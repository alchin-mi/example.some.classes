<?php

namespace App\Dto;


use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Goods;

class GoodsOutput
{
    #[ApiProperty(
        description: 'Дополнительная информация',
    )]
    #[Groups(groups: ['goods:read', 'stock:read'])]
    private Collection $info;

    #[ApiProperty(description: 'Результат',)]
    #[Groups(groups: ['goods:read', 'stock:read'])]
    private Collection $result;

    public function __construct()
    {
        $this->result = new ArrayCollection();
    }

    /**
     * @return Collection<Goods>
     */
    public function getResult(): Collection
    {
        return $this->result;
    }

    public function addResult(Goods $result): self
    {
        if (!$this->result->contains($result)) {
            $this->result->add($result);
        }

        return $this;
    }

    public function removeResult(Goods $result): self
    {
        $this->properties->removeElement($result);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getInfo(): Collection
    {
        return $this->info;
    }

    /**
     * @param Collection $info
     */
    public function setInfo(Collection $info): void
    {
        $this->info = $info;
    }
}