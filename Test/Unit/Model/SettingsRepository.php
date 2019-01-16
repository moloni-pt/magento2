<?php
/**
 * Created by PhpStorm.
 * User: nuno_
 * Date: 15/01/2019
 * Time: 22:58
 */

namespace Invoicing\Moloni\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use function PHPSTORM_META\type;
use PHPUnit\Framework\TestCase;

class Calculator extends TestCase
{
    private $objectManager;
    private $desiredResult;
    private $actulResult;
    private $settings;

    /**
     * unset the variables and objects after use
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * used to set the values to variables or objects.
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->settings = $this->objectManager->getObject("Invoicing\Moloni\Model\SettingsRepository");
        //can do stuff
    }

    /**
     * this function will perform the addition of two numbers
     *
     * @param float $a
     * @param float $b
     * @return float
     */
    public function testGetSettingsByCompany()
    {
        $this->actulResult = $this->settings->newOption();

        $this->desiredResult = false;
        $this->assertEquals($this->desiredResult, $this->actulResult);
        return true;
    }
}
