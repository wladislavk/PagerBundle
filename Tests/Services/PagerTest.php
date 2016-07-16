<?php
namespace VKR\PagerBundle\Tests\Services;

use VKR\PagerBundle\Services\Pager;
use VKR\PagerBundle\TestHelpers\PageableParser;
use VKR\SettingsBundle\Exception\SettingNotFoundException;
use VKR\SettingsBundle\Exception\WrongSettingTypeException;
use VKR\SettingsBundle\Services\SettingsRetriever;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    protected $settings = [
        'records_per_page' => 5,
        'invalid_setting' => 'foo',
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $settingsRetriever;

    /**
     * @var PageableParser
     */
    protected $parser;

    /**
     * @var Pager
     */
    protected $pager;

    public function setUp()
    {
        $this->mockSettingsRetriever();
        $this->parser = new PageableParser(11);
        $this->pager = new Pager($this->settingsRetriever);
    }

    public function testGetPagerProps()
    {
        $uri = 'http://test.com?a=1&b=2';
        $pagerProps = $this->pager->getPagerProps($this->parser, $uri, 10);
        $this->assertEquals(1, $pagerProps->getCurrentPage());
        $this->assertEquals('http://test.com?a=1&b=2&', $pagerProps->getUriWithoutPage());
        $this->assertEquals(10, $pagerProps->getRecordsPerPage());
        $this->assertEquals(0, $pagerProps->getFirstResult());
        $this->assertEquals(2, $pagerProps->getNumberOfPages());
    }

    public function testGetPagerPropsWithPageNumber()
    {
        $uri = 'http://test.com?page=2';
        $pagerProps = $this->pager->getPagerProps($this->parser, $uri, 10);
        $this->assertEquals(2, $pagerProps->getCurrentPage());
        $this->assertEquals('http://test.com?', $pagerProps->getUriWithoutPage());
        $this->assertEquals(10, $pagerProps->getFirstResult());
    }

    public function testDisablePagination()
    {
        $uri = 'http://test.com?page=2';
        $pagerProps = $this->pager->getPagerProps($this->parser, $uri, -1);
        $this->assertEquals(0, $pagerProps->getFirstResult());
    }

    public function testGetPagerPropsWithSetting()
    {
        $uri = 'http://test.com?page=2&a=1';
        $pagerProps = $this->pager->getPagerProps($this->parser, $uri, 'records_per_page');
        $this->assertEquals(2, $pagerProps->getCurrentPage());
        $this->assertEquals('http://test.com?a=1&', $pagerProps->getUriWithoutPage());
        $this->assertEquals($this->settings['records_per_page'], $pagerProps->getRecordsPerPage());
        $this->assertEquals(5, $pagerProps->getFirstResult());
        $this->assertEquals(3, $pagerProps->getNumberOfPages());
    }

    public function testGetPagerPropsWithAdditionalArguments()
    {
        $uri = 'http://test.com?page=2&a=1';
        $additionalArguments = [
            'add' => 5,
        ];
        $pagerProps = $this->pager->getPagerProps($this->parser, $uri, 'records_per_page', $additionalArguments);
        $this->assertEquals(4, $pagerProps->getNumberOfPages());
    }

    public function testGetPagerPropsWithBadSetting()
    {
        $uri = 'http://test.com?page=2&a=1';
        $exceptionReflection = new \ReflectionClass(WrongSettingTypeException::class);
        $this->setExpectedException($exceptionReflection->getName());
        $pagerProps = $this->pager->getPagerProps($this->parser, $uri, 'invalid_setting');
    }

    protected function mockSettingsRetriever()
    {
        $this->settingsRetriever = $this
            ->getMockBuilder(SettingsRetriever::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsRetriever->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'getMockedSettingValueCallback']));
    }

    public function getMockedSettingValueCallback($settingName)
    {
        if (isset($this->settings[$settingName])) {
            return $this->settings[$settingName];
        }
        throw new SettingNotFoundException($settingName);
    }
}
