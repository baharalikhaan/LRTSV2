<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Models\FromConfTool;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileExplorerController extends Controller
{


    //For project-wise search
    public function listProjects(Request $request)
    {
        $search = $request->input('search');

        $projects = FromConfTool::when($search, function ($query, $search) {
            return $query->where('old_project_id', 'like', "%{$search}%")
                ->orWhere('cycle', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10); // pagination

        return view('file_explorer/listProjects', compact('projects', 'search'));
    }



    //File explorer
    private $rootFolderLabels = [
        'ethical_approvals'     => ['label' => 'Ethical Approvals',     'icon' => 'fa-balance-scale text-teal'],
        'final_reports'         => ['label' => 'Final Reports',         'icon' => 'fa-file-lines text-success'],
        'lpi_project_proposals' => ['label' => 'LPI Project Proposals', 'icon' => 'fa-lightbulb text-warning'],
        'progress_reports'      => ['label' => 'Progress Reports',      'icon' => 'fa-chart-line text-info'],
        'readiness_reports'     => ['label' => 'Readiness Reports',     'icon' => 'fa-list-check text-secondary'],
        'reviewers_agreements'  => ['label' => 'Reviewers Agreements',  'icon' => 'fa-handshake text-danger'],
    ];


    public function index()
    {
        $basePath = storage_path('app/uploads');
        $structure = $this->getDirectoryTree($basePath, '');

        return view('file_explorer.index', [
            'structure' => $structure,
            'folderLabels' => $this->rootFolderLabels
        ]);
    }

    private function getDirectoryTree($fullPath, $relativePath)
    {
        $result = [];
        if (!File::exists($fullPath)) {
            return $result;
        }
        foreach (File::directories($fullPath) as $dir) {
            $name = basename($dir);
            $relPath = $relativePath . '/' . $name;

            $result[] = [
                'type' => 'folder',
                'name' => $name,
                'path' => $relPath,
                'relative_path' => $name, // <- Needed for root check
                'children' => $this->getDirectoryTree($dir, $relPath)
            ];
        }

        foreach (File::files($fullPath) as $file) {
            $result[] = [
                'type' => 'file',
                'name' => $file->getFilename(),
                'path' => $relativePath . '/' . $file->getFilename()
            ];
        }

        return $result;
    }

    public function downloadZip(Request $request)
    {
        $folder = $request->get('folder');


        $baseFolder = storage_path('app/uploads/');

        // Full path to the folder being zipped
        $fullPath = realpath($baseFolder . $folder);

        // Prevent zip traversal attack
        if (!$fullPath || strpos($fullPath, realpath($baseFolder)) !== 0) {
            abort(403, 'Invalid folder path');
        }

        if (!File::exists($fullPath)) {
            abort(404, 'Folder not found');
        }

        $zipFileName =    ltrim(str_replace('/', '_', $folder), '_') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $this->addFolderToZip($fullPath, $zip, strlen($fullPath) + 1);


            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } else {
            abort(500, 'Could not create ZIP');
        }
    }

    public function downloadfile(Request $request)
    {
        // Get the relative path inside storage/app
        $file = ltrim($request->get('file'), '/'); // remove leading slash if any

        // Full path to the file
        $fullPath = storage_path('app/uploads/' . $file);

        // Check if file exists
        if (!file_exists($fullPath)) {
            abort(404, 'File not found.');
        }

        // Return file as download response
        return response()->download($fullPath);
    }




    private function addFolderToZip($folder, ZipArchive $zip, $cutFromPathLength)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            $localPath = substr($realPath, $cutFromPathLength);
            if ($file->isDir()) {
                $zip->addEmptyDir($localPath);
            } else {
                $zip->addFile($realPath, $localPath);
            }
        }
    }
}
