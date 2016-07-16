<?php
namespace VKR\PagerBundle\Interfaces;

/**
 * Interface PageableInterface
 * Should be used by any data parser that uses pagination. Interface created to resolve the dependency cycle:
 * parser needs pager to get limit and offset, while pager needs parser to get total number of pages
 *
 * @package AppBundle\Interfaces
 */
interface PageableInterface
{
    /**
     * @param array $additionalArguments
     * @return int
     */
    public function getNumberOfRecords(array $additionalArguments = []);
}
