<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace MoloniLibrary;

use Invoicing\Moloni\Model\Moloni;

class Companies
{

    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    public function getOne($company_id = false)
    {
        $values = array("company_id" => ($company_id ? $company_id : $this->moloni->company_id));
        $result = $this->moloni->execute("companies/getOne", $values);
        if (is_array($result) && isset($result['company_id'])) {
            return $result;
        } else {
            $this->moloni->errors->throwError("Não tem acesso à informação da empresa", "Não tem acesso à informação da empresa.", __CLASS__ . "/" . __FUNCTION__);
            return false;
        }
    }

    public function getAll()
    {
        $result = $this->moloni->execute("companies/getAll", null, true);
        if (is_array($result) && isset($result[0]['company_id'])) {
            return $result;
        } else {
            return $this->moloni->errors->throwError("Não tem empresas disponíveis", "Não tem empresas disponíveis para serem usadas", __CLASS__ . "/" . __FUNCTION__);
        }
    }
}
