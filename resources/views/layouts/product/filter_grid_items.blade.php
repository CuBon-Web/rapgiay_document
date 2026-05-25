@if (count($product) > 0)
    @foreach ($product as $item)
        @php
            $itemTags = [];
            if (!empty($item->tags)) {
                $decodedTags = json_decode($item->tags, true);
                if (is_array($decodedTags)) {
                    $itemTags = $decodedTags;
                }
            }
            $itemPriceForFilter = (float) ($item->discount > 0 ? $item->discount : $item->price);
        @endphp
        <div
            class="col-xl-4 col-lg-6 col-md-6 col-12 item mb-2"
            data-tags="{{ implode(',', $itemTags) }}"
            data-name="{{ strtolower(trim((string) languageName($item->name))) }}"
            data-price="{{ $itemPriceForFilter }}"
            data-product-id="{{ $item->id }}"
        >
            @include('layouts.product.item',['pro'=>$item])
        </div>
    @endforeach
@else
    <div class="col-12">
        <div class="alert alert-warning">
            Không có sản phẩm nào.
        </div>
    </div>
@endif
