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
        if ((int) $document->applicant?->application_id !== (int) $consent->id) {
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
        if ((int) $document->applicant?->application_id !== (int) $consent->id || $document->document_type !== 'applicant_photo') {
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

    public function destroyIncomeDocument(ConsentApplication $consent, ConsentDocumentFile $document){
        if ((int) $document->applicant?->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if ($disk->exists($document->path)) {
            $disk->delete($document->path);
        }

        $document->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Remove one or more entries from an existing ZIP document.
     * Expects query param `inner[]` (multiple) or `inner` (single) which are the decoded
     * filenames as returned by `listZipContents`.
     */
    public function destroyZipEntry(Request $request, ConsentApplication $consent, ConsentDocumentFile $document) {
        if ((int) $document->applicant?->application_id !== (int) $consent->id) {
            abort(404);
        }

        $inners = $request->query('inner');
        if (is_null($inners) || $inners === '') {
            return response()->json(['ok' => false, 'message' => 'Missing inner file(s) to delete'], 400);
        }

        // Normalize to array
        if (!is_array($inners)) {
            $inners = [$inners];
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $origPath = $disk->path($document->path);

        $zip = new \ZipArchive();
        if ($zip->open($origPath) !== true) {
            return response()->json(['ok' => false, 'message' => 'ไม่สามารถเปิดไฟล์ ZIP ได้'], 500);
        }

        // Build set of original entry names to remove by matching decoded names
        $toRemove = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) continue;
            $entryName = $stat['name'];
            if (substr($entryName, -1) === '/') continue;
            $decoded = $this->decodeZipEntryName($entryName);
            if (in_array($decoded, $inners, true)) {
                $toRemove[] = $entryName;
            }
        }

        if (empty($toRemove)) {
            $zip->close();
            return response()->json(['ok' => false, 'message' => 'No matching entries found'], 404);
        }

        // If removing these entries would leave zero non-directory files, delete the
        // entire ZIP file and database record instead of creating an empty zip.
        $remainingCount = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) continue;
            $entryName = $stat['name'];
            if (substr($entryName, -1) === '/') continue;
            if (!in_array($entryName, $toRemove, true)) {
                $remainingCount++;
            }
        }

        if ($remainingCount === 0) {
            // close zip then delete original
            $zip->close();
            try {
                if ($disk->exists($document->path)) {
                    $disk->delete($document->path);
                }
                $document->delete();
            } catch (\Exception $e) {
                return response()->json(['ok' => false, 'message' => 'ไม่สามารถลบไฟล์ ZIP ได้'], 500);
            }

            return response()->json(['ok' => true, 'deleted_zip' => true]);
        }

        // Create a temporary ZIP file and copy entries except those to remove
        $tmpFile = tempnam(sys_get_temp_dir(), 'rezip_');
        $newZip = new \ZipArchive();
        if ($newZip->open($tmpFile, \ZipArchive::CREATE) !== true) {
            $zip->close();
            return response()->json(['ok' => false, 'message' => 'ไม่สามารถสร้างไฟล์ชั่วคราวได้'], 500);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) continue;
            $entryName = $stat['name'];
            // preserve directories
            if (substr($entryName, -1) === '/') {
                $newZip->addEmptyDir($entryName);
                continue;
            }
            if (in_array($entryName, $toRemove, true)) {
                continue; // skip
            }

            $stream = $zip->getStream($entryName);
            if ($stream === false) continue;
            $contents = stream_get_contents($stream);
            fclose($stream);
            $newZip->addFromString($entryName, $contents);
        }

        $zip->close();
        $newZip->close();

        // Replace original file with new zip
        try {
            // overwrite
            $disk->put($document->path, file_get_contents($tmpFile));

            // update model metadata
            $newSize = $disk->size($document->path);
            $document->size = $newSize;
            $document->mime_type = $disk->mimeType($document->path) ?: $document->mime_type;
            // keep original_name as-is (it's the stored zip filename)
            $document->save();

            // cleanup tmp
            @unlink($tmpFile);
        } catch (\Exception $e) {
            @unlink($tmpFile);
            return response()->json(['ok' => false, 'message' => 'ไม่สามารถอัพเดตไฟล์ ZIP ได้'], 500);
        }

        return response()->json(['ok' => true]);
    }

    public function destroyApplicantPhoto(ConsentApplication $consent, ConsentDocumentFile $document): JsonResponse {
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
            ->where('document_type', 'applicant_photo')
            ->whereHas('applicant', function ($q) use ($consent) {
                $q->where('application_id', $consent->id);
            })
            ->get()
            ->each(function (ConsentDocumentFile $existingDocument) use ($disk) {
                if ($disk->exists($existingDocument->path)) {
                    $disk->delete($existingDocument->path);
                }
                $existingDocument->delete();
            });

        $fileName = 'applicant-photo-' . now()->format('YmdHis') . '-' . uniqid() . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs("consent/{$consent->encrypted_id}/photos", $fileName, 'local');

        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        return ConsentDocumentFile::create([
            'applicant_id' => $applicant->id,
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
