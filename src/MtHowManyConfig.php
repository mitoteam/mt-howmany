<?php

namespace MiToTeam;

use Symfony\Component\Yaml\Yaml;

class MtHowManyConfig
{
  private $config = array();

  public function Load(string $full_file_path)
  {
    $this->config = Yaml::parseFile($full_file_path);
  }
}
