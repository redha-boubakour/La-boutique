<?php

namespace App\Classe;

use App\Entity\Category;

class Search
{
    /**
    * @var string
    */
    public $string = '';

    /**
    * @var null/integer
    */
    public $min;

    /**
    * @var null/integer
    */
    public $max;

    /**
    * @var Category[]
    */
    public $categories = [];
}


