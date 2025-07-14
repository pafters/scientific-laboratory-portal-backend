<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Object\AccrualReason;
use Phosagro\Object\AccrualReasonCode;

/**
 * @extends AbstractIblockElementManager<AccrualReason>
 */
final class AccrualReasonManager extends AbstractIblockElementManager
{
    /**
     * @param AccrualReasonCode[] $codeList
     *
     * @return \WeakMap<AccrualReasonCode,AccrualReason>
     */
    public function getReasonIndex(array $codeList): \WeakMap
    {
        /** @var \WeakMap<AccrualReasonCode,AccrualReason> $result */
        $result = new \WeakMap();

        /** @var array<string,AccrualReason> $index */
        $index = [];

        $codeStringList = array_map(static fn (AccrualReasonCode $code): string => $code->value, $codeList);

        $reasonList = $this->findAllElements(['CODE' => $codeStringList]);

        foreach ($reasonList as $reason) {
            $key = "~{$reason->reasonCode}";

            if (\array_key_exists($key, $index)) {
                throw new FoundMultipleException(
                    AccrualReason::class,
                    sprintf('%d', $index[$key]->reasonIdentifier),
                    sprintf('%d', $reason->reasonIdentifier),
                );
            }

            $index[$key] = $reason;
        }

        foreach ($codeList as $code) {
            $key = "~{$code->value}";

            if (!\array_key_exists($key, $index)) {
                throw new NotFoundException(
                    AccrualReason::class,
                );
            }

            $result[$code] = $index[$key];
        }

        return $result;
    }

    protected function createFromBitrixData(array $row): AccrualReason
    {
        return new AccrualReason(
            (string) $row['CODE'],
            (int) $row['ID'],
            (string) $row['NAME'],
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'CODE',
            'ID',
            'NAME',
        ];
    }
}
