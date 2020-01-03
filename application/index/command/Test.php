<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Test extends Command
{
    protected function configure(){
		$this->setName('test')->setDescription('this is a test');
	}

    protected function execute(Input $input, Output $output)
    {
        // 输出到日志文件
        $output->writeln("TestCommand:");
        // 定时器需要执行的内容
        echo "it is start";
        echo  date("Y-m-d H:i:s",time());
        echo "---";
        $output->writeln("end....");
        $res = db("AuthAdmin")->select();
        print_r($res);

	}
}
