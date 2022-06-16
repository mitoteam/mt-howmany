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
    $helper = new MtHowMany(new SymfonyStyle($input, $output));

    return $helper->Run();
  }
}
