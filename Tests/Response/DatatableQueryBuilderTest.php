<?php

/**
 * This file is part of the SgDatatablesBundle package.
 *
 * (c) stwe <https://github.com/stwe/DatatablesBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sg\DatatablesBundle\Tests\Response;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Prophecy\Prophecy\ObjectProphecy;
use Sg\DatatablesBundle\Datatable\Ajax;
use Sg\DatatablesBundle\Datatable\Column\ColumnBuilder;
use Sg\DatatablesBundle\Datatable\DatatableInterface;
use Sg\DatatablesBundle\Datatable\Features;
use Sg\DatatablesBundle\Datatable\Options;
use Sg\DatatablesBundle\Response\DatatableQueryBuilder;

class DatatableQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectProphecy|EntityManagerInterface */
    private $entityManager;

    /** @var ObjectProphecy|ClassMetadataFactory */
    private $classMetadataFactory;

    /** @var ObjectProphecy|Connection */
    private $connection;

    /** @var ObjectProphecy|QueryBuilder */
    private $queryBuilder;

    /** @var ObjectProphecy|ClassMetadata */
    private $classMetadata;

    /** @var ObjectProphecy|\ReflectionClass */
    private $reflectionClass;

    /** @var ObjectProphecy|ColumnBuilder */
    private $columnBuilder;

    /** @var ObjectProphecy|Options */
    private $options;

    /** @var ObjectProphecy|Features */
    private $features;

    /** @var ObjectProphecy|Ajax */
    private $ajax;

    /** @var ObjectProphecy|DatatableInterface */
    private $dataTable;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->classMetadataFactory = $this->prophesize(ClassMetadataFactory::class);
        $this->connection = $this->prophesize(Connection::class);
        $this->queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->classMetadata = $this->prophesize(ClassMetadata::class);
        $this->reflectionClass = $this->prophesize(\ReflectionClass::class);
        $this->columnBuilder = $this->prophesize(ColumnBuilder::class);
        $this->options = $this->prophesize(Options::class);
        $this->features = $this->prophesize(Features::class);
        $this->ajax = $this->prophesize(Ajax::class);
        $this->dataTable = $this->prophesize(DatatableInterface::class);
    }

    public function testUsingAPrefixedAliasWhenShortNameIsAReservedWord()
    {
        $entityName = '\App\Entity\Order';
        $shortName = 'Order';
        $this->queryBuilder->from($entityName, '_order')->willReturn($this->queryBuilder)->shouldBeCalled();

        $this->getDataTableQueryBuilder($entityName, $shortName);
    }

    public function testUsingTheSortNameWhenShortNameIsNotAReservedWord()
    {
        $entityName = '\App\Entity\Account';
        $shortName = 'Account';
        $this->queryBuilder->from($entityName, 'account')->willReturn($this->queryBuilder)->shouldBeCalled();

        $this->getDataTableQueryBuilder($entityName, $shortName);
    }

    /**
     * @param string $entityName
     * @param string $shortName
     * @return DatatableQueryBuilder
     */
    private function getDataTableQueryBuilder($entityName, $shortName)
    {
        $this->reflectionClass->getShortName()->willReturn($shortName);
        $this->classMetadata->getReflectionClass()->willReturn($this->reflectionClass->reveal());
        $this->classMetadata->getIdentifierFieldNames()->willReturn([]);
        $this->classMetadataFactory->getMetadataFor($entityName)->willReturn($this->classMetadata->reveal());
        $this->connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->entityManager->getMetadataFactory()->willReturn($this->classMetadataFactory->reveal());
        $this->entityManager->createQueryBuilder()->willReturn($this->queryBuilder->reveal());
        $this->entityManager->getConnection()->willreturn($this->connection->reveal());
        $this->columnBuilder->getColumns()->willReturn([]);
        $this->dataTable->getEntity()->willReturn($entityName);
        $this->dataTable->getEntityManager()->willReturn($this->entityManager->reveal());
        $this->dataTable->getColumnBuilder()->willReturn($this->columnBuilder->reveal());
        $this->dataTable->getOptions()->willReturn($this->options->reveal());
        $this->dataTable->getFeatures()->willReturn($this->features->reveal());
        $this->dataTable->getAjax()->willReturn($this->ajax->reveal());

        return new DatatableQueryBuilder([], $this->dataTable->reveal());
    }
}