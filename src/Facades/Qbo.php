<?php
namespace sdavis1902\QboLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Qbo extends Facade{
    protected static function getFacadeAccessor() { return 'qbo'; }
}
