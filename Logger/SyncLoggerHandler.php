<?php

namespace Invoicing\Moloni\Logger;

use Monolog\Logger;

class SyncLoggerHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/moloni.sync.log';
}
