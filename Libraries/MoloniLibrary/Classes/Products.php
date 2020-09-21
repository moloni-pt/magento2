<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class Products
{

    private $moloni;
    private $store = [];

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
     */
    public function getAll($companyId = false)
    {
        $values = ["company_id" => ($companyId ?: $this->moloni->session->companyId)];
        $result = $this->moloni->execute("products/getAll", $values);
        if (is_array($result) && !isset($result['error'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Erro ao aceder aos artigos"),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    public function getModifiedSinceAll($values, $companyId = false): array
    {
        if (!isset($values['lastmodified'])) {
            return [];
        }

        $results = [];
        $loopCounter = 0;
        $resultsCounter = 50;

        while ($resultsCounter > 0) {
            $values['offset'] = count($results);
            $products = $this->getModifiedSince($values, $companyId);
            $results = array_merge($results, $products);
            $resultsCounter = count($products);
            $loopCounter++;
            // 1500 Products
            if ($loopCounter > 30) {
                break;
            }
        }

        return $results;
    }

    public function getModifiedSince($values, $companyId)
    {
        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("products/getModifiedSince", $values);
        if (is_array($result) && !isset($result['error'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Erro ao aceder aos artigos"),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return [];
    }

    /**
     * @param array $values
     * @param int|bool $companyId
     * @return bool|mixed
     */
    public function getByReference($values, $companyId = false, $debug = false)
    {

        if (!isset($values['reference'])) {
            return false;
        }

        if (isset($this->store[__FUNCTION__][$values['reference']])) {
            return $this->store[__FUNCTION__][$values['reference']];
        }

        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $values['exact'] = true;
        $result = $this->moloni->execute("products/getByReference", $values);

        if (is_array($result) && isset($result[0]['product_id'])) {
            $this->store[__FUNCTION__][$values['reference']] = $result;
            return $result;
        }

        if (empty($result)) {
            // No error but empty result
            return false;
        }

        $this->moloni->errors->throwError(
            __("Erro ao aceder aos artigos"),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values https://www.moloni.pt/dev/index.php?action=getApiDocDetail&id=194
     * @param int|bool $companyId
     * @return bool|array
     */
    public function insert($values, $companyId = false)
    {
        $this->store = [];
        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("products/insert", $values);

        if (is_array($result) && isset($result['product_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Errro ao inserir o artigo: " . $values['reference']),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values https://www.moloni.pt/dev/index.php?action=getApiDocDetail&id=195
     * @param int|bool $companyId
     * @return bool|array
     */
    public function update($values, $companyId = false)
    {
        $this->store = [];
        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("products/update", $values);

        if (is_array($result) && isset($result['product_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Houve um erro ao actualziar o cliente"),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }
}
