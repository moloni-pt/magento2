<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies;

class ApiErrors
{
    private array $error_log = [];

    public function hasError(): bool
    {
        return !empty($this->error_log);
    }

    public function throwError($title, $message, $where, $received = false, $sent = false): bool
    {
        if (is_array($message)) {
            foreach ($message as $msg) {
                $this->logError($title, $msg, $where, $received, $sent);
            }
        } else {
            $this->logError($title, $message, $where, $received, $sent);
        }

        return false;
    }

    public function getErrors($order = "all")
    {
        if ($this->error_log && is_array($this->error_log)) {
            switch ($order) {
                case "first":
                    return $this->error_log[0];
                case "last":
                    return end($this->error_log);
                case "all":
                default:
                    return $this->error_log;
            }
        } else {
            return false;
        }
    }

    public function clearErrors(): void
    {
        $this->error_log = [];
    }

    private function logError($title, $message, $where, $received = false, $sent = false): void
    {
        $this->error_log[] = [
            "title" => $title,
            "message" => $this->translateMessage($message),
            "where" => $where,
            "values" => [
                "received" => $received,
                "sent" => $sent
            ]
        ];
    }

    private function translateMessage($string): string
    {
        switch ($string) {
            case "1 name":
                $string = "Campo nome não pode estar em branco";
                break;
            case "1 number":
                $string = "Campo number não pode estar em branco";
                break;
            case "2 maturity_date_id 1 0":
                $string = "Defina um prazo de vencimento nas configurações do plugin";
                break;
            case "2 unit_id 1 0":
                $string = "Unidade de medida errada";
                break;
            case "1 exemption_reason":
                $string = "Um dos artigos requer uma razão de isenção";
                break;
            case "5 exemption_reason":
                $string = "Um dos artigos não tem uma razão de isenção definida";
                break;
            case "5 document_set_id":
                $string = "Não está definida a série onde quer emitir o documento";
                break;
            case "2 price 0 null null 0":
                $string = "Um dos artigos tem o preço igual a 0";
                break;

            case "2 category_id 1 0":
                $string = "Um dos artigos não tem uma categoria definida.";
                break;
        }

        if (strpos($string, '1 exemption_reason')) {
            $string = "Um dos artigos requer uma razão de isenção";
        }

        return $string;
    }
}
