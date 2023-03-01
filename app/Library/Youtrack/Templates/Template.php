<?php
namespace App\Library\Youtrack\Templates;

class Template {
  protected array $entries = [];
  protected string $total;
  protected $sections = ["header", "rows"];

  public function clear() {
    $this->entries = [];
  }

  public function setTotal($total) {
    $this->total = $total;
  }

  public function getEntries() {
    return $this->entries;
  }

  public function getSections() {
    return $this->sections;
  }

  public function body()
  {
  }
}