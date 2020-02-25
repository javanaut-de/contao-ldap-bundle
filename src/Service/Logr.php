<?php

namespace Refulgent\ContaoLDAPSupportBundle\Service;

use Psr\Log\LoggerInterface;

class Logr {

	public $logger = null;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}