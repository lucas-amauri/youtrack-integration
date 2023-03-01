<?php

namespace App\Library\Youtrack;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class ImportTimesheets extends Youtrack {  
  const DEBUG = TRUE;

  private $template;

  public function setTemplate($template) {
    $this->template = $template;
  }

  public function create($project, $year, $month) {
    if (!$project) throw "Project not defined";

    $filename = 'youtrack-'.$project.'-'.$year . '-' . $month .'.csv';

    Storage::disk()->delete($filename);

    $result = $this->request("api/issues?fields=id,summary,description,project(id)&query=project:" . $project);

    $total_min = 0;

    if ($this->template) {
      $this->template->clear();
    }

    if ($result) {
      $projectInfo = $this->request("/api/admin/projects/" . $project . "?fields=name");

      $rows = [];

      if (ImportTimesheets::DEBUG) {
        ECHO $projectInfo->name . PHP_EOL;
      }

      foreach ($result as $entry) {
        $workItems = $this->request("/api/issues/".$entry->id."/timeTracking/workItems?fields=date,duration(presentation,minutes),text,created,type(name),attributes()");

        $sheetEntry = [
          "summary" => $entry->summary,
          "project_name" => $projectInfo->name
        ];

        if (ImportTimesheets::DEBUG) {
          echo $entry->summary . "|" . count($workItems);
        }

        foreach ($workItems as $item)  {
          $date = Carbon::createFromTimestamp($item->created/1000);  
          
          if (ImportTimesheets::DEBUG) {
            echo PHP_EOL;

            echo '      ';
            echo $item->text . '|';
            echo $item->duration->minutes . '|';
            echo $date->format("d/m/Y");
            echo PHP_EOL;
          }

          if (!($date->year == $year && $date->month == $month)) {
            if (ImportTimesheets::DEBUG) {
              ECHO "Not in this month" . PHP_EOL;
            }
            continue 1;
          }

          if (!$item->duration->minutes) {
            if (ImportTimesheets::DEBUG) {
              ECHO "No duration" . PHP_EOL;
            }
            continue 1;
          }

          
          $sheetEntry["text"] = $item->text ? str_replace(["\n", "\r\n"], "", $item->text) : "";
          //$sheetEntry["duration"] = $item->duration->presentation;
          $sheetEntry["duration"] = $this->formatMin2HumanView($item->duration->minutes);
          $sheetEntry["duration_min"] = $item->duration->minutes;
          $sheetEntry["type"] = $item->type ? $item->type->name : "";
          $sheetEntry["created"] = $date->format("Y-m-d H:i:s");
          $sheetEntry["finished"] = $date->addMinutes($item->duration->minutes)->format("Y-m-d H:i:s");

          $total_min += $item->duration->minutes;

          $rows[] = $sheetEntry;
        } 
        
        if (ImportTimesheets::DEBUG) {
          echo PHP_EOL;       
          echo str_repeat("_", 30);       
          echo PHP_EOL;       
        }
      }

      $total_formatted = $this->formatMin2HumanView($total_min);

      if ($this->template && $rows) {
        $this->template->setTotal(
          $total_formatted
        );

        uasort($rows, function ($a, $b) {
          if (strtotime($a["created"]) > strtotime($b["created"])) {
            return 1;
          }
    
          return 0;
        });

        foreach ($rows as $row) {
          $this->template->addRow(
            $row
          );
        }

        $this->template->header();
        $this->template->body();
        $this->template->footer();

        $entries = $this->template->getEntries();

        foreach ($this->template->getSections() as $section) {
          if (!$entries[$section]) continue;

          foreach ($entries[$section] as $row) {
            Storage::append($filename, implode(";", $row));
          }
        }
      }
    }
  }

  public function formatMin2HumanView($total_min) {
    $total_hours = $total_min / 60;
    $min = ceil($total_min % 60);

    return floor($total_hours) . ":" . ($min < 10 ? "0" : "") . $min;
  }
}