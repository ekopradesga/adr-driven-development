<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * AbstractCrudService
 *
 * Base class providing reusable CRUD operations for service classes.
 * Child services extend this class and customize behavior by overriding
 * protected methods (applyFilters, applyDefaultOrdering, applyDefaultRelationships).
 *
 * Business logic remains in child services.
 * This class provides only generic data access patterns.
 */
abstract class AbstractCrudService
{
    /**
     * The fully-qualified model class name.
     * Child services must set this property.
     */
    protected string $modelClass;

    /**
     * Paginated list with optional filters.
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query();

        $query = $this->applyDefaultRelationships($query);
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyDefaultOrdering($query);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Find a model by ID with optional relationships.
     */
    public function find(int $id, array $with = []): ?Model
    {
        return $this->modelClass::with($with)->find($id);
    }

    /**
     * Create a new model from array data.
     */
    public function create(array $data): Model
    {
        return $this->modelClass::create($data);
    }

    /**
     * Update an existing model from array data.
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * Delete a model.
     */
    public function delete(Model $model): void
    {
        $model->delete();
    }

    // -------------------------------------------------------------------------
    // Protected Customization Hooks
    // -------------------------------------------------------------------------

    /**
     * Returns the base query builder for the model.
     * Override to customize base query (e.g., add global scopes).
     */
    protected function query(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * Apply default relationships to eager-load.
     * Override in child classes to eager-load common relationships.
     */
    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Apply filters to the query based on the filters array.
     * Override in child classes to implement domain-specific filtering.
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query;
    }

    /**
     * Apply default ordering to the query.
     * Override in child classes to implement domain-specific ordering.
     */
    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query;
    }
}
