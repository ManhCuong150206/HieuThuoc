<?php

namespace App\Queries\Admin;

use App\Enums\AdminResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class ResourceQuery
{
    public function results(AdminResource $resource, ?string $search = null, ?int $page = null): array
    {
        $query = $resource->query($search)->orderByDesc('id');
        if ($resource === AdminResource::Promotions) {
            $query->withCount('products');
        }

        $records = $query->paginate(15, page: $page);
        if ($page !== null && $records->currentPage() > $records->lastPage()) {
            $records = $query->paginate(15, page: $records->lastPage(), total: $records->total());
        }

        return [
            'resource' => $resource->value,
            'search' => $search,
            'records' => $records,
            'categories' => $this->categories($resource),
        ];
    }

    public function categories(AdminResource $resource): Collection
    {
        return $resource === AdminResource::Products ? Category::orderBy('name')->get() : collect();
    }

    public function promotionProducts(AdminResource $resource): Collection
    {
        return $resource === AdminResource::Promotions ? Product::orderBy('name')->get(['id', 'name', 'is_active']) : collect();
    }
}
