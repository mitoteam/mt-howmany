<?php

namespace MiToTeam;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

class MtHowMany
{
  public const APP_NAME = 'mt-howmany';
  public const APP_VERSION = '1.3';

  private InputInterface $input;
  private OutputInterface $output;
  private SymfonyStyle $io;

  private ?string $single = null;

  public function __construct(InputInterface $input, OutputInterface $output)
  {
    $this->input = $input;
    $this->output = $output;

    $this->io = new SymfonyStyle($this->input, $this->output);
  }

  private string $working_dir = '';

  /**
   * @return string
   */
  public function getWorkingDir(): string
  {
    if(!$this->working_dir)
    {
      $this->working_dir = getcwd();
    }

    return $this->working_dir;
  }

  /**
   * @param string $working_dir
   */
  public function setWorkingDir(string $working_dir): void
  {
    $this->working_dir = $working_dir;
  }

  public function Run(): int
  {
    if($this->single = $this->input->getOption('single'))
    {
      if(!in_array($this->single, MtHowManyCommand::$SINGLE_OPTION_POSSIBLE_VALUES))
      {
        $this->io->error('Possible values for --single option are: ' . implode(', ', MtHowManyCommand::$SINGLE_OPTION_POSSIBLE_VALUES));
        exit -1;
      }
    }

    if(!$this->single)
    {
      $this->io->title(MtHowMany::APP_NAME . ' by MiTo Team');
    }

    $config = $this->GetConfig();

    if(!$this->single)
    {
      $this->io->writeln('Working directory: ' . $this->getWorkingDir());
      $this->io->writeln("\nStarting scan.\n", OutputInterface::VERBOSITY_VERBOSE);
    }

    foreach ($config->GetPathList() as $path)
    {
      $full_path = $this->GetFullPath($path);

      if(!$this->single)
      {
        $this->io->writeln('Scanning path: ' . $full_path, OutputInterface::VERBOSITY_VERBOSE);
      }

      $this->ScanPath($full_path);
    }

    if(!$this->single)
    {
      $this->io->writeln("\nScan finished. Printing results.", OutputInterface::VERBOSITY_VERBOSE);
      uasort($this->items_by_type, fn($a, $b) => $b->size - $a->size);
      ksort($this->items_by_path);
    }

    $this->PrintResults();

    return count($this->errors) ? -1 : 0;
  }

  /**
   * @var MtHowManyTypeItem[]
   */
  private array $items_by_type = array(); // extension => MtHowManyTypeItem

  /**
   * @var MtHowManyTotalsItem[]
   */
  private array $items_by_path = array(); // extension => MtHowManyTotalsItem

  /**
   * @var string[]
   */
  private array $errors = array();

  public function AddError(string $message)
  {
    $this->errors[] = $message;

    if($this->io->isVeryVerbose())
    {
      $this->io->error($message);
    }
  }

  private function ScanPath(string $path, ?MtHowManyTotalsItem $path_item = null)
  {
    $config = $this->GetConfig();

    if(is_dir($path))
    {
      foreach (scandir($path) as $base_name)
      {
        if($base_name == '.' || $base_name == '..')
        {
          continue;
        }

        //ignore by name
        foreach ($config->GetIgnoreNameList() as $regexp)
        {
          if(preg_match('/' . $regexp . '/u', $base_name))
          {
            continue 2;
          }
        }

        $full_path = $this->GetFullPath($base_name, $path);
        $relative_path = $this->GetRelativePath($full_path);

        //ignore by path
        foreach ($config->GetIgnorePathList() as $regexp)
        {
          if(preg_match('/' . $regexp . '/u', $relative_path))
          {
            continue 2;
          }
        }

        if(is_dir($full_path))
        {
          $child_path_item = new MtHowManyTotalsItem($this);

          //recursion
          $this->ScanPath($full_path, $child_path_item);

          $this->items_by_path[$relative_path] = $child_path_item;

          $path_item?->AggregateItem($child_path_item);
        }
        else
        {
          $this->ProcessFile($full_path, $path_item);
        }
      }
    }
    else //seems that upper level path is a file
    {
      $this->ProcessFile($path);
    }
  }

  private function ProcessFile(string $full_path, ?MtHowManyTotalsItem $path_item = null)
  {
    if($this->io->isDebug())
    {
      echo "Processing file: $full_path\n";
    }

    if(file_exists($full_path))
    {
      $info = pathinfo($full_path);
      $type = $info['extension'] ?? '[no extension]';

      $file_item = new MtHowManyFileItem($full_path, $this);
      ($this->items_by_type[$type] ??= new MtHowManyTypeItem($this))->AddFile($file_item);

      $path_item?->AggregateItem($file_item);
    }
  }

