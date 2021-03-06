<?php
namespace Grav\Console\Cli;

use Grav\Common\Backup\ZipBackup;
use Grav\Console\ConsoleCommand;
use RocketTheme\Toolbox\File\JsonFile;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class BackupCommand
 * @package Grav\Console\Cli
 */
class BackupCommand extends ConsoleCommand
{
    protected $source;
    protected $progress;

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName("backup")
            ->addArgument(
                'destination',
                InputArgument::OPTIONAL,
                'Where to store the backup (/backup is default)'

            )
            ->setDescription("Creates a backup of the Grav instance")
            ->setHelp('The <info>backup</info> creates a zipped backup. Optionally can be saved in a different destination.');

        $this->source = getcwd();
    }

    /**
     * @return int|null|void
     */
    protected function serve()
    {
        $this->progress = new ProgressBar($this->output);
        $this->progress->setFormat('Archiving <cyan>%current%</cyan> files [<green>%bar%</green>] %elapsed:6s% %memory:6s%');

        self::getGrav()['config']->init();

        $destination = ($this->input->getArgument('destination')) ? $this->input->getArgument('destination') : null;
        $log = JsonFile::instance(self::getGrav()['locator']->findResource("log://backup.log", true, true));
        $backup = ZipBackup::backup($destination, [$this, 'output']);

        $log->content([
            'time' => time(),
            'location' => $backup
        ]);
        $log->save();

        $this->output->writeln('');
        $this->output->writeln('');

    }

    /**
     * @param $args
     */
    public function output($args)
    {
        switch ($args['type']) {
            case 'message':
                $this->output->writeln($args['message']);
                break;
            case 'progress':
                if ($args['complete']) {
                    $this->progress->finish();
                } else {
                    $this->progress->advance();
                }
                break;
        }
    }

}

