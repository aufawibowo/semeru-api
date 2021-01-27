<?php

use Semeru\divisi-rtpo\Infrastructure\Services\JwtTokenService;
use Semeru\divisi-rtpo\Infrastructure\Services\HashBasedOtpService;
use Semeru\divisi-rtpo\Infrastructure\Services\GoSmsService;
use Semeru\divisi-rtpo\Infrastructure\Services\Md5UrlSignerService;
use Semeru\divisi-rtpo\Infrastructure\Services\ApiApituService;
use Semeru\divisi-rtpo\Infrastructure\Persistence\SqlDpcRepository;
use Semeru\divisi-rtpo\Infrastructure\Persistence\SqlUserRepository;

$container->setShared('tokenService', function() use ($container) {
    return new JwtTokenService($container->get('config'));
});

$container->setShared('otpService', function () use($container) {
    return new HashBasedOtpService($container->get('config'));
});

$container->setShared('smsService', function () use($container) {
    return new GoSmsService($container->get('config'));
});

$container->setShared('urlSignerService', function () use($container) {
    return new Md5UrlSignerService($container->get('config'));
});

$container->setShared('apituService', function() use ($container) {
    return new ApiApituService($container->get('config'));
});

$container->setShared('dpcRepository', function() use ($container) {
    return new SqlDpcRepository($container->get('db'));
});

$container->setShared('userRepository', function() use ($container) {
    return new SqlUserRepository($container->get('db'));
});