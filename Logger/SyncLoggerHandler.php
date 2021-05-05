<?php

namespace Invoicing\Moloni\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class SyncLoggerHandler extends Base
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
