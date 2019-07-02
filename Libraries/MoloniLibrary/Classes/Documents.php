<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Classes;

use \Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class Documents
{

    private $moloni;
    public $documentTypeId = 1;
    public $documentTypeName = "Faturas";
    public $documentTypeClass = "invoices";


    /**
     * @param Moloni $moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }


    public function setDocumentType($documentType = false)
    {
        if ($documentType) {
            $this->documentTypeClass = $documentType;
        } else {
            $this->documentTypeClass = $this->moloni->settings['document_type'];
        }


        // @todo check validations and parse documentTypeId and Name
        return $this;
    }

    /**
     * @param bool|int $companyId
     * @return bool|mixed
     */
    public function getAll($companyId = false)
    {
        $values = ["company_id" => ($companyId ? $companyId : $this->moloni->session->companyId)];
        $result = $this->moloni->execute($this->documentTypeClass . "/getAll", $values);
        if (is_array($result) && isset($result[0]['payment_method_id'])) {
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
     * @param int|string $documentType
     * @param array $values
     * @param bool|int $companyId
     * @return bool|array
     */
    public function insert($values, $companyId = false)
    {
        $values['company_id'] = ($companyId ? $companyId : $this->moloni->session->companyId);
        $result = $this->moloni->execute($this->documentTypeClass . "/insert", $values);

        if (is_array($result) && isset($result['document_id'])) {
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Houve um erro ao inserir o documento"),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }
}
