<?php

namespace Refulgent\ContaoLDAPSupport;

use Psr\Log\LoggerInterface;

class LogrFactory {
    public static function createLogr(LoggerInterface $logger) {
        return new Logr($logger);
    }
}

?>