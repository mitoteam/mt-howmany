<?php

namespace MiToTeam;

class MtHowManyTotalsItem extends MtHowManyBaseItem
{
  public function AggregateItem(MtHowManyBaseItem $item)
  {
    $this->lines += $item->lines;
    $this->size += $item->size;
  }

  public function Reset()
  {
    $this->lines = 0;
    $this->size = 0;
  }
}
