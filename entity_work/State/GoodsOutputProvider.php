<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\Extension\FilterExtension;
use ApiPlatform\Doctrine\Orm\State\LinksHandlerTrait;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Dto\GoodsOutput;
use App\Entity\Goods;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GoodsOutputProvider implements ProviderInterface
{
    use LinksHandlerTrait;

    private string $resourceClass;
    private QueryBuilder $queryBuilder;
    private array $paginationInfo;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Pagination             $pagination,
        private readonly FilterExtension        $filterExtension,
    )
    {
        $this->resourceClass = Goods::class;
        $this->queryBuilder = $this->entityManager->getRepository($this->resourceClass)->createQueryBuilder('a');
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GoodsOutput
    {
        $goodsOutput        = new GoodsOutput();
        $queryNameGenerator = new QueryNameGenerator();

        $this->handleLinks($this->queryBuilder, $uriVariables, $queryNameGenerator, $context, $this->resourceClass, $operation);
        $this->filterExtension->applyToCollection($this->queryBuilder, $queryNameGenerator, $this->resourceClass, $operation, $context);
        $this->setPaginationInfo($operation, $context);

        $goodsCollection = $this->queryBuilder->getQuery()->getResult();
        $goodsOutput->setInfo(new ArrayCollection($this->paginationInfo));

        foreach ($goodsCollection as $good) {
            if($operation->getShortName() === 'Goods') {
                unset($good->stocks);
            }
            $goodsOutput->addResult($good);
        }

        return $goodsOutput;
    }

    private function setPaginationInfo (Operation $operation, array $context): void
    {
        $request            = Request::createFromGlobals();
        $uriPath            = $request->getUriForPath($request->getPathInfo());
        $currentPage        = $this->pagination->getPage($context) ?: 1;
        $goodsPerPage       = $this->pagination->getLimit($operation, $context) ?: 30;
        $getParametersFirst = $getParametersLast  = $getParametersCurrent = $request->query->all();
        $goodsTotal         = (new Paginator($this->queryBuilder, true))->count();
        if (!$goodsTotal) {
            throw new NotFoundHttpException('Товары не найдены');
        }
        $pagesTotal         = ceil($goodsTotal / $goodsPerPage);
        $getParametersCurrent['page']   = $currentPage;
        $getParametersLast['page']      = $pagesTotal;
        $getParametersFirst['page']     = 1;
        $this->paginationInfo = [
            'Page'          => $currentPage,
            'GoodsPerPage'  => $goodsPerPage,
            'GoodsTotal'    => $goodsTotal,
            'PagesTotal'    => $pagesTotal,
            'CurrentPage'   => $uriPath . '?' . http_build_query($getParametersCurrent),
            'FirstPage'      => $uriPath . '?' . http_build_query($getParametersFirst),
            'LastPage'      => $uriPath . '?' . http_build_query($getParametersLast),
        ];

        $this->queryBuilder
            ->setFirstResult($goodsPerPage * ($currentPage - 1))
            ->setMaxResults($goodsPerPage);
    }
}