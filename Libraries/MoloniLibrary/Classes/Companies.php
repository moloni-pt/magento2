<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use \Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class Companies
{

    private $moloni;
    private $store;

    /**
     * Companies constructor.
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
    public function getOne($company_id = false)
    {
        if (isset($this->store[__FUNCTION__])) {
            return $this->store[__FUNCTION__];
        }

        $values = ["company_id" => ($company_id ? $company_id : $this->moloni->session->companyId)];
        $result = $this->moloni->execute("companies/getOne", $values);
        if (is_array($result) && isset($result['company_id'])) {
            $this->store[__FUNCTION__] = $result;
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Não tem acesso à informação da empresa"),
                __("Não tem acesso à informação da empresa."),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    public function getAll()
    {
        $result = $this->moloni->execute("companies/getAll", null);
        if (is_array($result) && isset($result[0]['company_id'])) {
            foreach ($result as $key => &$company) {
                // Unset company_id 5 (Empresa de Demonstração) due to lack of privleges
                if ($company['company_id'] == 5) {
                    unset($result[$key]);
                }
            }
            return $result;
        } else {
            return $this->moloni->errors->throwError(
                __("Não tem empresas disponíveis"),
                __("Não tem empresas disponíveis para serem usadas"),
                __CLASS__ . "/" . __FUNCTION__
            );
        }
    }
}
