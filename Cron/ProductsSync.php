<?php

namespace Invoicing\Moloni\Cron;

use \Invoicing\Moloni\Logger\Logger;
use \Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\ProductsFactory as MoloniProductsFactory;


class ProductsSync
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Moloni
     */
    protected $moloni;

    /**
     * @var MoloniProductsFactory
     */
    private $productsFactory;


    public function __construct(
        Logger $logger,
        Moloni $moloni,
        MoloniProductsFactory $productsFactory


    )
    {
        $this->logger = $logger;
        $this->moloni = $moloni;
        $this->productsFactory = $productsFactory;

    }

    /**
     * Write to system.log
     *
     * @return void|boolean
     * @throws \Exception
     */

    public function execute()
    {

        if ($this->moloni->checkActiveSession()) {
            $currentDate = gmdate('Y-m-d H:i:s');
            $updateSince = $this->moloni->settings['cron_date'];
            if (!$updateSince || !strtotime($updateSince)) {
                $updateSince = $currentDate;
            }

            $this->logger->info("Updating products since " . $updateSince);

            if ($this->moloni->settings['products_sync_stock'] || $this->moloni->settings['products_sync_price']) {
                $changedMoloniProducts = $this->moloni->products->getModifiedSinceAll(['lastmodified' => $updateSince]);

                if (!empty($changedMoloniProducts) && is_array($changedMoloniProducts)) {
                    foreach ($changedMoloniProducts as $moloniProduct) {
                        $syncProduct = $this->productsFactory->create();

                        if ($syncProduct->syncProductFromMoloni($moloniProduct)) {
                            $this->logger->info(
                                $moloniProduct['reference'] . ' ' .
                                __("Artigo atualizado com successo")
                            );
                        } else {
                            $this->logger->info(
                                $moloniProduct['reference'] . ' ' .
                                __('Erro ao actualizar o artigo ') .
                                $this->moloni->errors->getErrors('first')['title']
                            );
                        }
                    }
                }
                $this->logger->info("Finish updating products ");
            } else {
                $this->logger->info("Sync functions disabled ");
            }

            $companyId = $this->moloni->session->companyId;
            $this->moloni->settingsRepository->saveSetting($companyId, "cron_date", $currentDate);

        } else {
            throw new \Exception('Moloni session not initiated');
        }
    }

}
