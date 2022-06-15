<?php

namespace MiToTeam;

class MtHowManyTypeItem extends MtHowManyBaseItem
{
  /**
   * @var MtHowManyFileItem[]
   */
  private array $files = array();

  public function AddFile(MtHowManyFileItem $file)
  {
    $this->files[] = $file;

    $this->lines += $file->lines;
    $this->size += $file->size;
    $this->characters += $file->characters;
  }

  public function GetFilesList(): array
  {
    return $this->files;
  }

  public function GetCount(): int
  {
    return count($this->files);
  }
}
