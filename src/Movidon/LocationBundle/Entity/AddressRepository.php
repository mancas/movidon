<?php

namespace Movidon\LocationBundle\Entity;

use Movidon\FrontendBundle\Entity\CustomEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;

class AddressRepository extends CustomEntityRepository
{
    protected $specialFields = array();

    public function findAddressesByUser($user, $limit = null)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a');

        $qb->leftJoin('a.user', 'u');
        $qb->addOrderBy('a.updated','DESC');

        $and = $qb->expr()->andx();

        $and->add($qb->expr()->eq('u.email', '\''. $user->getEmail() .'\''));

        $qb->where($and);

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
