<?php

namespace MiToTeam;

use Symfony\Component\Console\Output\OutputInterface;

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

  public function Run(OutputInterface $output)
  {
    $this->ScanPath($this->getPath());
  }

  private array $items = array(); // extension => MtHowManyItem

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
          //mt_debug('$info', $info);
          $type = $info['extension'] ?? '[no extension]';

          $file_item = new MtHowManyFileItem($full_path);
          ($this->items[$type] ??= new MtHowManyTypeItem())->AddFile($file_item);
        }
      }

      echo print_r($this->items, 1) . "\n";
    }
  }
}
