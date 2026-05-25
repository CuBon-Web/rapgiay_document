@extends('layouts.main.master')
@section('title')
Thanh toán đơn hàng
@endsection
@section('description')
Bạn hoàn thành đơn hàng tại đây
@endsection
@section('image')
{{url('frontend/images/page-header-bg.jpg')}}
@endsection
@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Order",
  "name": {!! json_encode('Thanh toán đơn hàng') !!},
  "description": {!! json_encode('Bạn hoàn thành đơn hàng tại đây') !!},
  "image": {!! json_encode(url('frontend/images/page-header-bg.jpg')) !!},
  "sku": {!! json_encode('') !!},
  "url": {!! json_encode(url()->current()) !!},
  "brand": {
    "@type": "Brand",
    "name": {!! json_encode(config('app.name')) !!}
  }
}
</script>
@endsection
@section('css')
<link href="{{ asset('frontend/css/checkout.css') }}" rel="stylesheet">
@endsection
@section('js')
@endsection
@section('content')
@php
    $total = 0;
    $shippingFee = 0;
    $cartItems = (array) ($cart ?? []);
    foreach ($cartItems as $id => $item) {
        $unitPrice = (int) ($item['status_variant'] == 1 ? $item['price'] : (($item['discount'] ?? 0) > 0 ? $item['discount'] : $item['price']));
        $total += $unitPrice * (int) ($item['quantity'] ?? 1);
    }
    $grandTotal = $total;
@endphp

<div class="breadcrumb-section">
	<div class="container">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
				<li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
				<li class="breadcrumb-item active" aria-current="page">Thanh toán đơn hàng</li>
			</ol>
		</nav>
	</div>
</div>

