<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleDriveLink
{
    public static function isValidDriveUrl($url)
    {
        $value = trim((string) $url);
        if ($value === '') {
            return false;
        }

        return (bool) preg_match('~https?://(drive\.google\.com|docs\.google\.com)/~i', $value);
    }

    public static function extractFileId($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $patterns = [
            '~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~',
            '~drive\.google\.com/open\?[^#]*\bid=([a-zA-Z0-9_-]+)~',
            '~drive\.google\.com/uc\?(?:export=download&)?id=([a-zA-Z0-9_-]+)~',
            '~docs\.google\.com/document/d/([a-zA-Z0-9_-]+)~',
            '~docs\.google\.com/spreadsheets/d/([a-zA-Z0-9_-]+)~',
            '~docs\.google\.com/presentation/d/([a-zA-Z0-9_-]+)~',
            '~[?&]id=([a-zA-Z0-9_-]+)~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function extractFolderId($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~drive\.google\.com/(?:drive/(?:u/\d+/)?)?folders/([a-zA-Z0-9_-]+)~i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function isFolderUrl($url)
    {
        return self::extractFolderId($url) !== null && self::extractFileId($url) === null;
    }

    /**
     * @return array<int, array{id: string, name: string|null}>
     */
    public static function listPublicFolderFiles($folderId)
    {
        $folderId = trim((string) $folderId);
        if ($folderId === '') {
            return [];
        }

        $cacheKey = 'gdrive_folder_files_v2:' . $folderId;
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && (!isset($cached[0]) || is_array($cached[0]))) {
            return $cached;
        }

        try {
            $response = Http::timeout(45)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; RapmayDocument/1.0)'])
                ->get('https://drive.google.com/drive/folders/' . $folderId);
        } catch (\Throwable $e) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        $files = self::parseFolderFilesFromHtml($response->body(), $folderId);
        if ($files !== []) {
            Cache::put($cacheKey, $files, 300);
        }

        return $files;
    }

    /**
     * @return string[]
     */
    public static function listPublicFolderFileIds($folderId)
    {
        return array_column(self::listPublicFolderFiles($folderId), 'id');
    }

    public static function folderFileName($folderId, $fileId)
    {
        foreach (self::listPublicFolderFiles($folderId) as $file) {
            if ($file['id'] === $fileId) {
                return $file['name'] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{id: string, name: string|null}>
     */
    private static function parseFolderFilesFromHtml($body, $folderId)
    {
        $files = [];

        if (preg_match_all(
            '~data-id="([a-zA-Z0-9_-]{20,})"[^>]*data-target="doc"[^>]*>.*?</tr>~s',
            (string) $body,
            $rows,
            PREG_SET_ORDER
        )) {
            foreach ($rows as $row) {
                $id = $row[1];
                if ($id === $folderId) {
                    continue;
                }

                $name = null;
                if (preg_match('~>([^<>]+\.(pdf|jpg|jpeg|png|zip|docx|xlsx|pptx|gif|webp))</~i', $row[0], $match)) {
                    $name = html_entity_decode(trim($match[1]), ENT_QUOTES, 'UTF-8');
                }

                $files[] = [
                    'id' => $id,
                    'name' => $name,
                ];
            }
        }

        if ($files !== []) {
            return $files;
        }

        if (!preg_match_all('~\[null,"([a-zA-Z0-9_-]{20,})"~', (string) $body, $matches)) {
            return [];
        }

        foreach (array_unique($matches[1]) as $id) {
            if ($id === $folderId) {
                continue;
            }
            $files[] = [
                'id' => $id,
                'name' => null,
            ];
        }

        return $files;
    }

    public static function filenameFromContentDisposition($header)
    {
        $header = trim((string) $header);
        if ($header === '') {
            return null;
        }

        if (preg_match('/filename\*=(?:UTF-8\'\')([^;\s]+)/i', $header, $matches)) {
            return rawurldecode(trim($matches[1], "\" \t"));
        }

        if (preg_match('/filename="([^"]+)"/i', $header, $matches)) {
            return $matches[1];
        }

        if (preg_match('/filename=([^;\s]+)/i', $header, $matches)) {
            return trim($matches[1], "\" \t");
        }

        return null;
    }

    public static function sanitizeDriveFilename($filename)
    {
        $filename = trim((string) $filename);
        $filename = str_replace(['/', '\\'], '-', $filename);
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);

        return $filename !== '' ? $filename : 'tai-lieu.bin';
    }

    public static function resolveDownloadFilename($productName, $driveUrl, $contentType = null, $contentDisposition = null, $driveFileName = null)
    {
        $candidates = [
            $driveFileName,
            self::filenameFromContentDisposition($contentDisposition),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== null && trim((string) $candidate) !== '') {
                return self::sanitizeDriveFilename($candidate);
            }
        }

        return self::safeFilename($productName, $driveUrl, $contentType);
    }

    public static function attachmentContentDisposition($filename)
    {
        $filename = self::sanitizeDriveFilename($filename);
        $asciiFallback = preg_replace('/[^\x20-\x7E]+/', '_', $filename);
        if ($asciiFallback === null || $asciiFallback === '') {
            $asciiFallback = 'download.bin';
        }

        return 'attachment; filename="' . str_replace('"', '', $asciiFallback) . '"; filename*=UTF-8\'\'' . rawurlencode($filename);
    }

    public static function directDownloadUrlForFileId($fileId)
    {
        return 'https://drive.google.com/uc?export=download&id=' . $fileId;
    }

    /**
     * @return array{file_id: string, source_url: string}|null
     */
    public static function resolveDownloadTarget($origin, $requestedFileId = null)
    {
        $origin = trim((string) $origin);
        if ($origin === '') {
            return null;
        }

        $fileId = self::extractFileId($origin);
        if ($fileId) {
            $pick = $requestedFileId ?: $fileId;
            if ($requestedFileId && $requestedFileId !== $fileId) {
                return null;
            }

            $sourceUrl = self::directDownloadUrl($origin);
            if (!$sourceUrl) {
                return null;
            }

            return [
                'file_id' => $pick,
                'source_url' => $sourceUrl,
            ];
        }

        $folderId = self::extractFolderId($origin);
        if (!$folderId) {
            return null;
        }

        $allowed = self::listPublicFolderFileIds($folderId);
        if ($allowed === []) {
            return null;
        }

        if ($requestedFileId) {
            if (!in_array($requestedFileId, $allowed, true)) {
                return null;
            }
            $pick = $requestedFileId;
        } else {
            $pick = $allowed[0];
        }

        return [
            'file_id' => $pick,
            'source_url' => self::directDownloadUrlForFileId($pick),
        ];
    }

    public static function directDownloadUrl($url)
    {
        $url = trim((string) $url);
        $id = self::extractFileId($url);
        if (!$id) {
            return null;
        }

        if (preg_match('~docs\.google\.com/document~i', $url)) {
            return 'https://docs.google.com/document/d/' . $id . '/export?format=pdf';
        }
        if (preg_match('~docs\.google\.com/spreadsheets~i', $url)) {
            return 'https://docs.google.com/spreadsheets/d/' . $id . '/export?format=xlsx';
        }
        if (preg_match('~docs\.google\.com/presentation~i', $url)) {
            return 'https://docs.google.com/presentation/d/' . $id . '/export/pptx';
        }

        return self::directDownloadUrlForFileId($id);
    }

    public static function defaultExtension($url)
    {
        $url = strtolower((string) $url);
        if (strpos($url, 'docs.google.com/document') !== false) {
            return '.pdf';
        }
        if (strpos($url, 'docs.google.com/spreadsheets') !== false) {
            return '.xlsx';
        }
        if (strpos($url, 'docs.google.com/presentation') !== false) {
            return '.pptx';
        }

        return '';
    }

    public static function safeFilename($name, $url, $contentType = null)
    {
        $base = preg_replace('/[^\p{L}\p{N}\s\-_]+/u', '', (string) $name);
        $base = trim(preg_replace('/\s+/', ' ', $base));
        if ($base === '') {
            $base = 'tai-lieu';
        }

        $ext = self::defaultExtension($url);
        if ($ext === '' && $contentType) {
            $map = [
                'application/pdf' => '.pdf',
                'application/zip' => '.zip',
                'image/jpeg' => '.jpg',
                'image/jpg' => '.jpg',
                'image/png' => '.png',
                'image/gif' => '.gif',
                'image/webp' => '.webp',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
            ];
            $ext = $map[strtolower(explode(';', $contentType)[0])] ?? '';
        }

        $slug = Str::slug($base, '-');
        if ($slug === '') {
            $slug = 'tai-lieu';
        }

        if ($ext !== '' && substr($slug, -strlen($ext)) !== $ext) {
            $slug .= $ext;
        }

        return $slug;
    }
}
