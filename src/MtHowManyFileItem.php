<?php

namespace MiToTeam;

class MtHowManyFileItem extends MtHowManyBaseItem
{
  public string $path;

  public function __construct(string $full_path)
  {
    $this->size = filesize($full_path);
    $this->lines = $this->GetLinesCount($full_path);

    $this->path = str_replace(MtHowMany::gi()->getPath(), '', $full_path);

    if(DIRECTORY_SEPARATOR == '\\')
    {
      $this->path = str_replace('\\', '/', $this->path);
    }
  }

  private function GetLinesCount(string $filename): int
  {
    $r = 0;

    $handle = fopen($filename, "r");

    while(!feof($handle))
    {
      fgets($handle);
      $r++;
    }

    fclose($handle);

    return $r;
  }
}
