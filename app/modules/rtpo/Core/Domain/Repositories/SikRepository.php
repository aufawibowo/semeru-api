<?php


namespace Semeru\Rtpo\Core\Domain\Repositories;


use Semeru\Rtpo\Core\Domain\Models\Rtpo;

interface SikRepository
{
    public function setWaitingForApproval(Rtpo $rtpo);
    public function rejectByRtpo(Rtpo $rtpo);
}