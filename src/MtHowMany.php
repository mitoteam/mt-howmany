<?php

namespace MiToTeam;

use Symfony\Component\Console\Style\SymfonyStyle;

class MtHowMany
{
  private SymfonyStyle $io;

  public function __construct(SymfonyStyle $io)
  {
    $this->io = $io;
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

  public function Run()
  {
    $this->io->title('mt-howmany by MiTo Team');

    $this->io->writeln('Working directory: ' . $this->getWorkingDir());

    $config = $this->GetConfig();

    foreach ($config->GetPathList() as $path)
    {
      $full_path = $this->GetFullPath($path);
      $this->io->writeln('Scanning path: ' . $full_path);
      $this->ScanPath($full_path);
    }

    uasort($this->items_by_type, fn($a, $b) => $b->size - $a->size);

    $this->PrintResults();

    $this->io->success('Done');
  }

  /**
   * @var MtHowManyTypeItem[]
   */
  private array $items_by_type = array(); // extension => MtHowManyTypeItem

  private function ScanPath(string $path)
  {
    $config = $this->GetConfig();

    foreach(scandir($path) as $base_name)
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
        $this->ScanPath($full_path);
      }
      else
      {
        if(file_exists($full_path))
        {
          $info = pathinfo($full_path);
          $type = $info['extension'] ?? '[no extension]';

          $file_item = new MtHowManyFileItem($full_path, $relative_path);
          ($this->items_by_type[$type] ??= new MtHowManyTypeItem())->AddFile($file_item);
        }
      }
    }
  }

  private function PrintResults()
  {
    if($this->io->isVerbose())
    {
      $header = array('File', 'Size', 'Lines');
      $type_totals_item = new MtHowManyTotalsItem();

      foreach ($this->items_by_type as $type => $type_item)
      {
        $this->io->title('File type: ' . $type);

        $rows = array();
        $type_totals_item->Reset();

        foreach ($type_item->GetFilesList() as $file_item)
        {
          $row = array();

          $row[] = $file_item->path;
          $row[] = $file_item->GetSizeFormatted();
          $row[] = $file_item->lines;

          $type_totals_item->AggregateItem($file_item);

          $rows[] = $row;
        }

        $this->io->table($header, $rows);

        $this->io->writeln("'$type' Total Size: " . $type_totals_item->GetSizeFormatted());
        $this->io->writeln("'$type' Total Lines: " . $type_totals_item->lines);
      }
    }

    $this->io->title('By file type');

    $header = array('Type', 'Size', 'File Count', 'Lines');
    $rows = array();

    foreach ($this->items_by_type as $type => $type_item)
    {
      $row = array();

      $row[] = $type;
      $row[] = $type_item->GetSizeFormatted();
      $row[] = $type_item->GetCount();
      $row[] = $type_item->lines;

      $rows[] = $row;
    }

    $this->io->table($header, $rows);


    $this->io->title('Totals');

    $totals_item = new MtHowManyTotalsItem();
    foreach ($this->items_by_type as $item)
    {
      $totals_item->AggregateItem($item);
    }

    $this->io->writeln('Types count: ' . count($this->items_by_type));
    $this->io->writeln('Files count: ' . $totals_item->count);
    $this->io->writeln('Size: ' . $totals_item->GetSizeFormatted());
    $this->io->writeln('Lines: ' . $totals_item->lines);
    $this->io->writeln('Pages by Size: ' . $totals_item->GetPagesCountByCharacters());
    $this->io->writeln('Pages by Lines: ' . $totals_item->GetPagesCountByLines());
  }

  private ?MtHowManyConfig $config = null;
  public const CONFIG_FILE_NAME = 'mt-howmany.yml';

  private function GetConfig(): MtHowManyConfig
  {
    if(!$this->config)
    {
      $this->config = new MtHowManyConfig($this->io);

      $filename = $this->GetFullPath(self::CONFIG_FILE_NAME);

      if(file_exists($filename))
      {
        $this->config->Load($filename, $this->io);
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
