<?php


namespace A7Pro\Marketplace\Customer\Core\Domain\Repositories;

interface ReviewPhotoRepository
{
    public function save(array $photos, string $reviewId);
}