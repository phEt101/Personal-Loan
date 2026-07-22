<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentDocumentFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsentDocumentController extends ConsentController
{
    private const DEFAULT_STREAM_MIME_TYPE = 'application/octet-stream';
    private const INLINE_DISPOSITION_FORMAT = 'inline; filename="%s"';

    public function uploadApplicantPhoto(Request $request, ConsentApplication $consent): JsonResponse
    {
        $validated = $request->validate([
            'applicantPhoto' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $document = $this->storeApplicantPhoto($consent, $validated['applicantPhoto']);

        return response()->json([
            'ok' => true,
            'document' => $this->buildApplicantPhotoPayload($consent, $document),
        ]);
    }

    public function downloadIncomeDocument(ConsentApplication $consent, ConsentDocumentFile $document) {
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $mimeType = $document->mime_type ?: $disk->mimeType($document->path) ?: self::DEFAULT_STREAM_MIME_TYPE;
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
            'Content-Disposition' => sprintf(self::INLINE_DISPOSITION_FORMAT, str_replace('"', '', $fileName)),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadApplicantPhoto(ConsentApplication $consent, ConsentDocumentFile $document)
    {
        if ((int) $document->application_id !== (int) $consent->id || $document->document_type !== 'applicant_photo') {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $mimeType = $document->mime_type ?: $disk->mimeType($document->path) ?: self::DEFAULT_STREAM_MIME_TYPE;
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
            'Content-Disposition' => sprintf(self::INLINE_DISPOSITION_FORMAT, str_replace('"', '', $document->original_name)),
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
            $decoded = $this->decodeZipEntryName($name);
            $entries[] = [
                'name' => $decoded,
                'original_name' => $name,
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

        // Zip entries may have filenames stored in legacy encodings (CP437, Windows-874, etc.).
        // The client requests by the decoded UTF-8 name, so find the matching entry by
        // decoding each entry and comparing. Then open the stream using the original
        // entry name as stored in the archive.
        $found = false;
        $originalEntryName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) continue;
            $entryName = $stat['name'];
            if (substr($entryName, -1) === '/') continue; // skip dirs
            $decoded = $this->decodeZipEntryName($entryName);
            if ($decoded === $inner) {
                $found = true;
                $originalEntryName = $entryName;
                break;
            }
        }

        if (!$found) {
            $zip->close();
            abort(404);
        }

        $stream = $zip->getStream($originalEntryName);
        if ($stream === false) {
            $zip->close();
            abort(404);
        }

        $fileName = basename($decoded);
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

    public function destroyApplicantPhoto(ConsentApplication $consent, ConsentDocumentFile $document): JsonResponse
    {
        if ((int) $document->application_id !== (int) $consent->id || $document->document_type !== 'applicant_photo') {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if ($disk->exists($document->path)) {
            $disk->delete($document->path);
        }

        $document->delete();

        return response()->json(['ok' => true]);
    }

    private function storeApplicantPhoto(ConsentApplication $consent, $file): ConsentDocumentFile
    {
        $disk = Storage::disk('local');
        ConsentDocumentFile::query()
            ->where('application_id', $consent->id)
            ->where('document_type', 'applicant_photo')
            ->get()
            ->each(function (ConsentDocumentFile $existingDocument) use ($disk) {
                if ($disk->exists($existingDocument->path)) {
                    $disk->delete($existingDocument->path);
                }
                $existingDocument->delete();
            });

        $fileName = 'applicant-photo-' . now()->format('YmdHis') . '-' . uniqid() . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs("consent/{$consent->encrypted_id}/photos", $fileName, 'local');

        return ConsentDocumentFile::create([
            'application_id' => $consent->id,
            'document_type' => 'applicant_photo',
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Normalize/convert a ZIP entry name to UTF-8 for display.
     * Try common source encodings (CP437, CP866, WINDOWS-874) and fall back to the original.
     */
    private function decodeZipEntryName(string $name): string
    {
        // If already valid UTF-8, return as-is
        if (mb_check_encoding($name, 'UTF-8')) {
            return $name;
        }

        $candidates = ['CP437', 'CP866', 'WINDOWS-874', 'ISO-8859-1'];
        foreach ($candidates as $enc) {
            $converted = @mb_convert_encoding($name, 'UTF-8', $enc);
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        // Last resort: try iconv from CP437
        $converted = @iconv('CP437', 'UTF-8//TRANSLIT', $name);
        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        // Fallback to original string
        return $name;
    }

    private function buildApplicantPhotoPayload(ConsentApplication $consent, ConsentDocumentFile $document): array
    {
        return [
            'id' => $document->id,
            'originalName' => $document->original_name,
            'mimeType' => $document->mime_type,
            'size' => $document->size,
            'downloadUrl' => route('consent.applicant-photo.download', [
                'consent' => $consent->encrypted_id,
                'document' => $document->id,
            ]),
            'destroyUrl' => route('consent.applicant-photo.destroy', [
                'consent' => $consent->encrypted_id,
                'document' => $document->id,
            ]),
        ];
    }
}
