<?php
namespace VKR\PagerBundle\Entity\Perishable;

class PagerProps
{
    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var string
     */
    protected $uriWithoutPage;

    /**
     * @var int
     */
    protected $recordsPerPage;

    /**
     * @var int
     */
    protected $firstResult;

    /**
     * @var int
     */
    protected $numberOfPages;

    /**
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param string $uriWithoutPage
     * @return $this
     */
    public function setUriWithoutPage($uriWithoutPage)
    {
        $this->uriWithoutPage = $uriWithoutPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getUriWithoutPage()
    {
        return $this->uriWithoutPage;
    }

    /**
     * @param int $recordsPerPage
     * @return $this
     */
    public function setRecordsPerPage($recordsPerPage)
    {
        $this->recordsPerPage = $recordsPerPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecordsPerPage()
    {
        return $this->recordsPerPage;
    }

    /**
     * @param int $firstResult
     * @return $this
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
        return $this;
    }

    /**
     * @return int
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }

    /**
     * @param int $numberOfPages
     * @return $this
     */
    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }
}
