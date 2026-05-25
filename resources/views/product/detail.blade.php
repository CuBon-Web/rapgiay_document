@extends('layouts.main.master')
@section('title')
    {{ $product->seo_title ? $product->seo_title : $product->name }}
@endsection
@section('description')
    {{ $product->meta_description ? $product->meta_description : languageName($product->description) }}
@endsection
@section('image')
    @php
        $img = json_decode($product->images, true) ?? [];
        $ungdung = json_decode($product->preserve);
    @endphp
    {{ !empty($img[0]) ? url($img[0]) : url($setting->favicon ?? '') }}
@endsection
@section('schema')
    @php
        $cleanText = function ($value) {
            $text = (string) $value;
            // Remove zero-width chars that usually appear from copy/paste.
            return preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        };
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $toAbsoluteUrl = function ($path) {
            $value = trim((string) $path);
            if ($value === '') {
                return null;
            }
            if (preg_match('/^https?:\/\//i', $value)) {
                return $value;
            }
            return url($value);
        };

        $productUrl = url()->current();
        $homeUrl = route('home');
        $siteUrl = url('/');
        $categoryUrl = !empty($product->cate_slug) ? route('allListProCate', ['danhmuc' => $product->cate_slug]) : null;
        $siteName = $cleanText(config('app.name', 'Website'));
        $productName = $cleanText($product->name ?? '');
        $productDescription = $cleanText($product->meta_description ?: strip_tags(languageName($product->description)));
        $categoryName = $cleanText(optional($product->cate)->name ?? '');
        $sku = $cleanText($product->sku ?? '');
        $allImages = array_values(array_filter(array_map($toAbsoluteUrl, (array) $img)));
        $primaryImage = $allImages[0] ?? null;

        $price = (float) ($product->price ?? 0);
        $discount = (float) ($product->discount ?? 0);
        $offerPrice = $discount > 0 && $discount < $price ? $discount : $price;
        if ($offerPrice <= 0) {
            $offerPrice = $discount > 0 ? $discount : $price;
        }

        $schemaGraph = [
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '#website',
                'url' => $siteUrl,
                'name' => $siteName,
                'inLanguage' => 'vi-VN',
            ],
            [
                '@type' => 'Organization',
                '@id' => $siteUrl . '#organization',
                'name' => $siteName,
                'url' => $siteUrl,
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $productUrl . '#breadcrumb',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Trang chủ',
                        'item' => $homeUrl,
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => $categoryName !== '' ? $categoryName : 'Tài liệu',
                        'item' => $categoryUrl ?: route('allProduct'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $productName,
                        'item' => $productUrl,
                    ],
                ],
            ],
            [
                '@type' => 'Product',
                '@id' => $productUrl . '#product',
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $productUrl,
                ],
                'name' => $productName,
                'description' => $productDescription,
                'url' => $productUrl,
                'sku' => $sku !== '' ? $sku : null,
                'category' => $categoryName !== '' ? $categoryName : null,
                'image' => $allImages,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $siteName,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $productUrl,
                    'priceCurrency' => 'VND',
                    'price' => $offerPrice > 0 ? number_format($offerPrice, 0, '.', '') : null,
                    'availability' => 'https://schema.org/InStock',
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'seller' => [
                        '@type' => 'Organization',
                        '@id' => $siteUrl . '#organization',
                    ],
                ],
            ],
        ];

        if (!empty($primaryImage)) {
            $schemaGraph[1]['logo'] = [
                '@type' => 'ImageObject',
                'url' => $primaryImage,
            ];
        }

        if (empty($schemaGraph[3]['image'])) {
            unset($schemaGraph[3]['image']);
        }
        if (empty($schemaGraph[3]['sku'])) {
            unset($schemaGraph[3]['sku']);
        }
        if (empty($schemaGraph[3]['category'])) {
            unset($schemaGraph[3]['category']);
        }
        if (empty($schemaGraph[3]['offers']['price'])) {
            unset($schemaGraph[3]['offers']);
        }
    @endphp
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $schemaGraph], $jsonFlags) !!}</script>
@endsection
@section('css')
    <link rel="stylesheet" href="/frontend/css/document-detail.css">
