<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

use LWP\Components\Properties\Enums\HookNamesEnum;

trait HooksTrait
{
    // Hook callbacks storage.
    private array $hooks = [];
    // Throttled hooks storage.
    private array $throttle_data = [];


    // Generic hook registrator.

    public function onHook(
        HookNamesEnum $hook_name,
        \Closure $callback,
        bool $once = false,
        ?string $identifier = null,
        int $priority = 0
    ): int|string {

        $data = [
            'callback' => $callback,
            'once' => $once,
            'priority' => $priority,
        ];

        if ($identifier === null || $identifier === '') {

            $this->hooks[$hook_name->name][] = $data;
            return array_key_last($this->hooks[$hook_name->name]);

        } else {

            $this->hooks[$hook_name->name][$identifier] = $data;
            return $identifier;
        }
    }


    // Registers an "on before set value" hook.

    public function onBeforeSetValue(
        \Closure $callback,
        bool $once = false,
        string $identifier = null,
        int $priority = 0
    ): int|string {

        return $this->onHook(
            HookNamesEnum::BEFORE_SET_VALUE,
            $callback,
            $once,
            $identifier,
            $priority
        );
    }


    // Registers an "on after set value" hook.

    public function onAfterSetValue(
        \Closure $callback,
        bool $once = false,
        string $identifier = null,
        int $priority = 0
    ): int|string {

        return $this->onHook(
            HookNamesEnum::AFTER_SET_VALUE,
            $callback,
            $once,
            $identifier,
            $priority
        );
    }


    // Registers an "on after get value" hook.

    public function onAfterGetValue(
        \Closure $callback,
        bool $once = false,
        string $identifier = null,
        int $priority = 0
    ): int|string {

        return $this->onHook(
            HookNamesEnum::AFTER_GET_VALUE,
            $callback,
            $once,
            $identifier,
            $priority
        );
    }


    // Registers an on "unset value" hook.

    public function onUnsetValue(
        \Closure $callback,
        bool $once = false,
        string $identifier = null,
        int $priority = 0
    ): int|string {

        return $this->onHook(
            HookNamesEnum::UNSET_VALUE,
            $callback,
            $once,
            $identifier,
            $priority
        );
    }


    // Suppress calling callbacks for the given hook.

    public function throttleHooks(
        HookNamesEnum $hook_name,
        ?string $identifier = null
    ): void {

        if ($identifier === null) {
            $this->throttle_data[$hook_name->name] = true;
        } elseif (!isset($this->throttle_data[$hook_name->name][$identifier])) {
            $this->throttle_data[$hook_name->name][$identifier] = $identifier;
        }
    }


    // Unsuppress calling callbacks for the given hook.

    public function unthrottleHooks(
        HookNamesEnum $hook_name,
        ?string $identifier = null
    ): void {

        if ($identifier === null) {
            unset($this->throttle_data[$hook_name->name]);
        } elseif (isset($this->throttle_data[$hook_name->name][$identifier])) {
            unset($this->throttle_data[$hook_name->name][$identifier]);
        }
    }


    // Checks whether callbacks are throttled for the given hook.

    public function isThrottled(
        HookNamesEnum $hook_name,
        ?string $identifier = null
    ): bool {

        $is_anything = (
            isset($this->throttle_data[$hook_name->name])
            && $this->throttle_data[$hook_name->name] === true
        );

        return ($identifier === null)
            ? $is_anything
            : ($is_anything || isset($this->throttle_data[$hook_name->name][$identifier]));
    }


    // Generic hooks firing method.

    protected function fireHooks(
        HookNamesEnum $hook_name,
        array $params,
        ?string $identifier = null
    ): void {

        if (
            !$this->isThrottled($hook_name, $identifier)
            && !empty($this->hooks[$hook_name->name])
        ) {

            $callbacks_ordered = $this->hooks[$hook_name->name];

            // Sort by priority.
            usort($callbacks_ordered, fn (array $a, array $b): int
                => $a['priority'] <=> $b['priority']);

            foreach ($callbacks_ordered as $index => $callback_data) {

                ($callback_data['callback'])(...$params);

                if ($callback_data['once']) {
                    unset($this->hooks[$hook_name->name][$index]);
                }
            }
        }
    }


    // Generic hooks firing method with return value.

