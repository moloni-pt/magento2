<?php
/**
 * Module for Magento 2 by Moloni
 * Copyright (C) 2017  Moloni, lda
 *
 * This file is part of Invoicing/Moloni.
 *
 * Invoicing/Moloni is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Invoicing\Moloni\Controller\Adminhtml\Home;

use Exception;
use Invoicing\Moloni\Controller\Adminhtml\Home;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Welcome extends Home
{

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     *
     * @throws Exception
     */
    public function execute()
    {
        $page = $this->initAction();

        if ($this->request->getParam("developer_id") && $this->request->getParam('secret_token')) {
            $this->handleAuthentication();
        } elseif ($this->request->getParam("code")) {
            if (!$this->moloni->checkAuthorizationCode($this->request->getParam('code'))) {
                $this->messageManager->addErrorMessage($this->moloni->errors->getErrors('last')['message']);
            } else {
                return $this->redirectFactory->create()->setPath('moloni/home/company/');
            }
        } else {
            $this->moloni->dropActiveSession();
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        }

        return $page;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function handleAuthentication(): void
    {
        $tokens = $this->tokensRepository->getTokens();

        $tokens->setDeveloperId($this->request->getParam('developer_id'));
        $tokens->setRedirectUri($this->request->getParam('redirect_uri'));
        $tokens->setSecretToken($this->request->getParam('secret_token'));
        $tokens->save();

        $authenticationUrl = $this->moloni->getAuthenticationUrl();
        if ($authenticationUrl) {
            $this->redirect->redirect($this->response, $authenticationUrl);
        } else {
            $this->messageManager->addErrorMessage(__("Houve um erro ao guardar alterações"));
        }
    }
}
