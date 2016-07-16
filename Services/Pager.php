<?php
namespace VKR\PagerBundle\Services;

use VKR\PagerBundle\Interfaces\PageableInterface;
use VKR\SettingsBundle\Exception\WrongSettingTypeException;
use VKR\SettingsBundle\Services\SettingsRetriever;
use VKR\PagerBundle\Entity\Perishable\PagerProps;

/**
 * Class Pager
 * Class for retrieving pagination-based data. Depends on a parser class linked to a certain entity to operate.
 * Creating an object of this class and passing its data to the template is necessary for inclusion of
 * pager.html.twig macro and Pager JS model
 */
class Pager
{
    /**
     * @var SettingsRetriever
     */
    protected $settingsRetriever;

    /**
     * @param SettingsRetriever $settingsRetriever
     */
    public function __construct(SettingsRetriever $settingsRetriever)
    {
        $this->settingsRetriever = $settingsRetriever;
    }

    /**
     * Main function that collects and returns basic pager props: current page number, number of records per
     * page and position of SQL offset. Note that this is not enough for pager to operate, a call to
     * getNumberOfPages() is needed
     *
     * @param PageableInterface $parser
     * @param string $requestUri
     * @param int|string $recordsPerPageData
     * @param array $additionalArguments
     * @return PagerProps
     * @throws \Exception
     */
    public function getPagerProps(
        PageableInterface $parser,
        $requestUri,
        $recordsPerPageData,
        array $additionalArguments = []
    ) {
        $currentPage = $this->getCurrentPage($requestUri);
        $uriWithoutPage = $this->getCurrentUriWithoutPage($requestUri);
        $recordsPerPage = $this->getRecordsPerPage($recordsPerPageData);
        $firstResult = $this->getFirstResult($currentPage, $recordsPerPage);
        $numberOfPages = $this->getNumberOfPages($parser, $recordsPerPage, $additionalArguments);
        $pagerProps = new PagerProps();
        $pagerProps->setCurrentPage($currentPage);
        $pagerProps->setUriWithoutPage($uriWithoutPage);
        $pagerProps->setRecordsPerPage($recordsPerPage);
        $pagerProps->setFirstResult($firstResult);
        $pagerProps->setNumberOfPages($numberOfPages);
        return $pagerProps;
    }

    /**
     * Get total number of pages based on number of records. Needs an object of a parser class that implements
     * PageableInterface
     *
     * @param PageableInterface $parser
     * @param int $recordsPerPage
     * @param array $additionalArguments
     * @return int
     */
    protected function getNumberOfPages(
        PageableInterface $parser,
        $recordsPerPage,
        array $additionalArguments
    ) {
        if (!$recordsPerPage) { // suppress divide by zero error
            return 0;
        }
        if ($recordsPerPage == -1) {
            return 1;
        }
        $numberOfRecords = $parser->getNumberOfRecords($additionalArguments);
        $numberOfPages = intval(ceil($numberOfRecords / $recordsPerPage));
        return $numberOfPages;
    }

    /**
     * @param int $currentPage
     * @param int $recordsPerPage
     * @return int
     */
    protected function getFirstResult($currentPage, $recordsPerPage)
    {
        if ($recordsPerPage == -1) {
            return 0;
        }
        return ($currentPage - 1) * $recordsPerPage;
    }

    /**
     * Gets current page number from query string or 1
     *
     * @param string $requestUri
     * @return int
     */
    protected function getCurrentPage($requestUri)
    {
        $regexp = '/[\?&]page=(\d+)/';
        preg_match($regexp, $requestUri, $matches);
        if (sizeof($matches)) {
            return $matches[1];
        }
        return 1;
    }

    /**
     * Strips current URI from page variable in order to avoid duplicates in pager links
     *
     * @param string $requestUri
     * @return string
     */
    protected function getCurrentUriWithoutPage($requestUri)
    {
        $regexp = '/page=\d+?&?/'; // select 'page=' followed by a number and optional & sign
        $uriWithoutPage = preg_replace($regexp, '', $requestUri);
        $uriWithoutPage .= $this->appendCharacterToUri($uriWithoutPage);
        return $uriWithoutPage;
    }

    /**
     * @param string $uriWithoutPage
     * @return string
     */
    protected function appendCharacterToUri($uriWithoutPage)
    {
        if (!strstr($uriWithoutPage, '?')) {
            return '?';
        }
        $lastCharacter = substr($uriWithoutPage, -1);
        if (in_array($lastCharacter, ['?', '&']) !== true) {
            return '&';
        }
        return '';
    }

    /**
     * If $recordsPerPageData is int, returns it. Otherwise, it is a setting key, so returns the corresponding
     * value instead
     *
     * @param int|string $recordsPerPageData
     * @return int
     * @throws \Exception
     */
    protected function getRecordsPerPage($recordsPerPageData)
    {
        $recordsPerPage = $recordsPerPageData;
        if (!is_int($recordsPerPageData)) {
            $relevantSettingValue = intval($this->settingsRetriever->get($recordsPerPageData));
            if ($relevantSettingValue <= 0) {
                throw new WrongSettingTypeException($recordsPerPageData, 'integer');
            }
            $recordsPerPage = $relevantSettingValue;
        }
        return $recordsPerPage;
    }

}
