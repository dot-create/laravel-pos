<?php

namespace App\Console\Commands;

use App\CustomerRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckQuoteExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotes:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move expired quotes to dispute status after 30 days';

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
    public function handle()
    {
        $expiredDate = Carbon::now()->subDays(30);
    
        CustomerRequest::where('status', 'Quote')
            ->where('updated_at', '<', $expiredDate)
            ->update(['status' => 'DisputeQuote']);
        
        $this->info('Expired quotes moved to dispute.');
    }
}
