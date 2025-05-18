<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PriceList;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MemberTransactionController extends Controller
{
    public function create()
    {
        $priceLists = PriceList::with(['item', 'category', 'service'])->get();

        return view('member.transactions.create', compact('priceLists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.price_list_id' => 'required|exists:price_lists,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'member_id' => Auth::id(),
                'service_type_id' => 1, // default regular service
                'service_cost' => 0,
                'discount' => 0,
                'total' => 0,
                'payment_amount' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $priceList = PriceList::findOrFail($item['price_list_id']);
                $subTotal = $priceList->price * $item['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'price_list_id' => $priceList->id,
                    'quantity' => $item['quantity'],
                    'price' => $priceList->price,
                    'sub_total' => $subTotal,
                ]);

                $total += $subTotal;
            }

            $transaction->update([
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('member.transactions.create')
                ->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
