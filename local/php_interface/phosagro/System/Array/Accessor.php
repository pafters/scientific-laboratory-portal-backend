<?php

declare(strict_types=1);

namespace Phosagro\System\Array;

use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
use Phosagro\Util\Text;

final class Accessor
{
    public function __construct(
        public readonly array $data,
        private readonly string $path = '',
    ) {}

    /**
     * @param array<int,int|string> $keys
     */
    public function assertKeys(array $keys): void
    {
        $missing = array_diff($keys, $this->getKeys());

        if ([] !== $missing) {
            throw new MissingRequiredException($this->getPath(array_pop($missing)));
        }

        $unexpected = array_diff($this->getKeys(), $keys);

        if ([] !== $unexpected) {
            throw new ExistsUnexpectedException($this->getPath(array_pop($unexpected)));
        }
    }

    public function getArray(int|string $key): self
    {
        $value = $this->getValue($key);

        if (!\is_array($value) || !array_is_list($value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return new self($value, $this->getPath($key).'.');
    }

    public function getBase64(int|string $key): string
    {
        $value = $this->getStringFilled($key);

        $content = base64_decode($value, true);

        if (false === $content) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $content;
    }

    public function getBase64Filled(int|string $key): string
    {
        $value = $this->getBase64($key);

        if ('' === $value) {
            throw new EmptyRequiredException($this->getPath($key));
        }

        return $value;
    }

    public function getBool(int|string $key): bool
    {
        $value = $this->getValue($key);

        if (!\is_bool($value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
    }

    public function getBoolBitrix(int|string $key): bool
    {
        $value = $this->getStringFilled($key);

        if (('N' !== $value) && ('Y' !== $value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return 'Y' === $value;
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
        $value = $this->getValue($key);

        if ($value !== $constant) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
    }

    public function getConstantTrimmed(int|string $key, string $constant): string
    {
        $value = $this->getStringTrimmed($key);

        if ($value !== trim($constant)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
    }

    public function getDate(int|string $key): \DateTimeImmutable
    {
        $value = $this->getStringFilled($key);

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if (false === $date) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        }

        if (false === $date) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $date;
    }

    public function getEmail(int|string $key): string
    {
        $value = $this->getStringFilled($key);

        if (!str_contains($value, '@')) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
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
        $value = $this->getStringFilled($key);

        if (!is_subclass_of($enumClass, \BackedEnum::class)) {
            foreach ($enumClass::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }

            throw new WrongTypeException($this->getPath($key));
        }

        try {
            return $enumClass::from($value);
        } catch (\ValueError) {
            throw new WrongTypeException($this->getPath($key));
        }
    }

    public function getFloat(int|string $key): float
    {
        $value = $this->getValue($key);

        if (\is_int($value)) {
            $value = (float) $value;
        }
        if (!\is_float($value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
    }

    public function getFloatParsed(int|string $key): float
    {
        try {
            return $this->getFloat($key);
        } catch (WrongTypeException) {
            $numeric = $this->getStringTrimmed($key);
            $numeric = Text::replace($numeric, '~\s~');
            if ((1 === Text::count($numeric, ',')) && (0 === Text::count($numeric, '.'))) {
                $numeric = Text::replace($numeric, '~,~', '.');
            }
            $value = filter_var($numeric, FILTER_VALIDATE_FLOAT);
            if (!\is_float($value)) {
                throw new WrongTypeException($this->getPath($key));
            }

            return $value;
        }
    }

    public function getInt(int|string $key): int
    {
        $value = $this->getValue($key);

        if (!\is_int($value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
    }

    public function getIntParsed(int|string $key): int
    {
        try {
            return $this->getInt($key);
        } catch (WrongTypeException) {
            $value = filter_var($this->getString($key), FILTER_VALIDATE_INT);
            if (!\is_int($value)) {
                throw new WrongTypeException($this->getPath($key));
            }

            return $value;
        }
    }

    /**
     * @return array<int,int|string>
     */
    public function getKeys(): array
    {
        return array_keys($this->data);
    }

    public function getNullableArray(int|string $key): ?object
    {
        try {
            return $this->getArray($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableBase64(int|string $key): string
    {
        try {
            return $this->getBase64($key);
        } catch (EmptyRequiredException) {
            return '';
        }
    }

    public function getNullableBase64Filled(int|string $key): string
    {
        try {
            return $this->getBase64Filled($key);
        } catch (EmptyRequiredException) {
            return '';
        }
    }

    public function getNullableBool(int|string $key): ?bool
    {
        try {
            return $this->getBool($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableBoolBitrix(int|string $key): ?bool
    {
        try {
            return $this->getBoolBitrix($key);
        } catch (EmptyRequiredException) {
            return null;
        }
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
        try {
            return $this->getConstant($key, $constant);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableConstantTrimmed(int|string $key, string $constant): ?string
    {
        try {
            return $this->getConstantTrimmed($key, $constant);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableDate(int|string $key): ?\DateTimeImmutable
    {
        try {
            return $this->getDate($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableEmail(int|string $key): ?string
    {
        try {
            return $this->getEmail($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    /**
     * @template T
     *
     * @param class-string<T> $enumClass
     *
     * @return null|T
     */
    public function getNullableEnum(int|string $key, string $enumClass): ?object
    {
        try {
            return $this->getEnum($key, $enumClass);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableFloat(int|string $key): ?float
    {
        try {
            return $this->getFloat($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableFloatParsed(int|string $key): ?float
    {
        try {
            return $this->getFloatParsed($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableInt(int|string $key): ?int
    {
        try {
            return $this->getInt($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableIntParsed(int|string $key): ?int
    {
        try {
            return $this->getIntParsed($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableObject(int|string $key): ?object
    {
        try {
            return $this->getObject($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullablePhoneNumber(int|string $key): ?string
    {
        try {
            return $this->getPhoneNumber($key);
        } catch (EmptyRequiredException) {
            return null;
        }
    }

    public function getNullableString(int|string $key): string
    {
        try {
            return $this->getString($key);
        } catch (EmptyRequiredException) {
            return '';
        }
    }

    public function getNullableStringFilled(int|string $key): string
    {
        try {
            return $this->getStringFilled($key);
        } catch (EmptyRequiredException) {
            return '';
        }
    }

    public function getNullableStringTrimmed(int|string $key): string
    {
        try {
            return $this->getStringTrimmed($key);
        } catch (EmptyRequiredException) {
            return '';
        }
    }

    public function getNullableTrue(int|string $key): ?bool
    {
        return $this->getNullableConstant($key, true);
    }

    public function getObject(int|string $key): self
    {
        $value = $this->getValue($key);

        if (!\is_array($value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return new self($value, $this->getPath($key).'.');
    }

    public function getPhoneNumber(int|string $key): string
    {
        $value = $this->getStringFilled($key);

        $phone = Parser::getInstance()->parse($value);

        if (!$phone->isValid()) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $phone->format(Format::E164);
    }

    public function getString(int|string $key): string
    {
        $value = $this->getValue($key);

        if (!\is_string($value)) {
            throw new WrongTypeException($this->getPath($key));
        }

        return $value;
    }

    public function getStringFilled(int|string $key): string
    {
        $value = $this->getStringTrimmed($key);

        if ('' === $value) {
            throw new EmptyRequiredException($this->getPath($key));
        }

        return $value;
    }

    public function getStringTrimmed(int|string $key): string
    {
        return trim($this->getString($key));
    }

    public function getTrue(int|string $key): bool
    {
        return $this->getConstant($key, true);
    }

    public function hasKey(int|string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    private function getPath(int|string $key): string
    {
        return $this->path.(\is_int($key) ? sprintf('%d', $key) : $key);
    }

    private function getValue(int|string $key): mixed
    {
        $value = $this->getValueNullable($key);

        if (null === $value) {
            throw new NullRequiredException($this->getPath($key));
        }

        return $value;
    }

    private function getValueNullable(int|string $key): mixed
    {
        if (!$this->hasKey($key)) {
            throw new MissingRequiredException($this->getPath($key));
        }

        return $this->data[$key];
    }
}
