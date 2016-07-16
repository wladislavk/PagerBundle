<?php
namespace VKR\PagerBundle\Twig;

class PagerTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        $pageFilter = new \Twig_SimpleFilter('page', [$this, 'pageFilter']);
        return [$pageFilter];
    }

    public function pageFilter($urlWithoutPage, $pageNumber)
    {
        return $urlWithoutPage . 'page=' . $pageNumber;
    }

    public function getName()
    {
        return 'pager_extension';
    }
}