  private function PrintResults()
  {
    //aggregate totals
    $totals_item = new MtHowManyTotalsItem($this);
    foreach ($this->items_by_type as $item)
    {
      $totals_item->AggregateItem($item);
    }

    if($this->single)
    {
      switch ($this->single)
      {
        case 'CHARS':
          $this->io->writeln($totals_item->characters);
          break;

        case 'LINES':
          $this->io->writeln($totals_item->lines);
          break;

        case 'PAGES':
          $this->io->writeln($totals_item->GetPagesCountByLines());
          break;
      }
    }
    else
    {
      #region Each file
      if($this->io->isVeryVerbose())
      {
        $header = array('File', 'Size', 'Characters', 'Lines');
        $type_totals_item = new MtHowManyTotalsItem($this);

        foreach ($this->items_by_type as $type => $type_item)
        {
          $this->io->title('File type: ' . $type);

          $rows = array();
          $type_totals_item->Reset();

          $file_items_list = $type_item->GetFilesList();

          //sort by size
          usort($file_items_list, fn($a, $b) => $b->size - $a->size);

          foreach ($file_items_list as $file_item)
          {
            $row = array();

            $row[] = $file_item->path;
            $row[] = $file_item->GetSizeFormatted();
            $row[] = $file_item->characters;
            $row[] = $file_item->lines;

            $type_totals_item->AggregateItem($file_item);

            $rows[] = $row;
          }

          $this->io->table($header, $rows);

          $this->io->writeln("'$type' Total Size: " . $type_totals_item->GetSizeFormatted());
          $this->io->writeln("'$type' Total Characters: " . $type_totals_item->characters);
          $this->io->writeln("'$type' Total Lines: " . $type_totals_item->lines);
        }
      }
      #endregion

      #region By path
      if($this->io->isVerbose())
      {
        $this->io->title('Results by path');

        $header = array('Path', 'Size', 'Characters', 'Files Count', 'Lines');
        $rows = array();

        foreach ($this->items_by_path as $path => $path_item)
        {
          //skip empty folders
          if(!$path_item->GetCount())
          {
            continue;
          }

          $row = array();

          $row[] = $path;
          $row[] = $path_item->GetSizeFormatted();
          $row[] = $path_item->characters;
          $row[] = $path_item->GetCount();
          $row[] = $path_item->lines;

          $rows[] = $row;
        }

        $this->io->table($header, $rows);
      }
      #endregion

      #region By file type
      $this->io->title('Results by file extension');

      $header = array('Type', 'Size', 'Characters', 'Files Count', 'Lines');
      $rows = array();

      foreach ($this->items_by_type as $type => $type_item)
      {
        $row = array();

        $row[] = $type;
        $row[] = $type_item->GetSizeFormatted();
        $row[] = $type_item->characters;
        $row[] = $type_item->GetCount();
        $row[] = $type_item->lines;

        $rows[] = $row;
      }

      $this->io->table($header, $rows);
      #endregion

      #region Totals
      $this->io->title('Totals');

      $this->io->writeln('Types count: ' . count($this->items_by_type));
      $this->io->writeln('Paths count: ' . count($this->items_by_path));
      $this->io->writeln('Files count: ' . $totals_item->count);
      $this->io->writeln('Size: ' . $totals_item->GetSizeFormatted());
      $this->io->writeln('Characters: ' . $totals_item->characters);
      $this->io->writeln('Lines: ' . $totals_item->lines);
      $this->io->writeln('Pages by Characters: ' . $totals_item->GetPagesCountByCharacters());
      $this->io->writeln('Pages by Lines: ' . $totals_item->GetPagesCountByLines());
      #endregion
    }

    #region Errors
    if($cnt = count($this->errors))
    {
      if(!$this->io->isVerbose())
      {
        $this->io->error("Errors occured while processing files: $cnt. You can add -v argument to see full errors list.");
      }
      else
      {
        $this->io->error("Errors occured while processing files:");

        foreach ($this->errors as $msg)
        {
          $this->io->writeln('  * ' . $msg);
        }
      }
    }
    #endregion
  }

  public function GetIO(): SymfonyStyle
  {
    return $this->io;
  }

  private ?MtHowManyConfig $config = null;
  public const CONFIG_FILE_NAME = 'mt-howmany.yml';

  public function GetConfig(): MtHowManyConfig
  {
    if(!$this->config)
    {
      $this->config = new MtHowManyConfig($this);

      $filename = $this->GetFullPath(self::CONFIG_FILE_NAME);

      if(file_exists($filename))
      {
        $this->config->Load($filename, (bool)$this->single);

        if(!$this->single)
        {
          $this->config->PrintConfigInfo();
        }
      }
    }

    return $this->config;
  }

  public function GetFullPath(string $relative_path, ?string $base_path = null): string
  {
    if(DIRECTORY_SEPARATOR == '\\')
    {
      $relative_path = str_replace('/', '\\', $relative_path);
    }

    return ($base_path ?: $this->getWorkingDir()) . DIRECTORY_SEPARATOR . $relative_path;
  }

  public function GetRelativePath(string $full_path): string
  {
    $relative_path = str_replace($this->getWorkingDir(), '', $full_path);

    if(DIRECTORY_SEPARATOR == '\\')
    {
      $relative_path = str_replace('\\', '/', $relative_path);
    }

    //cut first directory separator
    $relative_path = substr($relative_path, 1);

    return $relative_path;
  }
}
