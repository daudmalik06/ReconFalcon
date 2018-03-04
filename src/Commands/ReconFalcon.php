<?php

namespace dawood\ReconFalcon\Commands;

use dawood\ReconFalcon\FalconClient;
use Pool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReconFalcon extends Command
{
    protected function configure()
    {
        $this
            ->setName('recon')
            ->setDescription('check if url/urls is/are live or not.')
            ->setHelp(
                '<options=bold>Examples</>:'.PHP_EOL.
                '<comment>Simple Usage</comment>'
                .PHP_EOL.PHP_EOL.'<info>recon</info> <fg=red;options=bold>--input</>=<options=bold>urlFile.txt</> <fg=red;options=bold>--output-directory</>=<options=bold>facebookResults</>'
                .PHP_EOL.'<info>recon</info> <fg=red;options=bold>-i</><options=bold>urlFile.txt</> <fg=red;options=bold>-o</><options=bold>facebookResults</>'
                .PHP_EOL.PHP_EOL.'<comment>Verbose mode</comment>'
                .PHP_EOL.PHP_EOL.'<info>recon</info> <fg=red;options=bold>--input</>=<options=bold>urlFile.txt</> <fg=red;options=bold>--verbose</> <fg=red;options=bold>-o</><options=bold>facebookResults</>'
                .PHP_EOL.'<info>recon</info> <fg=red;options=bold>-i</><options=bold>urlFile.txt</> <fg=red;options=bold>-v</> <fg=red;options=bold>-o</><options=bold>facebookResults</>'
                .PHP_EOL.PHP_EOL.'<comment>With threads</comment>'
                .PHP_EOL.PHP_EOL.'<info>recon</info> <fg=red;options=bold>--input</>=<options=bold>urlFile.txt</> <fg=red;options=bold>--threads</>=<options=bold>5</> <fg=red;options=bold>--verbose</> <fg=red;options=bold>-o</><options=bold>facebookResults</>'
                .PHP_EOL.'<info>recon</info> <fg=red;options=bold>-i</><options=bold>urlFile.txt</> <fg=red;options=bold>-t</><options=bold>5</> <fg=red;options=bold>-v</> <fg=red;options=bold>-o</><options=bold>facebookResults</>'
            )
            ->addOption('input','i',  InputOption::VALUE_REQUIRED, 'the file containing the urls to check')
            ->addOption('threads','t',  InputOption::VALUE_OPTIONAL, 'Threads to be used', 5)
            ->addOption('timeout',null,  InputOption::VALUE_OPTIONAL, 'timeout for a url', 3)
            ->addOption('output-name','o',  InputOption::VALUE_REQUIRED, 'name of the directory to save results')
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->printHelpIfArgumentMissing($input);
        $file = $input->getOption('input');
        $threads = $input->getOption('threads');
        $timeOut = $input->getOption('timeout');
        $verbose = $input->getOption('verbose');
        $outputFile = $input->getOption('output-name');

        $threadsAvailable = $threads;
        if(!extension_loaded('pthreads'))
        {
            $threadsAvailable=1;
        }

        $urls = file($file, FILE_IGNORE_NEW_LINES);

        $output->writeln('<info>Processing All Provided Urls</info>');
        $output->writeln('<comment>Using Threads:' . $threadsAvailable . '</comment>');
        $output->writeln('<comment>Loaded Urls:' . count($urls). '</comment>');
        $output->writeln('<info>Results will be saved in '.rootDirectory().DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.$outputFile.'</info>');

        echo PHP_EOL;

        $collector = function (FalconClient $task) use($output, $verbose, $outputFile){
            if($task->isDone())
            {
                $this->printAndStoreUrl($task, $verbose, $output, $outputFile);
                return true;
            }
            usleep(100);
            return false;
        };

        $pool = new Pool($threads);

        foreach ($urls as $url) {
            $url = trim($url);
            if(!strstr('http',$url))
            {
                $url = 'http://'.$url;
            }
            $pool->submit(new FalconClient($url, $timeOut, true));
            if(!extension_loaded('pthreads'))
            {
                $pool->collect($collector);
            }
        }

        while ($pool->collect($collector));
        
        $pool->shutdown();
        $output->writeln('<comment>Done Working</comment>');
    }

    /**
     * @param InputInterface $input
     */
    private function printHelpIfArgumentMissing(InputInterface $input)
    {
        $inputFile = $input->getOption('input');
        $outputFile = $input->getOption('output-name');
        if(empty($inputFile) or empty($outputFile ))
        {
            $command = $this->getApplication();

            $arguments = array(
                'command' => 'recon',
                '--help' => true,
            );
            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput);
            return ;
        }
    }

    /**
     * @param FalconClient $falconClient
     * @param bool $verbose
     * @param OutputInterface $output
     */
    private function printAndStoreUrl(FalconClient $falconClient, bool $verbose, OutputInterface $output, string $fileToStore)
    {
        $task=$falconClient;
        if($verbose)
        {
            $url = $task->getUrl();
            if($task->isLive())
            {
                $output->writeln('<info>'.$url.'</info>');
            }else{
                $output->writeln('<fg=red;options=bold>'.$url.'</>');
            }
        }
        $this->storeUrl($task->isLive(),$url, $fileToStore);

    }

    /**
     * @param bool $isLive
     * @param string $url
     */
    private function storeUrl(bool $isLive, string $url, string $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $url = parse_url($url)['host'];
        $outputDir = rootDirectory().$ds."Output".$ds.$file;
        if(!file_exists($outputDir))
        {
            mkdir($outputDir);
        }
        $status = 'live';
        if(!$isLive)
        {
            $status='not_reachable';
        }
        $file = $outputDir.$ds.$status.'.txt';
        file_put_contents($file,$url.PHP_EOL, FILE_APPEND);
    }


}