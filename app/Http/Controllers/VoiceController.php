<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoiceController extends Controller
{
    
    public function test(){        
        $whisperPath = 'C:\Users\Safarov\AppData\Local\Packages\PythonSoftwareFoundation.Python.3.13_qbz5n2kfra8p0\LocalCache\local-packages\Python313\Scripts\whisper.exe';

        
        $audioPath = public_path('voice/1.m4a');
        $outputDir = public_path('voice');

        $command = escapeshellarg($whisperPath) . ' ' .
           escapeshellarg($audioPath) . ' ' .
           '--language Japanese --model base ' .
           '--output_dir ' . escapeshellarg($outputDir);

        $output = [];
        $return_var = 0;

        exec($command, $output, $return_var);

        // dd("salom");
        if ($return_var !== 0) {
            return response()->json([
                'error' => 'Whisper ishlamadi',
                'return_code' => $return_var,
                'output' => $output,
            ]);
        }

        $txtPath = public_path('voice/1.txt');

        if (!file_exists($txtPath)) {
            return response()->json(['error' => 'Transkripsiya fayli topilmadi.']);
        }

        $transcription = file_get_contents($txtPath);

        return view('voice.index', ['text' => $transcription]);
    }

    public function index()
    {
        return view('voice.record');
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('audio')) {
            return response('Fayl yuborilmadi', 400);
        }

        $audio = $request->file('audio');
        $audioPath = $audio->storeAs('voice', 'recorded_audio.webm');
        

        $whisperPath = 'C:\Users\Safarov\AppData\Local\Packages\PythonSoftwareFoundation.Python.3.13_qbz5n2kfra8p0\LocalCache\local-packages\Python313\Scripts\whisper.exe';
        $fullAudioPath = storage_path('app/' . $audioPath);
        $outputDir = storage_path('app/voice');

        $command = "\"$whisperPath\" \"$fullAudioPath\" --language Japanese --model base --output_dir \"$outputDir\"";
        
        exec($command, $output, $returnCode);
        

        if ($returnCode !== 0) {
            return response('Whisper xatolikka uchradi', 500);
        }


        $transcriptionPath = $outputDir . '/recorded_audio.txt';
        
        if (!file_exists($transcriptionPath)) {
            return response('Transkriptsiya fayli topilmadi', 500);
        }

        $text = file_get_contents($transcriptionPath);
        // return response($text, 200);
        return response($text, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