@endsection
@section('js')
    <script src="{{ asset('frontend/js/quickview-cart.js') }}"></script>
    <script>
        (function($) {
            "use strict";

            function showDetailNotify(message, type) {
                var toastId = "detail-mini-notify";
                var $oldToast = $("#" + toastId);
                if ($oldToast.length) {
                    $oldToast.remove();
                }

                var bgColor = type === "error" ? "#dc3545" : "#198754";
                var $toast = $(
                    '<div id="' +
                    toastId +
                    '" style="position:fixed;top:18px;right:18px;z-index:99999;background:' +
                    bgColor +
                    ';color:#fff;padding:8px 12px;border-radius:6px;font-size:13px;line-height:1.3;box-shadow:0 4px 14px rgba(0,0,0,.18);max-width:260px;">' +
                    message +
                    "</div>"
                );

                $("body").append($toast);
                setTimeout(function() {
                    $toast.fadeOut(180, function() {
                        $(this).remove();
                    });
                }, 1600);
            }

            function getCsrfToken() {
                return $('meta[name="csrf-token"]').attr("content") || "";
            }

            function getDetailPayload() {
                var $actions = $("#detail-product-actions");
                var $content = $actions.closest(".shop-details-content");
                var quantity = parseInt($content.find(".quantity__input").first().val(), 10);
                var safeQuantity = Number.isNaN(quantity) || quantity < 1 ? 1 : quantity;

                return {
                    product_id: Number($actions.data("product-id") || 0),
                    quantity: safeQuantity,
                };
            }

            function postDetailAddToCart(payload, done) {
                var addCartUrl = $("#detail-product-actions").data("add-cart-url");
                if (!addCartUrl || !payload.product_id) {
                    if (typeof done === "function") {
                        done({
                            status: 422
                        });
                    }
                    return;
                }

                $.ajax({
                    url: addCartUrl,
                    method: "POST",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    data: payload,
                    success: function(res) {
                        if (window.MiniCartDomUpdater && typeof window.MiniCartDomUpdater.setCart ===
                            "function") {
                            window.MiniCartDomUpdater.setCart(res);
                        }
                        if (typeof done === "function") {
                            done(null, res);
                        }
                    },
                    error: function(xhr) {
                        if (typeof done === "function") {
                            done(xhr);
                        }
                    },
                });
            }

            $(document).on("click", "#detail-add-cart-btn", function(e) {
                e.preventDefault();
                var payload = getDetailPayload();
                postDetailAddToCart(payload, function(err) {
                    if (err) {
                        showDetailNotify("Thêm vào giỏ hàng thất bại", "error");
                        return;
                    }
                    showDetailNotify("Đã thêm vào giỏ hàng", "success");
                });
            });

            $(document).on("click", "#detail-buy-now-btn", function(e) {
                e.preventDefault();
                var checkoutUrl = $("#detail-product-actions").data("checkout-url");
                var payload = getDetailPayload();

                postDetailAddToCart(payload, function(err) {
                    if (err) {
                        showDetailNotify("Mua ngay thất bại", "error");
                        return;
                    }
                    showDetailNotify("Đã thêm tài liệu, chuyển tới thanh toán", "success");
                    if (checkoutUrl) {
                        window.location.href = checkoutUrl;
                    }
                });
            });
        })(jQuery);

        $('[data-fancybox="doc-detail-gallery"]').fancybox({
            buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"],
            loop: true,
            protect: true,
            transitionEffect: "slide",
            animationEffect: "zoom-in-out",
        });
    </script>