<div class="checkout-section pt-50 mb-50">
    <div class="container" id="checkout-page"
         data-update-url="{{ route('update.cart') }}"
         data-remove-url="{{ route('remove.from.cart') }}"
         data-cart-url="{{ route('listCart') }}"
         data-shipping-fee="{{ $shippingFee }}">
        @if (count($cartItems) === 0)
            <div class="alert alert-warning">
                Giỏ hàng đang trống. Quay lại <a href="{{ route('home') }}">trang chủ</a> để tiếp tục mua sắm.
            </div>
        @else
            <form method="POST" action="{{ route('postBill') }}" id="checkout-form">
                @csrf
                <input type="hidden" name="total_money" id="checkout-total-money" value="{{ $grandTotal }}">
                <input type="hidden" name="shippingMethod" id="checkout-shipping-method" value="0">
                <input type="hidden" name="payment_method" id="checkout-payment-method" value="online">

                <div class="row gy-5 justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <div class="added-product-summary mb-30">
                            <h5>Đơn hàng của bạn</h5>
                            <ul class="added-products" id="checkout-products">
                                @foreach ($cartItems as $id => $item)
                                    @php
                                        $unitPrice = (int) ($item['status_variant'] == 1 ? $item['price'] : (($item['discount'] ?? 0) > 0 ? $item['discount'] : $item['price']));
                                        $lineTotal = $unitPrice * (int) ($item['quantity'] ?? 1);
                                        $coverImg = $item['image'];
                                    @endphp
                                    <li class="single-product" id="checkout-item-{{ $id }}" data-id="{{ $id }}" data-price="{{ $unitPrice }}">
                                        <div class="product-area">
                                            <div class="product-img">
                                                @if ($coverImg)
                                                    <img src="{{ $coverImg }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async">
                                                @else
                                                    <div class="doc-card__placeholder" aria-hidden="true">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="product-info">
                                                <h5><a href="javascript:;">{{ $item['name'] }}</a></h5>
                                                @if (($item['status_variant'] ?? 0) == 1 && !empty($item['variant']))
                                                    <small>{{ $item['variant'] }}</small>
                                                @endif
                                                <div class="product-total">
                                                    <div class="quantity-counter d-flex align-items-center">
                                                        <button type="button" class="quantity-btn js-qty-minus" data-id="{{ $id }}">-</button>
                                                        <input type="number" min="1" class="quantity__input js-qty-input" data-id="{{ $id }}" value="{{ (int) ($item['quantity'] ?? 1) }}">
                                                        <button type="button" class="quantity-btn js-qty-plus" data-id="{{ $id }}">+</button>
                                                    </div>
                                                    <strong><span class="product-price js-line-total" id="line-total-{{ $id }}">{{ number_format($lineTotal) }}₫</span></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="delete-btn">
                                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-item" data-id="{{ $id }}">Xóa</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="cost-summary total-cost mb-30">
                            <table class="table cost-summary-table total-cost">
                                <tbody>
                                    <tr>
                                        <th>Tổng thanh toán</th>
                                        <th id="checkout-total-display">{{ number_format($grandTotal) }}₫</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="payment-methods mb-30">
                            <h5>Thanh toán online</h5>
                            <p class="para mb-0">Sau khi đặt hàng, bạn sẽ được chuyển sang cổng thanh toán PayOS (VietQR) để hoàn tất giao dịch và nhận link tài liệu.</p>
                        </div>

                        <div class="place-order-btn">
                            <button type="submit" class="primary-btn1 hover-btn3 w-100">Thanh toán online</button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var checkoutRoot = document.getElementById("checkout-page");
    if (!checkoutRoot) return;

    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute("content") : "";
    var updateUrl = checkoutRoot.getAttribute("data-update-url");
    var removeUrl = checkoutRoot.getAttribute("data-remove-url");
    var cartUrl = checkoutRoot.getAttribute("data-cart-url");
    var shippingFee = 0;
    var totalDisplay = document.getElementById("checkout-total-display");
    var totalMoneyInput = document.getElementById("checkout-total-money");
    var shippingMethodInput = document.getElementById("checkout-shipping-method");

    function formatMoney(value) {
        return new Intl.NumberFormat("vi-VN").format(Number(value || 0)) + "₫";
    }

    function postForm(url, data) {
        return fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken
            },
            body: new URLSearchParams(data).toString()
        }).then(function (res) {
            if (!res.ok) throw new Error("Request failed");
            return res.json();
        });
    }

    function recalculate() {
        var total = 0;
        var rows = document.querySelectorAll("#checkout-products .single-product");
        rows.forEach(function (row) {
            var id = row.getAttribute("data-id");
            var price = Number(row.getAttribute("data-price") || 0);
            var qtyInput = document.querySelector('.js-qty-input[data-id="' + id + '"]');
            var qty = Number(qtyInput ? qtyInput.value : 1);
            if (!qty || qty < 1) qty = 1;
            if (qtyInput) qtyInput.value = qty;

            var line = price * qty;
            var lineEl = document.getElementById("line-total-" + id);
            if (lineEl) lineEl.textContent = formatMoney(line);
            total += line;
        });
        var finalTotal = total;
        if (totalDisplay) totalDisplay.textContent = formatMoney(finalTotal);
        if (totalMoneyInput) totalMoneyInput.value = finalTotal;
        if (shippingMethodInput) shippingMethodInput.value = 0;
        if (rows.length === 0 && cartUrl) window.location.href = cartUrl;
    }

    document.addEventListener("click", function (event) {
        var plus = event.target.closest(".js-qty-plus");
        var minus = event.target.closest(".js-qty-minus");
        var remove = event.target.closest(".js-remove-item");

        if (plus || minus) {
            event.preventDefault();
            var btn = plus || minus;
            var id = btn.getAttribute("data-id");
            var input = document.querySelector('.js-qty-input[data-id="' + id + '"]');
            if (!input) return;
            var qty = Number(input.value || 1);
            qty = plus ? qty + 1 : Math.max(1, qty - 1);
            input.value = qty;
            recalculate();
            postForm(updateUrl, { id: id, quantity: qty }).catch(function () {
                alert("Không thể cập nhật số lượng.");
            });
        }

        if (remove) {
            event.preventDefault();
            var removeId = remove.getAttribute("data-id");
            postForm(removeUrl, { id: removeId }).then(function () {
                var row = document.getElementById("checkout-item-" + removeId);
                if (row) row.remove();
                recalculate();
            }).catch(function () {
                alert("Không thể xóa sản phẩm.");
            });
        }
    });

    document.addEventListener("change", function (event) {
        if (event.target.classList.contains("js-qty-input")) {
            var id = event.target.getAttribute("data-id");
            var qty = Number(event.target.value || 1);
            if (!qty || qty < 1) qty = 1;
            event.target.value = qty;
            recalculate();
            postForm(updateUrl, { id: id, quantity: qty }).catch(function () {
                alert("Không thể cập nhật số lượng.");
            });
        }
    });

    recalculate();
});
</script>
@endsection
