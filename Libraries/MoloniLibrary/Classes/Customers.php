<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use JsonException;

class Customers
{

    private Moloni $moloni;
    private array $store = [];

    /**
     * Customers constructor.
     * @param Moloni $moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    /**
     * @param int|bool $companyId
     * @return bool|mixed
     * @throws JsonException
     */
    public function getAll($companyId = false)
    {
        $values = ["company_id" => ($companyId ?: $this->moloni->session->companyId)];
        $result = $this->moloni->execute("customers/getAll", $values);
        if (is_array($result) && isset($result[0]['customer_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Não tem acesso à informação dos clientes"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values
     * @param int|bool $companyId
     * @return bool|mixed
     * @throws JsonException
     */
    public function getByEmail(array $values, $companyId = false)
    {
        if (!isset($values['email'])) {
            return false;
        }

        if (isset($this->store[__FUNCTION__][$values['email']])) {
            return $this->store[__FUNCTION__][$values['email']];
        }

        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("customers/getByEmail", $values);

        $this->store[__FUNCTION__][$values['email']] = $result;

        if (is_array($result) && isset($result[0]['customer_id'])) {
            return $result;
        }

        if (empty($result)) {
            // No error but empty result
            return false;
        }

        $this->moloni->errors->throwError(
            __("Não tem acesso à informação dos clientes"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values
     * @param int|bool $companyId
     * @return bool|mixed
     * @throws JsonException
     */
    public function getByVat(array $values, $companyId = false)
    {
        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("customers/getByVat", $values);

        if (is_array($result) && isset($result[0]['customer_id'])) {
            return $result;
        }

        if (empty($result)) {
            // No error but empty result
            return false;
        }

        $this->moloni->errors->throwError(
            __("Não tem acesso à informação dos clientes"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param int|bool $companyId
     * @return bool|string
     * @throws JsonException
     */
    public function getNextNumber($companyId = false)
    {
        $values = ["company_id" => ($companyId ?: $this->moloni->session->companyId)];
        $result = $this->moloni->execute("customers/getNextNumber", $values);
        if (is_array($result) && isset($result['number'])) {
            return $result['number'];
        }

        $this->moloni->errors->throwError(
            __("Houve um erro ao obter o próximo número de cliente"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values https://www.moloni.pt/dev/index.php?action=getApiDocDetail&id=204
     * @param int|bool $companyId
     * @return bool|array
     * @throws JsonException
     */
    public function insert(array $values, $companyId = false)
    {
        $this->store = [];
        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("customers/insert", $values);

        if (is_array($result) && isset($result['customer_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Houve um erro ao inserir o cliente"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values https://www.moloni.pt/dev/index.php?action=getApiDocDetail&id=204
     * @param int|bool $companyId
     * @return bool|array
     */
    public function update(array $values, $companyId = false)
    {
        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("customers/update", $values);

        if (is_array($result) && isset($result['customer_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Houve um erro ao atualizar o cliente"),
            __(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }
}
