<?php namespace Vinicius73\SEO\Facades;

use Illuminate\Support\Facades\Facade;

class Meta extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'vinicius73.seotools.generators.meta'; }
}
