<?php
namespace VKR\PagerBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\PagerBundle\Services\Pager;
use VKR\PagerBundle\TestHelpers\PageableParser;
use VKR\SettingsBundle\Exception\SettingNotFoundException;
use VKR\SettingsBundle\Exception\WrongSettingTypeException;
use VKR\SettingsBundle\Services\SettingsRetriever;

class PagerTest extends TestCase
{
    private $settings = [
        'records_per_page' => 5,
        'invalid_setting' => 'foo',
    ];

    /**
     * @var PageableParser
     */
    private $parser;

    /**
     * @var Pager
     */
    private $pager;

    public function setUp()
    {
        $settingsRetriever = $this->mockSettingsRetriever();
        $this->parser = new PageableParser(11);
        $this->pager = new Pager($settingsRetriever);
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
        $this->expectException(WrongSettingTypeException::class);
        $this->pager->getPagerProps($this->parser, $uri, 'invalid_setting');
    }

    private function mockSettingsRetriever()
    {
        $settingsRetriever = $this->createMock(SettingsRetriever::class);
        $settingsRetriever->method('get')->will($this->returnCallback([$this, 'getMockedSettingValueCallback']));
        return $settingsRetriever;
    }

    public function getMockedSettingValueCallback($settingName)
    {
        if (isset($this->settings[$settingName])) {
            return $this->settings[$settingName];
        }
        throw new SettingNotFoundException($settingName);
    }
}
