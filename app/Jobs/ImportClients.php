<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\CreditCard;
use App\Models\ClientImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportClients implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uploaded_data;
    public $user_id;
    public $fileName;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($uploaded_data, $user_id, $fileName)
    {
        $this->uploaded_data = $uploaded_data;
        $this->user_id = $user_id;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $read_count = count($this->uploaded_data); // Count records in file
        $import_count = 0;
        $update_client_count = 0;

        // Log Import
        $clientImport = ClientImport::create([
            'read_count' => $read_count,
            'import_count' => '0',
            'import_attempts' => '1',
            'import_file' => $this->fileName,
            'user_id' => $this->user_id
        ]);

        $clientImportId = $clientImport->id;

        foreach ($this->uploaded_data as $clientObj) {
            // Create Clients
            $clientObj['date_of_birth'] = date('Y-m-d H:i:s', strtotime($clientObj['date_of_birth'])); // Format date of birth
            // Client::create($clientObj);

            $client = Client::updateOrCreate(
                [
                    'email' => $clientObj['email']
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
                    'number' => $creditCardObj['number']
                ],
                [
                    'name' => $creditCardObj['name'],
                    'account' => $creditCardObj['account'],
                    'expirationDate' => $creditCardObj['expirationDate'],
                ]
            );


            if(!$client->wasRecentlyCreated && $client->wasChanged()){
                // updateOrCreate performed an update
                $update_client_count++;
            }
            
            $import_count++; // Count imported records

            $clientImport->import_count = $import_count;
            $clientImport->save();
        }
    }
}
