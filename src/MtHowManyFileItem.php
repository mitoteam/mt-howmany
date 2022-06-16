<?php

namespace MiToTeam;

use Symfony\Component\String\UnicodeString;

class MtHowManyFileItem extends MtHowManyBaseItem
{
  public string $path;
  public string $full_path;

  public function __construct(string $full_path, MtHowMany $app)
  {
    parent::__construct($app);

    $this->full_path = $full_path;
    $this->path = $app->GetRelativePath($full_path);

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
    catch (\Throwable $e)
    {
      $this->app->AddError('File: ' . $this->path .  '  Message: ' . $e->getMessage());
    }

    return $r;
  }

  public function GetCount(): int
  {
    return 1;
  }
}
