<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\Repair;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalBranches = Branch::count();

        $pendingRepairs = Repair::where('status', 'pending')->count();
        $inProgressRepairs = Repair::where('status', 'in_progress')->count();

        $recentMovements = StockMovement::with(['movable'])
            ->latest()
            ->limit(10)
            ->get();

        $recentRepairs = Repair::with('product')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_branches' => $totalBranches,
                'pending_repairs' => $pendingRepairs,
                'in_progress_repairs' => $inProgressRepairs,
                'recent_movements' => $recentMovements,
                'recent_repairs' => $recentRepairs,
            ],
        ]);
    }
}
