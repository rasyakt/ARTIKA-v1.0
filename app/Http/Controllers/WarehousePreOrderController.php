<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierPreOrder;

class WarehousePreOrderController extends Controller
{
    /**
     * Daftar semua pre-order (Gudang view)
     */
    public function index(Request $request)
    {
        $query = SupplierPreOrder::with(['supplier', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $preOrders = $query->paginate(15)->withQueryString();

        return view('warehouse.pre-orders.index', compact('preOrders'));
    }

    /**
     * Detail satu pre-order
     */
    public function show($id)
    {
        $preOrder = SupplierPreOrder::with(['supplier', 'items.product', 'user'])->findOrFail($id);

        return view('warehouse.pre-orders.show', compact('preOrder'));
    }
}
