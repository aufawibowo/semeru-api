<?php


namespace A7Pro\Marketplace\Customer\Infrastructure\Persistence;


use A7Pro\Marketplace\Customer\Core\Domain\Models\Date;
use A7Pro\Marketplace\Customer\Core\Domain\Models\ReviewPhotosId;
use A7Pro\Marketplace\Customer\Core\Domain\Repositories\ReviewPhotoRepository;
use Phalcon\Db\Adapter\Pdo\AbstractPdo;

class SqlReviewPhotoRepository extends SqlBaseRepository implements ReviewPhotoRepository
{
    private AbstractPdo $db;

    public function __construct(AbstractPdo $db)
    {
        $this->db = $db;
    }

    public function save(array $photos, string $reviewId)
    {
        $sql = "insert into review_photos
                    (id, review_id, photo_url)
                values 
                    (:id, :review_id, :photo_url)";

        $id = new ReviewPhotosId();

        $param = [
            'id'        => $id->id(),
            'review_id' => $reviewId,
            'photo_url' => json_encode($photos)
        ];

        try {
            $this->db->begin();
            $this->db->execute($sql, $param);
            $this->db->commit();

            return true;
        }
        catch (\Throwable $th) {
            var_dump($th->getMessage());
            $this->db->rollback();

            return false;
        }

    }


}
