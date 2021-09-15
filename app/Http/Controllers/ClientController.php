<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\CreditCard;
use App\Models\ClientImport;
use DataTables;

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
                ->addColumn('action', function($row){
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
                ->addColumn('action', function($row){
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
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Re-Import</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
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
        $uploaded_data = json_decode(file_get_contents($uploaded_file), true); 

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
            'user_id' => $user_id
        ]);

        $clientImportId = $clientImport->id;

        foreach ($uploaded_data as $clientObj) {
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

        if($read_count == $import_count){
            return redirect()->back()->with('success', 'Imported all '.$import_count.' records succesfully with '.$update_client_count.' Client update(s)');
        }else{
            return redirect()->back()->withErrors('Only imported '.$import_count.' out of '.$read_count.' records with '.$update_client_count.' Client update(s)');
        }
    }

    public function import_logs()
    {
        return view('clients.import_logs');
    }
}
