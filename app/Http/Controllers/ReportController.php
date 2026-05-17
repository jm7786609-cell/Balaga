<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Sales Report API Endpoint (for Bootstrap Table)
     */
    public function sales(Request $request)
    {
        // Validate it's an AJAX/table request
        if (!$request->ajax() && !$request->wantsJson()) {
            abort(404); // Or return redirect()->route('home');
        }

        $draw    = (int) $request->input('draw', 1);
        $start   = (int) $request->input('start', 0);
        $length  = (int) $request->input('length', 25);
        $search  = $request->input('search.value', '');
        $from    = $request->input('from');
        $to      = $request->input('to');

        // Base query: completed transactions with needed relations
        $baseQuery = Transaction::query()
            ->with(['items.product', 'cashier'])
            ->completed();

        // Apply date range
        if ($from) {
            $baseQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $baseQuery->whereDate('created_at', '<=', $to);
        }

        // Total records (without search)
        $totalRecords = (clone $baseQuery)->count();

        // Apply search
        $query = clone $baseQuery;

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('items.product', function ($pq) use ($search) {
                    $pq->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('product_brand', 'like', "%{$search}%");
                })
                    ->orWhereHas('cashier', function ($cq) use ($search) {
                        $cq->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        $filteredRecords = $query->count();

        // Get paginated transactions
        $transactions = $query
            ->orderBy('created_at', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // Flatten items for table rows
        $rows = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $product = $item->product;
                $rows[] = [
                    'transaction_id' => $transaction->id,
                    'created_at'     => $transaction->created_at->format('M d, Y h:i A'),
                    'cashier_name'   => $transaction->cashier?->full_name ?? 'N/A',
                    'product_code'   => $product?->product_code ?? '—',
                    'product_name'   => $product?->product_name ?? 'Deleted Product',
                    'brand'          => $product?->product_brand ?? '—',
                    'quantity'       => $item->quantity,
                    'price'          => number_format($item->price, 2),
                    'subtotal'       => number_format($item->subtotal, 2),
                    'total_amount'   => number_format($transaction->total_amount, 2),
                ];
            }
        }

        // Summary stats
        $summaryQuery = Transaction::completed();
        if ($from) $summaryQuery->whereDate('created_at', '>=', $from);
        if ($to)   $summaryQuery->whereDate('created_at', '<=', $to);

        $totalRevenue = $summaryQuery->sum('total_amount');
        $totalTransactions = $summaryQuery->count();
        $totalItemsSold = TransactionItem::whereHas('transaction', function ($q) use ($from, $to) {
            $q->completed();
            if ($from) $q->whereDate('created_at', '>=', $from);
            if ($to)   $q->whereDate('created_at', '<=', $to);
        })->sum('quantity');

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $rows,
            'summary'         => [
                'total_revenue'       => number_format($totalRevenue, 2),
                'total_transactions'  => $totalTransactions,
                'total_items_sold'    => (int) $totalItemsSold,
                'average_sale'        => $totalTransactions > 0 ? number_format($totalRevenue / $totalTransactions, 2) : '0.00'
            ]
        ]);
    }

    public function expiry(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return view('reports.expiry');
        }

        $draw   = (int)$request->input('draw', 1);
        $start  = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        $search = $request->input('search.value', '');

        $query = \App\Models\Inventory::with(['product'])
            ->whereNotNull('expiration_date')
            ->where(function ($q) {
                $q->where('expiration_date', '<=', now()->addDays(90))  // Next 90 days + expired
                    ->orWhere('expiration_date', '<', now());
            });

        // Search
        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('product_brand', 'like', "%{$search}%");
            });
        }

        $totalRecords = \App\Models\Inventory::whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(90))->count();

        $filteredRecords = (clone $query)->count();

        $inventories = $query
            ->orderByRaw("CASE WHEN expiration_date < CURDATE() THEN 0 ELSE 1 END")
            ->orderBy('expiration_date')
            ->skip($start)
            ->take($length)
            ->get();

        $rows = $inventories->map(function ($inv) {
            $days = $inv->expiration_date->diffInDays(now(), false);
            $isExpired = $inv->expiration_date->isPast();

            return [
                'product_code'     => $inv->product->product_code,
                'product_name'     => $inv->product->product_name,
                'brand'            => $inv->product->product_brand,
                'batch_no'         => $inv->batch_no ?: '—',
                'quantity'         => $inv->quantity,
                'expiration_date'  => $inv->expiration_date->format('M d, Y'),
                'days_remaining'   => $isExpired ? 'EXPIRED' : $days . ' days',
                'status'           => $isExpired ? 'expired' : ($days <= 30 ? 'near' : 'warning'),
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $rows
        ]);
    }

    public function lowStock(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return view('reports.low-stock');
        }

        $draw   = (int)$request->input('draw', 1);
        $start  = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', null);

        // Base query: products with current stock <= safety_stock (or reorder point)
        $query = \App\Models\Product::with(['category', 'supplier'])
            ->whereRaw('(
            SELECT COALESCE(SUM(quantity), 0)
            FROM inventories
            WHERE inventories.product_id = products.id
              AND (expiration_date IS NULL OR expiration_date > CURDATE())
        ) <= COALESCE(safety_stock, 0) + COALESCE(lead_time_days, 0)');

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('product_brand', 'like', "%{$search}%");
            });
        }

        $totalRecords = \App\Models\Product::whereRaw('(
        SELECT COALESCE(SUM(quantity), 0)
        FROM inventories
        WHERE inventories.product_id = products.id
          AND (expiration_date IS NULL OR expiration_date > CURDATE())
    ) <= COALESCE(safety_stock, 0) + COALESCE(lead_time_days, 0)')->count();

        $filteredRecords = (clone $query)->count();

        $products = $query
            ->orderByRaw('(
            SELECT COALESCE(SUM(quantity), 0)
            FROM inventories
            WHERE inventories.product_id = products.id
              AND (expiration_date IS NULL OR expiration_date > CURDATE())
        )')
            ->skip($start)
            ->take($length)
            ->get();

        $rows = $products->map(function ($product) {
            $currentStock = $product->current_stock ?? 0;
            $reorderPoint = ($product->safety_stock ?? 0) + ($product->lead_time_days ?? 0);
            $shortage = $reorderPoint - $currentStock;

            return [
                'product_code'   => $product->product_code,
                'product_name'   => $product->product_name,
                'brand'          => $product->product_brand,
                'category'       => $product->category?->category_name ?? '—',
                'current_stock'  => $currentStock,
                'safety_stock'   => $product->safety_stock ?? 0,
                'lead_time_days' => $product->lead_time_days ?? 0,
                'reorder_point'  => $reorderPoint,
                'shortage'       => $shortage > 0 ? $shortage : 0,
                'supplier'       => $product->supplier?->supplier_name ?? '—',
                'status'         => $currentStock == 0 ? 'out' : 'low',
            ];
        });

        // Apply filter to rows
        if ($filter === 'out') {
            $rows = $rows->filter(function ($row) {
                return $row['status'] === 'out';
            })->values();
        } elseif ($filter === 'low') {
            $rows = $rows->filter(function ($row) {
                return $row['status'] === 'low';
            })->values();
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => count($rows),
            'data'            => $rows->toArray()
        ]);
    }

    public function topSellers(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return view('reports.top-sellers');
        }

        $draw   = (int)$request->input('draw', 1);
        $start  = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        $from   = $request->input('from');
        $to     = $request->input('to');

        // Base query: aggregate sales by product
        $baseQuery = \App\Models\TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->selectRaw('
            products.id,
            products.product_code,
            products.product_name,
            products.product_brand,
            SUM(transaction_items.quantity) as total_qty,
            SUM(transaction_items.subtotal) as total_revenue,
            AVG(transaction_items.price) as avg_price
        ')
            ->groupBy('products.id', 'products.product_code', 'products.product_name', 'products.product_brand');

        // Date range
        if ($from) $baseQuery->whereDate('transactions.created_at', '>=', $from);
        if ($to)   $baseQuery->whereDate('transactions.created_at', '<=', $to);

        $totalRecords = $baseQuery->clone()->count();

        $topProducts = $baseQuery
            ->orderByDesc('total_qty')
            ->skip($start)
            ->take($length)
            ->get();

        $rows = $topProducts->map(function ($item, $index) use ($start) {
            return [
                'rank'          => $start + $index + 1,
                'product_code'  => $item->product_code,
                'product_name'  => $item->product_name,
                'brand'         => $item->product_brand,
                'total_qty'     => (int)$item->total_qty,
                'total_revenue' => number_format($item->total_revenue, 2),
                'avg_price'     => number_format($item->avg_price, 2),
                'percentage'    => 0 // will calculate in JS for accuracy
            ];
        });

        // Calculate total quantity for percentage
        $totalQtyAll = \App\Models\TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->when($from, fn($q) => $q->whereDate('transactions.created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('transactions.created_at', '<=', $to))
            ->sum('transaction_items.quantity');

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data'            => $rows,
            'total_qty_all'   => (int)$totalQtyAll
        ]);
    }

    public function deadStock(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return view('reports.dead-stock');
        }

        $draw   = (int)$request->input('draw', 1);
        $start  = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        $days   = $request->input('days', 90); // default: no sales in last 90 days

        // Products with current stock > 0 but no sales in last X days
        $query = \App\Models\Product::with(['category', 'supplier'])
            ->whereHas('inventories', fn($q) => $q->where('quantity', '>', 0))
            ->whereRaw("(
            SELECT COUNT(*) FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            WHERE ti.product_id = products.id
              AND t.status = 'completed'
              AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ) = 0", [$days]);

        $totalRecords = (clone $query)->count();
        $filteredRecords = $totalRecords;

        $products = $query
            ->withSum(['inventories' => fn($q) => $q->where('quantity', '>', 0)], 'quantity')
            ->orderByDesc('inventories_sum_quantity')
            ->skip($start)
            ->take($length)
            ->get();

        $rows = $products->map(function ($p) {
            $currentStock = $p->current_stock ?? 0;
            $value = $currentStock * $p->price;

            return [
                'product_code'  => $p->product_code,
                'product_name'  => $p->product_name,
                'brand'         => $p->product_brand,
                'category'      => $p->category?->category_name ?? '—',
                'current_stock' => $currentStock,
                'stock_value'   => number_format($value, 2),
                'supplier'      => $p->supplier?->supplier_name ?? '—',
                'last_sold'     => $p->transactionItems()->latest('created_at')->first()?->created_at?->format('M d, Y') ?? 'Never',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $rows
        ]);
    }
}
