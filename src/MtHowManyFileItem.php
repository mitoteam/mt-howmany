<?php

namespace MiToTeam;

use Symfony\Component\String\UnicodeString;

class MtHowManyFileItem extends MtHowManyBaseItem
{
  public string $path;
  public string $full_path;

  public function __construct(string $full_path, string $relative_path)
  {
    $this->full_path = $full_path;
    $this->path = $relative_path;

    $this->size = filesize($full_path);
    $this->lines = $this->GetLinesCount();
    $this->characters = $this->GetCharactersCount();
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

  private function GetCharactersCount(): int
  {
    $r = 0;

    try
    {
      if($text = file_get_contents($this->full_path))
      {
        $text = new UnicodeString($text);

        $r = $text->length();
      }
    }
    catch (Symfony\Component\String\Exception\InvalidArgumentException)
    {
      //can not read file as utf-8 string
    }

    return $r;
  }

  public function GetCount(): int
  {
    return 1;
  }
}
