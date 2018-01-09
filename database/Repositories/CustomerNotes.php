<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * CustomerNotes
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerNotes extends EntityRepository
{
    /**
     * Return an array of ordered customer notes
     *
     * @param $custid int The customer ID to fetch notes for
     * @return \Entities\CustomerNote[] An array of all customer notes objects
     */
    public function ordered( $custid, $publicOnly = false )
    {
        return $this->getEntityManager()->createQuery(
                "SELECT n FROM Entities\\CustomerNote n WHERE n.Customer = ?1 "
                . ( $publicOnly ? "AND n.private = 0 " : "" )
                . "ORDER BY n.created DESC"
            )
            ->setParameter( 1, $custid )
            ->getResult();
    }
    

    /**
     * Return an array of the latest created / updated note for all customer's with notes.
     *
     * Array has the form:
     *
     *     [
     *         0 => [
     *             'cname' => 'ABC Networks Limited',
     *             'cid' => 9,
     *             'cshortname' => 'abcnetworks'
     *             'latest' => '2013-04-02 16:34:15'
     *         ]
     *         ...
     *     ]
     *
     */
    public function getLatestUpdate()
    {
        return $this->getEntityManager()->createQuery(
                "SELECT c.name AS cname, c.id AS cid, c.shortname AS cshortname, MAX( cn.updated ) AS latest
                
                FROM Entities\\Customer c
                    LEFT JOIN c.Notes AS cn

                GROUP BY cname, cid, cshortname
                
                HAVING COUNT( cn.Customer ) > 0
                
                ORDER BY latest DESC"
            )
            ->getArrayResult();
    }
    
}
