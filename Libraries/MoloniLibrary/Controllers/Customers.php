<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Sales\Api\Data\OrderInterface;

class Customers
{

    public $customerId = false;
    public $alternateAddressId = false;

    /**
     * Holds the default values
     * @var array
     */
    private $defaults = [
        'vat' => '999999990',
        'name' => 'Cliente',
        'country_id' => 1,
        'language_id' => 1,
        'address' => 'Desoonhecido',
        'zip_code' => '1000-100',
        'city' => 'Desconhecida',
        'email' => '',
        'phone' => '',
        'fax' => '',
        'contact_name' => '',
        'contact_email' => '',
        'contact_phone' => '',
        'notes' => '',
        'salesman_id' => 0,
        'price_class_id' => 0,
        'maturity_date_id' => 0,
        'payment_day' => 0,
        'discount' => 0,
        'credit_limit' => 0,
        'copies' => 2,
        'payment_method_id' => 0,
        'delivery_method_id' => 0
    ];

    /**
     * Holds the values to be inserted/update
     * @var array
     */
    private $customer = [];

    /**
     * Hold an existing Moloni customer
     * @var array
     */
    private $moloniCustomer = [];

    /**
     * Assign a countryId to a languageId (1, 2 or 3)
     * If the countryId is not defined it shoud be 2 (english)
     * @var array
     */
    public $languageByCountry = [
        1 => 1 // Portugal > Portuguese
    ];

    /**
     * @var Moloni
     */
    private $moloni;

    /**
     * @var Tools
     */
    private $tools;

    public function __construct(
        Moloni $moloni,
        Tools $tools
    )
    {
        $this->moloni = $moloni;
        $this->tools = $tools;
    }


    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     */
    public function setCustomerFromOrder(OrderInterface $order)
    {
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            // First lets get the country id to know if we should format the vat number
            $countryCode = $billingAddress->getCountryId();
            $this->customer['country_id'] = $this->tools->getCountryIdByISO($countryCode);
            $this->customer['vat'] = $billingAddress->getVatId();
            $this->customer['email'] = $billingAddress->getEmail();

            $company = $billingAddress->getCompany();
            $name = $billingAddress->getFirstname();
            $name .= empty($billingAddress->getMiddlename()) ? '' : ' ' . $billingAddress->getMiddlename();
            $name .= empty($billingAddress->getLastname()) ? '' : ' ' . $billingAddress->getLastname();

            if (!empty($company)) {
                $this->customer['name'] = $company;
                $this->customer['contact_name'] = $name;
            } else {
                $this->customer['name'] = $name;
            }

            $street = $billingAddress->getStreet();
            if ($street && is_array($street)) {
                $address = implode(' ', $street);
                $this->customer['address'] = !empty($address) ? $address : $this->customer['address'];
            }

            if (!empty($billingAddress->getCity())) {
                $this->customer['city'] = $billingAddress->getCity();
            }

            if (!empty($billingAddress->getPostcode())) {
                $this->customer['zip_code'] = $billingAddress->getPostcode();
            }

            if (!empty($billingAddress->getTelephone())) {
                $this->customer['phone'] = $billingAddress->getTelephone();
            }

            if (!empty($billingAddress->getFax())) {
                $this->customer['fax'] = $billingAddress->getFax();
            }

            if (isset($this->languageByCountry[$this->customer['country_id']])) {
                $this->customer['languageId'] = $this->languageByCountry[$this->customer['country_id']];
            }
        }

        $this->parseCustomer();
        $this->handleCustomer();

        return $this;
    }

    private function parseCustomer()
    {
        if (empty($this->customer['vat'])) {
            $this->customer['vat'] = '999999990';
        }

        // If the country is Portugal validate the vat Number and the Zip Code
        if ($this->customer['country_id'] == 1) {
            $this->customer['zip_code'] = $this->tools->zipCheck($this->customer['zip_code']);
            $this->customer['vat'] = str_replace(' ', '', $this->customer['vat']);
            if (!$this->tools->validateVat($this->customer['vat'])) {
                $this->customer['vat'] = '999999990';
            }
        }

        // Lets check if the customer already exists
        // If the vat is 999 999 990 we check if it exists by email
        if ($this->customer['vat'] == '999999990') {
            if (!empty($this->customer['email'])) {
                $getCustomer = $this->moloni->customers->getByEmail(['email' => $this->customer['email']]);
                if ($getCustomer && count($getCustomer) > 0) {
                    $this->moloniCustomer = $getCustomer[0];
                }
            }
        } else {
            // Search for the customer by VAT
            if (!empty($this->customer['vat'])) {
                $getCustomer = $this->moloni->customers->getByVat(['vat' => $this->customer['vat']]);
                if ($getCustomer && count($getCustomer) > 0) {
                    $this->moloniCustomer = $getCustomer[0];
                }
            }
        }

        return true;
    }

    private function handleCustomer()
    {
        // Customer does not exist lets insert it
        if (empty($this->moloniCustomer)) {
            $nextCustomerNumber = $this->moloni->customers->getNextNumber();
            $this->moloniCustomer = array_merge($this->defaults, $this->customer);
            $this->moloniCustomer['number'] = !empty($nextCustomerNumber) ? $nextCustomerNumber : '001';
            $customerInsert = $this->moloni->customers->insert($this->moloniCustomer);
            if ($customerInsert && isset($customerInsert['customer_id'])) {
                $this->customerId = $customerInsert['customer_id'];
            }
        } else {
            // If the customer exists we update the customer
            // @todo manage the alternate addresses in here
            // Lets update only what it matters

            $this->customer['customer_id'] = $this->moloniCustomer['customer_id'];
            $this->moloniCustomer = array_merge($this->moloniCustomer, $this->customer);
            $customerUpdate = $this->moloni->customers->update($this->moloniCustomer);
            if ($customerUpdate && isset($customerUpdate['customer_id'])) {
                $this->customerId = $customerUpdate['customer_id'];
            }
        }
    }

}
