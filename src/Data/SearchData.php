<?php

namespace App\Data;

use App\Entity\Country;
use App\Entity\Product;



class SearchData
{
    /**
     * @var string
     */
    public $q = '';

    /**
     * @var int
     */
    public $minCapacity;

    /**
     * @var int
     */
    public $maxCapacity;

    /**
     * @var int
     */
    public $minPrice;

    /**
     * @var int
     */
    public $maxPrice;

    /**
     * @var Product
     */
    public $brand;

    /**
     * @var Product
     */
    public $category;

    /**
     * @var Country
     */
    public $country;

    /**
     * @var string
     */
    public $order = '';
}
