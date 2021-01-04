<?php


use think\console\Input;
use think\console\Output;

class VendorPublish extends \think\console\Command
{
    protected function configure()
    {
       $this
           ->setName('satan:publish')
           ->setDescription('thinkphp6 extends command publish');
    }
    protected function execute(Input $input, Output $output)
    {
        $output->info(1);
    }
}