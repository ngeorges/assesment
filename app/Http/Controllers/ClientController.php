<?php

namespace App\Http\Controllers;

use App\Jobs\ImportClients;
use App\Models\Client;
use App\Models\ClientImport;
use App\Models\CreditCard;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;
use Illuminate\Support\Facades\Log;



class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('clients.index');
    }

    public function getClients(Request $request)
    {
        if ($request->ajax()) {
            $data = Client::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function getCreditCards(Request $request)
    {
        if ($request->ajax()) {
            $data = CreditCard::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function getImportLogs(Request $request)
    {
        if ($request->ajax()) {
            $data = ClientImport::with('users')->latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('users', function (ClientImport $clientImport) {
                    return $clientImport->users->name;
                })
                ->addColumn('status', function ($row) {
                    if($row->status){
                        $statusBadge = json_decode($row->status, true);
                        $status = '<span class="badge badge-'.$statusBadge['type'].'">'.$statusBadge['value'].'</span>';
                    }else{
                        // $status = '<span class="badge badge-danger">incomplete</span>';
                        $status = '<span class="badge badge-danger">NULL</span>';
                    }
                    return $status;
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Re-Import</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
    }

    public function creditcards()
    {
        return view('clients.creditcards');
    }

    public function import_form()
    {
        return view('clients.import');
    }

    public function import_store(Request $request)
    {
        $request->validate([
            // 'file' => 'required|file|mimetypes:application/json',
            'file' => 'required|file',
        ]);

        $user_id = Auth::id();

        //change filename
        $fileRename = time() . '_' . $user_id;
        $fileName = $fileRename . '.' . $request->file->extension();
        // upload to storage folder
        $request->file->move(storage_path('client_imports'), $fileName);

        // Get file from storage folder
        $uploaded_file = storage_path('client_imports/' . $fileName);

        if ($uploaded_file) {

            $uploaded_chunk = json_decode(file_get_contents($uploaded_file), true);

            // Log Import
            $clientImport = ClientImport::create([
                'read_count' => count($uploaded_chunk),
                'import_count' => '0',
                'import_attempts' => '1',
                'status' => json_encode(['type' => 'info','value' => 'pending']),
                'import_file' => $fileName,
                'user_id' => $user_id,
            ]);

            // $clientImport->status = ['type' => 'info','value' => 'pending'];
            // $clientImport->save();
            $clientImportId = $clientImport->id;

            $batch = Bus::batch([])->then(function (Batch $batch) use ($clientImport){
                // All jobs completed successfully...
                $clientImport->status = ['type' => 'success','value' => 'complete'];
                $clientImport->save();
                Log::notice('All jobs completed successfully');
            })->catch(function (Batch $batch, Throwable $e) use ($clientImport){
                // First batch job failure detected...
                $clientImport->status = ['type' => 'danger','value' => 'failed'];
                $clientImport->save();
                Log::notice('First batch job failure detected');
            })->finally(function (Batch $batch) {
                // The batch has finished executing...
                Log::notice('The batch has finished executing');

            })->dispatch();

            foreach (array_chunk($uploaded_chunk, 500) as $uploaded_data) {
                // ImportClients::dispatch($uploaded_data, $clientImportId);
                $batch->add(new ImportClients($uploaded_data, $clientImportId));
            }

            return redirect()->back()->with('success', 'Client import is in progress. Please check the logs for the import status.');
        } else {
            return redirect()->back()->withErrors('File upload failed. Please contact the Admin.');
        }

        // print_r($uploaded_data);
        // exit();

        $read_count = count($uploaded_data); // Count records in file
        $import_count = 0;
        $update_client_count = 0;

        // Log Import
        $clientImport = ClientImport::create([
            'read_count' => $read_count,
            'import_count' => '0',
            'import_attempts' => '1',
            'import_file' => $fileName,
            'user_id' => $user_id,
        ]);

        $clientImportId = $clientImport->id;

        foreach ($uploaded_data as $clientObj) {
            // Create Clients
            $clientObj['date_of_birth'] = date('Y-m-d H:i:s', strtotime($clientObj['date_of_birth'])); // Format date of birth
            // Client::create($clientObj);

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
                $update_client_count++;
            }

            $import_count++; // Count imported records

            $clientImport->import_count = $import_count;
            $clientImport->save();
        }

        if ($read_count == $import_count) {
            return redirect()->back()->with('success', 'Imported all ' . $import_count . ' records succesfully with ' . $update_client_count . ' Client update(s)');
        } else {
            return redirect()->back()->withErrors('Only imported ' . $import_count . ' out of ' . $read_count . ' records with ' . $update_client_count . ' Client update(s)');
        }
    }

    public function import_logs()
    {
        return view('clients.import_logs');
    }
}
