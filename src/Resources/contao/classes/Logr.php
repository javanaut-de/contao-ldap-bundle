<?php

namespace Refulgent\ContaoLDAPSupport;

use Psr\Log\LoggerInterface;

class Logr {

	public $logger = null;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
		\System::log(json_encode($logger),'setLogger()','debug');
	}

//	public function setLogger(LoggerInterface $logger) {
//		$this->logger = $logger;
//		\System::log(json_encode($logger),'setLogger()','debug');
//		die('setLogger()');
//	}
}