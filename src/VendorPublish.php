<?php

namespace Satan\Think\Command;

use League\Flysystem\Adapter\Local;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Console;
use think\facade\Filesystem;
use think\helper\Arr;

class VendorPublish extends \think\console\Command
{
    protected function configure()
    {
       $this->setName('satan:publish')
           ->addOption('tag','t',Option::VALUE_OPTIONAL,'tag name')
           ->addOption('all','A',Option::VALUE_NONE,'sure all')
           ->addOption('provider','p',Option::VALUE_NONE,'provider class')
           ->addOption('force', 'f', Option::VALUE_NONE, 'Overwrite any existing files')
           ->setDescription('thinkphp6 extends command publish');
    }
    public function handle()
    {
        $this->tag = $this->input->getOption('tag')?:[null];
        $force = $this->input->getOption('force');
        if ($this->tag[0]==null)
        {
            return $this->vendor_publish($force);
        }
        $this->determineWhatShouldBePublished();
        $this->satan_publish($this->tag,$force);

        $this->output->info('Publishing complete.');
    }


    public function satan_publish($tag,$force)
    {
        $this->publishTag($tag);
    }
    /**
     * 获取要发布的所有路径。
     *
     * @param  string  $tag
     * @return array
     */
    protected function pathsToPublish($tag)
    {
        return BaseService::pathsToPublish(
            $this->provider, $tag
        );
    }
    protected function is_file($filename)
    {
        return (is_file($filename) and is_readable($filename));
    }
    /**
     * 将给定项从和发布到给定位置。
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function publishItem($from, $to)
    {
        if ($this->is_file($from)) {
            return $this->publishFile($from, $to);
        } elseif (is_dir($from)) {
            return $this->publishDirectory($from, $to);
        }

        $this->output->error("Can't locate path: <{$from}>");
    }

    /**
     * 将目录发布到给定目录。
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function publishDirectory($from, $to)
    {
        $this->moveManagedFiles($from,$to);

        $this->status($from, $to, 'Directory');
    }

    /**
     * @param $source
     * @param $destination
     * @param int $child
     * @return int
     */
    public function xCopy($source, $destination, $child = 1){//用法：
        if(!is_dir($source)){
            $this->output->error("Error:the $source is not a direction!");
            return 0;
        }
        if(!is_dir($destination)){
            mkdir($destination,0777);
        }

        $handle=dir($source);
        while($entry=$handle->read()) {
            if(($entry!=".")&&($entry!="..")){
                if(is_dir($source.DIRECTORY_SEPARATOR.$entry)){
                    if($child) $this->xCopy($source.DIRECTORY_SEPARATOR.$entry,$destination.DIRECTORY_SEPARATOR.$entry,$child);
                }
                else{
                    copy($source.DIRECTORY_SEPARATOR.$entry,$destination.DIRECTORY_SEPARATOR.$entry);
                }
            }
        }
        //return 1;
    }

    protected function moveManagedFiles($form,$to)
    {
        $this->xCopy($form,$to);
    }
    /**
     * 将文件发布到给定路径。
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function publishFile($from, $to)
    {
        if (! file_exists($to) || $this->option('force')) {

            $this->createParentDirectory(dirname($to));

            @copy($from, $to);

            $this->status($from, $to, 'File');
        }
    }

    /**
     * 向控制台写入状态消息。
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     * @return void
     */
    protected function status($from, $to, $type)
    {
        $from = str_replace(base_path(), '', realpath($from));

        $to = str_replace(base_path(), '', realpath($to));

        $this->output->writeln('<info>Copied '.$type.'</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
    }

    /**
     * 如果需要，创建目录来存放已发布的文件。
     *
     * @param  string  $directory
     * @return void
     */
    protected function createParentDirectory($directory)
    {
        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }
    }

    /**
     * 发布标记的资源。
     *
     * @param  string  $tag
     * @return mixed
     */
    protected function publishTag($tag)
    {
        $published = false;
        foreach ($this->pathsToPublish($tag) as $from => $to) {
            $this->publishItem($from, $to);

            $published = true;
        }

        if ($published === false) {
            $this->output->error('找不到可发布的资源.');
        }
    }
    //调用官方的命令
    protected function vendor_publish($force)
    {
        $input = $force?['--force']:[];
        $output=Console::call('vendor:publish',$input);
        $this->output = $output;
        return $this;
    }
    /**
     * 要发布的提供程序。
     *
     * @var string
     */
    protected $provider = null;

    /**
     * 要发布的标记。
     *
     * @var array
     */
    protected $tags = [];

    /**
     * 确定要发布的组合资源文件
     *
     * @return void
     */
    protected function determineWhatShouldBePublished()
    {
        if ($this->input->getOption('all')) {
            return;
        }

        [$this->provider, $this->tags] = [
            $this->input->getOption('provider'), (array) $this->input->getOption('tag'),
        ];
    }
}