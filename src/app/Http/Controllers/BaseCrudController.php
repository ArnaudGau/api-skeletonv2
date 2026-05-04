<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseCrudController extends Controller
{
    use AuthorizesRequests;

    abstract protected function modelClass(): string;

    abstract protected function resourceClass(): string;

    abstract protected function queryClass(Request $request);

    abstract protected function storeRequestClass(): string;

    abstract protected function updateRequestClass(): string;

    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $this->buildContext($context));
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, $this->buildContext($context));
    }

    protected function buildContext(array $context = []): array
    {
        // Construit un contexte de log commun à tous les contrôleurs
        return array_merge([
            'controller' => static::class,
            'model' => method_exists($this, 'modelClass') ? $this->modelClass() : 'N/A',
        ], $context);
    }

    /**
     * List all items
     *
     * This method authorizes the request, retrieves all items from the database,
     */
    public function index(Request $request)
    {
        try {
            Gate::authorize('viewAny', $this->modelClass());

            $this->logInfo('Call index function', [
                'request' => $request->all() ?? 'N/A',
            ]);

            $query = $this->queryClass($request);
            $items = $query->paginate($request->integer('per_page', config('app.paginate')));

            return $this->resourceClass()::collection($items);
        } catch (ValidationException $e) {
            $this->logError('Validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            throw $e;
        } catch (RuntimeException $e) {
            $this->logError('Call index function', [
                'request' => $request->all() ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('common.error') . ': ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show a single item by ID
     *
     * This method authorizes the request, retrieves the item by ID,
     */
    public function show(int $id)
    {
        try {
            $modelClass = $this->modelClass();
            $model = $modelClass::findOrFail($id);

            Gate::authorize('view', $model);

            $this->logInfo('Call show function', [
                'id' => $id ?? 'N/A',
            ]);

            return $this->resourceClass()::make($model);
        } catch (RuntimeException $e) {
            $this->logError('Call show function', [
                'id' => $id ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('common.error') . ': ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a new item
     *
     * This method authorizes the request, validates the data using the store request class,
     */
    public function store(Request $request)
    {
        try {
            Gate::authorize('create', $this->modelClass());

            $data = app($this->storeRequestClass())->validated();
            $model = $this->modelClass()::create($data);

            $this->logInfo('Call store function', [
                'request' => $request->all() ?? 'N/A',
            ]);

            return $this->resourceClass()::make($model);
        } catch (ValidationException $e) {
            $this->logError('Validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            throw $e;
        } catch (RuntimeException $e) {
            $this->logError('Call store function', [
                'request' => $request->all() ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('common.error') . ': ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing item
     *
     * This method authorizes the request, retrieves the item by ID,
     */
    public function update(Request $request, int $id)
    {
        try {
            $modelClass = $this->modelClass();
            $model = $modelClass::findOrFail($id);
            Gate::authorize('update', $this->modelClass());

            $data = app($this->updateRequestClass())->validated();
            $model->update($data);

            $this->logInfo('Call update function', [
                'request' => $request->all() ?? 'N/A',
                'id' => $id ?? 'N/A',
            ]);

            return $this->resourceClass()::make($model);
        } catch (ValidationException $e) {
            $this->logError('Validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            throw $e;
        } catch (RuntimeException $e) {
            $this->logError('Call update function', [
                'request' => $request->all() ?? 'N/A',
                'id' => $id ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('common.error') . ': ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete an item
     *
     * This method authorizes the request, retrieves the item by ID,
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $modelClass = $this->modelClass();
            $model = $modelClass::findOrFail($id);

            Gate::authorize('delete', $model);

            $this->logInfo('Call destroy function', [
                'id' => $id ?? 'N/A',
            ]);

            $model->delete();

            return response()->json([
                'message' => __('common.deleted'),
            ], Response::HTTP_OK);
        } catch (RuntimeException $e) {
            $this->logError('Call destroy function', [
                'id' => $id ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('common.error') . ': ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
