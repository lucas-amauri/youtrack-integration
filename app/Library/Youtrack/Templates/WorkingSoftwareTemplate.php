<?php
namespace App\Library\Youtrack\Templates;

class WorkingSoftwareTemplate extends Template implements TemplateInterface {  

  public function header()
  {
    $this->entries["header"][] = ["Relatório de Horas e Atividades"];
    $this->entries["header"][] = ["Nome", "Lucas Amauri"];
    $this->entries["header"][] = ["Total de horas trabalhadas no mês:", $this->total];
    $this->entries["header"][] = ["Total de horas trabalhadas em decimal:", ""];
  }

  public function footer()
  {
    
  }

  public function addRow(array $data) {
    $this->entries["rows"][] = [
      date("d/m/Y", strtotime($data["created"])),
      date("H:i", strtotime($data["created"])),
      date("H:i", strtotime($data["finished"])),
      $data["duration"],
      $data["duration"],
      $data["project_name"],
      "", // CLIENTE
      $data["summary"],
      $data["text"],
    ];
  }
}