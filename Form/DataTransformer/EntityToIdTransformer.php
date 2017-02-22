<?php
/*
 * Copyright (c) 2017, whatwedo GmbH
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

namespace whatwedo\CrudBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class EntityToIdTransformer
 * @package whatwedo\CrudBundle\Form\DataTransformer
 */
class EntityToIdTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    /**
     * EntityToIdTransformer constructor.
     * @param EntityManager $em
     * @param $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->class = $class;
    }

    /**
     * @param $entity
     * @return null
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return null;
        }
        $entityClass = get_class($entity);
        $idField = $this->em->getClassMetadata($entityClass)->getSingleIdentifierFieldName();
        $accessor = PropertyAccess::createPropertyAccessor();
        return $accessor->getValue($entity, $idField);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }
        $entity = $this->em->getRepository($this->class)->find($id);
        if (null === $entity) {
            throw new TransformationFailedException();
        }
        return $entity;
    }
}
