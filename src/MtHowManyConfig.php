<?php

namespace MiToTeam;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class MtHowManyConfig
{
  private $config = array();

  private SymfonyStyle $io;

  public function __construct(SymfonyStyle $io)
  {
    $this->io = $io;
  }

  public function Load(string $full_file_path)
  {
    try
    {
      $this->config = Yaml::parseFile($full_file_path);

      $this->io->writeln('Config file loaded: ' . $full_file_path);

      if($this->io->isVeryVerbose())
      {
        $this->io->writeln('Full config dump:');
        $this->io->writeln(print_r($this->config, true));
      }
      elseif($this->io->isVerbose())
      {
        if(count($list = $this->GetIgnoreNameList()))
        {
          $this->io->writeln("Ignored names:\n  " . implode("\n  ", $list));
        }

        if(count($list = $this->GetIgnorePathList()))
        {
          $this->io->writeln("Ignored paths:\n  " . implode("\n  ", $list));
        }
      }
    }
    catch (ParseException $e)
    {
      $this->config = array();
      $this->io->error($e->getMessage());
      exit(-1);
    }
  }

  public function GetPathList(): array
  {
    $value = $this->_GetArray('path');

    if(!count($value))
    {
      $this->io->writeln('No paths set in config, using working dir as default path');
      $this->config['path'] = array('');
    }

    return $value;
  }

  public function GetIgnoreNameList(): array
  {
    return $this->_GetArray('ignore_name');
  }

  public function GetIgnorePathList(): array
  {
    return $this->_GetArray('ignore_path');
  }

  private function _GetArray(string $key): array
  {
    if(!isset($this->config[$key]))
    {
      $this->config[$key] = array();
    }
    elseif(!is_array($this->config[$key]))
    {
      $this->config[$key] = array($this->config[$key]);
    }

    return $this->config[$key];
  }
}
