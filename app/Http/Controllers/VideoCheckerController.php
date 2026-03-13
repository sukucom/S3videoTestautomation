<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VideoLinkChecker\VideoLinkCheckerService;
use Illuminate\Support\Facades\Storage;

class VideoCheckerController extends Controller
{
    protected $checkerService;

    public function __construct(VideoLinkCheckerService $checkerService)
    {
        $this->checkerService = $checkerService;
    }

    public function index()
    {
        return view('checker');
    }

    public function process(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        try {
            $results = $this->checkerService->processCsv($path);
            
            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
