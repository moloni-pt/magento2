<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace MoloniLibrary;

class Companies
{

    private $moloni;

    /**
     * Companies constructor.
     * @param Moloni $moloni
     */
    public function __construct()
    {
    }

    /**
     * @param bool $company_id
     * @return bool|mixed
     */
    public function getOne($company_id = false)
    {
        $values = ["company_id" => ($company_id ? $company_id : $this->moloni->company_id)];
        $result = $this->moloni->execute("companies/getOne", $values);
        if (is_array($result) && isset($result['company_id'])) {
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
