<?php

namespace App\Http\Controllers;

use App\Models\BlockModel as BlockModel;
use Illuminate\Http\Request;
use phpseclib3\Net\SFTP;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlockChainController extends Controller
{

    protected $sftp;

    public function __construct()
    {
        // $sftp_server =   config('filesystems.sftp.host');
        // $sftp_username =  config('filesystems.sftp.username');
        // $sftp_password =   config('filesystems.sftp.password');

        // $this->sftp = new SFTP($sftp_server);

        // if (!$this->sftp->login($sftp_username, $sftp_password)) {

        //     abort(500, 'Could not log in to SFTP server');
        // }
    }

    public function summary()
    {


        // Step 1: Fetch all master project records
        $projects = DB::table('projects as p')
            ->Leftjoin('cycle as c', 'c.id', '=', 'p.cycle')
            ->join('users as u', 'u.id', '=', 'p.user_id')

            ->select(
                'c.id as cycle_id',
                'c.cycle_title',
                'c.prog_rpt_deadline',
                'c.extended_prog_rpt_deadline',
                'c.final_rpt_deadline',
                'c.extended_final_rpt_deadline',
                'p.id as project_id',
                'p.old_project_id as old_project_id',
                'p.title as project_title',
                'p.total_score',
                'p.created_at as project_registration_date',
                'p.status',
                'p.user_id as lpi'

            )
            ->where('p.id', '=', '15')
            ->get();


        // Step 2: Fetch detailed records for each project and organize the data
        foreach ($projects as $project) {

            //final report file
            $proposalpath = 'uploads/lpi_project_proposals/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
            if (Storage::exists($proposalpath)) {
                $fullPath = storage_path('app/' . $proposalpath);
                $fileHash = hash_file('sha256',  $fullPath);
                $fileSize = filesize($fullPath);
                $fileModifiedTime = filemtime($fullPath);
                $lastModified = date("Y-m-d H:i:s",   $fileModifiedTime);

                $fileMetadata = [
                    'file_path' => $fullPath,
                    'file_hash' => $fileHash,
                    'file_size' => $fileSize,
                    'last_modified' =>  $lastModified,
                ];
                $project->proposal_file = 'Yes';
                $project->proposal_file_metadata =  $fileMetadata;
            } else {
                $project->proposal_file = 'No proposal exists';
            }

            //final report file
            $progressreportpath = 'uploads/progress_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
            if (Storage::exists($progressreportpath)) {
                $fullPath = storage_path('app/' . $progressreportpath);
                $fileHash = hash_file('sha256',  $fullPath);
                $fileSize = filesize($fullPath);
                $fileModifiedTime = filemtime($fullPath);
                $lastModified = date("Y-m-d H:i:s",   $fileModifiedTime);

                $fileMetadata = [
                    'file_path' => $fullPath,
                    'file_hash' => $fileHash,
                    'file_size' => $fileSize,
                    'last_modified' =>  $lastModified,
                ];
                $project->progress_report_file = 'Yes';
                $project->progress_report_file_metadata =  $fileMetadata;
            } else {
                $project->progress_report_file = 'No progress report exists';
            }


            //final report file
            $finalreportpath = 'uploads/final_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
            if (Storage::exists($finalreportpath)) {

                $project->final_report_file = 'Yes';

                $fullPath = storage_path('app/' . $finalreportpath);
                $fileHash = hash_file('sha256',  $fullPath);
                $fileSize = filesize($fullPath);
                $fileModifiedTime = filemtime($fullPath);
                $lastModified = date("Y-m-d H:i:s",   $fileModifiedTime);

                $fileMetadata = [
                    'file_path' => $fullPath,
                    'file_hash' => $fileHash,
                    'file_size' => $fileSize,
                    'last_modified' =>  $lastModified,
                ];

                $project->final_report_file_metadata =  $fileMetadata;
            } else {
                $project->final_report_file = 'No final report exists';
            }


            //final report file
            $readinessreportpath = 'uploads/final_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
            if (Storage::exists($readinessreportpath)) {

                $project->readiness_report_file = 'Yes';

                $fullPath = storage_path('app/' . $readinessreportpath);
                $fileHash = hash_file('sha256',  $fullPath);
                $fileSize = filesize($fullPath);
                $fileModifiedTime = filemtime($fullPath);
                $lastModified = date("Y-m-d H:i:s",   $fileModifiedTime);

                $fileMetadata = [
                    'file_path' => $fullPath,
                    'file_hash' => $fileHash,
                    'file_size' => $fileSize,
                    'last_modified' =>  $lastModified,
                ];

                $project->readiness_report_file_metadata =  $fileMetadata;
            } else {
                $project->readiness_report_file = 'No readiness report exists';
            }


            //final report file
            $finalreportpath = 'uploads/readiness_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
            if (Storage::exists($finalreportpath)) {

                $project->readiness_report_file = 'Yes';

                $fullPath = storage_path('app/' . $finalreportpath);
                $fileHash = hash_file('sha256',  $fullPath);
                $fileSize = filesize($fullPath);
                $fileModifiedTime = filemtime($fullPath);
                $lastModified = date("Y-m-d H:i:s",   $fileModifiedTime);

                $fileMetadata = [
                    'file_path' => $fullPath,
                    'file_hash' => $fileHash,
                    'file_size' => $fileSize,
                    'last_modified' =>  $lastModified,
                ];

                $project->readiness_report_file_metadata =  $fileMetadata;
            } else {
                $project->final_report_file = 'No final report exists';
            }

            //project pillars
            $project->pillars = DB::table('project_pillar as pp')
                ->join('pillars as pi', 'pi.id', '=', 'pp.pillar_id')
                ->where('pp.project_id', $project->project_id)
                ->select(
                    'pp.id',
                    'pi.pillar',
                    'pi.subpillar'

                )
                ->get();

            //project Tags
            $project->tags = DB::table('project_tag as pt')
                ->join('tags as tg', 'tg.id', '=', 'pt.tag_id')
                ->where('pt.project_id', $project->project_id)
                ->select(
                    'tg.id',
                    'tg.tag',
                    'tg.tagtitle'

                )
                ->get();


            // Fetch Outcomes and their nested publication details
            $project->commitments = DB::table('commitments as co')
                ->where('co.project_id', $project->project_id)
                ->select(
                    'co.q1article',
                    'co.q2article',
                    'co.q3article',
                    'co.q4article',
                    'co.confArticle',

                    'co.books',
                    'co.editBooks',
                    'co.chapters',
                    'co.ip',
                    'co.filedPatent',

                    'co.openSourceSW',
                    'co.startUp',
                    'co.ethical',
                    'co.master',
                    'co.UG',
                    'co.Phd',
                    'co.crossCollege'

                )
                ->get();


            // Fetch Outcomes and their nested publication details
            $project->outcomes = DB::table('outcomes as po')
                ->where('po.project_id', $project->project_id)
                ->select(
                    'po.id',
                    'po.type as outcome_type',
                    'po.score as outcome_score'

                )
                ->get();

            foreach ($project->outcomes as $outcome) {
                $outcome->publication_details = DB::table('publication_detail as pd')
                    ->where('pd.outcome_id', $outcome->id)
                    ->select(
                        'pd.title',
                        'pd.publication_date',
                        'pd.venue'
                    )
                    ->get();
            }


            // Fetch Students
            $project->students = DB::table('attached_students as atts')
                ->where('atts.project_id', $project->project_id)
                ->select(
                    'atts.type as student_type',
                    'atts.std_id as student_id',
                    'atts.days as student_days',
                    'atts.score as student_score'
                )
                ->get();

            // Fetch Final Report
            $project->final_report_grading = DB::table('final_report_grading as fr')

                ->where('fr.project_id', $project->project_id)
                ->select(
                    'fr.user_id as reviewer',
                    'fr.gradeA as Achievements',
                    'fr.commentA as Comments_on_Achievements',
                    'fr.gradeB as Publications',
                    'fr.commentB as Comments_on_publications',
                    'fr.gradeC as Project_Impact',
                    'fr.commentC as Comment_on_Project_Impact',
                    'fr.gradeD as students_researchers_involement',
                    'fr.commentD as comments_on_students_researchers_involement',
                    'fr.total as total_score',
                //    DB::raw("CASE WHEN fr.isaccepted = 0 THEN 'rejected' ELSE 'accepted' END as isaccepted")
                )
                ->get();

            // Fetch Progress Report
            $project->progress_report_grading = DB::table('progress_report_grading as pr')

                ->join('ratings as rt1', 'rt1.id', '=', 'pr.achievementsRating')
                ->join('ratings as rt2', 'rt2.id', '=', 'pr.publicationsRating')
                ->join('ratings as rt3', 'rt3.id', '=', 'pr.studentsRating')
                ->join('ratings as rt4', 'rt4.id', '=', 'pr.budgetRating')
                ->join('users as u', 'u.id', '=', 'pr.user_id')

                ->where('pr.project_id', $project->project_id)
                ->select(
                    'pr.user_id as reviewer',
                    // 'pr.analysis as progress_report_analysis',
                    // 'pr.comments as progress_report_comments',
                    // 'pr.recommendation as progress_report_recommendation',

                    'rt1.rating as achievement_rating',
                    'pr.achievementsComments as  achievements_comments',
                    'rt2.rating as publications_rating',
                    'pr.publicationsComments as  publications_comments',
                    'rt3.rating as students_involements_rating',
                    'pr.studentsComments as  students_involvement_comments',
                    'rt4.rating as budget_rating',
                    'pr.budgetComments as budget_comments',


                    'pr.updated_at as grading_date',

                    DB::raw("CASE WHEN pr.isAccepted=0 THEN 'Rejected' ELSE 'Accepted' END as report_status")
                )
                ->get();
        }


        return response()->json($projects); // For JSON output

        // return view('blockchain.projects', ['projects' => $projects]);
    }

    //upload to ftp
    private function uploadsftp($localFilePath, $remoteFolder)
    {
        if (!$this->sftp->is_dir($remoteFolder)) {
            $this->sftp->mkdir($remoteFolder, -1, true);
        }

        $fileName = basename($localFilePath);
        $remoteFilePath = rtrim($remoteFolder, '/') . '/' . $fileName;
        return $this->sftp->put($remoteFilePath, $localFilePath, SFTP::SOURCE_LOCAL_FILE);
    }

    public function blockchainuploadpost(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:zip',
        ]);

        // Store file locally before uploading to SFTP
        $filePath = $request->file('file')->store('uploads');

        // Hash the file for integrity
        $fileHash = hash_file('sha256', storage_path('app/' . $filePath));

        // Upload to SFTP
        $this->uploadsftp(storage_path('app/' . $filePath), '/');

        // Save file hash and metadata to blockchain
        $blockchain = new Blockchain();
        $blockchain->addBlock([
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'uploaded_at' => now(),
        ]);

        return redirect()->back()->with('success', 'File uploaded successfully.');
    }
}


