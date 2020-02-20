<?php

namespace HeimrichHannot\Ldap;

use Psr\Log\LoggerInterface;

class LogrFactory {
    public static function createLogr(LoggerInterface $logger) {
        return new Logr($logger);
    }
}

?>