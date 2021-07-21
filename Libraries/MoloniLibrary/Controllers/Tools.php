<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class Tools
{

    private $moloni;

    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    /**
     * Regex expression to validate a Portuguese Zip Code
     * @var string
     */
    private $regexZip = "/[0-9]{4}\-[0-9]{3}/";


    /**
     * Get a country ID given a country ISO code
     * At the moment we only support ISO2 codes
     * @param $iso
     * @param int $chars
     * @return int
     */
    public function getCountryIdByISO($iso, int $chars = 2): int
    {
        $countryId = 1;

        $countries = $this->moloni->countries->getAll();
        if ($countries && is_array($countries)) {
            $countryISO = mb_strtoupper($iso);
            foreach ($countries as $country) {
                if ($chars === 2 && $countryISO === mb_strtoupper($country['iso_3166_1'])) {
                    $countryId = $country['country_id'];
                    break;
                }
            }
        }

        return $countryId;
    }

    /**
     * Validates a Portuguese VAT number
     * @param string $vatNumber
     * @return bool
     */
    public function validateVat(string $vatNumber): bool
    {
        if (preg_match('/^[123456789]\d{8}$/', $vatNumber)) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += $vatNumber[$i] * (10 - ($i + 1));
            }

            if (((int)$vatNumber[8] === 0) && ($sum % 11) !== 0) {
                $sum += 10;
            }

            if (($sum % 11) !== 0) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Validates a given Zip Code to check if the zip is valid in Portugal
     * Checks the number of inserted digits and tries to form a valid zip code
     * If in the end the zip code is not valid it returns the default 1000-100
     * @param string $input
     * @return string
     */
    public function zipCheck(string $input): string
    {
        $zip = trim(str_replace(" ", "", $input));
        $zip = preg_replace("/[^0-9]/", "", $zip);

        if (strlen($zip) === 7) {
            $zip = $zip[0] . $zip[1] . $zip[2] . $zip[3] . "-" . $zip[4] . $zip[5] . $zip[6];
        }

        if (strlen($zip) === 6) {
            $zip = $zip[0] . $zip[1] . $zip[2] . $zip[3] . "-" . $zip[4] . $zip[5] . "0";
        }

        if (strlen($zip) === 5) {
            $zip = $zip[0] . $zip[1] . $zip[2] . $zip[3] . "-" . $zip[4] . "00";
        }

        if (strlen($zip) === 4) {
            $zip .= "-" . "000";
        }

        if (strlen($zip) === 3) {
            $zip .= "0-" . "000";
        }

        if (strlen($zip) === 2) {
            $zip .= "00-" . "000";
        }

        if (strlen($zip) === 1) {
            $zip .= "000-" . "000";
        }

        if ($zip === '') {
            $zip = "1000-100";
        }

        return preg_match($this->regexZip, $zip) ? $zip : '1000-100';
    }
}
