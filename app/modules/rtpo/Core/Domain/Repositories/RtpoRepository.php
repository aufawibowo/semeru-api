<?php


namespace Semeru\Rtpo\Core\Domain\Repositories;


interface RtpoRepository
{
    public function getRtpoUserData(string $username);
    public function getRtpoId();
}