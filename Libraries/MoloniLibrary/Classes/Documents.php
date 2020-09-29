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
    private $store = [];
    public $documentTypeId = 1;
    public $documentTypeName = "Fatura";
    public $documentTypeClass = "invoices";
    public $documentTypeClassMoloni = "Faturas";


    /**
     * @param Moloni $moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }


    /**
     * @param bool|string $documentType
     * @return $this
     */
    public function setDocumentType($documentType = false)
    {
        if (!$documentType) {
            $documentType = $this->moloni->settings['document_type'];
        }


        switch ($documentType) {
            case 'documents':
            case '-1':
                $this->documentTypeId = -1;
                $this->documentTypeClass = "documents";
                break;
            case "invoices":
            case "Fatura":
            case "Faturas":
            case "1":
                $this->documentTypeId = 1;
                $this->documentTypeClass = "invoices";
                $this->documentTypeName = 'Fatura';
                $this->documentTypeClassMoloni = 'Faturas';
                break;

            case "invoiceReceipts":
            case "Fatura/Recibo":
            case "FaturasRecibo":
            case "27":
                $this->documentTypeId = 27;
                $this->documentTypeClass = "invoiceReceipts";
                $this->documentTypeName = 'Fatura/Recibo';
                $this->documentTypeClassMoloni = 'FaturasRecibo';
                break;

            case "simplifiedInvoices":
            case "Fatura Simplificada":
            case "FaturaSimplificada":
            case "21":
                $this->documentTypeId = 21;
                $this->documentTypeClass = "simplifiedInvoices";
                $this->documentTypeName = 'Fatura Simplificada';
                $this->documentTypeClassMoloni = 'FaturaSimplificada';
                break;

            case "billsOfLading":
            case "Guia de Transporte":
            case "GuiasTransporte":
            case "15":
                $this->documentTypeId = "15";
                $this->documentTypeClass = "billsOfLading";
                $this->documentTypeName = 'Guia de Transporte';
                $this->documentTypeClassMoloni = 'GuiasTransporte';
                break;

            case "deliveryNotes":
            case "Nota de encomenda":
            case "NotasEncomenda":
            case "28":
                $this->documentTypeId = "28";
                $this->documentTypeClass = "deliveryNotes";
                $this->documentTypeName = 'Nota de encomenda';
                $this->documentTypeClassMoloni = 'NotasEncomenda';
                break;

            case "estimates":
            case "Orçamento":
            case "Orcamentos":
            case "14":
                $this->documentTypeId = "14";
                $this->documentTypeClass = "estimates";
                $this->documentTypeName = 'Orçamento';
                $this->documentTypeClassMoloni = 'Orcamentos';
                break;

            case "receipts":
            case "Recibos":
            case "Recibo":
            case "2":
                $this->documentTypeId = "2";
                $this->documentTypeClass = "receitps";
                $this->documentTypeName = 'Recibo';
                $this->documentTypeClassMoloni = 'Recibos';
                break;

            case "creditNotes":
            case "NotasCredito":
            case "NotaCredito":
            case "3":
                $this->documentTypeId = "3";
                $this->documentTypeClass = "creditNotes";
                $this->documentTypeName = 'Nota de crédito';
                $this->documentTypeClassMoloni = 'NotasCredito';
                break;
        }

        return $this;
    }

    /**
     * @param int $documentId
     * @return string
     */
    public function getEditUrl($documentId)
    {
        $company = $this->moloni->companies->getOne();
        $url = "https://www.moloni.pt/" . $company['slug'] . "/" . $this->documentTypeClassMoloni . "/showUpdate/" .
            $documentId;

        return $url;
    }

    /**
     * @param int $documentId
     * @return string
     */
    public function getViewUrl($documentId)
    {
        $company = $this->moloni->companies->getOne();
        $url = "https://www.moloni.pt/" . $company['slug'] . "/" . $this->documentTypeClassMoloni . "/showDetail/" .
            $documentId;

        return $url;
    }

    public function getDownloadUrl($values, $companyId = false)
    {
        $values['company_id'] = ($companyId ? $companyId : $this->moloni->session->companyId);
        $result = $this->moloni->execute("documents/getPdfLink", $values);
        if (is_array($result) && isset($result['url'])) {
            return $result['url'];
        } else {
            $this->moloni->errors->throwError(
                __("Falhou a obter o documento para download " . $values['document_id']),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }

    /**
     * @param array $values
     * @param bool|int $companyId
     * @return bool|mixed
     */
    public function getAll($values = [], $companyId = false)
    {
        $values["company_id"] = $companyId ? $companyId : $this->moloni->session->companyId;

        $result = $this->moloni->execute($this->documentTypeClass . "/getAll", $values);
        if (is_array($result) && isset($result[0]['document_id'])) {
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Não tem acesso à informação dos documentos"),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }

    public function getOne($values, $companyId = false)
    {
        if (!isset($values['document_id'])) {
            return false;
        }

        if (isset($this->store[$values['document_id']])) {
            return $this->store[$values['document_id']];
        }

        $values['company_id'] = ($companyId ? $companyId : $this->moloni->session->companyId);
        $result = $this->moloni->execute("documents/getOne", $values);

        $this->store[$values['document_id']] = $result;

        if (is_array($result) && isset($result['document_id'])) {
            return $result;
        } else {
            $this->moloni->errors->throwError(
                __("Não tem acesso à informação do documento com id " . $values['document_id']),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }

    /**
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
                __("Erro ao inserir o documento: " . $this->getErrorMessage($result)),
                __(json_encode($result, JSON_PRETTY_PRINT)),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }
    }

    /**
     * @param array $values
     * @param bool|int $companyId
     * @return bool|array
     */
    public function update($values, $companyId = false)
    {
        $values['company_id'] = ($companyId ? $companyId : $this->moloni->session->companyId);
        $result = $this->moloni->execute($this->documentTypeClass . "/update", $values);

        if (is_array($result) && isset($result['document_id'])) {
            return $result;
        }

        $this->moloni->errors->throwError(
            __("Erro ao fechar o documento: " . $this->getErrorMessage($result)),
            __(json_encode($result, JSON_PRETTY_PRINT)),
            __CLASS__ . "/" . __FUNCTION__
        );
        return false;
    }

    public function getErrorMessage($result)
    {
        $message = $this->searchErrorMessage($result);

        switch ($message) {
            case "Field 'exemption_reason' is required":
                $message = __("Razão de isenção não definida. Verifique as taxas dos artigos ou a razão de isenção");
                break;
        }

        return $message;
    }

    private function searchErrorMessage($result)
    {
        if (isset($result['description'])) {
            return $result['description'];
        }

        if (is_array($result)) {
            foreach ($result as $item) {
                if (isset($item['description'])) {
                    return $item['description'];
                } else {
                    return $this->searchErrorMessage($item);
                }
            }
        }

        return '';
    }
}
