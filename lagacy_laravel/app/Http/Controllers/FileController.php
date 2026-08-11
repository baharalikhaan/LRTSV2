<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{



    public function getFolderStructure($path)
    {
        $folders = [];

        // Get the contents of the directory
        $contents = scandir($path);

        // Iterate through each item in the directory
        foreach ($contents as $item) {
            // Ignore . and .. directories
            if ($item == '.' || $item == '..') {
                continue;
            }

            // Check if the item is a directory
            if (is_dir($path . DIRECTORY_SEPARATOR . $item)) {
                // Recursively get the subfolder structure
                $folders[] = [
                    'name' => $item,
                    'type' => 'folder',
                    'subfolders' => $this->getFolderStructure($path . DIRECTORY_SEPARATOR . $item),
                    'files' => [] // Empty array for files in this folder (you can fill it if you want)
                ];
            } else {
                // If it's a file, you can include it in the array if needed
                $folders[] = [
                    'name' => $item,
                    'type' => 'file'
                ];
            }
        }

        return $folders;
    }


    public function getFolderTree(Request $request)
    {
        // Fetch folder structure
        $folders = $this->getFolderStructure('/');

        dd($folders);

        return view('browse', compact('folders'));
    }
}
