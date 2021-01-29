<?php

use Semeru\Rtpo\Infrastructure\Persistence\SqlMobilePartnerRepository;

$container->setShared('mobilePartnerRepository', function() use ($container) {
    return new SqlMobilePartnerRepository($container->get('db'));
});