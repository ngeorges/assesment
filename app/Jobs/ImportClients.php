<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientImport;
use App\Models\CreditCard;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportClients implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uploaded_data;
    public $clientImportId;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($uploaded_data, $clientImportId)
    {
        $this->uploaded_data = $uploaded_data;
        $this->clientImportId = $clientImportId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        foreach ($this->uploaded_data as $clientObj) {

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
                $clientImport = ClientImport::find($this->clientImportId);
                $clientImport->import_count = $clientImport->import_count + 1;
                $clientImport->save();

            } else {
                // TO-DO: Count the records that where skiped becuase of client age
            }

        }
    }
}
