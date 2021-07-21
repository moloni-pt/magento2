<?php
/**
 * Module for Magento 2 by Moloni
 * Copyright (C) 2017  Moloni, lda
 *
 * This file is part of Invoicing/Moloni.
 *
 * Invoicing/Moloni is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Invoicing\Moloni\Observer;

use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\DocumentsFactory as MoloniDocumentsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Logger\DocumentsLogger;
use Invoicing\Moloni\Model\DocumentsRepository;
use JsonException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;

class SalesOrderAfterSave implements ObserverInterface
{

    /**
     * @var DocumentsLogger
     */
    protected $logger;

    /**
     * @var Moloni
     */
    protected $moloni;

    /**
     * @var MoloniDocumentsFactory
     */
    protected $moloniDocumentsFactory;

    /**
     * @var DocumentsRepository
     */
    protected $documentsRepository;

    public function __construct(
        Moloni $moloni,
        DocumentsLogger $logger,
        MoloniDocumentsFactory $moloniDocumentsFactory,
        DocumentsRepository $documentsRepository

    )
    {
        $this->logger = $logger;
        $this->moloni = $moloni;
        $this->moloniDocumentsFactory = $moloniDocumentsFactory;
        $this->documentsRepository = $documentsRepository;

    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return SalesOrderAfterSave
     * @throws JsonException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $order AbstractModel
         */
        $order = $observer->getEvent()->getOrder();

        if ($order instanceof AbstractModel) {
            if ($this->moloni->checkActiveSession()) {
                if ($this->moloni->settings['document_auto'] &&
                    in_array($order->getState(), $this->moloni->settings['orders_statuses'])
                ) {
                    if (!$this->documentsRepository->getByOrderId($order->getId())) {
                        $this->logger->info('Trying to create a document from order ' . $order->getId());

                        $newDocument = $this->moloniDocumentsFactory->create();
                        $result = $newDocument->createDocumentFromOrderId($order->getId());

                        $this->logger->info(print_r($result, true));
                    } else {
                        $this->logger->info('The document was already created');
                    }
                }
            } else {
                $this->logger->info('Moloni valid session not found');
            }
        }

        return $this;
    }
}
