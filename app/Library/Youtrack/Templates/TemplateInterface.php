<?php
namespace App\Library\Youtrack\Templates;

interface TemplateInterface {
  public function header();
  public function body();
  public function footer();

  public function addRow(array $row);
}