<?php

namespace MiToTeam;

class MtHowManyTotalsItem extends MtHowManyBaseItem
{
  public int $count = 0;

  public function GetCount(): int
  {
    return $this->count;
  }

  public function AggregateItem(MtHowManyBaseItem $item)
  {
    $this->count += $item->GetCount();
    $this->lines += $item->lines;
    $this->size += $item->size;
    $this->characters += $item->characters;
  }

  public function Reset()
  {
    $this->count = $this->lines = $this->size = $this->characters = 0;
  }
}
