<?php

namespace SilverStripe\GoogleCloudStorage\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class GCSDebugAsset extends BuildTask
{
    protected string $title = 'GCS Debug Asset';

    protected static string $commandName = 'GCSDebugAsset';

    protected static string $description = 'Debug GCS Asset';

    protected function execute(InputInterface $input, PolyOutput $output) :int
    {
        $fileId = $input->getOption('fileId');

        if (!$fileId) {
            echo 'Please provide fileId';
            return Command::INVALID;
        }

        $file = \SilverStripe\Assets\File::get()->byID($fileId);

        if (!$file) {
            echo 'File not found';
            return Command::FAILURE;
        }

        $output->writeln($file->getAbsoluteURL());
        return Command::SUCCESS;
    }

    public function getOptions(): array
    {
        return [
            new InputOption('fileId', null, InputOption::VALUE_REQUIRED, 'provide the file ID to test grabbing the files URL'),
        ];
    }
}
