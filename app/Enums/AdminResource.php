<?php

namespace App\Enums;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

enum AdminResource: string
{
    case Products = 'products';
    case Categories = 'categories';
    case Promotions = 'promotions';
    case Posts = 'posts';

    public function label(): string
    {
        return match ($this) {
            self::Products => 'Sản phẩm & tồn kho',
            self::Categories => 'Danh mục',
            self::Promotions => 'Khuyến mãi',
            self::Posts => 'Bài viết',
        };
    }

    /** @return class-string<Model> */
    public function model(): string
    {
        return match ($this) {
            self::Products => Product::class,
            self::Categories => Category::class,
            self::Promotions => Promotion::class,
            self::Posts => Post::class,
        };
    }

    public function query(?string $search = null): Builder
    {
        $model = $this->model();
        $column = $this === self::Posts ? 'title' : 'name';

        return $model::query()->when($search !== null, fn (Builder $query) => $query->where($column, 'like', '%'.$search.'%'));
    }
}
