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

    public function formAuthenticationUrl()
    {
        $this->tokens = $this->moloni->_tokens->getTokens();
        if (!empty($this->tokens->getDeveloperId()) && !empty($this->tokens->getRedirectUri())) {
            return $this->moloni::API_URL . 'authorize/?response_type=code&client_id=' . $this->tokens->getDeveloperId() . '&redirect_uri=' . urlencode($this->tokens->getRedirectUri());
        } else {
            return false;
        }
    }

    public function doAuthorization($code)
    {
        if ($this->tokens && $this->tokens->getDeveloperId()) {
            $authorizationUrl = 'grant/?grant_type=authorization_code&client_id=' . $this->tokens->getDeveloperId() . '&redirect_uri=' . urlencode($this->tokens->getRedirectUri()) . '&client_secret=' . $this->tokens->getSecretToken() . '&code=' . $code;
            $response = $this->moloni->execute($authorizationUrl);
            
            if (isset($response['error'])) {
                return $this->moloni->errors->throwError(__('Erro de autenticação'), __('Ocorreu um erro durante a operação de autenticação<br>'.$response['error']), $authorizationUrl, $response);
            } else {
                $this->tokens->setAccessToken($response['access_token']);
                $this->tokens->setRefreshToken($response['refresh_token']);
                $this->tokens->setExpireDate($this->moloni->_dateTime->formatDate((time() + 3000), true));
                $this->tokens->setLoginDate($this->moloni->_dateTime->formatDate(true, true));

                $this->tokens->save();

                return true;
            }
        }

        return false;
    }

    public function validateSession()
    {
        if (!$this->handleSessionRefresh()) {
            $this->moloni->redirectTo = 'moloni/home/welcome/';
        } else {
            if (!$this->tokens->getCompanyId() && $this->moloni->_request->getActionName() !== 'company') {
                $this->moloni->redirectTo = 'moloni/home/company/';
            } else {
                return $this->tokens->toArray();
            }
        }

        if ($this->moloni->errors->hasError()) {
            $errorMessage = array(array('type' => 'error', 'message' => $this->moloni->errors->getError('last')['message']));
            $this->moloni->_dataPersistor->set('moloni_messages', $errorMessage);
        }

        return false;
    }

    private function handleSessionRefresh()
    {
        if ($this->tokens && $this->tokens->getAccessToken()) {
            $currentTime = time();
            $accessTokenExpireDate = $this->moloni->_dateTime->strToTime($this->tokens->getExpireDate());
            $refreshTokenExpireDate = $accessTokenExpireDate + 432000; // Add 5 days until the refresh expires

            if ($currentTime > $accessTokenExpireDate) {
                if ($currentTime > $refreshTokenExpireDate) {
                    $this->tokens->delete();
                    return $this->moloni->errors->throwError(__('Erro de sessão'), __('A sessão expirou no dia ' . $this->moloni->_dateTime->formatDate($refreshTokenExpireDate, true)), 'Refresh');
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
            $refreshUrl = 'grant/?grant_type=refresh_token&client_id=' . $this->tokens->getDeveloperId() . '&client_secret=' . $this->tokens->getSecretToken() . '&refresh_token=' . $this->tokens->getRefreshToken();
            $response = $this->moloni->execute($refreshUrl);

            if (isset($response['error'])) {
                return $this->moloni->errors->throwError(__('Erro de autenticação'), __('Ocorreu um erro ao refrescas as chaves de sessão'), $refreshUrl, $response);
            } else {
                $this->tokens->setAccessToken($response['access_token']);
                $this->tokens->setRefreshToken($response['refresh_token']);
                $this->tokens->setExpireDate($this->moloni->_dateTime->formatDate((time() + 3000), true));
                $this->tokens->setLoginDate($this->moloni->_dateTime->formatDate(true, true));

                $this->tokens->save();

                return true;
            }
        } else {
            return false;
        }
    }
}
