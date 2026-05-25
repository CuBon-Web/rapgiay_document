@extends('layouts.main.master')

@section('title')
Chi tiết đơn hàng #{{ $bill->code_bill }}
@endsection

@section('css')
<link href="{{ asset('frontend/css/account-orders.css') }}" rel="stylesheet">
@endsection

@section('content')
<section class="account-page">
    <div class="container">
        <nav class="account-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Trang chủ</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('accoungOrder') }}">Đơn hàng</a>
            <span aria-hidden="true">/</span>
            <span>#{{ $bill->code_bill }}</span>
        </nav>

        <div class="account-layout">
            @include('auth.partials.account-sidebar', ['active' => 'orders'])

            <main class="account-main">
                <a href="{{ route('accoungOrder') }}" class="account-back-link">← Quay lại danh sách</a>

                <header class="account-main__head account-main__head--detail">
                    <div>
                        <h1>Đơn hàng #{{ $bill->code_bill }}</h1>
                        <p>Đặt ngày {{ date_format($bill->created_at, 'd/m/Y · H:i') }}</p>
                    </div>
                    @if ((int) $bill->statu === 0)
                        <span class="order-badge order-badge--pending">Chờ thanh toán</span>
                    @elseif ((int) $bill->statu === 1)
                        <span class="order-badge order-badge--paid">Đã thanh toán</span>
                    @elseif ((int) $bill->statu === 4)
                        <span class="order-badge order-badge--failed">Thất bại</span>
                    @else
                        <span class="order-badge">Trạng thái #{{ $bill->statu }}</span>
                    @endif
                </header>

                @if ((int) $bill->statu === 1 && !empty($downloads) && count($downloads) > 0)
                <div class="account-download-panel" id="tai-lieu">
                    <h2>Tài liệu đã mua ({{ count($downloads) }})</h2>
                    <p class="account-download-hint">
                        Mỗi sản phẩm được đóng gói thành file <strong>.zip</strong>.
                        Bấm <strong>Tải về</strong> để tải toàn bộ tài liệu (có thể mất 30–60 giây, vui lòng đợi).
                    </p>
                    <ul class="account-download-list">
                        @foreach ($downloads as $item)
                        <li>
                            <span class="account-download-name">{{ $item['name'] }}.zip</span>
                            <a href="{{ $item['download_url'] }}" class="account-download-btn">Tải về</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @elseif ((int) $bill->statu === 1)
                <div class="account-download-panel" id="tai-lieu">
                    <h2>Tài liệu đã mua</h2>
                    <p class="account-download-hint">Không tìm thấy link tài liệu. Liên hệ hỗ trợ kèm mã đơn <strong>#{{ $bill->code_bill }}</strong>.</p>
                </div>
                @endif

                <div class="account-info-grid">
                    <div class="account-info-box">
                        <h3>Thông tin khách hàng</h3>
                        <p><strong>{{ $bill->cus_name ?? $profile->name }}</strong></p>
                        @if (!empty($bill->cus_email))
                        <p>{{ $bill->cus_email }}</p>
                        @elseif (!empty($profile->email))
                        <p>{{ $profile->email }}</p>
                        @endif
                        @if (!empty($bill->cus_phone))
                        <p>{{ $bill->cus_phone }}</p>
                        @endif
                    </div>
                    <div class="account-info-box">
                        <h3>Thanh toán</h3>
                        <p>{{ $bill->payment_method === 'payos' || $bill->payment_method === 'online' ? 'Thanh toán online' : ($bill->payment_method ?? 'Online') }}</p>
                        <p><strong>{{ number_format($bill->total_money) }}₫</strong></p>
                    </div>
                    @if (!empty($bill->note))
                    <div class="account-info-box" style="grid-column: 1 / -1;">
                        <h3>Ghi chú</h3>
                        <p>{{ $bill->note }}</p>
                    </div>
                    @endif
                </div>

                <div class="account-products">
                    <h3>Sản phẩm trong đơn</h3>
                    <div style="overflow-x:auto;">
                        <table class="account-product-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>SL</th>
                                    <th>Tổng</th>
                                    @if ((int) $bill->statu === 1)
                                    <th>Tải về</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($billdetail as $item)
                                @php
                                    $unitPrice = $item->price - ($item->price * ($item->discount / 100));
                                    $dl = ($downloadsByProduct ?? collect())->get($item->code_product);
                                @endphp
                                <tr>
                                    <td data-label="Sản phẩm">
                                        <div class="account-product-cell">
                                            <img src="{{ url('' . $item->images) }}" alt="">
                                            <span class="account-product-name">{{ $item->name }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Đơn giá" class="numeric">{{ number_format($unitPrice) }}₫</td>
                                    <td data-label="Số lượng" class="numeric">{{ $item->qty }}</td>
                                    <td data-label="Tổng" class="numeric">{{ number_format($unitPrice * $item->qty) }}₫</td>
                                    @if ((int) $bill->statu === 1)
                                    <td data-label="Tải về">
                                        @if ($dl)
                                            <a href="{{ $dl['download_url'] }}" class="account-download-btn account-download-btn-sm">Tải về</a>
                                        @else
                                            <span style="color:var(--account-muted);">—</span>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="account-total-box">
                        <div class="account-total-row">
                            <span>Phí vận chuyển</span>
                            <span>{{ number_format($bill->transport_price ?? 0) }}₫</span>
                        </div>
                        <div class="account-total-row">
                            <span>Tổng thanh toán</span>
                            <span>{{ number_format($bill->total_money) }}₫</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>
@endsection
