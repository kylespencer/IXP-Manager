<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * VlanInterface
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VlanInterface extends EntityRepository
{

    /**
     * Utility function to provide an array of all VLAN interfaces on a given
     * VLAN for a given protocol.
     *
     * Returns an array of elements such as:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65500
     *         [gmaxprefixes] => 20        // from cust table (global)
     *         [peeringmacro] => ABC
     *         [peeringmacrov6] => ABC
     *         [vliid] => 159
     *         [enabled] => 1              // VLAN interface enabled for requested protocol?
     *         [address] => 192.0.2.123    // assigned address for requested protocol?
     *         [bgpmd5secret] => qwertyui  // MD5 for requested protocol
     *         [maxbgpprefix] => 20        // VLAN interface max prefixes
     *         [as112client] => 1          // if the member is an as112 client or not
     *         [rsclient] => 1             // if the member is a route server client or not
     *     ]
     *
     * @param \Entities\Vlan $vlan The VLAN
     * @param int $proto Either 4 or 6
     * @param bool $useResultCache If true, use Doctrine's result cache (ttl set to one hour)
     * @return array As defined above.
     * @throws \IXP_Exception On bad / no protocol
     */
    public function getForProto( $vlan, $proto, $useResultCache = true )
    {
        if( !in_array( $proto, [ 4, 6 ] ) )
            throw new \IXP_Exception( 'Invalid protocol specified' );


        $qstr = "SELECT c.id AS cid, c.name AS cname, c.shortname AS cshortname, c.autsys AS autsys,
                       c.maxprefixes AS gmaxprefixes, c.peeringmacro as peeringmacro, c.peeringmacrov6 as peeringmacrov6,
                       vli.id AS vliid, vli.ipv{$proto}enabled AS enabled, addr.address AS address,
                       vli.ipv{$proto}bgpmd5secret AS bgpmd5secret, vli.maxbgpprefix AS maxbgpprefix,
                       vli.as112client AS as112client, vli.rsclient AS rsclient
                    FROM Entities\\VlanInterface vli
                        JOIN vli.VirtualInterface vi
                        JOIN vli.IPv{$proto}Address addr
                        JOIN vi.Customer c
                        JOIN vi.PhysicalInterfaces pi
                        JOIN vli.Vlan v
                    WHERE
                        v = :vlan
                        AND " . Customer::DQL_CUST_ACTIVE     . "
                        AND " . Customer::DQL_CUST_CURRENT    . "
                        AND " . Customer::DQL_CUST_TRAFFICING . "
                        AND pi.status = " . \Entities\PhysicalInterface::STATUS_CONNECTED;

        $qstr .= " ORDER BY c.autsys ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'vlan', $vlan );
        $q->useResultCache( $useResultCache, 3600 );
        return $q->getArrayResult();
    }

    
    /**
     * Utility function to provide an array of all VLAN interfaces on a given IXP.
     *
     * Returns an array of elements such as:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65500
     *         [vliid] => 159
     *
     *         [ipv4enabled]                   // VLAN interface enabled
     *         [ipv4canping]                   // Can ping for moniroting
     *         [ipv4hostname]                  // hostname
     *         [ipv4monitorrcbgp]              // Can monitor RC BGP session
     *         [ipv4address] => 192.0.2.123    // assigned address
     *         [ipv4bgpmd5secret] => qwertyui  // MD5
     *
     *         [ipv6enabled]                   // VLAN interface enabled
     *         [ipv6canping]                   // Can ping for moniroting
     *         [ipv6hostname]                  // hostname
     *         [ipv6monitorrcbgp]              // Can monitor RC BGP session
     *         [ipv6address] => 192.0.2.123    // assigned address
     *         [ipv6bgpmd5secret] => qwertyui  // MD5
     *
     *         [maxbgpprefix] => 20        // VLAN interface max prefixes
     *         [as112client] => 1          // if the member is an as112 client or not
     *         [rsclient] => 1             // if the member is a route server client or not
     *     ]
     *
     * @param \Entities\Vlan $vlan The VLAN
     * @param int $proto Either 4 or 6
     * @param bool $useResultCache If true, use Doctrine's result cache (ttl set to one hour)
     * @return array As defined above.
     * @throws \IXP_Exception On bad / no protocol
     */
    public function getForIXP( $ixp, $useResultCache = true )
    {
        $qstr = "SELECT c.id AS cid, c.name AS cname, c.shortname AS cshortname, c.autsys AS autsys,
                
                    vli.id AS vliid,
                    
                    vli.ipv4enabled      AS ipv4enabled,
                    vli.ipv4hostname     AS ipv4hostname,
                    vli.ipv4canping      AS ipv4canping,
                    vli.ipv4monitorrcbgp AS ipv4monitorrcbgp,
                    vli.ipv4bgpmd5secret AS ipv4bgpmd5secret,
                    v4addr.address       AS ipv4address,
                    
                    vli.ipv6enabled      AS ipv6enabled,
                    vli.ipv6hostname     AS ipv6hostname,
                    vli.ipv6canping      AS ipv6canping,
                    vli.ipv6monitorrcbgp AS ipv6monitorrcbgp,
                    vli.ipv6bgpmd5secret AS ipv6bgpmd5secret,
                    v6addr.address       AS ipv6address,
                    
                    vli.maxbgpprefix AS maxbgpprefix,
                    vli.as112client AS as112client,
                    vli.rsclient AS rsclient,
                
                    s.name AS switchname,
                    sp.name AS switchport,
                
                    v.number AS vlannumber,
                
                    ixp.shortname AS ixpname
                    
        FROM Entities\\VlanInterface vli
            JOIN vli.VirtualInterface vi
            JOIN vli.IPv4Address v4addr
            JOIN vli.IPv6Address v6addr
            JOIN vi.Customer c
            JOIN vi.PhysicalInterfaces pi
            JOIN pi.SwitchPort sp
            JOIN sp.Switcher s
            JOIN vli.Vlan v
            JOIN v.Infrastructure inf
            JOIN inf.IXP ixp
                
        WHERE
            ixp = :ixp
            AND " . Customer::DQL_CUST_ACTIVE     . "
            AND " . Customer::DQL_CUST_CURRENT    . "
            AND " . Customer::DQL_CUST_TRAFFICING . "
            AND pi.status = " . \Entities\PhysicalInterface::STATUS_CONNECTED;
    
        $qstr .= " ORDER BY c.shortname ASC, vli.id ASC";
    
        $q = $this->getEntityManager()->createQuery( $qstr );
        
        $q->setParameter( 'ixp', $ixp );
        $q->useResultCache( $useResultCache, 3600 );
        return $q->getArrayResult();
    }
    
}
