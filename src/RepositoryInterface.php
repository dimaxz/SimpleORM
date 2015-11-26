<?php

namespace SimpleORM;

/**
 * Description of IRepository
 *
 * @author Dmitriy
 */
interface RepositoryInterface
{

    public function findById($id);

    public function findBySpecification(ISpecificationCriteria $specification);

    public function findAll();

    public function findAllBySpecification(ISpecificationCriteria $specification);

    public function save(EntityInterface $Entity);

    public function delete(EntityInterface $Entity);
}
