<?php

namespace MiToTeam;

class MtHowManyFileItem extends MtHowManyBaseItem
{
  public string $path;
  public string $full_path;

  public function __construct(string $full_path, string $working_dir)
  {
    $this->full_path = $full_path;

    $this->size = filesize($full_path);
    $this->lines = $this->GetLinesCount();

    $this->path = str_replace($working_dir, '', $full_path);

    if(DIRECTORY_SEPARATOR == '\\')
    {
      $this->path = str_replace('\\', '/', $this->path);
    }
  }

  private function GetLinesCount(): int
  {
    $r = 0;

    $handle = fopen($this->full_path, "r");

    while(!feof($handle))
    {
      fgets($handle);
      $r++;
    }

    fclose($handle);

    return $r;
  }

  public function GetCount(): int
  {
    return 1;
  }
}
