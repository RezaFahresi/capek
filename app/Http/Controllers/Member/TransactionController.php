<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Item; // Import Item Model
use App\Models\Service; // Import Service Model
use App\Models\Category; // Import Category Model
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Method to show transactions history based on current logged on member
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $transactions = Transaction::with('status')->where('member_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->orderBy('status_id', 'ASC')
            ->get();

        return view('member.transactions_history', compact('user', 'transactions'));
    }

    /**
     * Method to show detail transaction
     *
     * @param  string|int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(string|int $id): View
    {
        $user = Auth::user();
        $transactions = TransactionDetail::where('transaction_id', $id)->get();

        return view('member.detail', compact('user', 'transactions', 'id'));
    }

    public function create()
{
    $items = \App\Models\Item::all();
    $services = \App\Models\Service::all();
    $categories = \App\Models\Category::all();
    
    // Mengirimkan nilai default jika belum ada input sebelumnya
    $selectedItem = old('item_id', 1);  // Default Baju
    $selectedService = old('service_id', 1);  // Default Cuci
    $selectedCategory = old('category_id', 1);  // Default Satuan
    $quantity = old('quantity', 1);  // Default 1

    return view('member.transactions.create', compact('items', 'services', 'categories', 'selectedItem', 'selectedService', 'selectedCategory', 'quantity'));
}

    

    public function store(Request $request)
    {
        // Validasi data yang diterima dari form
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'service_id' => 'required|exists:services,id',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Ambil data pengguna yang sedang login
        $user = Auth::user();

        // Buat transaksi baru
        $transaction = new Transaction();
        $transaction->member_id = $user->id;
        $transaction->status_id = 1; // Set status to "Pending" or whatever status corresponds
        $transaction->save();

        // Hitung harga berdasarkan item, service, dan quantity
        $item = Item::find($validatedData['item_id']);
        $service = Service::find($validatedData['service_id']);
        $category = Category::find($validatedData['category_id']);
        
        $price = $item->price * $validatedData['quantity']; // Price calculation logic

        // Buat detail transaksi
        $detail = new TransactionDetail();
        $detail->transaction_id = $transaction->id;
        $detail->item_id = $validatedData['item_id'];
        $detail->service_id = $validatedData['service_id'];
        $detail->category_id = $validatedData['category_id'];
        $detail->quantity = $validatedData['quantity'];
        $detail->price = $price; // Set price based on item and quantity
        $detail->save();

        // Redirect to transaction index with success message
        return redirect()->route('member.transactions.index')->with('success', 'Pesanan berhasil ditambahkan!');
    }
}
