<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard view
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        $user = Auth::user();

        // Ambil 10 transaksi regular (service_type_id = 1) yang belum selesai
        $recentTransactions = Transaction::whereNull('finish_date')
            ->with('status')
            ->where('service_type_id', 1)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Ambil 10 transaksi priority (service_type_id = 2) yang belum selesai
        $priorityTransactions = Transaction::whereNull('finish_date')
            ->with('status')
            ->where('service_type_id', 2)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Hitung jumlah member dan transaksi
        $membersCount = User::where('role', Role::Member)->count();
        $transactionsCount = Transaction::count();

        // Ambil data transaksi per tanggal dari 7 hari terakhir
        $transactionsPerDayRaw = DB::table('transactions')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', '>=', Carbon::now()->subDays(6)->startOfDay()) // 7 hari terakhir (termasuk hari ini)
            ->groupBy('date')
            ->orderBy('date', 'asc') // urut dari tanggal lama ke terbaru
            ->pluck('total', 'date');

        // Format tanggal ke '18 Mei 2025' dalam Bahasa Indonesia
        $transactionsPerDay = collect($transactionsPerDayRaw)->mapWithKeys(function ($total, $date) {
            return [Carbon::parse($date)->translatedFormat('d F Y') => $total];
        });

        return view('admin.index', compact(
            'user',
            'recentTransactions',
            'priorityTransactions',
            'membersCount',
            'transactionsCount',
            'transactionsPerDay'
        ));
    }
}
