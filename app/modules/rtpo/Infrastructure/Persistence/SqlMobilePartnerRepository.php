<?php


namespace Semeru\Rtpo\Infrastructure\Persistence;


use Semeru\Rtpo\Core\Domain\Repositories\MobilePartnerRepository;
use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use PDO;

class SqlMobilePartnerRepository implements MobilePartnerRepository
{
    private AbstractPdo $db;

    public function __construct(AbstractPdo $db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $sql = "select m.cluster as cluster, m.cluster_id as cluster_id, m.mbp_id, m.status as mbp_status, rh.rtpo_id as rtpo_id_home, rh.rtpo_name as rtpo_home, rn.rtpo_id as rtpo_id, rn.rtpo_name as rtpo
            from mbp as m
            inner join rtpo as rh on m.rtpo_id_home = rh.rtpo_id
            inner join rtpo as rn on m.rtpo_id = rn.rtpo_id
            inner join master_mbp as mm on m.mbp_id = mm.mbp_id
            inner join user_mbp as um on m.mbp_id = um.mbp_id
            inner join users as u on um.username = u.username
            where m.mbp_id = :mbp_id";

        $params = [
            'mbp_id' => $mbp_id
        ];

        return $this->db->fetchAll($sql, PDO::FETCH_ASSOC, $params);
    }
}