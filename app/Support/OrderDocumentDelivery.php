<?php

namespace App\Support;

use App\models\Bill\Bill;
use App\models\Bill\BillDetail;
use App\models\product\Product;

class OrderDocumentDelivery
{
    public static function itemsForBill(Bill $bill)
    {
        if ((int) $bill->statu !== 1) {
            return [];
        }

        $details = BillDetail::where('code_bill', $bill->code_bill)->get();
        $items = [];

        foreach ($details as $detail) {
            $product = Product::find($detail->code_product);
            if (!$product) {
                continue;
            }

            $driveUrl = trim((string) ($product->origin ?? ''));
            if (!GoogleDriveLink::isValidDriveUrl($driveUrl)) {
                continue;
            }

            if (!self::productHasDownloadableFiles($driveUrl)) {
                continue;
            }

            $baseName = $detail->name ?: $product->name;
            $items[] = [
                'name' => $baseName,
                'product_id' => (int) $product->id,
                'download_url' => route('order.downloadDocument', [
                    'billCode' => $bill->code_bill,
                    'productId' => $product->id,
                ]),
            ];
        }

        return $items;
    }

    public static function productHasDownloadableFiles($driveUrl)
    {
        $driveUrl = trim((string) $driveUrl);
        $folderId = GoogleDriveLink::extractFolderId($driveUrl);

        if ($folderId && GoogleDriveLink::extractFileId($driveUrl) === null) {
            return GoogleDriveLink::listPublicFolderFiles($folderId) !== [];
        }

        return GoogleDriveLink::resolveDownloadTarget($driveUrl) !== null;
    }
}
