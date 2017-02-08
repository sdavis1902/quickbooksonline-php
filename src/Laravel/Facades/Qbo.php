<?php
namespace sdavis1902\QboPhp\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Qbo extends Facade{
    protected static function getFacadeAccessor() { return 'qbo'; }
}
