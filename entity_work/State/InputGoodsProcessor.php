<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\GoodsInput;
use App\Entity\Categories;
use App\Entity\CertificateDates;
use App\Entity\GoodDocs;
use App\Entity\GoodDocsTypes;
use App\Entity\Goods;
use App\Entity\Images;
use App\Entity\Manufacturers;
use App\Entity\OurProperties;
use App\Entity\OurPropertiesType;
use App\Entity\Properties;
use App\Entity\PropertiesLink;
use App\Entity\PropertiesName;
use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InputGoodsProcessor implements ProcessorInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(mixed $input, Operation $operation, array $uriVariables = [], array $context = []): null|Goods
    {
        if ($operation->getMethod() == "POST") {
            $goods = $this->findGood($input->guid1c) ?: new Goods();
            $goods->setGuid1c($input->guid1c);
        } elseif ($operation->getMethod() == "PUT") {
            $input->guid1c = $uriVariables['guid1c'];
            $goods = $this->findGood($input->guid1c) ?: throw new NotFoundHttpException(sprintf('The product with GUID "%s" does not exist.', $input->guid));
        }

        $goods
            ->setName($input->name)
            ->setUrl($input->url)
            ->setVideo($input->video ?: '')
            ->setClass($input->class ?: '')
            ->setVendorcode($input->vendorcode)
            ->setDescription($input->description)
            ->setPrice($input->price)
            ->setMrp($input->mrp ?: '0.00')
            ->setBarcode($input->barcode)
            ->setTermreceipts($input->termreceipts)
            ->setMinimallot($input->minimallot)
            ->setUpdated();

        if ($input->manufacture instanceof Manufacturers) {
            $manufacture = $this->findManufacture($input->manufacture) ?: $input->manufacture;
            $manufacture->addGood($goods);
            $manufacture->setUrl($manufacture->getName());
            $goods->setManufacture($manufacture);
            if (!$this->entityManager->contains($manufacture)) $this->entityManager->persist($manufacture);
        }

        if ($input->category instanceof Categories) {
            $category = $this->findCategory($input->category) ?: $input->category;
            $category->setUrl($category->getHeader());
            $goods->setCategory($category);
            if (!$this->entityManager->contains($category)) $this->entityManager->persist($category);
        }

        $propertiesLink = clone $goods->getPropertiesLink();
        foreach ($input->propertiesLink as $propertiesLinkDTO) {
            if ($propertiesLinkDTO instanceof PropertiesLink) {
                $exists = $propertiesLink->exists(function ($key, $element) use ($propertiesLinkDTO, &$goods, $propertiesLink) {
                    if ($element->getPropertiesName()->getGuid1c() === null) {
                        $goods->removePropertiesLink($element);
                    }

                    if (($element->getPropertiesName()->getGuid1c() === $propertiesLinkDTO->getPropertiesName()->getGuid1c()) &&
                        $element->getValue() === $propertiesLinkDTO->getValue()) {
                        return true;
                    }

                    if ($element->getPropertiesName()->getGuid1c() !== null && $propertiesLinkDTO->getPropertiesName()->getGuid1c() === null) {
                        $goods->removePropertiesLink($element);
                    }

                    return false;
                });
                if ($exists) continue;
                $propertiesLinkDTO->setGoods($goods);
                $propertiesNameDTO = $propertiesLinkDTO->getPropertiesName();
                $propertiesNameDTO = $this->findPropertiesNameDTO($propertiesNameDTO) ? : $propertiesNameDTO;
                $propertiesLinkDTO->setPropertiesName($propertiesNameDTO);
                if (!$this->entityManager->contains($propertiesLinkDTO)) {
                    $this->entityManager->persist($propertiesLinkDTO);
                }
                if (!$this->entityManager->contains($propertiesNameDTO)) {
                    $this->entityManager->persist($propertiesNameDTO);
                }
            }
        }

        /* processing a collection of documents */
        $oldGoodDocs = clone $goods->getGoodDocs();
        foreach ($input->goodDocs as $doc) {

            if ($doc instanceof GoodDocs) {

                $exists = $oldGoodDocs->exists(function ($key, $oldDoc) use ($doc, $oldGoodDocs, $goods) {

                    if (
                        $oldDoc->getFilename() == $doc->getFilename()
                        && $oldDoc->getGood()->getId() == $goods->getId()
                        && $oldDoc->getDocType()->getName() == $doc->getDocType()->getName()
                    ) {
                        $oldCertificateDates = $oldDoc->getCertificateDates();
                        $newCertificateDates = $doc->getCertificateDates();

                        if (!is_null($oldCertificateDates) && !is_null($newCertificateDates)) {

                            if (
                                $oldCertificateDates->getBeginning() == $newCertificateDates->getBeginning()
                                && $oldCertificateDates->getEnding() == $newCertificateDates->getEnding()
                            ) {
                                $oldGoodDocs->removeElement($oldDoc);
                                return true;
                            }

                        }
                    }

                    return false;

                });

                if ($exists) {
                    continue;
                }

                $doc->setGood($goods);

                $goodDocsTypes = $doc->getDocType();
                $goodDocsTypes = $this->findGoodDocsTypes($goodDocsTypes) ?: $goodDocsTypes;
                $doc->setDocType($goodDocsTypes);

                if (!$this->entityManager->contains($doc)) {
                    $this->entityManager->persist($doc);
                }

                if (!$this->entityManager->contains($goodDocsTypes)) {
                    $this->entityManager->persist($goodDocsTypes);
                }

                if (!is_null($doc->getCertificateDates())) {
                    $certificateDates = $doc->getCertificateDates();
                    $certificateDates = $this->findCertificateDates($doc) ?: $certificateDates;
                    $doc->setCertificateDates($certificateDates);

                    if (!$this->entityManager->contains($certificateDates)) {
                        $this->entityManager->persist($certificateDates);
                    }
                }
            }
        }

        /* remove unused docs */
        foreach ($oldGoodDocs as $oldDoc) {
            $goods->removeGoodDoc($oldDoc);
        }


        $posImage = 0;
        foreach ($input->image as $img) {
            if ($img instanceof Images) {
                $picture = $this->findImage($img) ?: $img;
                $picture->addGood($goods);
                $picture->setPosition($picture->getPosition() ?: $posImage);
                $goods->addImage($picture);
                if (!$this->entityManager->contains($picture)) $this->entityManager->persist($picture);
                $posImage++;
            }
        }

        if ($input->unit_link instanceof Unit) {
            $unit = $this->findUnit($input->unit_link) ?? $input->unit_link;
            $unit->addGood($goods);
            //$goods->setUnitLink($unit);
        }

        if (!$this->entityManager->contains($goods)) $this->entityManager->persist($goods);
        $this->entityManager->flush();
        return $goods;
    }

    /**
     * @param Images $pocture
     *
     * @return Images|null
     */
    protected function findManufacture(Manufacturers $manufacture)
    {
        return $this->entityManager->getRepository(Manufacturers::class)->findOneBy(array('name' => $manufacture->getName()));
    }


    /**
     * @param Categories $category
     *
     * @return Categories|null
     */
    protected function findCategory(Categories $category)
    {
        return $this->entityManager->getRepository(Categories::class)->findOneBy(array('guid1c' => $category->getGuid1c()));
    }

    /**
     * @param PropertiesName $etim
     *
     * @return PropertiesName|null
     */
    protected function findPropertiesNameDTO(PropertiesName $propertiesName)
    {
        return $this->entityManager->getRepository(PropertiesName::class)->findOneBy(['guid1c' => $propertiesName->getGuid1c()]);
    }

    /**
     * @param GoodDocsTypes $goodDocsTypes
     *
     * @return GoodDocsTypes|null
     */
    protected function findGoodDocsTypes(GoodDocsTypes $goodDocsTypes)
    {
        return $this->entityManager->getRepository(GoodDocsTypes::class)->findOneBy(['name' => $goodDocsTypes->getName()]);
    }

    protected function findCertificateDates(GoodDocs $goodDoc)
    {
        return $this->entityManager->getRepository(CertificateDates::class)->findOneBy(['doc' => $goodDoc]);
    }

    /**
     * @param Images $img
     *
     * @return Images|null
     */
    protected function findImage(Images $img)
    {
        return $this->entityManager->getRepository(Images::class)->findOneBy(array('fname' => $img->getFname()));

    }

    /**
     * @param string $guid1c
     *
     * @return Goods|null
     */
    protected function findGood(string $guid1c)
    {
        return $this->entityManager->getRepository(Goods::class)->findOneBy(array('guid1c' => $guid1c));
    }

    /**
     * @param Unit $unit
     *
     * @return Unit|null
     */
    protected function findUnit(Unit $unit)
    {
        return $this->entityManager->getRepository(Unit::class)->findOneBy(array('name' => $unit->getName()));
    }


}
