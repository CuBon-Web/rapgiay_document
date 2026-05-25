@php
    $img = json_decode($pro->images, true) ?? [];
    $coverImg = $img[0] ?? null;
    $productUrl = route('detailProduct', [
        'cate' => $pro->cate_slug,
        'type' => $pro->type_slug ? $pro->type_slug : 'loai',
        'id' => $pro->slug,
    ]);

    $originalPrice = (float) $pro->price;
    $salePrice = (float) $pro->discount;
    $displayPrice = $salePrice > 0 ? $salePrice : $originalPrice;
    $hasDiscount = $originalPrice > 0 && $salePrice > 0 && $salePrice < $originalPrice;

    $discountPercent = 0;
    if ($hasDiscount) {
        $discountPercent = 100 - ceil(($salePrice / $originalPrice) * 100);
    }

    $shortDesc = '';
    if (!empty($pro->description)) {
        $descData = is_string($pro->description) ? json_decode($pro->description, true) : $pro->description;
        if (is_array($descData) && !empty($descData[0]['content'])) {
            $shortDesc = \Illuminate\Support\Str::limit(strip_tags($descData[0]['content']), 55);
        }
    }
@endphp

<article class="doc-card">
    <a href="{{ $productUrl }}" class="doc-card__media" aria-label="{{ $pro->name }}">
        @if ($coverImg)
            <img src="{{ $coverImg }}" alt="{{ $pro->name }}" loading="lazy" decoding="async">
        @else
            <div class="doc-card__placeholder" aria-hidden="true">
                <i class="bi bi-file-earmark-text"></i>
            </div>
        @endif
        @if ($discountPercent > 0)
            <span class="doc-card__badge">-{{ $discountPercent }}%</span>
        @endif
    </a>

    <div class="doc-card__body">
        @if ($pro->cate != null)
            <a href="{{ route('allListProCate', ['danhmuc' => $pro->cate->slug]) }}" class="doc-card__cate">
                {{ languageName($pro->cate->name) }}
            </a>
        @endif

        <h6 class="doc-card__title">
            <a href="{{ $productUrl }}" title="{{ $pro->name }}">{{ $pro->name }}</a>
        </h6>

        @if ($shortDesc !== '')
            <p class="doc-card__excerpt">{{ $shortDesc }}</p>
        @endif

        <div class="doc-card__footer">
            @if ($displayPrice > 0)
                <div class="doc-card__price">
                    <strong>{{ number_format($displayPrice) }}₫</strong>
                    @if ($hasDiscount)
                        <del>{{ number_format($originalPrice) }}₫</del>
                    @endif
                </div>
            @else
                <div class="doc-card__price doc-card__price--free">
                    <strong>Miễn phí</strong>
                </div>
            @endif

            <div class="doc-card__actions">
                <a
                    href="{{$productUrl}}"
                    class="doc-card__action"
                   
                    title="Xem nhanh"
                    aria-label="Xem nhanh"
                >
                    <i class="bi bi-eye"></i>
                </a>
                <a
                    href="#"
                    class="doc-card__action doc-card__action--cart quick-add-cart-btn"
                    data-product-id="{{ $pro->id }}"
                    data-has-variant="0"
                    data-fallback-price="{{ (int) $displayPrice }}"
                    title="Thêm giỏ hàng"
                    aria-label="Thêm giỏ hàng"
                >
                    <i class="bi bi-cart-plus"></i>
                </a>
            </div>
        </div>
    </div>
</article>

@once
<style>
.doc-card {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    gap: 0;
    height: 100%;
    min-height: 76px;
    background: #fff;
    border: 1px solid #e8ecf1;
    border-radius: 8px;
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.doc-card:hover {
    border-color: #c5d4e8;
    box-shadow: 0 4px 12px rgba(19, 20, 26, 0.07);
}
.doc-card__media {
    position: relative;
    flex: 0 0 68px;
    width: 68px;
    display: block;
    background: linear-gradient(145deg, #f4f7fb 0%, #e9eef5 100%);
    overflow: hidden;
    border-right: 1px solid #eef2f6;
}
.doc-card__media img {
    width: 100%;
    height: 100%;
    min-height: 76px;
    object-fit: cover;
}
.doc-card__placeholder {
    width: 100%;
    height: 100%;
    min-height: 76px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7b8a9a;
    font-size: 1.35rem;
}
.doc-card__badge {
    position: absolute;
    top: 4px;
    left: 4px;
    z-index: 2;
    padding: 1px 5px;
    border-radius: 4px;
    background: #e53935;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    line-height: 1.3;
}
.doc-card__body {
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 4px;
    padding: 8px 10px;
    min-width: 0;
    min-height: 0;
}
.doc-card__cate {
    display: inline-block;
    max-width: 100%;
    font-size: 11px;
    font-weight: 600;
    color: #4b6b8a;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.doc-card__cate:hover {
    color: #1f4f78;
}
.doc-card__title {
    margin: 0;
    font-size: 13px;
    line-height: 1.3;
    font-weight: 600;
}
.doc-card__title a {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    color: var(--title-color, #13141a);
}
.doc-card__title a:hover {
    color: #1f4f78;
}
.doc-card__excerpt {
    margin: 0;
    font-size: 11px;
    line-height: 1.35;
    color: #6b7280;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.doc-card__footer {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.doc-card__price {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 4px;
    min-width: 0;
}
.doc-card__price strong {
    font-size: 13px;
    font-weight: 700;
    color: #c62828;
    line-height: 1.2;
}
.doc-card__price del {
    font-size: 11px;
    color: #9ca3af;
}
.doc-card__price--free strong {
    color: #2e7d32;
}
.doc-card__actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.doc-card__action {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #dbe3ec;
    background: #f8fafc;
    color: #334155;
    font-size: 14px;
    line-height: 1;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
.doc-card__action:hover {
    background: #eef4fa;
    border-color: #b7c9dc;
    color: #1f4f78;
}
.doc-card__action--cart {
    background: #e53935;
    border-color: #e53935;
    color: #fff;
}
.doc-card__action--cart:hover {
    background: #163a5c;
    border-color: #163a5c;
    color: #fff;
}
</style>
@endonce
