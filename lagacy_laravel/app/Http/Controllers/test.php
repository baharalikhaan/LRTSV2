<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
//require_once 'vendor\autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//require 'vendor\autoload.php';

//For excel import
use App\Models\FromConfTool;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class test extends Controller
{
    public function __construct()
    {

        //  dd(Auth::user()->type);
        //  dd($user);
        //  $this->middleware('auth');
    }

    public function test()
    {

        //     dd(hash('sha256'));
        $nonce = time();
        $passHash = hash('sha256', $nonce . "xAqYGhr7yp");
        // dd($passHash);
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://www.conftool.net/demo/igrants_26n/rest.php', [
            'query' => [
                'nonce' => $nonce,
                'passhash' => $passHash,
                'page' => 'adminExport',
                'export_select' => 'topics'

            ],
            'verify' => false
        ]);

        // "200"

        // 'application/json; charset=utf8'
        echo $res->getBody()->getContents();
    }


    //For excel import
    public function excelForm()
    {

        return view('excel');
    }

    public function pdfForm()
    {

        return view('pdf');
    }

    public function excelImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        $expectedHeaders = ['id', 'old_project_id', 'cycle', 'title', 'author', 'email', 'added', 'created_at', 'updated_at', 'pillars', 'tags'];
        $excelHeaders = array_shift($data);
        if (count($excelHeaders) !== count($expectedHeaders)) {

            return redirect()->back()->with('successexcel', '<h1><i style="color:red; border-radius: 50%;  font-size: 4rem;" class="fas fa fa-times"></i> </h1><h3>Error!</h3> Number of columns does not match');
        }

        if ($excelHeaders !== $expectedHeaders) {
            return redirect()->back()->with('successexcel', '<h1><i style="color:red; border-radius: 50%;  font-size: 4rem;" class="fas fa fa-times"></i> </h1><h3>Error!</h3> Order of columns does not match');
        }
        foreach (array_slice($data, 1) as $row) {

            if ($row[0] <> null)
                FromConfTool::create([
                    'id' => null,
                    'old_project_id' => $row[1],
                    'cycle' => $row[2],
                    'title' => $row[3],
                    'author' => $row[4],
                    'email' => $row[5],
                    'added' => $row[6],
                    'created_at' => $row[7],
                    'updated_at' => $row[8],
                    'pillars' => $row[9],
                    'tags' => $row[10],

                ]);
        }
        //Total <b>' . count($data) . '</b> records imported.'
        return redirect()->back()->with('successexcel', '<h1><i style="color:green; border-radius: 50%;  font-size: 4rem;" class="fa-solid fa fa-check"></i> </h1><h3>Data Imported Successfully</h3>');
    }


    public function uploadProposals(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,zip',
        ]);

        $path = $request->file('file')->getRealPath();


        if ($request->hasFile('file')) {
            $zipFile = $request->file('file');
            $fileName = $zipFile->getClientOriginalName();

            // Store the uploaded zip file
            $zipFile->storeAs('uploads', $fileName);

            // Extract the contents of the zip file
            // $extractPath = storage_path('app/uploads/' . pathinfo($fileName, PATHINFO_FILENAME));
            $extractPath = storage_path('app/uploads/');

            //     File::makeDirectory($extractPath); // Create directory to extract files
            $zip = new \ZipArchive;
            if ($zip->open(storage_path('app/uploads/' . $fileName)) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
            }

     
            File::delete(storage_path('app/uploads/' . $fileName));
        }

        return redirect()->back()->with('successzipproposals', '<h1><i style="color:green; border-radius: 50%;  font-size: 4rem;" class="fa-solid fa fa-check"></i> </h1><h3>Files uploaded Successfully</h3>');
    }
}
