<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class DriveFolderZipBuilder
{
    /**
     * @param array<int, array{id: string, name: string|null}> $files
     * @return array{path: string, filename: string}|null
     */
    public static function build(array $files, $productName)
    {
        if (!class_exists(ZipArchive::class)) {
            return null;
        }

        $files = array_values($files);
        if ($files === []) {
            return null;
        }

        $tempBase = tempnam(sys_get_temp_dir(), 'drive_zip_');
        if ($tempBase === false) {
            return null;
        }

        @unlink($tempBase);
        $tempZip = $tempBase . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $usedNames = [];
        $added = 0;

        foreach ($files as $index => $file) {
            $body = self::downloadFileBody($file['id']);
            if ($body === null) {
                continue;
            }

            $entryName = self::uniqueEntryName($file['name'] ?? null, $index, $usedNames);
            $zip->addFromString($entryName, $body);
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tempZip);

            return null;
        }

        return [
            'path' => $tempZip,
            'filename' => self::zipFilenameForProduct($productName),
        ];
    }

    public static function zipFilenameForProduct($productName)
    {
        $base = preg_replace('/[^\p{L}\p{N}\s\-_]+/u', '', (string) $productName);
        $base = trim(preg_replace('/\s+/', ' ', $base));
        $slug = Str::slug($base !== '' ? $base : 'tai-lieu', '-');

        if ($slug === '') {
            $slug = 'tai-lieu';
        }

        if (substr($slug, -4) === '.zip') {
            return $slug;
        }

        return $slug . '.zip';
    }

    private static function downloadFileBody($fileId)
    {
        try {
            $response = Http::timeout(180)
                ->withOptions(['allow_redirects' => ['max' => 10]])
                ->get(GoogleDriveLink::directDownloadUrlForFileId($fileId));
        } catch (\Throwable $e) {
            Log::warning('Drive zip: file download failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!$response->successful()) {
            Log::warning('Drive zip: bad HTTP status', [
                'file_id' => $fileId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $body = $response->body();
        if (stripos($body, '<html') !== false && stripos($body, 'Google Drive') !== false) {
            Log::warning('Drive zip: confirmation page returned', ['file_id' => $fileId]);

            return null;
        }

        return $body;
    }

    private static function uniqueEntryName($name, $index, array &$used)
    {
        if ($name === null || trim((string) $name) === '') {
            $name = 'tai-lieu-' . ($index + 1) . '.bin';
        } else {
            $name = GoogleDriveLink::sanitizeDriveFilename($name);
        }

        $candidate = $name;
        $suffix = 2;
        while (isset($used[$candidate])) {
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $base = $extension !== '' ? pathinfo($name, PATHINFO_FILENAME) : $name;
            $candidate = $extension !== ''
                ? $base . '-' . $suffix . '.' . $extension
                : $base . '-' . $suffix;
            $suffix++;
        }

        $used[$candidate] = true;

        return $candidate;
    }
}
