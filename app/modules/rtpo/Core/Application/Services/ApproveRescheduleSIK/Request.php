<?php


namespace Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK;


class Request
{
    public ?string $sik_no;
    public ?string $username;
    public ?string $reason;
    public ?string $is_approved;

    /**
     * Request constructor.
     * @param string|null $sik_no
     * @param string|null $username
     * @param string|null $reason
     * @param string|null $is_approved
     */
    public function __construct(?string $sik_no, ?string $username, ?string $reason, ?string $is_approved)
    {
        $this->sik_no = $sik_no;
        $this->username = $username;
        $this->reason = $reason;
        $this->is_approved = $is_approved;
    }


}