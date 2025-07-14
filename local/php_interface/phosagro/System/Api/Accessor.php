<?php

declare(strict_types=1);

namespace Phosagro\System\Api;

use Bitrix\Main\Localization\Loc;
use Phosagro\Captcha\Captcha;
use Phosagro\System\Api\Errors\BadRequestError;
use Phosagro\System\Array\Accessor as ArrayAccessor;
use Phosagro\System\Array\AccessorException;
use Phosagro\System\Array\EmptyRequiredException;
use Phosagro\System\Array\MissingRequiredException;
use Phosagro\System\Array\NullRequiredException;
use Phosagro\System\Array\WrongTypeException;
use Phosagro\Util\Text;

final class Accessor
{
    private const CAPTCHA_CODE = 'code';
    private const CAPTCHA_ID = 'id';
    private array $errors = [];
    private array $prepared = [];

    private function __construct(
        private readonly ArrayAccessor $accessor,
        private readonly \CMain $bitrix,
        private readonly string $path = '',
        private readonly string $translation = '',
        private readonly bool $dynamic = false,
    ) {}

    public function addErrorDuplicate(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::DUPLICATE, $mesages);
    }

    public function addErrorExceeded(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::EXCEEDED, $mesages);
    }

    public function addErrorInvalid(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::INVALID, $mesages);
    }

    public function addErrorRequired(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::REQUIRED, $mesages);
    }

    public function addErrorRequiredAny(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::REQUIRED_ANY, $mesages);
    }

    public function addErrorRequiredOne(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::REQUIRED_ONE, $mesages);
    }

    public function addErrorUnexpected(int|string $key, array $mesages = []): void
    {
        $this->addFieldError($key, Error::UNEXPECTED, $mesages);
    }

    public function addFieldError(int|string $key, Error $error, array $mesages = []): void
    {
        $this->addError($this->getPath($key), $this->getTranslationPrefix($key), $error, $mesages);
    }

    public function assertArray(int|string $key): void
    {
        $this->prepareValue($key, fn (int|string $key): self => new self(
            $this->accessor->getArray($key),
            $this->bitrix,
            $this->getPrefix($key),
            $this->getTranslationPrefix($key),
            true,
        ));
    }

    public function assertBase64(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getBase64(...));
    }

    public function assertBase64Filled(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getBase64Filled(...));
    }

    public function assertBool(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getBool(...));
    }

    public function assertBoolBitrix(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getBoolBitrix(...));
    }

    public function assertCaptcha(int|string $key): void
    {
        $this->assertCaptchaObject($key);

        try {
            $code = $this->getCaptchaCode($key);
            $id = $this->getCaptchaId($key);
        } catch (AccessorException) {
            return;
        }

        if (!$this->bitrix->CaptchaCheckCode($code, $id)) {
            $this->addErrorInvalid($key);
        }
    }

    public function assertCaptchaObject(int|string $key): void
    {
        $this->prepareValue($key, function () use ($key): self {
            $captcha = $this->getObject($key);
            $captcha->assertStringFilled(self::CAPTCHA_CODE);
            $captcha->assertStringFilled(self::CAPTCHA_ID);

            return $captcha;
        });
    }

    public function assertConstant(int|string $key, mixed $constant): void
    {
        $this->prepareValue($key, fn (): mixed => $this->accessor->getConstant($key, $constant));
    }

    public function assertConstantTrimmed(int|string $key, string $constant): void
    {
        $this->prepareValue($key, fn (): string => $this->accessor->getConstantTrimmed($key, $constant));
    }

    public function assertDate(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getDate(...));
    }

    public function assertEmail(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getEmail(...));
    }

    public function assertEnum(int|string $key, string $enumClass): void
    {
        $this->prepareValue($key, fn (int|string $key): object => $this->accessor->getEnum($key, $enumClass));
    }

    public function assertFloat(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getFloat(...));
    }

    public function assertFloatParsed(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getFloatParsed(...));
    }

    public function assertInt(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getInt(...));
    }

    public function assertIntParsed(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getIntParsed(...));
    }

    public function assertMissing(int|string $key): void
    {
        try {
            $this->accessor->getString($key);
        } catch (MissingRequiredException) {
            return;
        } catch (NullRequiredException) {
            return;
        } catch (AccessorException) {
            // Exists, need to add unexpected error.
        }
        $this->addErrorUnexpected($key);
    }

    public function assertNullableArray(int|string $key): void
    {
        $this->prepareValue($key, function (int|string $key): ?self {
            $object = $this->accessor->getNullableArray($key);

            if (null === $object) {
                return null;
            }

            return new self(
                $object,
                $this->bitrix,
                $this->getPrefix($key),
                $this->getTranslationPrefix($key),
                true,
            );
        });
    }

    public function assertNullableBase64(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableBase64(...));
    }

    public function assertNullableBase64Filled(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableBase64Filled(...));
    }

    public function assertNullableBool(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableBool(...));
    }

    public function assertNullableBoolBitrix(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableBoolBitrix(...));
    }

    public function assertNullableCaptchaObject(int|string $key): void
    {
        $this->prepareValue($key, function () use ($key): ?self {
            $captcha = $this->getNullableObject($key);

            if (null !== $captcha) {
                $captcha->assertNullableStringTrimmed(self::CAPTCHA_CODE);
                $captcha->assertNullableStringTrimmed(self::CAPTCHA_ID);
            }

            return $captcha;
        });
    }

    public function assertNullableConstant(int|string $key, mixed $constant): void
    {
        $this->prepareValue($key, fn (): mixed => $this->accessor->getNullableConstant($key, $constant));
    }

    public function assertNullableConstantTrimmed(int|string $key, string $constant): void
    {
        $this->prepareValue($key, fn (): ?string => $this->accessor->getNullableConstantTrimmed($key, $constant));
    }

    public function assertNullableDate(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableDate(...));
    }

    public function assertNullableEmail(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableEmail(...));
    }

    public function assertNullableEnum(int|string $key, string $enumClass): void
    {
        $this->prepareValue($key, fn (int|string $key): ?object => $this->accessor->getNullableEnum($key, $enumClass));
    }

    public function assertNullableFloat(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableFloat(...));
    }

    public function assertNullableFloatParsed(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableFloatParsed(...));
    }

    public function assertNullableInt(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableInt(...));
    }

    public function assertNullableIntParsed(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableIntParsed(...));
    }

    public function assertNullableObject(int|string $key, bool $dynamic = false): void
    {
        $this->prepareValue($key, function (int|string $key) use ($dynamic): ?self {
            $object = $this->accessor->getNullableObject($key);

            if (null === $object) {
                return null;
            }

            return new self(
                $object,
                $this->bitrix,
                $this->getPrefix($key),
                $this->getTranslationPrefix($key),
                $dynamic,
            );
        });
    }

    public function assertNullablePhoneNumber(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullablePhoneNumber(...));
    }

    public function assertNullableString(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableString(...));
    }

    public function assertNullableStringFilled(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableStringFilled(...));
    }

    public function assertNullableStringTrimmed(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getNullableStringTrimmed(...));
    }

    public function assertNullableTrue(int|string $key): void
    {
        $this->assertNullableConstant($key, true);
    }

    public function assertObject(int|string $key, bool $dynamic = false): void
    {
        $this->prepareValue($key, fn (int|string $key): self => new self(
            $this->accessor->getObject($key),
            $this->bitrix,
            $this->getPrefix($key),
            $this->getTranslationPrefix($key),
            $dynamic,
        ));
    }

    public function assertOptionalArray(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getArray(...));
    }

    public function assertOptionalBase64(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getBase64(...));
    }

    public function assertOptionalBase64Filled(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getBase64Filled(...));
    }

    public function assertOptionalBool(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getBool(...));
    }

    public function assertOptionalBoolBitrix(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getBoolBitrix(...));
    }

    public function assertOptionalConstant(int|string $key, mixed $constant): void
    {
        $this->assertOptional($key, fn (): mixed => $this->accessor->getConstant($key, $constant));
    }

    public function assertOptionalConstantTrimmed(int|string $key, string $constant): void
    {
        $this->assertOptional($key, fn (): string => $this->accessor->getConstantTrimmed($key, $constant));
    }

    public function assertOptionalDate(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getDate(...));
    }

    public function assertOptionalEmail(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getEmail(...));
    }

    public function assertOptionalEnum(int|string $key, string $enumClass): void
    {
        $this->assertOptional($key, fn (): object => $this->accessor->getEnum($key, $enumClass));
    }

    public function assertOptionalFloat(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getFloat(...));
    }

    public function assertOptionalFloatParsed(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getFloatParsed(...));
    }

    public function assertOptionalInt(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getInt(...));
    }

    public function assertOptionalIntParsed(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getIntParsed(...));
    }

    public function assertOptionalObject(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getObject(...));
    }

    public function assertOptionalPhoneNumber(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getPhoneNumber(...));
    }

    public function assertOptionalString(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getString(...));
    }

    public function assertOptionalStringFilled(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getStringFilled(...));
    }

    public function assertOptionalStringTrimmed(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getStringTrimmed(...));
    }

    public function assertOptionalTrue(int|string $key): void
    {
        $this->assertOptional($key, $this->accessor->getTrue(...));
    }

    public function assertPhoneNumber(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getPhoneNumber(...));
    }

    public function assertString(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getString(...));
    }

    public function assertStringFilled(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getStringFilled(...));
    }

    public function assertStringTrimmed(int|string $key): void
    {
        $this->prepareValue($key, $this->accessor->getStringTrimmed(...));
    }

    public function assertTrue(int|string $key): void
    {
        $this->assertConstant($key, true);
    }

    public function checkErrors(): void
    {
        if ($this->hasErrors()) {
            $this->throwErrors();
        }
    }

    public static function create(\CMain $bitrix, array $data, string $translationPrefix = ''): self
    {
        return new self(new ArrayAccessor($data), $bitrix, translation: $translationPrefix);
    }

    public function fetchErrors(): array
    {
        $errors = $this->errors;

        foreach ($this->prepared as $value) {
            if ($value instanceof self) {
                foreach ($value->fetchErrors() as $childKey => $childError) {
                    $errors[$childKey] = $childError;
                }
            }
        }

        ksort($errors, SORT_NATURAL);

        $this->errors = [];

        return $errors;
    }

    public function getArray(int|string $key): self
    {
        return $this->prepared[$key] ??= new self(
            $this->accessor->getArray($key),
            $this->bitrix,
            $this->getPrefix($key),
            $this->getTranslationPrefix($key),
            true,
        );
    }

    public function getBase64(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getBase64($key);
    }

    public function getBase64Filled(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getBase64Filled($key);
    }

    public function getBool(int|string $key): bool
    {
        return $this->prepared[$key] ??= $this->accessor->getBool($key);
    }

    public function getBoolBitrix(int|string $key): bool
    {
        return $this->prepared[$key] ??= $this->accessor->getBoolBitrix($key);
    }

    public function getCaptchaCode(int|string $key): string
    {
        return $this->getObject($key)->getStringFilled(self::CAPTCHA_CODE);
    }

    public function getCaptchaId(int|string $key): string
    {
        return $this->getObject($key)->getStringFilled(self::CAPTCHA_ID);
    }

    public function getCaptchaObject(int|string $key): Captcha
    {
        return new Captcha($this->getCaptchaId($key), $this->getCaptchaCode($key));
    }

    /**
     * @template T
     *
     * @param T $constant
     *
     * @return T
     */
    public function getConstant(int|string $key, mixed $constant): mixed
    {
        return $this->prepared[$key] ??= $this->accessor->getConstant($key, $constant);
    }

    public function getConstantTrimmed(int|string $key, string $constant): string
    {
        return $this->prepared[$key] ??= $this->accessor->getConstantTrimmed($key, $constant);
    }

    public function getData(): array
    {
        return $this->accessor->data;
    }

    public function getDate(int|string $key): \DateTimeImmutable
    {
        return $this->prepared[$key] ??= $this->accessor->getDate($key);
    }

    public function getEmail(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getEmail($key);
    }

    /**
     * @template T
     *
     * @param class-string<T> $enumClass
     *
     * @return T
     */
    public function getEnum(int|string $key, string $enumClass): object
    {
        return $this->prepared[$key] ??= $this->accessor->getEnum($key, $enumClass);
    }

    public function getFloat(int|string $key): float
    {
        return $this->prepared[$key] ??= $this->accessor->getFloat($key);
    }

    public function getFloatParsed(int|string $key): float
    {
        return $this->prepared[$key] ??= $this->accessor->getFloatParsed($key);
    }

    public function getInt(int|string $key): int
    {
        return $this->prepared[$key] ??= $this->accessor->getInt($key);
    }

    public function getIntParsed(int|string $key): int
    {
        return $this->prepared[$key] ??= $this->accessor->getIntParsed($key);
    }

    /**
     * @return array<int,int|string>
     */
    public function getKeys(): array
    {
        return $this->accessor->getKeys();
    }

    public function getNullableArray(int|string $key): ?self
    {
        $result = $this->prepared[$key] ?? null;

        if (null === $result) {
            $object = $this->accessor->getNullableArray($key);

            if (null !== $object) {
                $result = new self(
                    $object,
                    $this->bitrix,
                    $this->getPrefix($key),
                    $this->getTranslationPrefix($key),
                    true,
                );
            }
        }

        return $result;
    }

    public function getNullableBase64(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableBase64($key);
    }

    public function getNullableBase64Filled(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableBase64Filled($key);
    }

    public function getNullableBool(int|string $key): ?bool
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableBool($key);
    }

    public function getNullableBoolBitrix(int|string $key): ?bool
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableBoolBitrix($key);
    }

    public function getNullableCaptchaCode(int|string $key): string
    {
        return $this->getNullableObject($key)?->getNullableStringTrimmed(self::CAPTCHA_CODE) ?? '';
    }

    public function getNullableCaptchaId(int|string $key): string
    {
        return $this->getNullableObject($key)?->getNullableStringTrimmed(self::CAPTCHA_ID) ?? '';
    }

    public function getNullableCaptchaObject(int|string $key): Captcha
    {
        return new Captcha($this->getNullableCaptchaId($key), $this->getNullableCaptchaCode($key));
    }

    /**
     * @template T
     *
     * @param T $constant
     *
     * @return ?T
     */
    public function getNullableConstant(int|string $key, mixed $constant): mixed
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableConstant($key, $constant);
    }

    public function getNullableConstantTrimmed(int|string $key, string $constant): ?string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableConstantTrimmed($key, $constant);
    }

    public function getNullableDate(int|string $key): ?\DateTimeImmutable
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableDate($key);
    }

    public function getNullableEmail(int|string $key): ?string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableEmail($key);
    }

    /**
     * @template T
     *
     * @param class-string<T> $enumClass
     *
     * @return ?T
     */
    public function getNullableEnum(int|string $key, string $enumClass): ?object
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableEnum($key, $enumClass);
    }

    public function getNullableFloat(int|string $key): ?float
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableFloat($key);
    }

    public function getNullableFloatParsed(int|string $key): ?float
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableFloatParsed($key);
    }

    public function getNullableInt(int|string $key): ?int
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableInt($key);
    }

    public function getNullableIntParsed(int|string $key): ?int
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableIntParsed($key);
    }

    public function getNullableObject(int|string $key, bool $dynamic = false): ?self
    {
        $result = $this->prepared[$key] ?? null;

        if (null === $result) {
            $object = $this->accessor->getNullableObject($key);

            if (null !== $object) {
                $result = new self(
                    $object,
                    $this->bitrix,
                    $this->getPrefix($key),
                    $this->getTranslationPrefix($key),
                    $dynamic,
                );
            }
        }

        return $result;
    }

    public function getNullablePhoneNumber(int|string $key): ?string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullablePhoneNumber($key);
    }

    public function getNullableString(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableString($key);
    }

    public function getNullableStringFilled(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableStringFilled($key);
    }

    public function getNullableStringTrimmed(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getNullableStringTrimmed($key);
    }

    public function getNullableTrue(int|string $key): ?bool
    {
        return $this->getNullableConstant($key, true);
    }

    public function getObject(int|string $key, bool $dynamic = false): self
    {
        return $this->prepared[$key] ??= new self(
            $this->accessor->getObject($key),
            $this->bitrix,
            $this->getPrefix($key),
            $this->getTranslationPrefix($key),
            $dynamic,
        );
    }

    public function getPhoneNumber(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getPhoneNumber($key);
    }

    public function getString(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getString($key);
    }

    public function getStringFilled(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getStringFilled($key);
    }

    public function getStringTrimmed(int|string $key): string
    {
        return $this->prepared[$key] ??= $this->accessor->getStringTrimmed($key);
    }

    public function getTrue(int|string $key): bool
    {
        return $this->getConstant($key, true);
    }

    public function hasErrors(): bool
    {
        if ([] !== $this->errors) {
            return true;
        }

        foreach ($this->prepared as $value) {
            if ($value instanceof self) {
                if ($value->hasErrors()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasFieldError(int|string $key): bool
    {
        $path = $this->getPath($key);

        if (\array_key_exists($path, $this->errors)) {
            return true;
        }

        $prepared = $this->prepared[$path] ?? null;

        if ($prepared instanceof self) {
            return $prepared->hasErrors();
        }

        return false;
    }

    public function hasKey(int|string $key): bool
    {
        return $this->accessor->hasKey($key);
    }

    public function throwErrors(): never
    {
        throw new BadRequestError('wrong_fields', $this->fetchErrors());
    }

    /**
     * @param string[] $messages
     */
    private function addError(string $key, string $translationPrefix, Error $error, array $messages = []): void
    {
        $code = Text::lower($error->name);
        $translationKey = $translationPrefix.Text::upper($code);
        $this->errors[$key] = [
            'code' => $code,
            'message' => [$this->translateMessage($code, $key, $translationKey), ...$messages],
        ];
    }

    private function assertOptional(int|string $key, callable $accessor): void
    {
        try {
            $accessor($key);
        } catch (MissingRequiredException) {
            // ok
        } catch (EmptyRequiredException $error) {
            $this->addError($error->field, $this->getTranslationPrefix($key), Error::REQUIRED);
        } catch (WrongTypeException $error) {
            $this->addError($error->field, $this->getTranslationPrefix($key), Error::INVALID);
        }
    }

    private function getPath(int|string $key): string
    {
        return $this->path.(\is_int($key) ? sprintf('%d', $key) : $key);
    }

    private function getPrefix(int|string $key): string
    {
        return $this->getPath($key).'.';
    }

    private function getTranslationPrefix(int|string $key): string
    {
        return $this->translation.($this->dynamic ? '0' : Text::upper($key)).'_';
    }

    private function prepareValue(int|string $key, \Closure $accessor): void
    {
        try {
            $this->prepared[$key] = $accessor($key);
        } catch (EmptyRequiredException $error) {
            $this->addError($error->field, $this->getTranslationPrefix($key), Error::REQUIRED);
        } catch (WrongTypeException $error) {
            $this->addError($error->field, $this->getTranslationPrefix($key), Error::INVALID);
        }
    }

    private function translateMessage(string $code, string $field, string $translationKey): string
    {
        $translation = Loc::getMessage($translationKey);

        if (null === $translation) {
            $translation = Loc::getMessage(match ($code) {
                'invalid' => 'API_FIELD_INVALID',
                'required' => 'API_FIELD_REQUIRED',
                default => 'API_FIELD_WRONG',
            }, ['#ERROR#' => $code, '#FIELD#' => $field]);
        }

        if (null === $translation) {
            throw new \LogicException(sprintf('Not found translation "%s".', $translationKey));
        }

        return $translation;
    }
}
