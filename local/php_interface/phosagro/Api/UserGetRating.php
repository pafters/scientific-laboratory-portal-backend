<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Rating\RatingCalculator;
use Phosagro\Rating\RatingToApiConverter;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetRating
{
    public function __construct(
        private readonly AuthorizationContext $authorizationContext,
        private readonly RatingCalculator $ratingCalculator,
        private readonly RatingToApiConverter $ratingToApiConverter,
    ) {}

    #[Route(pattern: '~^/api/user/get-rating/(?:(?<event>[^/]+)/)?$~')]
    public function execute(string $event): array
    {
        $user = $this->authorizationContext->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $eventIdentifier = (int) filter_var($event, FILTER_VALIDATE_INT);

        return $this->ratingToApiConverter->convertRatingResultToApi(
            $this->ratingCalculator->calculate(
                $eventIdentifier,
                $user->userIdentifier,
            ),
        );
    }
}
