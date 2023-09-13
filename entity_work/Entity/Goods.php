<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\GoodsInput;
use App\Dto\GoodsOutput;
use App\Repository\GoodsRepository;
use App\Service\ConvertString;
use App\State\InputGoodsProcessor;
use App\State\GoodsOutputProvider;
use App\State\GoodsStateProvider;
use App\Filter\GoodsFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Table(name: 'goods')]
#[ORM\Index(columns: ['category_id'], name: 'goods_FK_1')]
#[ORM\UniqueConstraint(name: 'guid1c_unique', columns: ['guid1c'])]
#[ORM\Entity(repositoryClass: GoodsRepository::class)]
#[UniqueEntity('guid1c')]
#[ApiResource(
    description: "Данные о товарах и услугах",
    operations: [
        new Get(
            openapiContext: [
                'description' => 'Получить данные одной позиции продукции',
                'summary' => 'Получить данные об одной позиции продукции'
            ],
            security: "is_granted('ROLE_CLIENT_API')",
        ),
        new GetCollection(
            openapiContext: [
                'description' => 'Получить список позиций товаров',
                'summary' => 'Получить список данных о товарах'
            ],
            paginationItemsPerPage: 50,
            paginationClientItemsPerPage: false,
            security: "is_granted('ROLE_CLIENT_API')",
            output: GoodsOutput::class,
            provider: GoodsOutputProvider::class,
        ),
        new Post(
            openapiContext: [
                'description' => 'Добавить новую позицию номенклатуры',
                'summary' => 'Добавить новую позицию номенклатуры'
            ],
            security: "is_granted('ROLE_1C_API')",
            securityMessage: 'Sorry, but you do not have access to this operation.',
            validationContext: ['groups' => ['postGoodsValidation']],
            input: GoodsInput::class,
            processor: InputGoodsProcessor::class,
        ),
        new Put(
            openapiContext: [
                'description' => 'Изменить существующую позицию номенклатуры',
                'summary' => 'Изменить позицию номенклатуры'
            ],
            denormalizationContext: ["groups" => ["goods:put"]],
            security: "is_granted('ROLE_1C_API')",
            securityMessage: 'Sorry, but you do not have access to this operation.',
            validationContext: ['groups' => ['putGoodsValidation']],
            input: GoodsInput::class,
            processor: InputGoodsProcessor::class
        ),
        new Delete(
            openapiContext: [
                'description' => 'Удалить существующую позицию номенклатуры',
                'summary' => 'Удалить позицию номенклатуры'
            ],
            security: "is_granted('ROLE_1C_API')",
            securityMessage: 'Sorry, but you do not have access to this operation.'
        )
    ],
    normalizationContext: ["groups" => ["goods:read", "stock:read"]],
    denormalizationContext: ["groups" => ["goods:write"]],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'price', 'vendorcode'], arguments: ['orderParameterName' => 'sort'])]
