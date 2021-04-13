<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class ProductsTaxExemptions
{

    private array $store = [];
    private Moloni $moloni;

    /**
     * ProductsTaxes constructor.
     * @param Moloni $moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    /**
     * @param bool $company_id
     * @return bool|mixed
     * @throws \JsonException
     */
    public function getAll($company_id = false)
    {
        if (isset($this->store[__FUNCTION__])) {
            return $this->store[__FUNCTION__];
        }

        $values = ["company_id" => ($company_id ?: $this->moloni->session->companyId)];
        $result = $this->moloni->execute("taxExemptions/getAll", $values);
        if (is_array($result) && isset($result[0]['code'])) {
            $this->store[__FUNCTION__] = $result;
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Não tem acesso à informação das razões de isenção"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }
}