@endsection
@section('content')
    @php
        $img = json_decode($product->images, true) ?? [];
        $img = is_array($img) ? array_values(array_filter($img)) : [];
        $productTags = json_decode($product->tags ?? '[]', true);
        $productTags = is_array($productTags) ? $productTags : [];
        $categoryUrl = route('allListProCate', ['danhmuc' => $product->cate_slug]);
    @endphp
    <div class="doc-detail-page">
    <!-- Start Breadcrumb Section -->
    <div class="doc-detail-breadcrumb breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ $categoryUrl }}">{{ languageName($product->cate->name) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->seo_title ?: $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb Section -->

    <!-- Start Document Detail -->
    <div class="doc-detail-top mt-40 mb-50">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="doc-detail-cover">
                        @if (count($img) > 0)
                            <div class="doc-detail-gallery-wrap">
                                <div class="row g-3 doc-detail-gallery">
                                    @foreach ($img as $index => $image)
                                        <div class="{{ count($img) === 1 ? 'col-12' : 'col-6 col-lg-6' }}">
                                            <a href="{{ $image }}" class="doc-detail-gallery__item"
                                                data-fancybox="doc-detail-gallery"
                                                data-caption="{{ $product->name }} — Ảnh {{ $index + 1 }}/{{ count($img) }}"
                                                aria-label="Xem ảnh {{ $index + 1 }}">
                                                <img src="{{ $image }}" alt="{{ $product->name }} — ảnh {{ $index + 1 }}"
                                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async">
                                                <span class="doc-detail-gallery__zoom" aria-hidden="true">
                                                    <i class="bi bi-zoom-in"></i>
                                                </span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="doc-detail-cover__placeholder">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Tài liệu số</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="doc-detail-main shop-details-content">
                        @php
                            $originalPrice = (float) $product->price;
                            $salePrice = (float) $product->discount;
                            $displayPrice = $salePrice > 0 && $salePrice < $originalPrice ? $salePrice : $originalPrice;
                        @endphp
                        <span class="doc-detail-badge">Tài liệu số</span>
                        @if ($product->cate)
                            <a href="{{ $categoryUrl }}" class="doc-detail-cate">{{ languageName($product->cate->name) }}</a>
                        @endif
                        <h1>{{ $product->name }}</h1>

                        @if (languageName($product->description))
                            <p class="doc-detail-excerpt">{{ languageName($product->description) }}</p>
                        @endif

                        <div class="doc-detail-price price-area">
                            @if ($displayPrice <= 0)
                                <p class="price doc-detail-price--free" id="detail-product-price">Miễn phí</p>
                            @elseif ($salePrice > 0 && $salePrice < $originalPrice)
                                <p class="price" id="detail-product-price">{{ number_format($salePrice) }}₫
                                    <del>{{ number_format($originalPrice) }}₫</del></p>
                            @else
                                <p class="price" id="detail-product-price">{{ number_format($originalPrice) }}₫</p>
                            @endif
                        </div>
                        <input type="hidden" name="quantity" class="quantity__input" value="1">
                        <div class="doc-detail-actions shop-details-btn" id="detail-product-actions" data-product-id="{{ $product->id }}"
                            data-add-cart-url="{{ route('add.to.cart') }}" data-checkout-url="{{ route('checkout') }}">
                            <a href="#" class="primary-btn1 hover-btn3" id="detail-add-cart-btn">Thêm vào giỏ hàng</a>
                            <a href="{{ route('checkout') }}" class="primary-btn1 style-3 hover-btn4"
                                id="detail-buy-now-btn">Mua ngay</a>
                        </div>
                        <ul class="doc-detail-meta product-info-list">
                            @if (!empty($product->sku))
                                <li><span>Mã tài liệu:</span> <span id="detail-product-sku">{{ $product->sku }}</span></li>
                            @endif
                            @if ($product->cate)
                                <li><span>Danh mục:</span> <a href="{{ $categoryUrl }}">{{ languageName($product->cate->name) }}</a></li>
                            @endif
                            <li><span>Nguồn:</span> <span>rapgiay.com</span></li>
                            @if (count($productTags) > 0)
                                <li><span>Thẻ:</span>
                                    <span class="doc-detail-tags">
                                        @foreach ($productTags as $tag)
                                            @php
                                                $tagLabel = is_array($tag) ? ($tag['label'] ?? $tag['name'] ?? '') : $tag;
                                            @endphp
                                            @if ($tagLabel !== '')
                                                <span class="doc-detail-tag">{{ $tagLabel }}</span>
                                            @endif
                                        @endforeach
                                    </span>
                                </li>
                            @endif
                        </ul>
                        <div class="doc-detail-benefits">
                            <div class="doc-detail-benefit">
                                <i class="bi bi-cloud-download"></i>
                                <span>Nhận link tải tài liệu ngay sau khi thanh toán thành công.</span>
                            </div>
                            <div class="doc-detail-benefit">
                                <i class="bi bi-shield-check"></i>
                                <span>Tài liệu số chính hãng, cập nhật theo chương trình mới nhất.</span>
                            </div>
                            <div class="doc-detail-benefit">
                                <i class="bi bi-headset"></i>
                                <span>Hỗ trợ qua hotline {{ $setting->hotline ?? $setting->phone1 }} nếu gặp sự cố tải xuống.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Document Detail -->

    <!-- Start Document Description -->
    <div class="doc-detail-description shop-details-description mb-60" id="doc-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="shop-details-description-nav mb-20">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <button class="nav-link active" id="nav-description-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-description" type="button" role="tab"
                                    aria-controls="nav-description" aria-selected="true">Mô tả tài liệu</button>
                            </div>
                        </nav>
                    </div>
                    <div class="shop-details-description-tab">
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-description" role="tabpanel"
                                aria-labelledby="nav-description-tab">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="content-post">
                                            {!! languageName($product->content) !!}
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Document Description -->
    <div class="doc-detail-related newest-product-section mb-80">
        <div class="container">
            <div class="section-title2 style-2">
                <h3>Tài liệu tương tự</h3>
                <div class="slider-btn">
                    <div class="prev-btn">
                        <i class="bi bi-chevron-left"></i>
                    </div>
                    <div class="next-btn">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="swiper newest-slider">
                        <div class="swiper-wrapper">
							@foreach ($productlq as $item)
							<div class="swiper-slide">
                                @include('layouts.product.item',['pro'=>$item])
                            </div>
							@endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
