<?php


namespace Semeru\Rtpo\Infrastructure\Persistence;


use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Semeru\Rtpo\Core\Domain\Repositories\SikRepository;
use Semeru\Rtpo\Core\Domain\Models\Rtpo;

class SqlSikRepository implements SikRepository
{
    private AbstractPdo $db;

    public function __construct(AbstractPdo $db)
    {
        $this->db = $db;
    }

    public function setWaitingForApproval(Rtpo $rtpo)
    {
        $sql = "update propose_reschedule
                set 
                    status = '1', 
                    status_desc = 'WAITING FOR NOS APPROVAL',
                    rtpo_nik = :rtpo_nik,
                    rtpo_cn = :rtpo_cn,
                    last_updated = :date_now,
                    is_sync = '0'
                where sik_no = :sik_no";

        $params = [
            'rtpo_nik' => $rtpo->rtpoNik(),
            'rtpo_cn' => $rtpo->rtpoCn(),
            'date_now' => $rtpo->lastUpdated(),
            'sik_no' => $rtpo->sikNo()
        ];

        return $this->db->fetchAll($sql, PDO::FETCH_ASSOC, $params);
    }

    public function rejectByRtpo(Rtpo $rtpo)
    {
        $sql = "update propose_reschedule
                set 
                    status = '2', 
                    status_desc = 'REJECTED BY RTPO',
                    rtpo_nik = :rtpo_nik,
                    rtpo_cn = :rtpo_cn,
                    last_updated = :date_now,
                    is_sync = '0'
                where sik_no = :sik_no";

        $params = [
            'rtpo_nik' => $rtpo->rtpoNik(),
            'rtpo_cn' => $rtpo->rtpoCn(),
            'date_now' => $rtpo->lastUpdated(),
            'sik_no' => $rtpo->sikNo()
        ];

        return $this->db->fetchAll($sql, PDO::FETCH_ASSOC, $params);
    }
}