// the block
class Block
{
    public $id;
    public $timestamp;
    public $data;
    public $previousHash;
    public $hash;

    public function __construct($id, $previousHash, $data)
    {
        $this->id = $id;
        $this->timestamp = now();
        $this->data = $data;
        $this->previousHash = $previousHash;
        $this->hash = $this->calculateHash();
    }

    // Method to calculate the hash of the block
    public function calculateHash()
    {
        return hash('sha256', $this->id . $this->previousHash . $this->timestamp . json_encode($this->data));
    }
}

class Blockchain
{

    public $chain = [];
    public function __construct()
    {
        $this->chain = $this->loadBlockchain();
        if (empty($this->chain)) {
            $this->chain[] = $this->createGenesisBlock();
        }
    }

    private function loadBlockchain()
    {
        //  return BlockModel::orderBy('id')->get()->toArray();

        $blocks = BlockModel::orderBy('id')->get();
        $blockchain = [];
        foreach ($blocks as $blockData) {
            $blockchain[] = new Block(
                $blockData->id,
                $blockData->previous_hash,
                json_decode($blockData->data)
            );
        }
        return $blockchain;
    }

    private function createGenesisBlock()
    {
        $block = new Block(0, "0", "Genesis Block");
        BlockModel::create([
            'id' => $block->id,
            'previous_hash' => $block->previousHash,
            'data' => json_encode($block->data),
            'hash' => $block->hash,
            'timestamp' => now()
        ]);
        return $block;
    }

    public function addBlock($data)
    {
        $lastBlock = end($this->chain);
        $newBlock = new Block($lastBlock->id + 1, $lastBlock->hash, $data);
        BlockModel::create([
            'id' => $newBlock->id,
            'previous_hash' => $newBlock->previousHash,
            'data' => json_encode($newBlock->data),
            'hash' => $newBlock->hash,
            'timestamp' => now()
        ]);
        $this->chain[] = $newBlock;
    }

    public function verifyBlock($block)
    {
        $storedHash = $block['file_hash'];
        $calculatedHash = hash_file('sha256', storage_path('app/' . $block['file_path']));

        return $storedHash === $calculatedHash;
    }

    public function verifyAllBlocks()
    {
        foreach ($this->chain as $block) {
            if (!$this->verifyBlock($block)) {
                // Log or notify about altered file
                return false;
            }
        }
        return true;
    }
}
