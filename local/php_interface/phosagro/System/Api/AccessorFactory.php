<?php

declare(strict_types=1);

namespace Phosagro\System\Api;

use Phosagro\System\Api\Errors\BadRequestError;
use Phosagro\Util\File;
use Phosagro\Util\Json;

final class AccessorFactory
{
    public function __construct(
        private readonly \CMain $bitrix,
    ) {}

    public function createFromArray(array $data): Accessor
    {
        return Accessor::create($this->bitrix, $data);
    }

    public function createFromGet(): Accessor
    {
        return Accessor::create($this->bitrix, $_GET);
    }

    public function createFromRequest(string $translationPrefix = ''): Accessor
    {
        try {
            $parameters = Json::decode(File::readInput());
        } catch (\JsonException) {
            throw new BadRequestError('not_a_json_request');
        }

        if (!\is_array($parameters)) {
            throw new BadRequestError('not_an_object_request');
        }

        return Accessor::create($this->bitrix, $parameters, $translationPrefix);
    }
}
