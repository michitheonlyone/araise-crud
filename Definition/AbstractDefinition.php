<?php
/*
 * Copyright (c) 2016, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace whatwedo\CrudBundle\Definition;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use whatwedo\CrudBundle\Builder\DefinitionBuilder;
use whatwedo\CrudBundle\Controller\CrudController;
use whatwedo\CrudBundle\Enum\RouteEnum;
use whatwedo\CrudBundle\View\DefinitionViewInterface;
use whatwedo\TableBundle\Model\Type\DateFilterType;
use whatwedo\TableBundle\Model\Type\NumberFilterType;
use whatwedo\TableBundle\Model\Type\RelationFilterType;
use whatwedo\TableBundle\Model\Type\TextFilterType;
use whatwedo\TableBundle\Table\Table;

/**
 * @author Ueli Banholzer <ueli@whatwedo.ch>
 */
abstract class AbstractDefinition implements DefinitionInterface
{

    /**
     * listen on changes from this element (and get / set)
     */
    const AJAX_LISTEN = 1;

    /**
     * just get and set values
     */
    const AJAX = 2;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var DefinitionViewInterface
     */
    protected $definitionView;

    /**
     * @var DefinitionBuilder
     */
    protected $builder;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * {@inheritdoc}
     */
    public function getTitle($entity = null, $route = null)
    {
        switch ($route) {
            case RouteEnum::INDEX:
                return 'Übersicht';
            case RouteEnum::SHOW:
                return 'Ansehen';
            case RouteEnum::DELETE:
                return 'Löschen';
            case RouteEnum::EDIT:
                return 'Bearbeiten';
            case RouteEnum::CREATE:
                return 'Erstellen';
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public static function getCapabilities()
    {
        return [
            RouteEnum::INDEX,
            RouteEnum::SHOW,
            RouteEnum::DELETE,
            RouteEnum::EDIT,
            RouteEnum::CREATE,
        ];
    }

    public static function hasCapability($string)
    {
        return in_array($string, static::getCapabilities());
    }

    /**
     * {@inheritdoc}
     */
    public static function getController()
    {
        return CrudController::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->getDoctrine()->getRepository($this->getEntity());
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->getRepository()->createQueryBuilder($this->getQueryAlias());
    }

    /**
     * {@inheritdoc}
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * set the doctrine registry
     *
     * @param Registry $registry
     */
    public function setDoctrine(Registry $registry)
    {
        $this->doctrine = $registry;
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     * @return AbstractDefinition
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateDirectory()
    {
        return 'whatwedoCrudBundle:Crud';
    }

    /**
     * @param DefinitionViewInterface $definitionView
     */
    public function setDefinitionView(DefinitionViewInterface $definitionView)
    {
        $this->definitionView = $definitionView;
    }

    /**
     * @param null $data
     * @return DefinitionViewInterface
     */
    public function createView($data = null)
    {
        $this->builder = new DefinitionBuilder();

        $this->configureView($this->builder, $data);

        $this->definitionView->setDefinition($this);
        $this->definitionView->setData($data);
        $this->definitionView->setBlocks($this->builder->getBlocks());

        return $this->definitionView;
    }

    public function configureTableFilter(Table $table)
    {
        $reader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(static::getEntity());
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property)
        {
            /** @var Column $ormColumn */
            $ormColumn = $reader->getPropertyAnnotation($property, Column::class);
            /** @var ManyToOne $ormManyToOne */
            $ormManyToOne = $reader->getPropertyAnnotation($property, ManyToOne::class);
            $acronym = $property->getName();
            $label = $this->getLabelFor($table, $property->getName());
            $accessor = $acronym;
            if (!is_null($ormColumn)) {
                $accessor = sprintf('%s.%s', static::getQueryAlias(), $acronym);
                switch ($ormColumn->type){
                    case 'string':
                        $table->addFilter($acronym, $label, new TextFilterType($accessor));
                        break;
                    case 'datetime':
                        $table->addFilter($acronym, $label, new DateFilterType($accessor));
                        break;
                    case 'integer':
                    case 'float':
                    case 'decimal':
                        $table->addFilter($acronym, $label, new NumberFilterType($accessor));
                        break;
                }
            } else if (!is_null($ormManyToOne)) {
                $target = $ormManyToOne->targetEntity;
                if (strpos($target, '\\') === false) {
                    $target = preg_replace('#[a-zA-Z0-9]+$#i', $target, static::getEntity());
                }
                $choices = $this->getQueryBuilder()->getEntityManager()->getRepository($target)->findAll();
                if (!in_array($acronym, $this->getQueryBuilder()->getAllAliases())) {
                    $joins = [$acronym => sprintf('%s.%s', static::getQueryAlias(), $acronym)];
                } else {
                    $joins = [];
                }
                $table->addFilter($acronym, $label, new RelationFilterType($accessor, $choices, $joins));
            }
        }
    }

    private function getLabelFor(Table $table, $property)
    {
        /** @var \whatwedo\TableBundle\Table\Column $column */
        foreach ($table->getColumns() as $column) {
            if ($column->getAcronym() == $property) {
                return $column->getLabel();
            }
        }
        return $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteRedirect(RouterInterface $router, $entity = null)
    {
        return new RedirectResponse($router->generate(static::getRoutePrefix() . '_index'));
    }

    /**
     * {@inheritdoc}
     */
    public static function getRoutePrefix()
    {
        return static::getAlias();
    }

    /**
     * @return string
     */
    public static function getChildRouteAddition()
    {
        return static::getQueryAlias();
    }

    public function allowDelete($data = null)
    {
        return self::hasCapability(RouteEnum::DELETE);
    }

    public function allowCreate($data = null)
    {
        return self::hasCapability(RouteEnum::CREATE);
    }

    public function allowEdit($data = null)
    {
        return self::hasCapability(RouteEnum::EDIT);
    }

    public function getExportAttributes()
    {
        return [];
    }

    public function getExportCallbacks()
    {
        return [];
    }

    public function addAjaxOnChangeListener()
    {
    }

    public function ajaxOnChange(Request $request)
    {
    }

}
