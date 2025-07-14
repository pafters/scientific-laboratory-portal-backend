<?php

declare(strict_types=1);

namespace Phosagro\Subscription;

use Phosagro\Manager\Errors\NotFoundException;

/**
 * Управление рассылками.
 */
final class RubricMap
{
    /** @var array<string,Rubric> */
    private array $byCode = [];

    /** @var array<int,Rubric> */
    private array $byIdentifier = [];

    private bool $loaded = false;

    public function findRubricByCode(string $code): ?Rubric
    {
        $this->loadRubrics();

        return $this->byCode["~{$code}"] ?? null;
    }

    public function findRubricByIdentifier(int $identifier): ?Rubric
    {
        $this->loadRubrics();

        return $this->byIdentifier[$identifier] ?? null;
    }

    public function getKnownRubric(RubricCode $code): Rubric
    {
        $found = $this->findRubricByCode($code->name);

        if (!$found) {
            throw new NotFoundException('Rubric');
        }

        return $found;
    }

    private function loadRubrics(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $found = \CRubric::GetList(['ID' => 'ASC']);

        while ($row = $found->Fetch()) {
            $this->registerRubric('Y' === $row['ACTIVE'], (string) $row['CODE'], (int) $row['ID']);
        }

        $manager = new \CRubric();

        $sort = 0;

        foreach (RubricCode::cases() as $code) {
            $sort += 10;

            try {
                $this->getKnownRubric($code);
            } catch (NotFoundException) {
                $addResult = $manager->Add([
                    'ACTIVE' => 'Y',
                    'AUTO' => 'N',
                    'CODE' => $code->name,
                    'DAYS_OF_MONTH' => '',
                    'DAYS_OF_WEEK' => '',
                    'DESCRIPTION' => '',
                    'FROM_FIELD' => '',
                    'LAST_EXECUTED' => '',
                    'LID' => 's1',
                    'NAME' => GetMessage($code->getTranslationKey()),
                    'SORT' => $sort,
                    'TEMPLATE' => '',
                    'TIMES_OF_DAY' => '',
                    'VISIBLE' => 'Y',
                ]);

                if (!$addResult) {
                    throw new \RuntimeException(sprintf(
                        'Can not add rubric "%s". %s',
                        $code->name,
                        $manager->LAST_ERROR,
                    ));
                }

                $this->registerRubric(true, $code->name, (int) $addResult);
            }
        }
    }

    private function registerRubric(bool $active, string $code, int $identifier): void
    {
        $rubric = new Rubric($active, $code, $identifier);
        $this->byCode["~{$code}"] = $rubric;
        $this->byIdentifier[$identifier] = $rubric;
    }
}
