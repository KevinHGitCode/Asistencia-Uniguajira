<?php

namespace App\Http\Controllers\Configuration;

use App\Exports\BannerReportExport;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerDailyStat;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderByDesc('created_at')->get();

        return view('administration.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'nullable|url|max:2048',
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'weight' => 'nullable|integer|min:1|max:100',
        ], [
            'name.required' => 'El nombre del banner es obligatorio.',
            'target_url.url' => 'El enlace debe ser una URL válida (incluye https://).',
            'image.required' => 'La imagen del banner es obligatoria.',
            'image.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'image.max' => 'La imagen no debe superar los 2MB.',
            'ends_at.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);

        $banner = Banner::create([
            'name' => $request->name,
            'target_url' => $request->target_url,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'active' => $request->boolean('active'),
            'weight' => $request->integer('weight') ?: 1,
        ]);

        $file = $request->file('image');
        $banner->storeImage($file->get(), $file->getMimeType());

        ActivityLogService::log('crear', 'banners', "Creó el banner '{$banner->name}'", $banner);

        return redirect()->route('banners.index')->with('success', 'Banner creado correctamente.');
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'nullable|url|max:2048',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'weight' => 'nullable|integer|min:1|max:100',
        ], [
            'name.required' => 'El nombre del banner es obligatorio.',
            'target_url.url' => 'El enlace debe ser una URL válida (incluye https://).',
            'image.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'image.max' => 'La imagen no debe superar los 2MB.',
            'ends_at.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);

        $original = $banner->only(['name', 'target_url', 'starts_at', 'ends_at', 'active', 'weight']);

        $banner->update([
            'name' => $request->name,
            'target_url' => $request->target_url,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'active' => $request->boolean('active'),
            'weight' => $request->integer('weight') ?: 1,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $banner->storeImage($file->get(), $file->getMimeType());
        }

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $banner->$field;
            if ((string) ($oldValue ?? '') !== (string) ($newValue ?? '')) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }
        if ($request->hasFile('image')) {
            $changes['imagen'] = ['old' => '—', 'new' => 'imagen reemplazada'];
        }

        ActivityLogService::log('editar', 'banners', "Editó el banner '{$banner->name}'", $banner, $changes);

        return redirect()->route('banners.index')->with('success', 'Banner actualizado correctamente.');
    }

    public function destroy(Banner $banner)
    {
        $name = $banner->name;
        $banner->delete(); // banner_files cae en cascada

        ActivityLogService::log('eliminar', 'banners', "Eliminó el banner '{$name}'");

        return redirect()->route('banners.index')->with('success', 'Banner eliminado correctamente.');
    }

    /**
     * Sirve la imagen del banner desde la BD (ruta pública, cacheable).
     */
    public function image(Banner $banner)
    {
        $record = $banner->fileRecord;
        abort_unless($record, 404);

        return response(base64_decode($record->content), 200, [
            'Content-Type' => $record->mime ?? 'image/png',
            'Cache-Control' => 'public, max-age=3600',
            'ETag' => '"'.$record->hash.'"',
        ]);
    }

    /**
     * Registra una impresión real (ruta pública). La página la reporta con
     * sendBeacon solo cuando el banner se mostró de verdad (ADR-0030 fase 2).
     */
    public function impression(Banner $banner)
    {
        // Query builder (no el modelo) para no tocar updated_at en cada hit.
        Banner::whereKey($banner->id)->increment('impressions');
        BannerDailyStat::bump($banner->id, 'impressions');

        return response()->noContent();
    }

    /**
     * Registra el clic y redirige al enlace del patrocinador (ruta pública).
     */
    public function click(Banner $banner)
    {
        abort_unless($banner->target_url, 404);

        // Query builder (no el modelo) para no tocar updated_at en cada clic.
        Banner::whereKey($banner->id)->increment('clicks');
        BannerDailyStat::bump($banner->id, 'clicks');

        return redirect()->away($banner->target_url);
    }

    /**
     * Reporte por rango de fechas para el patrocinador (ADR-0031).
     */
    public function report(Request $request, Banner $banner)
    {
        [$dateFrom, $dateTo, $days, $totals] = $this->reportData($request, $banner);

        return view('administration.banners.report', compact('banner', 'dateFrom', 'dateTo', 'days', 'totals'));
    }

    public function reportExport(Request $request, Banner $banner)
    {
        [$dateFrom, $dateTo, $days, $totals] = $this->reportData($request, $banner);

        $fileName = 'reporte-banner-'.Str::slug($banner->name).'-'.$dateFrom.'-a-'.$dateTo.'.xlsx';

        return Excel::download(
            new BannerReportExport($banner, $days, $totals, $dateFrom, $dateTo),
            $fileName,
        );
    }

    /**
     * Resuelve el rango (últimos 30 días por defecto), las filas por día y los
     * totales con CTR. Devuelve [dateFrom, dateTo, days, totals].
     */
    private function reportData(Request $request, Banner $banner): array
    {
        $request->validate([
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
        ]);

        $dateFrom = $request->input('dateFrom') ?: now()->subDays(29)->toDateString();
        $dateTo = $request->input('dateTo') ?: now()->toDateString();

        $days = $banner->dailyStats()
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        $impressions = $days->sum('impressions');
        $clicks = $days->sum('clicks');
        $totals = [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0.0,
        ];

        return [$dateFrom, $dateTo, $days, $totals];
    }
}
