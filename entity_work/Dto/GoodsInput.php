<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Categories;
use App\Entity\GoodDocs;
use App\Entity\Images;
use App\Entity\Manufacturers;
use App\Entity\OurProperties;
use App\Entity\Properties;
use App\Entity\PropertiesLink;
use App\Entity\Unit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

class GoodsInput
{
    /**
     * @var string|null
     */
    #[ApiProperty(
        openapiContext: [
            'example' => 'Труба гофрированная ПНД безгалогенная (HF) черная с/з d63 мм (15м/360м уп/пал) Строитель'
        ],
        description: 'Наименование'
    )]
    #[Assert\NotBlank(groups: ['postGoodsValidation','putGoodsValidation'])]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $name;

    /**
     * @var string
     */
    #[ApiProperty(
        openapiContext: [
            'example' => 'PR.026351'
        ],
        description: 'Артикул'
    )]
    #[Assert\NotBlank(groups: ['postGoodsValidation','putGoodsValidation'])]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $vendorcode = '';

    /**
     * @var string|null
     */
    #[ApiProperty(
        openapiContext: [
            'example' => 'Гофрированная труба из полиэтилена низкого давления отлично подходит для защиты изолированных проводов и кабелей от механических повреждений и агрессивного воздействия окружающей среды при прокладке сложных систем и трасс. Материал изготовления является безгалогенным, что позволяет использовать изделие в общественных местах, с высоким скоплением людей. Благодаря высокой коррозионной и химической стойкости, трубы из ПНД очень долговечны - срок эксплуатации более 50 лет.'
        ]
    )]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $description;

    /**
     * @var string
     */
    #[ApiProperty(
        openapiContext: [
            'example' => '5.55'
        ]
    )]
    #[Assert\NotBlank(groups: ['postGoodsValidation','putGoodsValidation'])]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $price = '0.00';

    /**
     * @var string
     */
    #[ApiProperty(
        openapiContext: [
            'example' => '5.55'
        ]
    )]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $mrp = '0.00';


    /**
     * @var Manufacturers|null
     */
    #[ORM\ManyToOne(inversedBy: 'goods')]
    #[Groups(groups: ['goods:write','goods:put'])]
    #[ApiProperty(
        description: 'Бренд (производитель)'
    )]
    public ?Manufacturers $manufacture = null;


    /**
     * @var string
     */

    #[ApiProperty(
        openapiContext: [
            'example' => '2eb9432f-3fd9-11e9-b61e-ac1f6b02456'
        ],
        description: 'Уникальный глобальный идентефикатор товара (из 1С)'
    )]
    #[Assert\NotBlank(groups: ['postGoodsValidation'])]
    #[Groups(groups: ['goods:write'])]
    #[SerializedName("guid")]
    public $guid1c = '';

    /**
     * @var string|null
     */
    #[ApiProperty(
        description: 'Ссылка на сайт',
        openapiContext: [
            'example' => 'https://www.promrukav.ru/catalog/truba-gofrirovannaya-iz-pnd/truba-gofrirovannaya-pnd-bezgalogennaya-hf-chernaya-s-z-d63-mm-15m-360m-up-pal-stroitel/'
        ]
    )]
    #[Groups(groups: ['goods:write', 'goods:put'])]
    public $url;

    /**
     * @var string|null
     */
    #[ApiProperty(
        description: 'Ссылка на видео',
        openapiContext: [
            'example' => 'https://www.youtube.com/embed/6Jvny-Gx1Gs'
        ]
    )]
    #[Groups(groups: ['goods:write', 'goods:put'])]
    public $video = '';

    /**
     * @var string|null
     */
    #[ApiProperty(
        openapiContext: [
            'example' => 'EC001175'
        ]
    )]
    #[Groups(groups: ['goods:write', 'goods:put'])]
    public $class = '';

    /**
     * @var string|null
     */
    #[ApiProperty(
        description: 'Штрихкод',
        openapiContext: [
            'example' => '4610016240152'
        ]
    )]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $barcode;

    /**
     * @var int
     */
    #[ApiProperty(
        description: 'Период поступления на склад (дней)',
        openapiContext: [
            'example' => 3
        ]
    )]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $termreceipts;

    /**
     * @var int
     */
    #[ApiProperty(
        description: 'Минимальная партия'
    )]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $minimallot = '1';

    /**
     * @var Categories
     */
    #[ApiProperty(
        openapiContext: [
            'example' => '2eb9432f-3fd9-11e9-b61e-ac1f6b022a71'
        ]
    )]
    #[Groups(groups: ['goods:write','goods:put'])]
    public $category;

    #[Groups(groups: ['goods:write','goods:put'])]
    #[ApiProperty(
        description: 'Коллекция изображений'
    )]
    public $image = array();

    #[ORM\ManyToOne(inversedBy: 'good')]
    #[Groups(groups: ['goods:write','goods:put'])]
    #[ApiProperty(
        description: 'Еденица измерения'
    )]
    public ?Unit $unit_link = null;

    #[ORM\OneToMany(targetEntity: GoodDocs::class, mappedBy: 'good')]
    #[Groups(groups: ['goods:read', 'goods:write','goods:put'])]
    #[ApiProperty(
        description: 'Коллекция документов'
    )]
    #[SerializedName("docs")]
    public Collection $goodDocs;

    #[ORM\OneToMany(mappedBy: 'goods', targetEntity: PropertiesLink::class)]
    #[Groups(groups: ['goods:read', 'goods:write','goods:put'])]
    #[ApiProperty(
        description: 'Коллекция свойств'
    )]
    #[SerializedName("properties")]
    public Collection $propertiesLink;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->image = new ArrayCollection();
        $this->goodDocs = new ArrayCollection();
        $this->propertiesLink = new ArrayCollection();
    }

    public function getCategory(): ?Categories
    {
        return $this->category;
    }

    public function setCategory(?Categories $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImage(): Collection
    {
        return $this->image;
    }

    public function addImage(Images $image): self
    {
        if (!$this->image->contains($image)) {
            $this->image->add($image);
            //$image->addGood($this);
        }

        return $this;
    }

    public function getUnitLink(): ?Unit
    {
        return $this->unit_link;
    }

    public function setUnitLink(?Unit $unit_link): self
    {
        $this->unit_link = $unit_link;

        return $this;
    }

    public function getManufacture(): ?Manufacturers
    {
        return $this->manufacture;
    }

    public function setManufacture(?Manufacturers $manufacture): self
    {
        $this->manufacture = $manufacture;

        return $this;
    }

    /**
     * @return Collection<int, GoodDocs>
     */
    public function getGoodDocs(): Collection
    {
        return $this->goodDocs;
    }

    public function addGoodDoc(GoodDocs $goodDoc): self
    {
        if (!$this->goodDocs->contains($goodDoc)) {
            $this->goodDocs->add($goodDoc);
           // $goodDoc->setGood($this);
        }

        return $this;
    }

    public function removeGoodDoc(GoodDocs $goodDoc): self
    {
        if ($this->goodDocs->removeElement($goodDoc)) {
            // set the owning side to null (unless already changed)
            if ($goodDoc->getGood() === $this) {
                $goodDoc->setGood(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PropertiesLink>
     */
    public function getPropertiesLink(): Collection
    {
        return $this->propertiesLink;
    }

    public function addPropertiesLink(PropertiesLink $properties): self
    {
        if (!$this->propertiesLink->contains($properties)) {
            $this->propertiesLink->add($properties);
        }

        return $this;
    }

    public function removePropertiesLink(PropertiesLink $properties): self
    {
        $this->propertiesLink->removeElement($properties);

        return $this;
    }
}