#[ApiFilter(GoodsFilter::class, properties: ['properties'])]
class Goods
{
    const API_PATH = 'https://api.promrukav.ru/';
    const IMAGES_PATH = 'images/';
    const DOCS_PATH = 'docs/';
    const CERT_PATH = 'certs/';
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ApiProperty(identifier: false)]
    protected $id;

    /**
     * @var string|null
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    public $name;

    /**
     * @var string
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    #[ORM\Column(name: 'vendorCode', type: 'string', length: 50, nullable: false)]
    public $vendorcode = '';

    /**
     * @var string|null
     */
    #[Groups(groups: ['goods:read', 'goods:write'])]
    #[ORM\Column(name: 'description', type: 'text', length: 65535, nullable: true)]
    public $description;

    /**
     * @var Manufacturers|null
     */
    #[Groups(groups: ['goods:write'])]
    #[ORM\ManyToOne(inversedBy: 'goods')]
    private ?Manufacturers $manufacture = null;

    #[Groups(groups: ['goods:read', 'stock:read'])]
    private ?string $manufacturer = null;

    /**
     * @var string
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write', 'customers_prices_post_response:read'])]
    #[ORM\Column(name: 'guid1c', type: 'string', length: 255, nullable: false)]
    #[ApiProperty(identifier: true)]
    #[SerializedName("guid")]
    public $guid1c;

    /**
     * @var string
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    #[ORM\Column(name: 'price', type: 'decimal', precision: 9, scale: 2, nullable: false, options: ['default' => '0.00'])]
    public $price = '0.00';

    /**
     * @var string
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    #[ORM\Column(name: 'mrp', type: 'decimal', precision: 9, scale: 2, nullable: false, options: ['default' => '0.00'])]
    public $mrp = '0.00';

    /**
     * @var string|null
     */
    #[Groups(groups: ['goods:read', 'goods:write', 'goods:put'])]
    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    public $url;

    #[Groups(groups: ['goods:read', 'goods:write', 'goods:put'])]
    #[ORM\Column(length: 255, nullable: false)]
    public string $video = '';

    /**
     * @var string|null
     */
    #[Groups(groups: ['goods:read', 'goods:write'])]
    #[ORM\Column(name: 'barcode', type: 'string', length: 13, nullable: true)]
    public $barcode;

    /**
     * @var int
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    #[ORM\Column(name: 'termReceipts', type: 'smallint', nullable: false)]
    public $termreceipts;


    /**
     * @var int
     */
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    #[ORM\Column(name: 'minimalLot', type: 'integer', nullable: false, options: ['unsigned' => true])]
    protected $minimallot = '0';

    /**
     * @var Unit|null
     */
    #[Groups(groups: ['goods:read', 'stock:read'])]
    private string $unit = '';

    /**
     * @var Unit|null
     */
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'good')]
    private ?Unit $unit_link = null;

    /**
     * @var \DateTime
     */
    #[Timestampable]
    #[ORM\Column(name: 'updated', type: Types::DATETIME_MUTABLE)]
    #[Groups(groups: ['goods:read'])]
    private $updated;

    #[ORM\OneToMany(mappedBy: 'goods', targetEntity: PropertiesLink::class, cascade: ['all'], orphanRemoval: true)]
    #[Groups(groups: ['goods:write','goods:put'])]
    private Collection $propertiesLink;

    /**
     * @var Categories
     */
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: 'Categories', cascade: ['persist'])]
    #[Groups(groups: ['goods:read', 'stock:read', 'goods:write'])]
    protected $category;

    #[Groups(groups: ['goods:read', 'goods:write', 'goods:put'])]
    #[ORM\Column(length: 255, nullable: false)]
    public string $class = '';

    #[Groups(groups: ['goods:read'])]
    public function getProperties()
    {
        return $this->propertiesLink->map(function($element) {
            $propertyData = [];
            if ($element instanceof PropertiesLink) {
                $propertyData = [
                    'name' => $element->getPropertiesName()->getName(),
                    'description' => $element->getPropertiesName()->getDescription(),
                    'value' => $element->getValue(),
                    'etim' => $element->getPropertiesName()->getEtim() ?: '',
                ];
            }
            return count($propertyData) > 0 ? $propertyData : '';
        });
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[Groups(groups: ['goods:write'])]
    #[ORM\ManyToMany(targetEntity: 'Images', mappedBy: 'good', cascade:['persist'] )]
    protected Collection $image;

    #[Groups(groups: ['goods:read'])]
    private Collection $images;

    /**
     * @var Collection
     */
    #[Groups(groups: ['goods:write'])]
    #[ORM\OneToMany(targetEntity: GoodDocs::class, mappedBy: 'good', orphanRemoval: true)]
    protected Collection $goodDocs;

    #[Groups(groups: ['goods:read'])]
    private array $certs;

    #[Groups(groups: ['goods:read'])]
    private Collection $docs;

    /**
     * @var \DateTime
     */
    #[Timestampable(on: 'create')]
    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE)]
    private $created;

    #[Groups(groups: ['goods:read', 'stock:read'])]
    #[ORM\OneToMany(mappedBy: 'goods', targetEntity: Stock::class, cascade: ['persist', 'remove'])]
    private Collection $stocks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->image = new ArrayCollection();
        $this->goodDocs = new ArrayCollection();
        $this->propertiesLink = new ArrayCollection();
        $this->created = new DateTime();
        $this->stocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVendorcode(): ?string
    {
        return $this->vendorcode;
    }

    public function setVendorcode(string $vendorcode): self
    {
        $this->vendorcode = $vendorcode;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getMrp(): ?string
    {
        return $this->mrp;
    }

    public function setMrp(string $mrp): self
    {
        $this->mrp = $mrp;

        return $this;
    }

    public function getGuid1c(): ?string
    {
        return $this->guid1c;
    }

    public function setGuid1c(string $guid1c): self
    {
        $this->guid1c = $guid1c;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getTermreceipts(): ?int
    {
        return $this->termreceipts;
    }

    public function setTermreceipts(int $termreceipts): self
    {
        $this->termreceipts = $termreceipts;

        return $this;
    }

    public function getMinimallot(): ?int
    {
        return $this->minimallot;
    }

    public function setMinimallot(int $minimallot): self
    {
        $this->minimallot = $minimallot;

        return $this;
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
            $image->addGood($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->image->removeElement($image)) {
            $image->removeGood($this);
        }

        return $this;
    }

    public function getImages(): Collection
    {
        return $this->image->map(function($element) {
            return $element instanceof Images ? $this::API_PATH.$this::IMAGES_PATH.$element->getFname() : null;
        });
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

    /*public function getCustomersPrices(): ?CustomersPrices
    {
        return $this->customersPrices;
    }

    public function setCustomersPrices(CustomersPrices $customersPrices): self
    {
        // set the owning side of the relation if necessary
        if ($customersPrices->getGood() !== $this) {
            $customersPrices->setGood($this);
        }

        $this->customersPrices = $customersPrices;

        return $this;
    }*/

    public function getManufacture(): ?Manufacturers
    {
        return $this->manufacture;
    }

    public function setManufacture(?Manufacturers $manufacture): self
    {
        $this->manufacture = $manufacture;

        return $this;
    }

    #[Groups(groups: ['goods:read'])]
    public function getManufacturer(): ?string
    {
        return $this->manufacture->getName();
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
            $goodDoc->setGood($this);
        }

        return $this;
    }

    public function removeGoodDoc(GoodDocs $goodDoc): self
    {
        // set the owning side to null (unless already changed)
        if ($this->goodDocs->removeElement($goodDoc) && $goodDoc->getGood() === $this) {
            $goodDoc->setGood(null);
        }

        return $this;
    }

    public function getDocs(): Collection
    {
        return $this->goodDocs->filter(function($element) {
            if (($element instanceof GoodDocs) && $element->getDocType()->getName() !== 'Сертификат') {
                return $element;
            }
        })->map(function($element) {
                return [
                    'name' => $element->getDocType()->getName(),
                    'link' => $this::API_PATH.$this::DOCS_PATH.$element->getFilename(),
                ];
        });
    }

    public function getCerts(): array
    {
        return array_values($this->goodDocs->filter(
            function($element) {
                if (($element instanceof GoodDocs) && $element->getDocType()->getName() === 'Сертификат') {
                    return $element;
                }
            }
        )->map(
            function($element) {

                $result = [
                    'name' => $element->getHeader(),
                    'link' => $this::API_PATH.$this::CERT_PATH.$element->getFilename(),
                    //'certs_date' => $element->getCertificateDates()->getBeginning(),
                ];

                if (!is_null($element->getCertificateDates())) {
                    $result['date_from'] = $element->getCertificateDates()->getBeginning() ?? '';
                    $result['date_before'] = $element->getCertificateDates()->getEnding() ?? '';
                }

                return $result;

            }
        )->toArray());
    }

    /**
     * @return Collection<int, PropertiesLink>
     */
    public function getPropertiesLink(): Collection
    {
        return $this->propertiesLink;
    }

    public function addPropertiesLink(PropertiesLink $propertiesLink): self
    {
        if (!$this->propertiesLink->contains($propertiesLink)) {
            $this->propertiesLink->add($propertiesLink);
            $propertiesLink->setGoods($this);
        }

        return $this;
    }

    public function removePropertiesLink(PropertiesLink $propertiesLink): self
    {
        if ($this->propertiesLink->removeElement($propertiesLink)) {
            if ($propertiesLink->getGoods() === $this) {
                $propertiesLink->setGoods(null);
            }
        }

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @return DateTime
     */
    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    /**
     * @return mixed
     */
    public function setUpdated(): self
    {
        $this->updated = new DateTime();
        return $this;
    }

    /**
     * @return Unit|null
     */
    public function getUnit(): string
    {
        return $this->unit_link->getName();
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setGoods($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getGoods() === $this) {
                $stock->setGoods(null);
            }
        }

        return $this;
    }

    public function getVideo(): string
    {
        return $this->video;
    }

    public function setVideo(string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function __unset($name) {
        if ($name === 'stocks') {
            unset($this->stocks);
        }
    }
}
