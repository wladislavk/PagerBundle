<?php
namespace VKR\PagerBundle\TestHelpers;

use VKR\PagerBundle\Interfaces\PageableInterface;

class PageableParser implements PageableInterface
{
    /**
     * @var int
     */
    private $numberOfRecords;

    public function __construct($numberOfRecords)
    {
        $this->numberOfRecords = $numberOfRecords;
    }

    public function getNumberOfRecords(array $additionalArguments = [])
    {
        if (array_key_exists('add', $additionalArguments)) {
            return $this->numberOfRecords + $additionalArguments['add'];
        }
        return $this->numberOfRecords;
    }
}
