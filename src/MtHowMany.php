<?php

namespace MiToTeam;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MtHowMany
{
  private static ?MtHowMany $instance = null;

  /**
   * Singleton instantiation method.
   *
   * @return mixed
   */
  public static function gi(): MtHowMany
  {
    if (!self::$instance)
    {
      self::$instance = new MtHowMany();
    }

    return self::$instance;
  }

  /**
   * Protected constructor to prevent creating a new instance of the
   * *Singleton* via the `new` operator from outside of this class.
   */
  protected function __construct()
  {
  }

  private string $path = '';

  /**
   * @return string
   */
  public function getPath(): string
  {
    if(!$this->path)
    {
      $this->path = getcwd();
    }

    return $this->path;
  }

  /**
   * @param string $path
   */
  public function setPath(string $path): void
  {
    $this->path = $path;
  }

  public function Run(SymfonyStyle $io)
  {
    $this->ScanPath($this->getPath());

    uasort($this->items_by_type, fn($a, $b) => $b->size - $a->size);

    if($io->isVerbose())
    {
      $header = array('File', 'Size', 'Lines');
      $type_totals_item = new MtHowManyTotalsItem();

      foreach ($this->items_by_type as $type => $type_item)
      {
        $io->title('File type: ' . $type);

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

        $io->table($header, $rows);

        $io->writeln("'$type' Total Size: " . $type_totals_item->GetSizeFormatted());
        $io->writeln("'$type' Total Lines: " . $type_totals_item->lines);
      }
    }
    else
    {
      $io->title('By file type');

      $header = array('Type', 'Size', 'Lines');
      $rows = array();

      foreach ($this->items_by_type as $type => $type_item)
      {
        $row = array();

        $row[] = $type;
        $row[] = $type_item->GetSizeFormatted();
        $row[] = $type_item->lines;

        $rows[] = $row;
      }

      $io->table($header, $rows);
    }

    $io->title('Totals');

    $totals_item = new MtHowManyTotalsItem();
    foreach ($this->items_by_type as $item)
    {
      $totals_item->AggregateItem($item);
    }

    $io->writeln('Size: ' . $totals_item->GetSizeFormatted());
    $io->writeln('Lines: ' . $totals_item->lines);
    $io->writeln('Pages by Size: ' . $totals_item->GetPagesCountByCharacters());
    $io->writeln('Pages by Lines: ' . $totals_item->GetPagesCountByLines());
  }

  /**
   * @var MtHowManyTypeItem[]
   */
  private array $items_by_type = array(); // extension => MtHowManyTypeItem

  private function ScanPath(string $path)
  {
    foreach(scandir($path) as $item)
    {
      if($item == '.' || $item == '..')
      {
        continue;
      }

      $full_path = $path . DIRECTORY_SEPARATOR . $item;

      if(is_dir($full_path))
      {

      }
      else
      {
        if(file_exists($full_path))
        {
          $info = pathinfo($full_path);
          $type = $info['extension'] ?? '[no extension]';

          $file_item = new MtHowManyFileItem($full_path);
          ($this->items_by_type[$type] ??= new MtHowManyTypeItem())->AddFile($file_item);
        }
      }
    }
  }
}
