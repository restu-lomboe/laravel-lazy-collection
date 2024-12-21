<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;

class CustomerController extends Controller
{
    public function index()
    {
        // $filename = public_path('uploads/customers-1000000.csv');
        // // get time
        // $start = Carbon::now();

        // LazyCollection::make(function () use ($filename) {
        //     $handle = fopen($filename, 'r');
        //     while (($row = fgets($handle)) !== false) {
        //         yield str_getcsv($row);
        //     }
        // })
        // ->map(function ($log) {
        //     return [
        //         'customerId' => $log[0],
        //         'first_name' => $log[1],
        //         'last_name' => $log[2],
        //         'company' => $log[3],
        //         'city' => $log[4],
        //         'country' => $log[5],
        //         'phone' => $log[6],
        //         'email' => $log[7],
        //     ];
        // })
        // ->chunk(500)
        // ->each(function ($chunk) {
        //     Customer::insert($chunk->toArray());
        // });

        // save customer with default system
        // $handle = fopen($filename, 'r');
        // while (($row = fgets($handle)) !== false) {
        //     $log = str_getcsv($row);
        //     Customer::insert([
        //         'customerId' => $log[0],
        //         'first_name' => $log[1],
        //         'last_name' => $log[2],
        //         'company' => $log[3],
        //         'city' => $log[4],
        //         'country' => $log[5],
        //         'phone' => $log[6],
        //         'email' => $log[7],
        //     ]);
        // }

        // $end = Carbon::now();
        // // get time duration
        // $duration = $start->diffInSeconds($end);
        // dd($duration);

        // add session memory
        // session()->put('memory', []);
        // session()->put('time', []);
        // session()->put('resource', []);

        return view('customer');
    }

    public function store(Request $request)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $file->move(public_path('uploads'), $fileName);

        return response()->json([
            'message' => 'File uploaded successfully!',
        ]);
    }

    public function uploadChunks(Request $request)
    {
        $file = $request->file('file');
        $tempPath = public_path('uploads');

        // Simpan chunk sementara
        $file->move($tempPath, $request->resumableFilename . '.part' . $request->resumableChunkNumber);

        // Jika ini adalah chunk terakhir, gabungkan semua
        if ($this->allChunksUploaded($tempPath, $request->resumableFilename, $request->resumableTotalChunks)) {
            $finalPath = public_path('uploads/' . $request->resumableFilename);
            $this->combineChunks($tempPath, $finalPath, $request->resumableFilename, $request->resumableTotalChunks);

            // Hapus chunk sementara
            $this->cleanupChunks($tempPath, $request->resumableFilename);

            $filename = public_path('uploads/'. $request->resumableFilename);
            $this->insertData($filename);

            return response()->json([
                'message' => 'File uploaded successfully',
            ]);
        }

        return response()->json(['message' => 'Chunk uploaded']);
    }

    private function allChunksUploaded($tempPath, $filename, $totalChunks)
    {
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!file_exists($tempPath . '/' . $filename . '.part' . $i)) {
                return false;
            }
        }
        return true;
    }

    private function combineChunks($tempPath, $finalPath, $filename, $totalChunks)
    {
        $output = fopen($finalPath, 'wb');
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkPath = $tempPath . '/' . $filename . '.part' . $i;
            $chunk = fopen($chunkPath, 'rb');
            stream_copy_to_stream($chunk, $output);
            fclose($chunk);
        }
        fclose($output);
    }

    private function cleanupChunks($tempPath, $filename)
    {
        foreach (glob($tempPath . '/' . $filename . '.part*') as $file) {
            unlink($file);
        }
    }

    private function insertData($filename){
        LazyCollection::make(function () use ($filename) {
            $handle = fopen($filename, 'r');
            while (($row = fgets($handle)) !== false) {
                yield str_getcsv($row);
            }
        })
        ->map(function ($log) {
            return [
                'customerId' => $log[0],
                'first_name' => $log[1],
                'last_name' => $log[2],
                'company' => $log[3],
                'city' => $log[4],
                'country' => $log[5],
                'phone' => $log[6],
                'email' => $log[7],
            ];
        })
        ->chunk(500)
        ->each(function ($chunk) {
            Customer::insert($chunk->toArray());
        });
    }
}
