<?php

namespace MiToTeam;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MtHowManyCommand extends Command
{
  public static array $SINGLE_OPTION_POSSIBLE_VALUES = array('CHARS', 'LINES', 'PAGES');

  protected static $defaultName = 'run';

  protected function configure(): void
  {
    $version = MtHowMany::APP_VERSION;

    $this
      ->setName('run')
      ->setDescription(<<<TXT
Calculates project's files, lines and characters number.
See https://www.mito-team.com/projects/mt-howmany for details.

Version: {$version}
MiTo Team, http://mito-team.com
TXT)
      ->addOption(
        'single',
        mode: InputOption::VALUE_REQUIRED,
        description:
          'Output just single value without any other output. Possible values: '
          . implode(', ', self::$SINGLE_OPTION_POSSIBLE_VALUES)
      );
  }
  
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    return (new MtHowMany($input, $output))->Run();
  }
}
