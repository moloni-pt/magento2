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

use Invoicing\Moloni\Controller\Adminhtml\Home;

class Welcome extends Home
{

    public function execute()
    {
        $page = $this->initAction();
        if ($this->getRequest()->getPostValue("developer_id") && $this->getRequest()->getPostValue('secret_token')) {
            $this->handleAuthentication();
        } elseif ($this->getRequest()->getParam("code")) {
            if (!$this->moloni->checkAuthorizationCode($this->getRequest()->getParam('code'))) {
                $this->messageManager->addErrorMessage($this->moloni->errors->getErrors('last')['message']);
            } else {
                $this->_redirect->redirect($this->_response, 'moloni/home/company/');
            }
        }
        return $page;
    }

    private function handleAuthentication()
    {
        $tokens = $this->tokensRepository->getTokens();

        $tokens->setDeveloperId($this->getRequest()->getPostValue('developer_id'));
        $tokens->setRedirectUri($this->getRequest()->getPostValue('redirect_uri'));
        $tokens->setSecretToken($this->getRequest()->getPostValue('secret_token'));
        $tokens->save();

        $authenticationUrl = $this->moloni->getAuthenticationUrl();
        if ($authenticationUrl) {
            $this->_redirect($authenticationUrl);
        } else {
            $this->messageManager->addErrorMessage(__("Houve um erro ao guardar alterações"));

        }
    }
}
