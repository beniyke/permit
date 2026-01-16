<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Gate management service for custom authorization callbacks.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services;

use App\Models\User;

class GateManagerService
{
    /**
     * Registered gates (authorization callbacks).
     */
    private array $gates = [];

    /**
     * Before callbacks that run before any gate check.
     */
    private array $before = [];

    /**
     * After callbacks that run after any gate check.
     */
    private array $after = [];

    /**
     * Define a gate (authorization callback).
     */
    public function define(string $ability, callable $callback): self
    {
        $this->gates[$ability] = $callback;

        return $this;
    }

    public function before(callable $callback): self
    {
        $this->before[] = $callback;

        return $this;
    }

    public function after(callable $callback): self
    {
        $this->after[] = $callback;

        return $this;
    }

    /**
     * Check if a gate authorizes the ability.
     * Returns null if no gate is defined, true/false otherwise.
     */
    public function check(string $ability, User $user, mixed $resource = null): ?bool
    {
        // Run before callbacks
        foreach ($this->before as $callback) {
            $result = $callback($user, $ability, $resource);
            if ($result !== null) {
                return $result;
            }
        }

        // Check if gate is defined
        if (!isset($this->gates[$ability])) {
            return null;
        }

        $result = call_user_func($this->gates[$ability], $user, $resource);

        // Run after callbacks
        foreach ($this->after as $callback) {
            $afterResult = $callback($user, $ability, $result, $resource);
            if ($afterResult !== null) {
                $result = $afterResult;
            }
        }

        return (bool) $result;
    }

    /**
     * Check if a gate is defined for an ability.
     */
    public function has(string $ability): bool
    {
        return isset($this->gates[$ability]);
    }

    public function abilities(): array
    {
        return array_keys($this->gates);
    }

    /**
     * Remove a gate definition.
     */
    public function forget(string $ability): void
    {
        unset($this->gates[$ability]);
    }

    /**
     * Define a resource-based gate for a model.
     * Creates gates for: view, create, update, delete, restore, forceDelete
     */
    public function resource(string $name, string $policyClass): self
    {
        $abilities = [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'forceDelete',
        ];

        foreach ($abilities as $abilityMethod) {
            $ability = "{$name}.{$abilityMethod}";

            $this->define($ability, function (User $user, $resource = null) use ($policyClass, $abilityMethod) {
                $policy = new $policyClass();

                if (!method_exists($policy, $abilityMethod)) {
                    return false;
                }

                return $policy->$abilityMethod($user, $resource);
            });
        }

        return $this;
    }

    public function clear(): void
    {
        $this->gates = [];
        $this->before = [];
        $this->after = [];
    }
}
