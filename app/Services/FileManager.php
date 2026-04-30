<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileManager
{
    /** All file operations are restricted to this base path. */
    protected string $allowedBase = '/home';

    public function fetchServerContents($params, $path, $queryPath): array
    {
        $headers = explode('/', Str::after($queryPath, '/'));

        if (in_array($queryPath, $headers)) {
            array_shift($headers);
        }

        $fullPath = $path . '/' . $params;

        // Ensure we stay within /home
        $this->guardPath($fullPath);

        $directories = File::directories($fullPath);
        $files = File::files($fullPath);

        $directoryContent = collect();
        foreach ($directories as $directory) {
            $directoryContent->push([
                'full_path'   => $directory,
                'folder_name' => Str::afterLast($directory, '/'),
                'type'        => 'folder',
            ]);
        }

        $fileContents = collect();
        foreach ($files as $file) {
            $fileContents->push([
                'filename'      => $file->getFilename(),
                'size'          => $file->getSize(),
                'pathName'      => $file->getPathname(),
                'last_modified' => Carbon::parse($file->getMTime())->format('M d, Y , H:i:s'),
                'type'          => 'file',
            ]);
        }

        $pathContents = collect($directoryContent)->merge($fileContents);

        return compact('pathContents', 'params', 'path', 'queryPath', 'headers');
    }

    public function storeFile(array $validatedFileData): bool
    {
        $content  = json_decode($validatedFileData['content'], true);
        $pathName = $content['pathName'];
        $data     = $validatedFileData['data'];

        $this->guardPath($pathName);

        try {
            return (bool) File::put($pathName, $data);
        } catch (Exception $e) {
            return false;
        }
    }

    public function createDirectory(array $validatedFileData): bool
    {
        $path          = $validatedFileData['path'];
        $directoryName = $validatedFileData['new-directory-name'];
        $full          = $path . '/' . $directoryName;

        $this->guardPath($full);

        try {
            mkdir($full);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function createFile(array $validatedFileData): bool
    {
        $path     = $validatedFileData['path'];
        $fileName = $validatedFileData['new-file-name'];
        $full     = $path . '/' . $fileName;

        $this->guardPath($full);

        try {
            fopen($full, 'w');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function renameFile(array $validatedFileData): bool
    {
        $fullPath = json_decode($validatedFileData['content'])->pathName;
        $path     = Str::beforeLast($fullPath, '/');
        $newName  = $validatedFileData['rename-file-name'];
        $newPath  = $path . '/' . $newName;

        $this->guardPath($fullPath);
        $this->guardPath($newPath);

        try {
            rename($fullPath, $newPath);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function copyFile(array $validatedFileData): bool
    {
        $decodedData = json_decode($validatedFileData['content']);
        $fullPath    = $decodedData->pathName;
        $fileName    = $decodedData->filename;
        $ext         = Str::afterLast($fileName, '.');
        $copyPath    = $validatedFileData['copy-file-path'];
        $copyFull    = $copyPath . '/' . $fileName;

        $this->guardPath($fullPath);
        $this->guardPath($copyPath);

        try {
            $dest = ($fullPath === $copyFull)
                ? $copyPath . '/' . Str::beforeLast($fileName, '.') . '-1.' . $ext
                : $copyFull;

            copy($fullPath, $dest);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function moveFile(array $validatedFileData): bool
    {
        $decodedData = json_decode($validatedFileData['content']);
        $fullPath    = $decodedData->pathName;
        $fileName    = $decodedData->filename;
        $movePath    = $validatedFileData['move-file-path'];

        $this->guardPath($fullPath);
        $this->guardPath($movePath);

        if ($fullPath === $movePath) {
            return false;
        }

        try {
            rename($fullPath, $movePath . '/' . $fileName);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Ensure a path stays within /home to prevent path traversal attacks.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function guardPath(string $path): void
    {
        // Resolve symlinks and .. segments where possible
        $real = realpath($path) ?: $path;

        if (!str_starts_with($real, $this->allowedBase . '/')) {
            abort(403, 'Access denied: path outside allowed directory.');
        }
    }
}