    protected function fireHooksWithReturnValue(
        HookNamesEnum $hook_name,
        mixed $return_value,
        array $params,
        ?string $identifier = null
    ): mixed {

        if (
            // Unless throttling is enabled.
            !$this->isThrottled($hook_name, $identifier)
            // Non-empty storage.
            && !empty($this->hooks[$hook_name->name])
        ) {

            $callbacks_ordered = $this->hooks[$hook_name->name];

            // Sort by priority.
            usort($callbacks_ordered, fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

            foreach ($callbacks_ordered as $index => $callback_data) {

                $return_value = ($callback_data['callback'])(...$params);
                // Updates value for the next callback
                // Value element must always be the first one
                $params[0] = $return_value;

                if ($callback_data['once']) {
                    unset($this->hooks[$hook_name->name][$index]);
                }
            }
        }

        return $return_value;
    }


    // Fires all "on before set value" hooks.

    protected function fireOnBeforeSetValueCallbacks(
        mixed $value,
        array $extra_params = [],
        ?string $identifier = null
    ): mixed {

        return $this->fireHooksWithReturnValue(
            HookNamesEnum::BEFORE_SET_VALUE,
            $value,
            [
                $value,
                $this,
                ...$extra_params,
            ],
            $identifier
        );
    }


    // Fires all "on after set value" hooks.

    protected function fireOnAfterSetValueCallbacks(
        mixed $value,
        array $extra_params = [],
        ?string $identifier = null
    ): mixed {

        return $this->fireHooksWithReturnValue(
            HookNamesEnum::AFTER_SET_VALUE,
            $value,
            [
                $value,
                $this,
                ...$extra_params,
            ],
            $identifier
        );
    }


    // Fires all "on before get value" hooks.

    public function fireOnBeforeGetValueCallbacks(
        array $extra_params = [],
        ?string $identifier = null
    ): void {

        $this->fireHooks(HookNamesEnum::BEFORE_GET_VALUE, [
            $this,
            ...$extra_params,
        ]);
    }


    // Fires all "on after get value" hooks.

    protected function fireOnAfterGetValueCallbacks(
        mixed $value,
        array $extra_params = [],
        ?string $identifier = null
    ): mixed {

        return $this->fireHooksWithReturnValue(
            HookNamesEnum::AFTER_GET_VALUE,
            $value,
            [
                $value,
                $this,
                ...$extra_params,
            ],
            $identifier
        );
    }


    // Fires all "on unset value" hooks.

    protected function fireOnUnsetValueCallbacks(
        array $extra_params = [],
        ?string $identifier = null
    ): void {

        $this->fireHooks(HookNamesEnum::UNSET_VALUE, [
            $this,
            ...$extra_params,
        ]);
    }


    // Generic method to unset a given hook from any chosen storage.

    public function unsetHook(
        HookNamesEnum $hook_name,
        int|string $identifier
    ): ?true {

        // Not found.
        if (!isset($this->hooks[$hook_name->name][$identifier])) {
            return null;
        }

        unset($this->hooks[$hook_name->name][$identifier]);

        return true;
    }


    // Unsets an "on before set value" hook.

    public function unsetOnBeforeSetValueCallback(
        int|string $identifier
    ): ?true {

        return $this->unsetHook(
            HookNamesEnum::BEFORE_SET_VALUE,
            $identifier
        );
    }


    // Unsets an "on after set value" hook.

    public function unsetOnAfterSetValueCallback(
        int|string $identifier
    ): ?true {

        return $this->unsetHook(
            HookNamesEnum::AFTER_SET_VALUE,
            $identifier
        );
    }


    // Unsets an "on before get value" hook.

    public function unsetOnBeforeGetValueCallback(
        int|string $identifier
    ): ?true {

        return $this->unsetHook(
            HookNamesEnum::BEFORE_GET_VALUE,
            $identifier
        );
    }


    // Unsets an "on after get value" hook.

    public function unsetOnAfterGetValueCallback(
        int|string $identifier
    ): ?true {

        return $this->unsetHook(
            HookNamesEnum::AFTER_GET_VALUE,
            $identifier
        );
    }


    // Unsets an "on unset" hook.

    public function unsetOnUnsetValueCallback(
        int|string $identifier
    ): ?true {

        return $this->unsetHook(
            HookNamesEnum::UNSET_VALUE,
            $identifier
        );
    }


    // Truncates/Resets all hooks.

    public function truncateAllHooks(): void
    {

        $this->hooks = [];
    }


    // Checks if hook exists

    public function hasHook(
        HookNamesEnum $hook_name,
        int|string $identifier
    ): bool {

        return isset($this->hooks[$hook_name->name][$identifier]);
    }
}
