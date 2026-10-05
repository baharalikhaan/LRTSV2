<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use phpseclib3\Net\SFTP;
use ZipArchive;
use App\Models\FileMetadata;
use File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FtpController extends Controller
{

    // 1. Ingest: Upload and extract the zip files, generating metadata for each.
    // 2. Archival Storage: Store extracted files locally and upload them to the FTP server.
    // 3. Data Management: Use the database to store file paths, metadata, and file hashes.
    // 4. Access: Set up routes and controllers to allow file retrieval from the archive.
    // 5. Preservation Planning: Implement backup strategies and consider file format migration.
    // 6. Integrity Verification: Hash each file and verify against stored hashes to ensure data hasn't been altered.

    protected $sftp;

    public function __construct()
    {
        $sftp_server =   config('filesystems.sftp.host');
        $sftp_username =  config('filesystems.sftp.username');
        $sftp_password =   config('filesystems.sftp.password');
        $this->sftp = new SFTP($sftp_server);
        if (!$this->sftp->login($sftp_username, $sftp_password)) {

            return view('ftp', [
                'directory' => null,
                'files' => null,
                'folders' => null,
            ]);
            //    abort(500, 'Could not log in to SFTP server');
        }
    }

    //main backup route
    public function systemBackup()
    {

        //zip the files
        $folderPath = 'D:\Personal\Projects\New Projects\Commercial Projects\Students\LRTS\LRTS';
        $zipFilePath = 'D:/SourceCode.zip';
        $this->zipFolder($zipFilePath, $folderPath);

        //take db backup
        $dbFilePath = "D:\Database.sql";
        $this->dbbackup();

        //upload the backup files
        $folder = date('Y-m-d');
        if (!$this->sftp->is_dir($folder)) {
            $this->sftp->mkdir($folder, -1, true);
        }
        $this->upload($zipFilePath, '/' . $folder);
        $this->upload($dbFilePath, '/' . $folder);

        // remove the backup files
        if (File::exists($zipFilePath)) {
            File::delete($zipFilePath);
        }


        if (File::exists($dbFilePath)) {
            File::delete($dbFilePath);
        }
    }


    public function dbbackup()
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $command = "mysqldump --host={$dbHost} --port={$dbPort} --user={$dbUser} --password={$dbPass} {$dbName} > \"D:\Database.sql\" ";
        $output = null;
        $returnVar = null;
        exec($command, $output, $returnVar);
        return $returnVar;
    }


    //Zipping
    public function zipFolder($zipFilePath, $folderPath)
    {
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['message' => 'Failed to create zip file'], 500);
        }
        $this->addFolderToZip($folderPath, $zip);
        $zip->close();
    }

    private function addFolderToZip($folderPath, ZipArchive $zip, $parentFolder = '')
    {
        $folderPath = realpath($folderPath);
        if (!$folderPath || !is_dir($folderPath)) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $relativePath = substr($file->getPathname(), strlen($folderPath) + 1);
                $zip->addFile($file->getPathname(), $parentFolder . '/' . $relativePath);
            }
        }
    }


    //upload to ftp
    public function upload($localFilePath, $remoteFolder)
    {
        if (!$this->sftp->is_dir($remoteFolder)) {
            $this->sftp->mkdir($remoteFolder, -1, true);
        }

        $fileName = basename($localFilePath);
        $remoteFilePath = rtrim($remoteFolder, '/') . '/' . $fileName;
        return $this->sftp->put($remoteFilePath, $localFilePath, SFTP::SOURCE_LOCAL_FILE);
    }


    public function listFilesAndFolders(Request $request)
    {


        $directory = $request->get('dir', '/');
        if (!$this->sftp->chdir($directory)) {
            abort(500, 'Could not change directory to ' . $directory);
        }
        $contents = $this->sftp->nlist();

        $files = [];
        $folders = [];

        foreach ($contents as $item) {
            if ($this->sftp->is_dir($item)) {
                $folders[] = $item;
            } else {
                $files[] = $item;
            }
        }
        return view('ftp', [
            'directory' => $directory,
            'files' => $files,
            'folders' => $folders,
        ]);
    }


    public function downloadFile(Request $request)
    {
        // Get the file path from the request
        $file = $request->get('file');
        dd($file);
        dd($this->sftp->file_exists($file));
        // Check if the file path is provided
        if (empty($file)) {
            return back()->with('error', 'No file specified.');
        }


        try {

            if (!$this->sftp->file_exists($file)) {
                return back()->with('error', 'File not found.');
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'sftp');
            $handle = fopen($tempFile, 'w');

            $content = $sftp->get($file);
            if ($content === false) {
                fclose($handle);
                return back()->with('error', 'Could not download the file.');
            }

            fwrite($handle, $content);
            fclose($handle);

            $filename = basename($file);

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }



    private function processFiles($directory)
    {
        set_time_limit(60);
        $dateFolder = date('Y-m-d');
        $this->processDirectory($directory, $directory, $dateFolder);
        return response()->json(['message' => 'Files and folders processed, metadata saved, and uploaded successfully.']);
    }

    private function processDirectory($currentDirectory, $baseDirectory, $remoteRoot)
    {
        $excludedDirectories = ['vendor', '.vscode', '.git'];
        if (in_array(basename($currentDirectory), $excludedDirectories)) {
            return;
        }

        $allFiles = File::allFiles($currentDirectory, true);
        $files = array_filter($allFiles, function ($file) {
            return !is_dir($file);
        });

        $subDirectories = $this->getAllDirectories($currentDirectory);
        $relativeDirPath = str_replace($baseDirectory, '', $currentDirectory);

        $remoteFolder = ltrim($relativeDirPath, '/');
        $remoteFolder = $remoteRoot . '/' . $remoteFolder;
        if (!$this->sftp->is_dir($remoteFolder)) {
            $this->sftp->mkdir($remoteFolder, -1, true);
        }

        // Process all files in the current directory
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $path = $file->getPathname();
            $size = $file->getSize();
            $mimeType = File::mimeType($path);
            $extension = $file->getExtension();
            $lastModified = $file->getMTime();
            $hash = hash_file('sha256', $path);

            // Upload on SFTP
            $relativeFilePath = ltrim(str_replace($baseDirectory, '', $file->getPathname()), '/');
            $remoteFilePath = $remoteRoot . '/' . $relativeFilePath;
            $this->sftp->put($remoteFilePath, $path, SFTP::SOURCE_LOCAL_FILE);

            // Dump the metadata with hash to the database
            FileMetadata::create([
                'filename' => $filename,
                'path' => str_replace('/', '', $remoteFilePath), //$file->getRelativePath(),
                'mime_type' => $mimeType,
                'size' => $size,
                'extension' => $extension,
                'hash' => $hash,
                'last_modified' => date('Y-m-d H:i:s', $lastModified),
            ]);
        }

        foreach ($subDirectories as $subDirectory) {
            if (in_array(basename($subDirectory), $excludedDirectories)) {
                continue;
            }
            $this->processDirectory($subDirectory, $baseDirectory, $remoteRoot);
        }
    }

    private function getAllDirectories($directory)
    {
        $allDirectories = [];
        $directories = scandir($directory);

        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $fullPath = $directory . DIRECTORY_SEPARATOR . $dir;

            if (is_dir($fullPath)) {
                $allDirectories[] = $fullPath;
                $allDirectories = array_merge($allDirectories, $this->getAllDirectories($fullPath));
            }
        }

        return $allDirectories;
    }
}
