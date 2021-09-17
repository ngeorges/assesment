<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientImport;
use App\Models\CreditCard;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportClients implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uploaded_data;
    public $clientImportId;
    public $queueImport;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($uploaded_data, $clientImportId, $queueImport = null)
    {
        $this->uploaded_data = $uploaded_data;
        $this->clientImportId = $clientImportId;
        $this->queueImport = $queueImport;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // foreach ($this->uploaded_data as $clientObj) {
        foreach (array_slice($this->uploaded_data, $this->queueImport) as $clientObj) {

            $clientImport = ClientImport::find($this->clientImportId);
            $clientImport->status = ['type' => 'primary','value' => 'running'];
            $clientImport->save();

            // Get Client age
            if ($clientObj['date_of_birth']) {
                // Convert date of birth
                $clientObj['date_of_birth'] = date('Y-m-d H:i:s', strtotime($clientObj['date_of_birth'])); // Format date of birth
                $age = Carbon::parse(date('Y-m-d', strtotime($clientObj['date_of_birth'])))->age;
            } else {
                $age = null;
            }


            // Check if client age is between 18 and 65
            if (($age > 17 && $age < 66) || $age == null) {
                $client = Client::updateOrCreate(
                    [
                        'email' => $clientObj['email'],
                    ],
                    [
                        'name' => $clientObj['name'],
                        'address' => $clientObj['address'],
                        'checked' => $clientObj['checked'],
                        'description' => $clientObj['description'],
                        'interest' => $clientObj['interest'],
                        'date_of_birth' => $clientObj['date_of_birth'],
                        'account' => $clientObj['account'],
                    ]
                );

                // Create Credit Cards
                $creditCardObj = $clientObj['credit_card'];
                $creditCardObj['account'] = $clientObj['account']; // Add client account to Credit Card Array
                // CreditCard::create($creditCardObj);

                $creditCard = CreditCard::updateOrCreate(
                    [
                        'type' => $creditCardObj['type'],
                        'number' => $creditCardObj['number'],
                    ],
                    [
                        'name' => $creditCardObj['name'],
                        'account' => $creditCardObj['account'],
                        'expirationDate' => $creditCardObj['expirationDate'],
                    ]
                );

    
                    
        

                if (!$client->wasRecentlyCreated && $client->wasChanged()) {
                    // updateOrCreate performed an update
                    // $update_client_count++;

                    // TO-DO: Count client updates
                    // ClientImport::find($this->clientImportId)->increment('client_update_count');

                }

                // Count imported records
                $clientImport->import_count = $clientImport->import_count + 1;
                $clientImport->save();

            } else {
                // Count skiped records
                $clientImport->import_skiped = $clientImport->import_skiped + 1;
                $clientImport->save();
            }

            // Track record queue 
            $clientImport->queue = $clientImport->queue + 1;
            $clientImport->save();

            // Check if all records have been queued
            if($clientImport->read_count == $clientImport->queue){
                // $clientImport->status = 1;
                // $clientImport->save();
                // $clientImport->status = ['type' => 'success','value' => 'complete'];
                // $clientImport->save(); 
            }

        }
    }
}
