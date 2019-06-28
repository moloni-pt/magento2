<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use \Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class DeliveryMethods
{

    private $moloni;

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
    public function getAll($company_id = false)
    {
        $values = ["company_id" => ($company_id ? $company_id : $this->moloni->session->companyId)];
        $result = $this->moloni->execute("deliveryMethods/getAll", $values);
        if (is_array($result) && isset($result[0]['delivery_method_id'])) {
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Não tem acesso à informação das dos métodos de pagamento"),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }

    /**
     * @param array $values [name => delivery method name]
     * @param bool|int $companyId
     * @return bool|array
     */
    public function insert($values, $companyId = false)
    {
        $values['company_id'] = ($companyId ? $companyId : $this->moloni->session->companyId);
        $result = $this->moloni->execute("deliveryMethods/insert", $values);

        if (is_array($result) && isset($result['delivery_method_id'])) {
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Houve um erro ao inserir o método de entrega"),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }
}
