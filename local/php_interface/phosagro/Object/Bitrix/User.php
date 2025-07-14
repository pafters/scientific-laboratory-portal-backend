<?php

declare(strict_types=1);

namespace Phosagro\Object\Bitrix;

use Phosagro\Enum\Edu;
use Phosagro\Enum\Gender;
use Phosagro\Object\City;
use Phosagro\Object\PhosagroCompany;

final class User
{
    private const EMAIL_CONFIRMATION_TIME = 60;

    public readonly bool $emailIsConfirmed;

    public function __construct(
        public readonly bool $active,
        public readonly ?\DateTimeImmutable $birthday,
        public readonly bool $blocked,
        public readonly ?City $city,
        public readonly string $companyName,
        public readonly string $confirmCode,
        public readonly ?\DateTimeImmutable $confirmRequestTime,
        public readonly ?PhosagroCompany $companyPhosagro,
        public readonly string $eduName,
        public readonly ?Edu $eduType,
        public readonly string $email,
        public readonly ?Gender $gender,
        public readonly string $login,
        public readonly string $name,
        public readonly string $password,
        public readonly string $phone,
        public readonly bool $phoneIsConfirmed,
        public readonly string $surname,
        public readonly int $userIdentifier,
        public readonly string $userLid,
    ) {
        $this->emailIsConfirmed = ('' === $confirmCode);
    }

    public function calculateAge(\DateTimeImmutable $now): int
    {
        $birthdayDiff = $this->birthday->diff($now);

        if (false === $birthdayDiff) {
            throw new \RuntimeException('Can not calculate age.');
        }

        return $birthdayDiff->invert ? -$birthdayDiff->y : $birthdayDiff->y;
    }

    public function getEmailConfirmationRemainingTime(\DateTimeImmutable $now): int
    {
        $last = $this->confirmRequestTime;

        if (null === $last) {
            return 0;
        }

        $interval = new \DateInterval(sprintf('PT%dS', self::EMAIL_CONFIRMATION_TIME));

        return $last->add($interval)->getTimestamp() - $now->getTimestamp();
    }

    public function toApi(): array
    {
        $data = [
            'email' => $this->email,
            'login' => $this->login,
            'name' => $this->name,
            'phone' => $this->phone,
            'surname' => $this->surname,
        ];

        if (null !== $this->birthday) {
            $data['birthday'] = $this->birthday->format('Y-m-d');
        }

        if (null !== $this->city) {
            $data['city'] = $this->city->toApi();
        }

        if (('' !== $this->companyName) || (null !== $this->companyPhosagro)) {
            $data['company'] = [];
            if ('' !== $this->companyName) {
                $data['company']['name'] = $this->companyName;
            }
            if (null !== $this->companyPhosagro) {
                $data['company']['phosagro'] = $this->companyPhosagro->toApi();
            }
        }

        if (('' !== $this->eduName) && (null !== $this->eduType)) {
            $data['edu'] = [
                'name' => $this->eduName,
                'type' => $this->eduType->value,
            ];
        }

        if (null !== $this->gender) {
            $data['gender'] = $this->gender->value;
        }

        ksort($data, SORT_STRING);

        return $data;
    }
}
