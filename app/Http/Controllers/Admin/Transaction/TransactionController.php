<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\PriceList;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Status;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\UserVoucher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $currentMonth = $request->input('month', date('m'));
        $currentYear = $request->input('year', date('Y'));
        $user = Auth::user();

        $ongoingTransactions = Transaction::with('member')->whereYear('created_at', '=', $currentYear)
            ->whereMonth('created_at', '=', $currentMonth)
            ->where('service_type_id', 1)
            ->whereNull('finish_date')
            ->orderBy('created_at', 'DESC')
            ->get();

        $ongoingPriorityTransactions = Transaction::with('member')->whereYear('created_at', '=', $currentYear)
            ->whereMonth('created_at', '=', $currentMonth)
            ->where('service_type_id', 2)
            ->whereNull('finish_date')
            ->orderBy('created_at', 'DESC')
            ->get();

        $finishedTransactions = Transaction::with('member')->whereYear('created_at', '=', $currentYear)
            ->whereMonth('created_at', '=', $currentMonth)
            ->whereNotNull('finish_date')
            ->orderBy('created_at', 'DESC')
            ->get();

        $status = Status::all();
        $years = Transaction::selectRaw('YEAR(created_at) as Tahun')->distinct()->get();

        return view('admin.transactions_history', compact(
            'user',
            'status',
            'years',
            'currentYear',
            'currentMonth',
            'ongoingTransactions',
            'ongoingPriorityTransactions',
            'finishedTransactions'
        ));
    }

    public function create(Request $request): View
    {
        $user = Auth::user();
        $items = Item::all();
        $categories = Category::all();
        $services = Service::all();
        $serviceTypes = ServiceType::all();

        if ($request->session()->has('transaction') && $request->session()->has('memberIdTransaction')) {
            $sessionTransaction = $request->session()->get('transaction');
            $memberIdSessionTransaction = $request->session()->get('memberIdTransaction');

            $vouchers = UserVoucher::where([
                'user_id' => $memberIdSessionTransaction,
                'used' => 0,
            ])->get();

            $totalPrice = array_sum(array_column($sessionTransaction, 'subTotal'));

            return view('admin.transaction_input', compact(
                'user', 'items', 'categories', 'services',
                'serviceTypes', 'sessionTransaction',
                'memberIdSessionTransaction', 'totalPrice', 'vouchers'
            ));
        }

        return view('admin.transaction_input', compact(
            'user', 'items', 'categories', 'services', 'serviceTypes'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'payment-amount' => ['required', 'integer'],
        ]);

        DB::beginTransaction();

        $memberId = $request->session()->get('memberIdTransaction');
        $user = Auth::user();
        $adminId = $user->id;

        $sessionTransaction = $request->session()->get('transaction');
        $totalPrice = array_sum(array_column($sessionTransaction, 'subTotal'));
        $discount = 0;

        if ($request->input('voucher') != 0) {
            $userVoucher = UserVoucher::where('id', $request->input('voucher'))->firstOrFail();
            $discount = $userVoucher->voucher->discount_value ?? 0;
            $totalPrice -= $discount;
            $totalPrice = max($totalPrice, 0);

            $userVoucher->used = 1;
            $userVoucher->save();
        }

        $cost = 0;
        if ($request->input('service-type') != 0) {
            $serviceTypeCost = ServiceType::findOrFail($request->input('service-type'));
            $cost = $serviceTypeCost->cost;
            $totalPrice += $cost;
        }

        if ($request->input('payment-amount') < $totalPrice) {
            return redirect()->route('admin.transactions.create')->with('error', 'Pembayaran kurang');
        }

        $transaction = new Transaction([
            'status_id' => 1,
            'member_id' => $memberId,
            'admin_id' => $adminId,
            'finish_date' => null,
            'discount' => $discount,
            'total' => $totalPrice,
            'service_type_id' => $request->input('service-type'),
            'service_cost' => $cost,
            'payment_amount' => $request->input('payment-amount'),
            'created_by' => 'admin', // ✅ tambahan penting
        ]);
        $transaction->save();

        foreach ($sessionTransaction as $trs) {
            $price = PriceList::where([
                'item_id' => $trs['itemId'],
                'category_id' => $trs['categoryId'],
                'service_id' => $trs['serviceId'],
            ])->firstOrFail();

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'price_list_id' => $price->id,
                'quantity' => $trs['quantity'],
                'price' => $price->price,
                'sub_total' => $trs['subTotal'],
            ]);
        }

        $user = User::findOrFail($memberId);
        $user->point += 1;
        $user->save();

        $request->session()->forget('transaction');
        $request->session()->forget('memberIdTransaction');

        DB::commit();

        return redirect()->route('admin.transactions.create')
            ->with('success', 'Transaksi berhasil disimpan')
            ->with('id_trs', $transaction->id);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $transaction = $transaction->load([
            'transaction_details',
            'transaction_details.price_list',
            'transaction_details.price_list.item',
            'transaction_details.price_list.service',
            'transaction_details.price_list.category',
            'service_type',
        ]);

        return response()->json($transaction);
    }

    public function update(Transaction $transaction, Request $request): JsonResponse
    {
        $currentDate = $request->input('val') == 3 ? now() : null;
        $transaction->status_id = $request->input('val', 2);
        $transaction->finish_date = $currentDate;
        $transaction->save();

        return response()->json();
    }
}
