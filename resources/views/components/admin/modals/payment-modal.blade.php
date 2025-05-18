@if ($show)
@props(['serviceTypes', 'vouchers', 'totalPrice', 'show' => false])

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Bayar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('admin.transactions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Subtotal --}}
                    <div class="form-group">
                        <label for="sub-total">Sub Total</label>
                        <input type="number" class="form-control form-control-lg" id="sub-total"
                               value="{{ $totalPrice ?? '0' }}" disabled>
                    </div>

                    {{-- Tipe Servis --}}
                    <div class="form-group">
                        <label for="service-type">Tipe Servis</label>
                        <select name="service-type" class="form-control form-control-lg" id="service-type" required>
                            <option value="" selected hidden disabled>Pilih tipe service</option>
                            @foreach ($serviceTypes as $type)
                                <option value="{{ $type->id }}" data-type-cost="{{ $type->cost }}">
                                    {{ $type->name }} ({{ $type->getFormattedCost() }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Voucher --}}
                    <div class="form-group">
                        <label for="voucher">Voucher</label>
                        <select name="voucher" class="form-control form-control-lg" id="voucher">
                            @if (!empty($vouchers))
                                <option value="0" data-potong="0">Pilih voucher</option>
                                @foreach ($vouchers as $voucher)
                                    <option value="{{ $voucher->id }}" data-potong="{{ $voucher->voucher->discount_value }}">
                                        {{ $voucher->voucher->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="0" data-potong="0">Tidak ada voucher yang dimiliki</option>
                            @endif
                        </select>
                    </div>

                    {{-- Harga Dibayar --}}
                    <div class="form-group">
                        <label for="total-harga">Harga Yang Dibayar</label>
                        <input type="number" class="form-control form-control-lg" id="total-harga"
                               value="{{ $totalPrice ?? '0' }}" disabled>
                    </div>

                    {{-- Bayar --}}
                    <div class="form-group">
                        <label for="input-bayar">Bayar</label>
                        <input type="number" class="form-control form-control-lg" id="input-bayar" name="payment-amount">
                    </div>

                    <h4>Kembalian : <span id="kembalian"></span></h4>

                    {{-- Metode Pembayaran --}}
                    <div class="form-group">
                        <label for="payment_method">Metode Pembayaran</label>
                        <select class="form-control" name="payment_method" id="payment_method" required>
                            <option value="">-- Pilih --</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>

                    {{-- Upload Bukti Transfer --}}
                    <div class="form-group" id="bukti-transfer-group" style="display: none;">
                        <label for="bukti_transfer">Upload Bukti Transfer</label>
                        <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control-file" accept="image/*">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Bayar</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Script tambahan --}}
@push('js')
    <script src="{{ asset('js/quantity-increment.js') }}"></script>
    <script src="{{ asset('js/input-transaksi.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentSelect = document.getElementById('payment_method');
            const buktiTransferGroup = document.getElementById('bukti-transfer-group');
            const buktiTransferInput = document.getElementById('bukti_transfer');

            paymentSelect.addEventListener('change', function () {
                if (this.value === 'transfer') {
                    buktiTransferGroup.style.display = 'block';
                    buktiTransferInput.required = true;
                } else {
                    buktiTransferGroup.style.display = 'none';
                    buktiTransferInput.required = false;
                }
            });
        });
    </script>
@endpush
@endif
