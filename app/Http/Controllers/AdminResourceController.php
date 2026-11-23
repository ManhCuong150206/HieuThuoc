<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminResourceController extends Controller
{
    public const RESOURCES = [
        'products' => [Product::class, 'Sản phẩm & tồn kho'],
        'categories' => [Category::class, 'Danh mục'],
        'promotions' => [Promotion::class, 'Khuyến mãi'],
        'posts' => [Post::class, 'Bài viết'],
    ];

    private function definition(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);

        return self::RESOURCES[$resource];
    }

    private function data(Request $request, string $resource, ?Model $model = null): array
    {
        $rules = match ($resource) {
            'products' => [
                'category_id' => ['required', 'integer', 'exists:categories,id'], 'name' => ['required', 'string', 'max:200'],
                'price' => ['required', 'integer', 'min:0', 'max:1000000000'], 'quantity' => ['required', 'integer', 'between:0,1000000'],
                'brand' => ['nullable', 'string', 'max:100'], 'composition' => ['nullable', 'string', 'max:500'],
                'used_for' => ['nullable', 'string', 'max:500'], 'how_to_use' => ['nullable', 'string', 'max:500'],
                'contraindications' => ['nullable', 'string', 'max:500'], 'side_effects' => ['nullable', 'string', 'max:500'],
                'image_url' => ['nullable', 'url:http,https', 'max:500'], 'expiry_date' => ['nullable', 'date_format:Y-m-d'],
                'is_active' => ['required', 'boolean'],
            ],
            'categories' => ['name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($model?->id)]],
            'promotions' => [
                'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:500'],
                'discount_percent' => ['required', 'integer', 'between:0,100'],
                'starts_at' => ['required', 'date_format:Y-m-d'], 'ends_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_at'],
                'is_active' => ['required', 'boolean'],
            ],
            'posts' => [
                'title' => ['required', 'string', 'max:200'], 'content' => ['required', 'string', 'max:500'],
                'cover_url' => ['nullable', 'string', 'max:500', Rule::when(
                    ! in_array($request->input('cover_url'), array_keys(config('health_articles')), true),
                    'url:http,https',
                )],
                'status' => ['required', Rule::in(['DRAFT', 'PUBLISHED'])],
                'published_at' => ['required_if:status,PUBLISHED', 'nullable', 'date_format:Y-m-d'],
            ],
        };

        return $request->validate($rules);
    }

    public function index(Request $request, string $resource): View
    {
        [$class, $title] = $this->definition($resource);
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $column = $resource === 'posts' ? 'title' : 'name';

        return view('pages.admin.resources', [
            'resource' => $resource, 'title' => $title,
            'records' => $class::when($data['q'] ?? null, fn ($q, $text) => $q->where($column, 'like', '%'.$text.'%'))->orderByDesc('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function form(string $resource, ?int $record = null): View
    {
        [$class, $title] = $this->definition($resource);

        return view('pages.admin.resource-form', [
            'resource' => $resource, 'title' => $title,
            'record' => $record ? $class::findOrFail($record) : new $class,
            'categories' => $resource === 'products' ? Category::orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        [$class] = $this->definition($resource);
        $data = $this->data($request, $resource);
        if ($resource === 'posts') {
            $data['author_account_id'] = $request->user()->id;
        }
        $class::create($data);

        return redirect()->route('admin.resources.index', $resource)->with('status', 'Đã tạo thành công.');
    }

    public function update(Request $request, string $resource, int $record): RedirectResponse
    {
        [$class] = $this->definition($resource);
        DB::transaction(function () use ($request, $resource, $record, $class): void {
            $model = $class::lockForUpdate()->findOrFail($record);
            $model->update($this->data($request, $resource, $model));
        }, 3);

        return redirect()->route('admin.resources.index', $resource)->with('status', 'Đã lưu thay đổi.');
    }

    public function destroy(string $resource, int $record): RedirectResponse
    {
        [$class] = $this->definition($resource);
        DB::transaction(function () use ($class, $record, $resource): void {
            $model = $class::lockForUpdate()->findOrFail($record);
            if ($resource === 'products' || $resource === 'promotions') {
                $model->update(['is_active' => false]);
            } elseif ($resource === 'posts') {
                $model->update(['status' => 'DRAFT']);
            } else {
                if ($model->products()->exists()) {
                    throw ValidationException::withMessages(['category' => 'Danh mục đang có sản phẩm, không thể xóa.']);
                }
                $model->delete();
            }
        });

        return back()->with('status', 'Đã cập nhật. Sản phẩm/ưu đãi được ngừng sử dụng; bài viết được chuyển về bản nháp.');
    }
}
