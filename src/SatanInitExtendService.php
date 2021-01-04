<?php
namespace SaTan\Think\Command;

class SatanInitExtendService extends \think\Service
{
    public function boot()
    {
        $this->commands(
            'satan:publish',VendorPublish::class
        );
    }
}