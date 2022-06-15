<?php

namespace MiToTeam;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MtHowManyCommand extends Command
{
  protected static $defaultName = 'run';

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $io = new SymfonyStyle($input, $output);

    $io->title('mt-howmany by MiTo Team');
    $io->writeln('Path: ' . MtHowMany::gi()->getPath());

    MtHowMany::gi()->Run($io);

    $io->success('Done');

    return 0;
  }
}
