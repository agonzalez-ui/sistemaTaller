<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderIntakePhoto;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderIntakePhotoController extends Controller
{
    public function show(Order $order, OrderIntakePhoto $photo): StreamedResponse
    {
        abort_unless(request()->user()->canViewOrder($order), 403);
        abort_unless($photo->order_id === $order->id, 404);
        abort_unless(Storage::disk($photo->disk)->exists($photo->path), 404);

        return Storage::disk($photo->disk)->response($photo->path, $photo->original_name, [
            'Content-Type' => $photo->mime_type,
            'Content-Disposition' => 'inline; filename="'.addslashes($photo->original_name).'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function destroy(Request $request, Order $order, OrderIntakePhoto $photo, OrderService $service): RedirectResponse
    {
        abort_unless($request->user()->canEditOrder($order), 403);
        $service->removeIntakePhoto($request, $order, $photo);

        return back()->with('success', 'Fotografía de recepción eliminada correctamente.');
    }
}
