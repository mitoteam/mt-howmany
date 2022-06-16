<?php

namespace MiToTeam;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class MtHowManyConfig
{
  private $config = array();

  private MtHowMany $app;
  private SymfonyStyle $io;

  public function __construct(MtHowMany $app)
  {
    $this->app = $app;
    $this->io = $app->GetIO();
  }

  public function Load(string $full_file_path)
  {
    $this->config = array();

    try
    {
      $this->config = Yaml::parseFile($full_file_path);
    }
    catch (\Throwable $e)
    {
      $this->io->error($e->getMessage());
      exit(-1);
    }

    if(count($this->GetImportsList()))
    {
      $list_keys_to_merge = ['path', 'ignore_name', 'ignore_path'];

      foreach ($this->GetImportsList() as $import_path)
      {
        $imported_config = new MtHowManyConfig($this->app);

        $imported_config->Load($this->app->GetFullPath($import_path));

        //merge known list keys
        foreach ($list_keys_to_merge as $key)
        {
          if(isset($imported_config->config[$key]))
          {
            $this->config[$key] = array_merge(
              $imported_config->config[$key],
              $this->config[$key] ?? array()
            );
          }
        }

        //add all other keys if they are not added yet
        $this->config = $this->config + $imported_config->config;
      }

      //ensure there are no duplicates in merged lists
      foreach ($list_keys_to_merge as $key)
      {
        if(isset($this->config[$key]))
        {
          $this->config[$key] = array_unique($this->config[$key]);
        }
      }
    }

    $this->io->writeln('Config file loaded: ' . $full_file_path);
  }

  public function PrintConfigInfo()
  {
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

      $this->io->writeln("Lines per page: " . $this->GetLinesPerPage());
      $this->io->writeln("Characters per page: " . $this->GetCharactersPerPage());
    }
  }

  public function GetPathList(): array
  {
    if(!count($value = $this->_GetArray('path')))
    {
      $this->io->writeln('No paths set in config, using working dir as default path');
      $this->config['path'] = array('');
    }

    return $this->_GetArray('path');
  }

  public function GetIgnoreNameList(): array
  {
    return $this->_GetArray('ignore_name');
  }

  public function GetIgnorePathList(): array
  {
    return $this->_GetArray('ignore_path');
  }

  public function GetImportsList(): array
  {
    return $this->_GetArray('import');
  }

  public function GetLinesPerPage(): int
  {
    return $this->_GetInt('lines_per_page', 36);
  }

  public function GetCharactersPerPage(): int
  {
    return $this->_GetInt('characters_per_page', 3600);
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

  private function _GetInt(string $key, int $default): int
  {
    if(!isset($this->config[$key]))
    {
      $this->config[$key] = $default;
    }
    elseif(!is_integer($this->config[$key]))
    {
      $this->config[$key] = (int)($this->config[$key]);
    }

    return $this->config[$key];
  }
}
