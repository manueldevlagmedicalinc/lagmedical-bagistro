<?php

namespace LagMedical\CmsPro\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LagMedical\CmsPro\Services\CmsProService;
use Throwable;
use Webkul\Admin\Http\Controllers\Controller;

class MediaController extends Controller
{
    protected array $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public function __construct(protected CmsProService $cmsPro) {}

    public function index(): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.media'), 403);

        $channelId = request()->integer('channel_id') ?: null;

        return response()->json(['data' => $this->cmsPro->media($channelId)]);
    }

    public function store(): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.media'), 403);

        $validated = request()->validate([
            'files' => 'required|array|max:20',
            'files.*' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'channel_id' => 'required|integer|exists:channels,id',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $media = collect($validated['files'])
            ->map(fn (UploadedFile $file) => $this->storeImage(
                $file->get(),
                $file->getMimeType(),
                $file->getClientOriginalName(),
                $validated['channel_id'],
                $validated['alt_text'] ?? null,
            ));

        return response()->json(['data' => $media], 201);
    }

    public function import(): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.media'), 403);

        $validated = request()->validate([
            'url' => 'required|url|max:2000',
            'channel_id' => 'required|integer|exists:channels,id',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $publicIp = $this->validatedPublicIp($validated['url']);

        abort_unless($publicIp, 422, 'La URL de la imagen no está permitida.');

        $host = parse_url($validated['url'], PHP_URL_HOST);
        $tempPath = tempnam(sys_get_temp_dir(), 'cms-pro-');

        try {
            $response = Http::timeout(12)
                ->withOptions([
                    'allow_redirects' => false,
                    'sink' => $tempPath,
                    'curl' => [
                        CURLOPT_RESOLVE => ["{$host}:443:{$publicIp}"],
                        CURLOPT_MAXFILESIZE => 10 * 1024 * 1024,
                        CURLOPT_NOPROGRESS => false,
                        CURLOPT_XFERINFOFUNCTION => static fn ($resource, $downloadTotal, $downloaded) => $downloaded > 10 * 1024 * 1024 ? 1 : 0,
                    ],
                ])
                ->get($validated['url']);

            abort_unless($response->successful(), 422, 'No se pudo descargar la imagen.');

            $mime = strtolower(trim(strtok($response->header('Content-Type', ''), ';')));
            $contents = file_get_contents($tempPath);
        } catch (Throwable) {
            abort(422, 'No se pudo descargar la imagen de forma segura.');
        } finally {
            @unlink($tempPath);
        }

        abort_unless(is_string($contents) && isset($this->allowedMimes[$mime]) && strlen($contents) <= 10 * 1024 * 1024, 422, 'El archivo remoto no es una imagen válida.');

        $name = basename(parse_url($validated['url'], PHP_URL_PATH)) ?: 'imported-image.'.$this->allowedMimes[$mime];
        $media = $this->storeImage($contents, $mime, $name, $validated['channel_id'], $validated['alt_text'] ?? null);

        return response()->json(['data' => [$media]], 201);
    }

    public function delete(int $media): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.media.delete'), 403);

        $validated = request()->validate(['channel_id' => 'required|integer|exists:channels,id']);
        $record = DB::table('cms_pro_media')->find($media);

        abort_unless($record, 404);
        abort_unless((int) $record->channel_id === (int) $validated['channel_id'], 403);
        abort_if($this->isReferenced($media), 409, 'La imagen está siendo utilizada y no puede eliminarse.');

        abort_unless(Storage::disk($record->disk)->delete($record->path), 500, 'No se pudo eliminar el archivo.');
        DB::table('cms_pro_media')->where('id', $media)->delete();

        return response()->json(['message' => 'Imagen eliminada.']);
    }

    protected function storeImage(string $contents, string $mime, string $name, ?int $channelId, ?string $altText): object
    {
        abort_unless(isset($this->allowedMimes[$mime]), 422, 'Tipo de imagen no permitido.');

        $dimensions = @getimagesizefromstring($contents);

        abort_unless($dimensions, 422, 'El contenido de la imagen no es válido.');
        abort_if($dimensions[0] > 12000 || $dimensions[1] > 12000 || ($dimensions[0] * $dimensions[1]) > 40000000, 422, 'Las dimensiones de la imagen son demasiado grandes.');

        $path = 'cms-pro/'.date('Y/m').'/'.Str::uuid().'.'.$this->allowedMimes[$mime];

        $disk = Storage::disk('public');
        $directory = dirname($path);

        abort_unless($disk->makeDirectory($directory) || $disk->directoryExists($directory), 500, 'No se pudo preparar el almacenamiento de imágenes.');
        abort_unless($disk->put($path, $contents, ['visibility' => 'public']), 500, 'No se pudo almacenar la imagen en el disco público.');

        try {
            $id = DB::table('cms_pro_media')->insertGetId([
                'channel_id' => $channelId,
                'admin_id' => auth()->guard('admin')->id(),
                'disk' => 'public',
                'path' => $path,
                'name' => Str::limit($name, 255, ''),
                'mime' => $mime,
                'size' => strlen($contents),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'alt_text' => $altText,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        return $this->cmsPro->findMedia($id);
    }

    protected function validatedPublicIp(string $url): ?string
    {
        $parts = parse_url($url);

        if (
            ($parts['scheme'] ?? '') !== 'https'
            || empty($parts['host'])
            || (isset($parts['port']) && $parts['port'] !== 443)
        ) {
            return null;
        }

        $ips = gethostbynamel($parts['host']) ?: [];

        return collect($ips)->first(fn ($ip) => filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        )) ?: null;
    }

    protected function isReferenced(int $mediaId): bool
    {
        $needle = ':'.$mediaId;

        foreach (['cms_pro_pages' => ['blocks', 'draft_blocks'], 'cms_pro_revisions' => ['blocks']] as $table => $columns) {
            foreach ($columns as $column) {
                $records = DB::table($table)->where($column, 'like', "%{$needle}%")->pluck($column);

                foreach ($records as $json) {
                    if ($this->containsMediaId(json_decode($json ?: '[]', true) ?: [], $mediaId)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function containsMediaId(array $values, int $mediaId): bool
    {
        foreach ($values as $key => $value) {
            if (is_array($value) && $this->containsMediaId($value, $mediaId)) {
                return true;
            }

            if (str_ends_with((string) $key, '_id') && $key !== 'category_id' && (int) $value === $mediaId) {
                return true;
            }
        }

        return false;
    }
}
