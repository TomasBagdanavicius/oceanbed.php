<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

use LWP\Common\Common;
use LWP\Common\Exceptions\DuplicateException;
use LWP\Components\Template\TemplateInterface;
use LWP\Network\Headers;
use LWP\Network\Http\Response\Formats\Exceptions\ResponseFormatNotFoundException;

class ResponseFormatsFactory
{
    protected array $registry;


    public function __construct()
    {

        $cases = DefaultResponseFormatsEnum::cases();

        foreach ($cases as $case) {
            $this->registry[$case->value] = (__NAMESPACE__ . '\\' . implode(array_map('ucfirst', explode('_', strtolower($case->value)))));
        }
    }


    //

    public function register(string $name, string $class_name): void
    {

        if (isset($this->registry[$name])) {
            throw new DuplicateException(sprintf("Name \"%s\" already exists", $name));
        }

        if (in_array($class_name, $this->registry)) {
            throw new DuplicateException(sprintf("Class name \"%s\" is already registered", $class_name));
        }

        Common::assertClassNameExistence($class_name);

        $this->registry[$name] = $class_name;
    }


    //

    public function getClassName(string $name): ?string
    {

        return $this->registry[$name] ?? null;
    }


    //

    public function getNames(): array
    {

        return array_keys($this->registry);
    }


    //

    public function getRegistry(): array
    {

        return $this->registry;
    }


    //

    public function fromName(string $name, Headers $headers, TemplateInterface $template, array $extra_params = []): ResponseFormat
    {

        $this->validateName($name);
        $class_name = $this->getClassName($name);
        return new $class_name($headers, $template, ...$extra_params);
    }


    //

    public function has(string $name): bool
    {

        return isset($this->registry[$name]);
    }


    //

    public function validateName(string $name): void
    {

        if (!$this->has($name)) {
            throw new ResponseFormatNotFoundException(sprintf("Response format \"%s\" is not registered", $name));
        }
    }
}
