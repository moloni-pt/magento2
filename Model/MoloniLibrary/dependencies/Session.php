<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace MoloniLibrary;

use Invoicing\Moloni\Model\Moloni;

class Session
{

    public $hasValidSession = false;
    public $tokens;

    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
        $this->tokens = $this->moloni->_tokens->getTokens();
    }

    public function validateSession()
    {
        if (!$this->hasValidSession()) {
            $this->moloni->redirectTo = 'moloni/home/welcome/';
        } else {
            if (!$this->tokens->getCompanyId() && $this->moloni->_request->getControllerName() !== 'company') {
                $this->moloni->redirectTo = 'moloni/home/company/';
            }else{
                $this->hasValidSession = true;
            }
        }

        if ($this->moloni->errors->hasError()) {
            $errorMessage = array(array('type' => 'error', 'message' => $this->errors->getError('last')['message']));
            $this->_dataPersistor->set('moloni_messages', $errorMessage);
        }

        return $this->hasValidSession;
    }

    private function hasValidSession()
    {
        if ($this->tokens && $this->tokens->getAccessToken()) {
            $currentTime = time();
            $accessTokenExpireDate = $this->moloni->_dateTime->strToTime($this->tokens->getExpireDate());
            $refreshTokenExpireDate = $accessTokenExpireDate + 432000; // Add 5 days until the refresh expires

            if ($currentTime > $accessTokenExpireDate) {
                if ($currentTime > $refreshTokenExpireDate) {
                    $this->moloni->errors->throwError(
                        __('Erro de sessão'), __('A sessão expirou no dia ' . $this->moloni->_dateTime->formatDate($refreshTokenExpireDate, true)), 'Refresh');
                    $this->tokens->delete();
                    return false;
                } else {
                    if ($this->doRefresh()) {
                        return true;
                    } else {
                        $this->tokens->delete();
                        return false;
                    }
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function doRefresh()
    {
        if ($this->tokens && $this->tokens->getRefreshToken()) {
            $refreshUrl = self::API_URL . 'grant/?grant_type=refresh_token&client_id=' . $this->tokens->getDeveloperId() . '&client_secret=' . $this->tokens->getSecretToken() . '&refresh_token=' . $this->tokens->getRefreshToken();
            $response = $this->execute($refreshUrl);

            if (isset($response['error'])) {
                $this->errors->throwError(__('Erro de autenticação'), __('Ocorreu um erro ao refrescas as chaves de sessão'), $refreshUrl, $response);
                return false;
            } else {
                $this->tokens->setAccessToken($response['access_token']);
                $this->tokens->setRefreshToken($response['refresh_token']);
                $this->tokens->setExpireDate($this->_dateTime->formatDate((time() + 3000), true));
                $this->tokens->setLoginDate($this->_dateTime->formatDate(true, true));

                $this->tokens->save();
               
                return true;
            }
        } else {
            return false;
        }
    }
}
