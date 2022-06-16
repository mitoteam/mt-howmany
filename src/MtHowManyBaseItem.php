<?php

namespace MiToTeam;

abstract class MtHowManyBaseItem
{
  protected MtHowMany $app;

  public function __construct(MtHowMany $app)
  {
    $this->app = $app;
  }

  public const LINES_ON_PAGE = 40;
  public const CHARACTERS_ON_PAGE = 3600;

  public int $size = 0;
  public int $characters = 0;
  public int $lines = 0;

  public function GetSizeFormatted(): string
  {
    $base = 1024;

    if($this->size > $base ** 3)
    {
      $suffix = 'Gb';
      $value = $this->size / ($base ** 3);
    }
    elseif($this->size > $base ** 2)
    {
      $suffix = 'Mb';
      $value = $this->size / ($base ** 2);
    }
    elseif($this->size > $base)
    {
      $suffix = 'Kb';
      $value = $this->size / $base;
    }
    else
    {
      $suffix = '';
      $value = $this->size;
    }

    if(!$suffix)
      $digits = 0;
    elseif($value < 10)
      $digits = 2;
    elseif($value < 100)
      $digits = 1;
    else
      $digits = 0;

    $r = sprintf('%.' . $digits . 'f', $value);

    $r .= $suffix;

    return $r;
  }

  public function GetPagesCountByCharacters(): int
  {
    return (int)ceil($this->characters / $this->app->GetConfig()->GetCharactersPerPage());
  }

  public function GetPagesCountByLines(): int
  {
    return (int)ceil($this->lines / $this->app->GetConfig()->GetLinesPerPage());
  }

  abstract public function GetCount(): int;
}
