<?php


namespace Semeru\Rtpo\Infrastructure\Persistence;


use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Semeru\Rtpo\Core\Domain\Repositories\RtpoRepository;

class SqlRtpoRepository implements  RtpoRepository
{
    private AbstractPdo $db;

    public function __construct(AbstractPdo $db)
    {
        $this->db = $db;
    }

    public function getRtpoUserData(string $username)
    {
        $sql = "select *
        from users
        where username = :username";

        $params = [
            'username' => $username
        ];

        return $this->db->fetchAll($sql, PDO::FETCH_ASSOC, $params);
    }

    public function getRtpoId()
    {
        $data = $this->getRtpoUserData();
        return $data['name'];
    }


}