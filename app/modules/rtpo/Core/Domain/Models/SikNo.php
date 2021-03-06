<?php


namespace Semeru\Rtpo\Core\Domain\Models;


use Ramsey\Uuid\Uuid;

class SikNo
{
    private string $id;

    /**
     * ReviewId constructor.
     * @param string $id
     */
    public function __construct(string $id = null)
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }
}