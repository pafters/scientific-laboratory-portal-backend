<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Listeners;

use Bitrix\Main\EventManager;
use Phosagro\Enum\PageType;
use Phosagro\System\Api\Headers;
use Phosagro\System\ListenerInterface;
use Phosagro\Util\Json;

final class OutputStaticPage implements ListenerInterface
{
    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('main', 'OnEndBufferContent', $this->onAfterEnd(...));
    }

    private function onAfterEnd(string &$content): void
    {
        if (!filter_input(INPUT_GET, 'api', FILTER_VALIDATE_BOOL)) {
            if (\defined('ADMIN_SECTION') && ADMIN_SECTION) {
                return;
            }

            if (filter_input(INPUT_GET, 'menu', FILTER_VALIDATE_BOOL)) {
                return;
            }

            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                return;
            }

            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/bitrix/')) {
                return;
            }

            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/assets/index.html')) {
                $content = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/assets/index.html');
            }

            return;
        }

        /** @var array<int,PageType|string> $chunkList */
        $chunkList = [$content];

        foreach (PageType::cases() as $type) {
            /** @var array<int,PageType|string> $explodedChunkList */
            $explodedChunkList = [];
            foreach ($chunkList as $chunk) {
                if ($chunk instanceof PageType) {
                    $explodedChunkList[] = $chunk;
                } else {
                    foreach (explode($type->value, $chunk) as $piece) {
                        $explodedChunkList[] = $piece;
                        $explodedChunkList[] = $type;
                    }
                    array_pop($explodedChunkList);
                }
            }
            $chunkList = $explodedChunkList;
        }

        /** @var array<int,PageType|string> $trimmedChunkList */
        $trimmedChunkList = [];

        foreach ($chunkList as $chunk) {
            if ($chunk instanceof PageType) {
                $trimmedChunkList[] = $chunk;
            } else {
                if ('' !== trim(strip_tags($chunk))) {
                    $trimmedChunkList[] = trim($chunk);
                }
            }
        }

        $chunkList = $trimmedChunkList;

        /** @var array[] $responseData */
        $responseData = [];

        for ($index = 0, $total = \count($chunkList); $index < $total; ++$index) {
            $chunk = $chunkList[$index];
            if ($chunk instanceof PageType) {
                $next = $chunkList[++$index] ?? null;
                if (!\is_string($next)) {
                    throw new \RuntimeException(sprintf(
                        'Bad chunk list. Not a string chunk %d. %s',
                        $index,
                        var_export($chunkList, true),
                    ));
                }
                $last = $chunkList[++$index] ?? null;
                if ($last !== $chunk) {
                    throw new \RuntimeException(sprintf(
                        'Bad chunk list. Wrong ending chunk %d. %s',
                        $index,
                        var_export($chunkList, true),
                    ));
                }
                $json = base64_decode($next, true);
                if (!\is_string($json)) {
                    throw new \RuntimeException(sprintf(
                        'Bad chunk list. Not a base64 data in chunk %d. %s',
                        $index,
                        var_export($chunkList, true),
                    ));
                }
                $responseData[] = $chunk->toApi(Json::decode($json));
            } else {
                $responseData[] = ['html' => $chunk];
            }
        }

        $lastBlock = array_pop($responseData);

        if ([] !== $responseData) {
            throw new \RuntimeException('Not supported more than one block in one page.');
        }

        $content = Json::encode(['data' => $lastBlock, 'title' => $GLOBALS['APPLICATION']->GetTitle()]);

        Headers::writeHeaders();
    }
}
