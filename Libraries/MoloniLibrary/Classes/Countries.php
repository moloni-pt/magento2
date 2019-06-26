<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use \Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class Countries
{

    private $store = [];
    private $moloni;

    /**
     * Countries constructor.
     * @param Moloni $moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    /**
     * @param bool $company_id
     * @return bool|mixed
     */
    public function getAll($company_id = false)
    {
        if (isset($this->store[__FUNCTION__])) {
            return $this->store[__FUNCTION__];
        }

        $values = ["company_id" => ($company_id ? $company_id : $this->moloni->session->companyId)];
        $result = $this->moloni->execute("countries/getAll", $values);
        if (is_array($result) && isset($result[0]['country_id'])) {
            $this->store[__FUNCTION__] = $result;
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Não tem acesso à informação dos países"),
                __(print_r($result, true)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }
}
