<?php


namespace App\Entity\Model;

use App\Form\Model\PriceRange;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ListingSearchRequest
 *
 * Represent the listing search request
 *
 */
class ListingSearchRequest
{
    protected $geohash;
    protected $priceRange;
    protected $sortBy;
    protected $page;
    protected $maxPerPage;
    /** @var RequestStack requestStack */
    protected $requestStack;
    /** @var Request request */
    protected $request;
    protected $similarListings;
    protected $locale;
    protected $isXmlHttpRequest = false;
    protected $keywords;
    /** @var  DateRange */
    protected $dateRange;
    /** @var  TimeRange */
    protected $timeRange;


    public static $sortByValues = array(
        'distance' => 'listing.search.sort_by.distance',
        'recommended' => 'listing.search.sort_by.recommended',
    );

    /**
     * @param RequestStack $requestStack
     * @param int          $maxPerPage
     */
    public function __construct(RequestStack $requestStack, $maxPerPage)
    {
        //Params
        $this->requestStack = $requestStack;
        $this->request = $this->requestStack->getCurrentRequest();
        if ($this->request) {
            $this->locale = $this->request->getLocale();
            if ($this->request->isXmlHttpRequest()) {
                $this->isXmlHttpRequest = true;
            }
        }

        $this->maxPerPage = $maxPerPage;
        $this->page = 1;

        //Price
        $this->priceRange = new PriceRange();

        //Location
        $this->geohash = $this->request->query->get("geohash");


        $this->setSimilarListings(array());


        //Keywords
        $keywords = $this->request->query->get("keywords");
        if ($keywords) {
            $this->keywords = $keywords;
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return PriceRange
     */
    public function getPriceRange()
    {
        return $this->priceRange;
    }

    /**
     * @param PriceRange $priceRange
     */
    public function setPriceRange($priceRange)
    {
        $this->priceRange = $priceRange;
    }

    public function getGeohash()
    {
        return $this->geohash;
    }

    /**
     * @param mixed $geohash
     */
    public function setGeohash($geohash)
    {
        $this->geohash = $geohash;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @param mixed $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * @return int[]
     */
    public function getSimilarListings()
    {
        return $this->similarListings;
    }

    /**
     * @param int[] $similarListings
     */
    public function setSimilarListings($similarListings)
    {
        $this->similarListings = $similarListings;
    }

    /**
     * @return DateRange
     */
    public function getDateRange()
    {
        return $this->dateRange;
    }

    /**
     * @return TimeRange
     */
    public function getTimeRange()
    {
        return $this->timeRange;
    }

    /**
     * @return DateTimeRange
     */
    public function getDateTimeRange()
    {
        return new DateTimeRange($this->getDateRange(), array($this->getTimeRange()));
    }

    /**
     * @param DateRange $dateRange
     */
    public function setDateRange(DateRange $dateRange = null)
    {
        $this->dateRange = $dateRange;
    }

    /**
     * @param TimeRange $timeRange
     */
    public function setTimeRange(TimeRange $timeRange = null)
    {
        $this->timeRange = $timeRange;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return boolean
     */
    public function getIsXmlHttpRequest()
    {
        return $this->isXmlHttpRequest;
    }

    /**
     * @param boolean $isXmlHttpRequest
     */
    public function setIsXmlHttpRequest($isXmlHttpRequest)
    {
        $this->isXmlHttpRequest = $isXmlHttpRequest;
    }


    /**
     * Remove some Object properties while serialisation
     *
     * @return array
     */
    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('requestStack', 'request'));
    }
}