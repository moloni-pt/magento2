<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies;

use Invoicing\Moloni\Model\TokensRepository;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\RequestInterface;

class ApiSession
{
    const API_URL = 'https://api.moloni.pt/v1/';

    private $dateTime;
    private $curl;
    private $errors;
    private $tokens;
    private $dataPersistor;
    private $request;
    public $accessToken = null;
    public $companyId = null;

    /**
     * Override the parent constructor.
     * @param ApiErrors $errors
     * @param TokensRepository $tokens
     * @param DataPersistorInterface $dataPersistant
     * @param RequestInterface $request
     * @param DateTime $dateTime
     * @param Curl $curl
     */
    public function __construct(
        ApiErrors $errors,
        TokensRepository $tokens,
        DataPersistorInterface $dataPersistant,
        RequestInterface $request,
        DateTime $dateTime,
        Curl $curl
    ) {
        $this->errors = $errors;
        $this->tokens = $tokens;
        $this->dataPersistor = $dataPersistant;
        $this->request = $request;
        $this->dateTime = $dateTime;
        $this->curl = $curl;
    }

    /**
     * @param $code
     * @return bool
     */
    public function isValidAuthorizationCode($code)
    {
        $tokens = $this->tokens->getTokens();
        if ($tokens && $tokens->getDeveloperId()) {
            $authorizationUrl = self::API_URL;
            $authorizationUrl .= 'grant/?grant_type=authorization_code';
            $authorizationUrl .= '&client_id=' . $tokens->getDeveloperId();
            $authorizationUrl .= '&client_secret=' . $tokens->getSecretToken();
            $authorizationUrl .= '&redirect_uri=' . urlencode($tokens->getRedirectUri());
            $authorizationUrl .= '&code=' . $code;

            $this->curl->get($authorizationUrl);
            $rawResponse = $this->curl->getBody();

            $response = json_decode($rawResponse);

            if (isset($response->error)) {
                return $this->errors->throwError(
                    __('Erro de autenticação'),
                    __('Ocorreu um erro durante a operação de autenticação<br>' . $response->error),
                    $authorizationUrl,
                    $response
                );
            } else {
                $tokens->setAccessToken($response->access_token);
                $tokens->setRefreshToken($response->refresh_token);
                $tokens->setExpireDate($this->dateTime->formatDate((time() + 3000), true));
                $tokens->setLoginDate($this->dateTime->formatDate(true, true));

                $tokens->save();

                return true;
            }
        }

        return false;
    }

    public function isValidSession()
    {
        if ($this->handleSessionRefresh()) {
            $tokens = $this->tokens->getTokens();
            $this->accessToken = $tokens->getAccessToken();
            $this->companyId = $tokens->getCompanyId();
            return true;
        }

        if ($this->errors->hasError()) {
            $errorMessage = [['type' => 'error', 'message' => $this->errors->getErrors('last')['message']]];
            $this->dataPersistor->set('moloni_messages', $errorMessage);
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function handleSessionRefresh()
    {
        $tokens = $this->tokens->getTokens();
        if ($tokens && $tokens->getAccessToken()) {
            $currentTime = time();
            $accessTokenExpireDate = $this->dateTime->strToTime($tokens->getExpireDate());
            $refreshTokenExpireDate = $accessTokenExpireDate + 432000; // Add 5 days until the refresh expires

            if ($currentTime > $accessTokenExpireDate) {
                if ($currentTime > $refreshTokenExpireDate) {
                    $tokens->delete();
                    return $this->errors->throwError(
                        __('Erro de sessão'),
                        __('A sessão expirou no dia ') . $this->dateTime->formatDate($refreshTokenExpireDate, true),
                        'Refresh'
                    );
                } else {
                    if ($this->doRefresh()) {
                        return true;
                    } else {
                        $tokens->delete();
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

    /**
     * @return bool
     */
    private function doRefresh()
    {
        $tokens = $this->tokens->getTokens();
        if (!empty($tokens) && $tokens->getRefreshToken()) {
            $refreshUrl = self::API_URL;
            $refreshUrl .= 'grant/?grant_type=refresh_token&';
            $refreshUrl .= 'client_id=' . $tokens->getDeveloperId();
            $refreshUrl .= '&client_secret=' . $tokens->getSecretToken();
            $refreshUrl .= '&refresh_token=' . $tokens->getRefreshToken();

            $this->curl->get($refreshUrl);
            $rawResponse = $this->curl->getBody();

            $response = json_decode($rawResponse);

            if (isset($response->error)) {
                return $this->errors->throwError(
                    __('Erro de autenticação'),
                    __('Ocorreu um erro ao refrescar as chaves de sessão'),
                    $refreshUrl,
                    $response
                );
            } else {
                $tokens->setAccessToken($response->access_token);
                $tokens->setRefreshToken($response->refresh_token);
                $tokens->setExpireDate($this->dateTime->formatDate((time() + 3000), true));
                $tokens->setLoginDate($this->dateTime->formatDate(true, true));

                $tokens->save();

                return true;
            }
        } else {
            return false;
        }
    }
}
