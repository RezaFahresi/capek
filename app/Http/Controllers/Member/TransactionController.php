<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Item;
use App\Models\Service;
use App\Models\Category;
use App\Models\ServiceType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    /**
     * Menampilkan riwayat transaksi member yang login.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $years = Transaction::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        $currentYear = now()->year;
        $currentMonth = now()->month;

        $yearFilter = $request->input('year', $currentYear);
        $monthFilter = $request->input('month', $currentMonth);

        $transactions = Transaction::with('status')
            ->where('member_id', $user->id)
            ->whereYear('created_at', $yearFilter)
            ->whereMonth('created_at', $monthFilter)
            ->orderBy('created_at', 'desc')
            ->orderBy('status_id', 'asc')
            ->get();

        return view('member.transactions_history', compact('user', 'transactions', 'years', 'currentYear', 'currentMonth'));
    }

    /**
     * Menampilkan form untuk membuat transaksi baru.
     */
    public function create(): View
    {
        $items = Item::all();
        $services = Service::all();
        $categories = Category::all();
        $serviceTypes = ServiceType::all();

        return view('member.transactions.create', compact('items', 'services', 'categories', 'serviceTypes'));
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'item_id'          => 'required|exists:items,id',
        'service_id'       => 'required|exists:services,id',
        'category_id'      => 'required|exists:categories,id',
        'service_type_id'  => 'required|exists:service_types,id',
        'quantity'         => 'required|integer|min:1',
        'payment_method'   => 'required|in:cash,transfer',
        'bukti_transfer'   => 'required_if:payment_method,transfer|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    $user = Auth::user();

    // Ambil data item & service type
    $item = Item::findOrFail($validatedData['item_id']);
    $serviceType = ServiceType::findOrFail($validatedData['service_type_id']);
    $quantity = $validatedData['quantity'];
    $price = $item->price * $quantity;

    // Buat transaksi baru
    $transaction = new Transaction();
    $transaction->member_id       = $user->id;
    $transaction->status_id       = $validatedData['payment_method'] === 'transfer' ? 2 : 1; // 2 = Menunggu validasi
    $transaction->payment_method  = $validatedData['payment_method'];
    $transaction->created_by      = 'member';
    $transaction->service_type_id = $validatedData['service_type_id'];
    $transaction->service_cost    = $serviceType->cost;
    $transaction->total           = $price + $serviceType->cost;
    $transaction->payment_amount  = $transaction->total; // diasumsikan member membayar lunas saat input

    // Simpan bukti transfer jika metode transfer
    if ($request->hasFile('bukti_transfer')) {
        $buktiPath = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
        $transaction->bukti_transfer = $buktiPath;
    }

    $transaction->save();

    // Simpan detail transaksi
    $detail = new TransactionDetail();
    $detail->transaction_id   = $transaction->id;
    $detail->item_id          = $validatedData['item_id'];
    $detail->service_id       = $validatedData['service_id'];
    $detail->category_id      = $validatedData['category_id'];
    $detail->service_type_id  = $validatedData['service_type_id'];
    $detail->quantity         = $quantity;
    $detail->price            = $price;
    $detail->save();

    return redirect()->route('member.transactions.index')
        ->with('success', 'Pesanan berhasil ditambahkan!');
}

    /**
     * Menampilkan detail transaksi tertentu.
     */
    public function show($id): View
    {
        $user = Auth::user();

        $transactions = TransactionDetail::with(['item', 'service', 'category', 'serviceType'])
            ->where('transaction_id', $id)
            ->get();

        $items = Item::all();
        $services = Service::all();
        $categories = Category::all();
        $serviceTypes = ServiceType::all();

        return view('member.transactions.create', compact('user', 'transactions', 'id', 'items', 'services', 'categories', 'serviceTypes'));
    }

    /**
     * Menghapus transaksi tertentu.
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Hapus bukti transfer jika ada
        if ($transaction->bukti_transfer && Storage::disk('public')->exists($transaction->bukti_transfer)) {
            Storage::disk('public')->delete($transaction->bukti_transfer);
        }

        $transaction->delete();

        return redirect()->route('member.transactions.index')
            ->with('success', 'Transaksi berhasil dihapus!');
    }
}
