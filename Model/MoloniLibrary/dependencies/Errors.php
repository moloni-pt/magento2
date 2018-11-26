<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace MoloniLibrary;

class Errors
{
    private $error_log = array();
    
    public function hasError(){
        return !empty($this->error_log);
    }
    
    public function throwError($title, $message, $where, $received = false, $sent = false)
    {
        if (is_Array($message)) {
            foreach ($message as $msg) {
                $this->logError($title, $msg, $where, $received, $sent);
            }
        } else {
            $this->logError($title, $message, $where, $received, $sent);
        }
    }

    public function logError($title, $message, $where, $received = false, $sent = false)
    {
        $this->error_log[] = array(
            "title" => $title,
            "message" => $this->translateMessage($message),
            "where" => $where,
            "values" => array(
                "received" => $received,
                "sent" => $sent
            )
        );
    }

    // @params $order all|first|last
    public function getError($order = "all")
    {
        if ($this->error_log && is_array($this->error_log)) {
            switch ($order) {
                case "first" :
                    return $this->error_log[0];
                case "last" :
                    $aux = end($this->error_log);
                    return $aux;
                case "all":
                default:
                    return $this->error_log;
            }
        } else {
            return false;
        }
    }

    private function translateMessage($string)
    {
        switch ($string) {
            case "1 name" :
                $string = "Campo nome não pode estar em branco";
                break;
            case "1 number" :
                $string = "Campo number não pode estar em branco";
                break;
            case "2 maturity_date_id 1 0" :
                $string = "Defina um prazo de vencimento nas configurações do plugin";
                break;
            case "2 unit_id 1 0" :
                $string = "Unidade de medida errada";
                break;
            case "1 exemption_reason" :
                $string = "Um dos artigos requer uma razão de isenção";
                break;
            case "5 exemption_reason" :
                $string = "Um dos artigos não tem uma razão de isenção definida";
                break;
            case "5 document_set_id" :
                $string = "Não está definida a série onde quer emitir o documento";
                break;
            case "2 price 0 null null 0" :
                $string = "Um dos artigos tem o preço igual a 0";
                break;

            case "2 category_id 1 0" :
                $string = "Um dos artigos não tem uma categoria definida.";
                break;
        }
        return $string;
    }

}
