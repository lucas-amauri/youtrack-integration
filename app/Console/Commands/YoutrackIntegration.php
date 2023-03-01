<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Youtrack\ImportTimesheets;
use App\Library\Youtrack\Templates\WorkingSoftwareTemplate;

class YoutrackIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtrack:integrate {--year=} {--month=} --debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ImportTimesheets $ImportTimesheets)
    {
        $this->info("Import started...");

        $projects = explode(",", env("YOUTRACK_PROJECTS"));

        $ImportTimesheets->setTemplate(
            new WorkingSoftwareTemplate()
        );

        foreach ($projects as $project) {
            $this->info($project);
            $ImportTimesheets->create(
                $project, 
                (int)$this->option("year"), 
                (int)$this->option("month")
            );
        }        

        $this->info("Import finished");

        return 0;
    }
}
