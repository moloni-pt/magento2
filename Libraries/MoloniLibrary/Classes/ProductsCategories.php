<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use JsonException;

class ProductsCategories
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
     * @param array $values https://www.moloni.pt/dev/index.php?action=getApiDocDetail&id=204
     * @param int|bool $companyId
     * @return bool|mixed
     */
    public function getAll(array $values, $companyId = false)
    {

        if (!isset($values['parent_id'])) {
            $values['parent_id'] = 0;
        }

        if (isset($values['parent_id'], $this->store[$values['parent_id']])) {
            return $this->store[$values['parent_id']];
        }

        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("productCategories/getAll", $values);

        $this->store[$values['parent_id']] = $result;

        if (is_array($result) && isset($result[0]['category_id'])) {
            return $result;
        }

        if (empty($result)) {
            // No error but empty result
            return false;
        }

        $this->moloni->errors->throwError(
            __("Erro ao obter todas as categorias"),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    /**
     * @param array $values https://www.moloni.pt/dev/index.php?action=getApiDocDetail&id=204
     * @param int|bool $companyId
     * @return bool|array
     */
    public function insert(array $values, $companyId = false)
    {
        if (!isset($values['parent_id'])) {
            $values['parent_id'] = 0;
        }

        unset($this->store[$values['parent_id']]);

        $values['company_id'] = ($companyId ?: $this->moloni->session->companyId);
        $result = $this->moloni->execute("productCategories/insert", $values);

        if (is_array($result) && isset($result['category_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Houve um erro ao inserir a categoria"),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }
}
