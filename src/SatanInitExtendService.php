<?php
namespace Satan\Think\Command;


use think\facade\Console;

class SatanInitExtendService extends BaseService
{

    public function boot()
    {
       $this->commands([
           'satan:publish'=>VendorPublish::class
       ]);
    }
}