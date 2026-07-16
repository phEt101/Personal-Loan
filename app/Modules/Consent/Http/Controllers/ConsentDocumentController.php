<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentDocumentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsentDocumentController extends ConsentController
{
    public function downloadIncomeDocument(ConsentApplication $consent, ConsentDocumentFile $document) {
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $mimeType = $document->mime_type ?: $disk->mimeType($document->path) ?: 'application/octet-stream';
        $fileName = $document->original_name;

        $stream = $disk->readStream($document->path);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $fileName) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function listZipContents(ConsentApplication $consent, ConsentDocumentFile $document){
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $filePath = $disk->path($document->path);
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return response()->json(['ok' => false, 'message' => 'ไม่สามารถเปิดไฟล์ ZIP ได้'], 500);
        }

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }
            $name = $stat['name'];
            // skip directories
            if (substr($name, -1) === '/') {
                continue;
            }
            $entries[] = [
                'name' => $name,
                'size' => $stat['size'],
                'compressed_size' => $stat['comp_size'],
            ];
        }

        $zip->close();
        return response()->json(['ok' => true, 'entries' => $entries]);
    }

    public function streamZipEntry(Request $request, ConsentApplication $consent, ConsentDocumentFile $document){
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $inner = (string) $request->query('inner', '');
        if ($inner === '') {
            return response()->json(['ok' => false, 'message' => 'Missing inner file path'], 400);
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $filePath = $disk->path($document->path);
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return response()->json(['ok' => false, 'message' => 'ไม่สามารถเปิดไฟล์ ZIP ได้'], 500);
        }

        $stream = $zip->getStream($inner);
        if ($stream === false) {
            $zip->close();
            abort(404);
        }

        $fileName = basename($inner);
        // try to infer mime from extension, fallback to binary
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeMap = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
            'pdf' => 'application/pdf', 'txt' => 'text/plain', 'csv' => 'text/csv'
        ];
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';

        return response()->stream(function () use ($stream, $zip) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
            }
            fclose($stream);
            $zip->close();
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $fileName) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroyIncomeDocument(ConsentApplication $consent, ConsentDocumentFile $document){
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if ($disk->exists($document->path)) {
            $disk->delete($document->path);
        }

        $document->delete();

        return response()->json(['ok' => true]);
    }
}
