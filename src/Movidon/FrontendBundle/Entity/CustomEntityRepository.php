<?php

namespace Movidon\FrontendBundle\Entity;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\Functions\LengthFunction;
use Movidon\FrontendBundle\Util\ArrayHelper;
use Doctrine\ORM\EntityRepository;

class CustomEntityRepository extends EntityRepository
{
    const DEFAULT_LIMIT = 15;
    const BIG_LIMIT = 50;
    const LEFT_LIMIT = 10;
    const MEDIUM_LIMIT = 30;

    protected $specialFields = array();
    protected $defaultOrder = 'id';
    protected $order = 'DESC';
    protected $joinForOrder = 'false';

    public function findBySearchCriteria($criterias)
    {
        $qb = $this->findSearchCriteria($criterias);

        return $qb->getQuery();
    }

    protected function findSearchCriteria($criterias)
    {
        $ordered = false;
        $criteria = ArrayHelper::multiLevelArrayToSingleLevel($criterias);
        $qb = $this->createQueryBuilder('i');
        if (isset($criteria['order_by'])) {
            $this->prepareCriteria($criteria);
        }

        foreach ($criteria as $field => $value) {
            if ($value) {
                if (in_array($field, $this->specialFields)) {
                    $method = 'addToQueryBuilderSpecialField' . ucfirst($field);
                    $this->$method($qb, $value);
                } else {
                    if ($field == 'order_by') {
                        if ($this->joinForOrder == 'true') {
                            $qb->innerJoin('i.' . $value, $value);
                            $qb->orderBy($value . '.' . $this->defaultOrder, $this->order);
                        } else {
                            $qb->orderBy('i.' . $value, $this->order);
                        }
                        $ordered = true;
                    } else if ($field == 'id') {
                        $class = $this->getMetadataFor()->getName();
                        if ($value instanceof $class) {
                            $value = $value->getId();
                        }
                        $qb->andWhere($qb->expr()->eq('i.' . $field, ':i_' . $field))->setParameter('i_'. $field, $value);
                    } else if (is_string($value)) {
                        $qb->andWhere($qb->expr()->like('i.'.$field, ':i_'.$field))->setParameter('i_'.$field, '%'.$value.'%');
                    } else {
                        $qb->andWhere($qb->expr()->eq('i.' . $field, ':i_' . $field))->setParameter('i_'. $field, $value);
                    }
                }
            }
        }

        $this->addSpecialSearchCriteria($qb);

        if (!$ordered) {
            $qb->orderBy('i.' . $this->defaultOrder);
        }
//ldd($qb->getQuery()->getSQL());
        return $qb;
    }
    protected function addSpecialSearchCriteria(&$qb)
    {
    }

    public function prepareCriteria(&$criteria)
    {
        if (isset($criteria['join'])) {
            $this->joinForOrder = $criteria['join'];
        }
        if (isset($criteria['order'])) {
            $this->order = $criteria['order'];
        }
        if (isset($criteria['field'])) {
            $this->defaultOrder = $criteria['field'];
        }

        unset($criteria['join']);
        unset($criteria['order']);
        unset($criteria['field']);
        unset($criteria['repository_method']);
    }

    public function getCountOfEntity()
    {
        $qb = $this->createQueryBuilder('i');
        $qb->select('count(i)');

        return $qb->getQuery()->getResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    public function countFindBySearchCriteria($criterias)
    {
        $qb = $this->findSearchCriteria($criterias);
        $qb->select('count(distinct i.id)');

        return $qb->getQuery()->getResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    public function getSqlContent($sql)
    {
        $em = $this->getEntityManager();
        $result = $em->getConnection()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }
} 