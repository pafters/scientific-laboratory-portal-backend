<?php

declare(strict_types=1);

namespace Phosagro;

use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\BindRequestBody;
use Phosagro\System\Api\Definition;
use Phosagro\System\Api\HasCustomResponseData;
use Phosagro\System\Api\Route;
use Phosagro\Util\Text;

final class ApiResolver
{
    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly ServiceContainer $serviceContainer,
    ) {}

    public function resolve(string $uri, array $routes): void
    {
        $result = null;

        $origin = $_SERVER['HTTP_ORIGIN'];
        if ('http://suntsov.phosagro.picom.su' === $origin
             || 'https://suntsov.phosagro.picom.su' === $origin
             || 'https://phosagro.picom.su' === $origin
             || 'https://phosagro.picom.su' === $origin
             || 'http://preprod.phosagro.picom.su/' === $origin
             || 'https://preprod.phosagro.picom.su/' === $origin
             || 'http://localhost:5173' === $origin) {
            header("Access-Control-Allow-Origin: {$origin}");
        }

        header('Access-Control-Allow-Credentials: true');

        $definitionList = $this->getRoutes();

        foreach ($routes as $pattern => [$class, $method]) {
            $definitionList[] = new Definition(
                $class,
                $method,
                'GET',
                $pattern,
            );
        }

        $requestMethod = Text::upper($_SERVER['REQUEST_METHOD'] ?? '');

        foreach ($definitionList as $definition) {
            $pattern = $definition->pattern;
            if (1 === preg_match($pattern, $uri, $match)) {
                $definitionMethod = Text::upper($definition->method);

                if ('OPTIONS' === $requestMethod) {
                    header(sprintf('Allow: OPTIONS,%s', $definitionMethod));
                    header(sprintf('Access-Control-Allow-Methods: OPTIONS,%s', $definitionMethod));
                    header('Access-Control-Allow-Headers: content-type');

                    return;
                }

                if ($definitionMethod !== $requestMethod) {
                    \CHTTP::SetStatus(405);

                    header(sprintf('Allow: %s', $definitionMethod));
                    header('Content-Type: text/plain; charset=utf-8');

                    echo sprintf('Only %s method allowed.', $definitionMethod);

                    return;
                }

                $class = $definition->class;
                $method = $definition->function;
                $instance = $this->serviceContainer->get($class);
                $instanceMeta = new \ReflectionObject($instance);
                $methodMeta = $instanceMeta->getMethod($method);

                $arguments = [];

                foreach ($methodMeta->getParameters() as $parameter) {
                    if ([] !== $parameter->getAttributes(BindRequestBody::class)) {
                        $arguments[] = $this->accessors->createFromRequest();

                        continue;
                    }
                    if (!\array_key_exists($parameter->getName(), $match)) {
                        throw new \LogicException(sprintf('Missing required parameter "%s" in route "%s".', $parameter->getName(), $pattern));
                    }
                    $arguments[] = $match[$parameter->getName()];
                }

                try {
                    $data = (object) $methodMeta->invokeArgs($instance, $arguments);
                    if ([] !== $methodMeta->getAttributes(HasCustomResponseData::class)) {
                        $result = $data;
                    } else {
                        $result = ['data' => $data];
                    }
                } catch (ApiError $error) {
                    \CHTTP::SetStatus($error->getCode());

                    $result = [
                        'error' => $error->getMessage(),
                        'data' => (object) $error->data,
                    ];
                }

                break;
            }
        }

        if (null === $result) {
            if ('OPTIONS' === $requestMethod) {
                header('Allow: OPTIONS,GET');
                header('Access-Control-Allow-Methods: OPTIONS,GET');
                header('Access-Control-Allow-Headers: content-type');

                return;
            }

            \CHTTP::SetStatus(404);

            header('Content-Type: text/plain');

            echo 'Not found.';

            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return Definition[]
     */
    private function getRoutes(): array
    {
        return BitrixCache::get('/api-routes/api-routes', $this->scanRoutes(...), 86400);
    }

    /**
     * @return Definition[]
     */
    private function scanRoutes(): array
    {
        /** @var Definition[] $definitionList */
        $definitionList = [];

        /** @var string[] $classNameList */
        $classNameList = [];

        foreach (new \DirectoryIterator(__DIR__.\DIRECTORY_SEPARATOR.'Api') as $item) {
            if ($item->isFile()) {
                $classNameList[] = pathinfo($item->getFilename(), PATHINFO_FILENAME);
            }
        }

        foreach ($classNameList as $name) {
            $fqcn = "\\Phosagro\\Api\\{$name}";
            $class = new \ReflectionClass($fqcn);
            foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes() as $attributeMetadata) {
                    $attribute = $attributeMetadata->newInstance();
                    if ($attribute instanceof Route) {
                        $definitionList[] = new Definition(
                            $class->getName(),
                            $method->getName(),
                            $attribute->method,
                            $attribute->pattern,
                        );
                    }
                }
            }
        }

        return $definitionList;
    }
}
