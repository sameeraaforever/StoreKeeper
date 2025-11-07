<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\SupplyRecord;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalProducts = Product::count();
        $totalUsers = User::count();
        $totalCompanies = Company::count();

        $currentMonthSupplyCost = SupplyRecord::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('total_amount');

        $currentMonthProductsSupplied = SupplyRecord::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('quantity');

 
        $today = Carbon::today();
        $end = $today->copy()->endOfMonth();
        $start = $today->copy()->subMonths(11)->startOfMonth(); // last 12 months inclusive


        // Monthly arrays (labels and values)
        $months = [];
        $supplyCostSeries = []; // total_amount sum per month
        $productsSuppliedSeries = []; // quantity sum per month

        // Prepare month labels
        $period = $start->copy();
        while ($period->lte($end)) {
            $months[] = $period->format('M Y'); // e.g., "Nov 2024"
            $period->addMonth();
        }

        // Use supply_date when present, otherwise created_at
        // We'll query supply_records grouped by year-month using supply_date if not null, else created_at
        // Build a query that selects the date to use: COALESCE(supply_date, DATE(created_at))
        $records = SupplyRecord::selectRaw("
                DATE_FORMAT(COALESCE(supply_date, DATE(created_at)), '%Y-%m') as ym,
                SUM(total_amount) as total_amount_sum,
                SUM(quantity) as qty_sum
            ")
            ->where(function($q) use ($start, $end) {
                // Filter by supply_date or created_at between range
                $q->whereBetween(DB::raw("COALESCE(supply_date, DATE(created_at))"), [$start->toDateString(), $end->toDateString()]);
            })
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym'); // keyed by "YYYY-MM"

        // Fill series arrays aligned with $months
        $period = $start->copy();
        while ($period->lte($end)) {
            $key = $period->format('Y-m');
            $rec = $records->get($key);
            $supplyCostSeries[] = $rec ? (float) $rec->total_amount_sum : 0.0;
            $productsSuppliedSeries[] = $rec ? (float) $rec->qty_sum : 0.0;
            $period->addMonth();
        }

        // Top 5 companies by spend (current year)
        $yearStart = Carbon::now()->startOfYear()->toDateString();
        $yearEnd = Carbon::now()->endOfYear()->toDateString();

        $companySums = SupplyRecord::selectRaw("
                company_id,
                SUM(total_amount) as total_spend
            ")
            ->whereBetween(DB::raw("COALESCE(supply_date, DATE(created_at))"), [$yearStart, $yearEnd])
            ->groupBy('company_id')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();

        // Map company names
        $companyIds = $companySums->pluck('company_id')->all();
        $companies = \App\Models\Company::whereIn('id', $companyIds)->pluck('name', 'id');

        $topCompaniesLabels = [];
        $topCompaniesSeries = [];
        foreach ($companySums as $row) {
            $topCompaniesLabels[] = $companies[$row->company_id] ?? ("Company #{$row->company_id}");
            $topCompaniesSeries[] = (float) $row->total_spend;
        }

        $top5Products = SupplyRecord::selectRaw('product_id, SUM(quantity) as total_qty, SUM(quantity * unit_price) as total_cost')
            ->with('product.activePrice')
            ->groupBy('product_id')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        $pieSeries = $top5Products->map(function($item) {
            return (float) $item->total_cost; // Force numeric
        });

        $pieLabels = $top5Products->pluck('product.name')->values();


        // Top 10 products table
        $top10Products = \App\Models\SupplyRecord::selectRaw('product_id, SUM(quantity) as total_qty, SUM(total_amount) as total_cost')
            ->with(['product.activePrice'])
            ->groupBy('product_id')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();

        return view('home', [
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'totalCompanies' => $totalCompanies,
            'months' => $months,
            'supplyCostSeries' => $supplyCostSeries,
            'productsSuppliedSeries' => $productsSuppliedSeries,
            'topCompaniesLabels' => $topCompaniesLabels,
            'topCompaniesSeries' => $topCompaniesSeries,
            'pieSeries'=>$pieSeries,
            'pieLabels'=>$pieLabels,
            'top10Products'=>$top10Products,
            'currentMonthSupplyCost' => array_sum($supplyCostSeries) ? $this->sumForLatestMonth($start, $end, $supplyCostSeries, $months) : 0,
            'currentMonthProductsSupplied' => array_sum($productsSuppliedSeries) ? $this->sumForLatestMonth($start, $end, $productsSuppliedSeries, $months) : 0,

        ]);
    }

    // Helper to get latest month value (last element of series)
    private function sumForLatestMonth($start, $end, $series, $months)
    {
        if (count($series) === 0) return 0;
        return end($series);
    }

}
