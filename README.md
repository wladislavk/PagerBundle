About
=====

This is a simple pager bundle for Symfony. Unlike some other pagers, it is purely front-end
agnostic and is written in SOA manner - you write your own service, then you get that
service and Pager service from the controller, and then you get all the pagination data
you need for later usage in the view layer.

This bundle depends upon VKRSettingsBundle, please read its docs before using it.

Installation
============

There is almost nothing to do except enabling the bundle in Composer and AppKernel.php.
However, if you want to use settings-based records per page, you will need to create a
setting for every instance of pager you invoke. Read VKRSettingsBundle documentation for
specifics on creating settings.

Usage
=====

Parser
------

There is a sole public method called ```getPagerProps()```. In order to use it, you need
to write a parser that implements ```VKR\PagerBundle\Interfaces\PageableInterface```.
This interface has a method called ```getNumberOfRecords()``` that should return the total
number of records that can be shown using the pager. Here is an example of a parser class.

```
class MyParser implements VKR\PagerBundle\Interfaces\PageableInterface
{
    private $em;

    public function __construct(Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function getNumberOfRecords(array $additionalArguments = [])
    {
        $allRecords = $this->em->getRepository('AppBundle:MyEntity')->findAll();
        if (!$allRecords) {
            return 0;
        }
        return sizeof($allRecords);
    }
}
```

Take a note that this query can be really slow on big chunks of data, so try to use
aggregate functions and indexing.

Using additional arguments
--------------------------

If your implementation of ```getNumberOfRecords()``` needs an argument, you have two options.
You can either pass it as a class property, or use an optional $additionalArguments array,
that is passed as fourth argument to ```getPagerProps()``` from the controller - this
way is recommended for use cases when you do not need the query results anywhere else
and you are registering your parser class as a service.

Parser:

```
public function getNumberOfRecords(array $additionalArguments = [])
{
    $query = "SELECT a FROM table1 a WHERE a.id IN ($additionalArguments['ids'])";
    ...
}
```

Controller:

```
public function myControllerAction()
{
    ...
    $additionalArguments = [
        'ids' => [1,2,3]
    ];
    $pagerProps = $pager->getPagerProps($parser, $request->getRequestUri(), $recordsPerPage, $additionalArguments);
    ...
}
```

Controller
----------

Add this to your controller:

```
$parser = $this->get('my_parser_service');
$pager = $this->get('vkr_pager.pager');
$recordsPerPage = 20;
$pagerProps = $pager->getPagerProps($parser, $request->getRequestUri(), $recordsPerPage);
```

If you want to disable pagination and show all results on a single page, use

```
$recordsPerPage = -1;
```

One more way to use ```getPagerProps()``` is to define a setting for easier customization
of ```$recordsPerPage```. If you have such a setting, you can use it as a third argument:

```
$recordsPerPageSettingName = 'records_per_page';
$pagerProps = $pager->getPagerProps($parser, $request->getRequestUri(), $recordsPerPageSettingName);
```

The resulting ```PagerProps``` object is an in-memory entity with the following properties:

- ```$currentPage``` - the current page number, corresponds to the ```page``` query string
parameter. Default is 1.
- ```$uriWithoutPage``` - the current page URI with all query string parameters except
for ```page```. It also has ? or & appended to its end.
- ```$recordsPerPage``` - maximum number of records that can be displayed on a page.
- ```$firstResult``` - if you have a zero-indexed array of N records, this key tells
the first index of a record that needs to be displayed on a current page. ```$firstResult```
and ```$recordsPerPage``` roughly correspond to two arguments of SQL LIMIT clause.
- ```$numberOfPages``` - total number of pages for your selection.

These properties are accessible via standard getters (```getCurrentPage()``` etc).

Note that there are no actual records here, because this bundle does not make any DB
queries. You need to write a class that would transform this data into a query.

Views
-----

You need to manually pass the resulting array to the view. This bundle does not help
you to display things, so it can be used with any templating technique. There is a small
example showing how it can be used in Twig at ```Resources/views/pager_macro.html.twig```,
it includes some Twitter Bootstrap classes.

If you are using Twig, you can also use a custom filter called ```page()``` that is
included in the bundle. It appends page attribute to the query string.

```
<a href="{{ pagerProps.getUriWithoutPage() | page(page_number) }}">
```

API
===

*void Pager::__construct(VKR\SettingsBundle\SettingsRetriever $settingsRetriever)*

*VKR\PagerBundle\Entity\Perishable\PagerProps Pager::getPagerProps(VKR\PagerBundle\Interfaces\PageableInterface $parser, string $requestUri, int|string $recordsPerPageData, array $additionalArguments = [])*

If the third argument is a string, it is interpreted as a setting name, if it is integer,
it is considered to be an actual number of records per page.

*int PageableInterface::getNumberOfRecords(array $additionalArguments = [])*

Gets the total number of records that can possibly be displayed in this view.

Also, there are getters and setters on *VKR\PagerBundle\Entity\Perishable\PagerProps* entity
that are omitted for the sake of brevity.
