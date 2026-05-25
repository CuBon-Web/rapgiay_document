@extends('layouts.main.master')

@section('title')
Đơn hàng của tôi
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
            <span>Đơn hàng của tôi</span>
        </nav>

        <div class="account-layout">
            @include('auth.partials.account-sidebar', ['active' => 'orders'])

            <main class="account-main">
                <header class="account-main__head">
                    <h1>Đơn hàng của bạn</h1>
                    <p>Quản lý đơn hàng và tải lại tài liệu số đã mua.</p>
                </header>

                @if ($bill->isEmpty())
                    <div class="account-empty">
                        <div class="account-empty__icon" aria-hidden="true">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                        </div>
                        <h2>Chưa có đơn hàng</h2>
                        <p>Bạn chưa mua tài liệu nào. Khám phá kho tài liệu và đặt hàng ngay.</p>
                        <a href="{{ route('home') }}" class="account-btn account-btn--primary">Xem tài liệu</a>
                    </div>
                @else
                    <div class="order-list">
                        @foreach ($bill as $item)
                            @php
                                $docCount = $downloadCounts[$item->code_bill] ?? 0;
                                $isPaid = (int) $item->statu === 1;
                                $canDownload = $isPaid && $docCount > 0;
                            @endphp
                            <article class="order-card">
                                <div class="order-card__top">
                                    <a href="{{ route('accoungOrderDetail', ['billid' => $item->code_bill]) }}" class="order-card__code">
                                        #{{ $item->code_bill }}
                                    </a>
                                    @if ((int) $item->statu === 0)
                                        <span class="order-badge order-badge--pending">Chờ thanh toán</span>
                                    @elseif ($isPaid)
                                        <span class="order-badge order-badge--paid">Đã thanh toán</span>
                                    @elseif ((int) $item->statu === 4)
                                        <span class="order-badge order-badge--failed">Thất bại</span>
                                    @else
                                        <span class="order-badge">Trạng thái #{{ $item->statu }}</span>
                                    @endif
                                </div>

                                <div class="order-card__meta">
                                    <span>{{ date_format($item->created_at, 'd/m/Y · H:i') }}</span>
                                    <span class="order-card__dot" aria-hidden="true">·</span>
                                    <span class="order-card__price">{{ number_format($item->total_money) }}₫</span>
                                </div>

                                <div class="order-card__docs">
                                    @if ($canDownload)
                                        <span class="order-card__docs-ok">{{ $docCount }} sản phẩm · có thể tải lại</span>
                                    @elseif ($isPaid)
                                        <span class="order-card__docs-none">Không có link tài liệu</span>
                                    @else
                                        <span class="order-card__docs-none">Tài liệu sau khi thanh toán</span>
                                    @endif
                                </div>

                                <div class="order-card__actions">
                                    @if ($canDownload)
                                        <a href="{{ route('accoungOrderDetail', ['billid' => $item->code_bill]) }}#tai-lieu"
                                           class="account-btn account-btn--primary account-btn--sm">
                                            Tải lại
                                        </a>
                                    @endif
                                    <a href="{{ route('accoungOrderDetail', ['billid' => $item->code_bill]) }}"
                                       class="account-btn account-btn--outline account-btn--sm">
                                        Chi tiết
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>
</section>
@endsection
