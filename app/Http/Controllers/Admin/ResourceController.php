<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveResourceRequest;
use App\Models\Promotion;
use App\Queries\Admin\ResourceQuery;
use App\Services\Admin\ResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResourceController extends Controller
{
    private const SEARCH_RULES = ['nullable', 'string', 'max:100'];

    public function __construct(private ResourceService $resources, private ResourceQuery $listing) {}

    public function index(Request $request, string $resource): View
    {
        $definition = AdminResource::from($resource);
        $class = $definition->model();
        $data = $this->listContext($request, includePage: false);

        return view('pages.admin.resources', [
            ...$this->resourceResults($resource, $data['q'] ?? null),
            'title' => $definition->label(),
            'draft' => new $class,
            'promotionProducts' => $this->listing->promotionProducts($definition),
        ]);
    }

    public function form(Request $request, string $resource, ?int $record = null): View|RedirectResponse|JsonResponse
    {
        $definition = AdminResource::from($resource);
        $class = $definition->model();

        if ($resource === 'products' && $record === null) {
            return redirect()->route('admin.resources.index', ['resource' => 'products', 'them' => 1]);
        }

        $model = $record ? $class::findOrFail($record) : new $class;

        $form = [
            'resource' => $resource, 'title' => $definition->label(),
            'record' => $model,
            'categories' => $this->listing->categories($definition),
            'promotionProducts' => $this->listing->promotionProducts($definition),
            'assignedProductIds' => $model instanceof Promotion && $model->exists
                ? $model->products()->pluck('products.id')->all()
                : [],
        ];

        if ($model->exists && $request->expectsJson()) {
            $context = $this->listContext($request);

            return response()->json([
                'html' => view('pages.admin.resource-fields', $form)->render(),
                'url' => route('admin.resources.update', ['resource' => $resource, 'record' => $model->id, ...$context]),
            ])->header('Cache-Control', 'no-store');
        }

        return view('pages.admin.resource-form', $form);
    }

    public function store(SaveResourceRequest $request, string $resource): RedirectResponse|JsonResponse
    {
        $definition = AdminResource::from($resource);
        $data = $request->validated();
        if ($resource === 'products') {
            try {
                $imagePath = $this->resources->saveProduct($data);
            } catch (ValidationException $exception) {
                throw $request->forProductCreation($exception);
            }
            $message = $imagePath ? 'Đã tạo sản phẩm và lưu ảnh.' : 'Đã tạo sản phẩm. Chưa có tệp ảnh tải lên.';

            return $request->expectsJson()
                ? $this->resourceResponse($resource, $message, imagePath: $imagePath, status: 201)
                : $this->productSaved($imagePath, $message);
        }

        if ($resource === 'posts') {
            $data['author_account_id'] = $request->user()->id;
        }
        $this->resources->create($definition, $data);

        $message = 'Đã tạo thành công.';

        return $request->expectsJson()
            ? $this->resourceResponse($resource, $message, status: 201)
            : redirect()->route('admin.resources.index', $resource)->with('status', $message);
    }

    public function update(SaveResourceRequest $request, string $resource, int $record): RedirectResponse|JsonResponse
    {
        $definition = AdminResource::from($resource);
        $data = $request->validated();
        $context = $request->expectsJson() ? $this->listContext($request) : null;
        $message = 'Đã lưu thay đổi.';
        if ($resource === 'products') {
            $imagePath = $this->resources->saveProduct($data, $record);
            $message = $imagePath ? 'Đã cập nhật sản phẩm và lưu ảnh.' : $message;

            return $context !== null
                ? $this->resourceResponse($resource, $message, $context, $imagePath)
                : $this->productSaved($imagePath, $message);
        }

        $this->resources->update($definition, $record, $data);

        return $context !== null
            ? $this->resourceResponse($resource, $message, $context)
            : redirect()->route('admin.resources.index', $resource)->with('status', $message);
    }

    public function destroy(string $resource, int $record): RedirectResponse
    {
        $this->resources->destroy(AdminResource::from($resource), $record);

        return back()->with('status', $resource === 'products'
            ? 'Đã ẩn sản phẩm.'
            : 'Đã cập nhật. Sản phẩm/ưu đãi được ngừng sử dụng; bài viết được chuyển về bản nháp.');
    }

    private function resourceResults(string $resource, ?string $search = null, ?int $page = null): array
    {
        $results = $this->listing->results(AdminResource::from($resource), $search, $page);
        $results['records']->withPath(route('admin.resources.index', $resource))->appends(['q' => $search]);

        return $results;
    }

    private function listContext(Request $request, bool $includePage = true): array
    {
        return validator($request->query(), [
            'q' => self::SEARCH_RULES,
            ...($includePage ? ['page' => ['nullable', 'integer', 'min:1']] : []),
        ])->validate();
    }

    private function resourceResponse(string $resource, string $message, array $context = [], ?string $imagePath = null, int $status = 200): JsonResponse
    {
        $search = $context['q'] ?? null;
        $results = $this->resourceResults($resource, $search, (int) ($context['page'] ?? 1));
        $page = $results['records']->currentPage();

        return response()->json([
            'message' => $message,
            'html' => view('pages.admin.resource-results', $results)->render(),
            'url' => route('admin.resources.index', ['resource' => $resource, 'q' => $search, 'page' => $page > 1 ? $page : null]),
            'image_url' => $imagePath === null ? null : asset($imagePath),
        ], $status);
    }

    private function productSaved(?string $imagePath, string $message): RedirectResponse
    {
        $response = redirect()->route('admin.resources.index', 'products')->with('status', $message);
        if ($imagePath !== null) {
            $response->with('status_action', ['url' => asset($imagePath), 'label' => 'Xem ảnh vừa lưu']);
        }

        return $response;
    }
}
