<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Bitrix\Main\Config\Option;
use Bitrix\Main\UserConsent\Consent;
use Phosagro\Enum\Edu;
use Phosagro\Enum\Gender;
use Phosagro\Manager\Bitrix\UserFieldManager;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\CityManager;
use Phosagro\Manager\PhosagroCompanyManager;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;
use Phosagro\System\Array\EmptyRequiredException;
use Phosagro\System\Array\MissingRequiredException;
use Phosagro\User\AuthorizationContext;

final class UserEdit
{
    use ChangePasswordTrait;

    private const AGREEMENT = 'agreement';
    private const BIRTHDAY = 'birthday';
    private const CAPTCHA = 'captcha';
    private const CITY = 'city';
    private const COMPANY = 'company';
    private const EDU = 'edu';
    private const EMAIL = 'email';
    private const GENDER = 'gender';
    private const ID = 'id';
    private const LOGIN = 'login';
    private const NAME = 'name';
    private const PASSWORD = 'password';
    private const PASSWORD_CONFIRM = 'password-confirm';
    private const PASSWORD_OLD = 'password-old';
    private const PHONE = 'phone';
    private const PHOSAGRO = 'phosagro';
    private const SURNAME = 'surname';
    private const TYPE = 'type';

    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly AuthorizationContext $authorization,
        private readonly \CUser $bitrixUser,
        private readonly CityManager $cities,
        private readonly PhosagroCompanyManager $phosagroCompanies,
        private readonly UserFieldManager $userFields,
        private readonly UserManager $users,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/edit/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $city = null;

        $updateFields = [];

        $accessor = $this->accessors->createFromRequest();

        $accessor->assertNullableTrue(self::AGREEMENT);
        $accessor->assertOptionalDate(self::BIRTHDAY);
        $accessor->assertNullableCaptchaObject(self::CAPTCHA);
        $accessor->assertOptionalObject(self::CITY);
        if (!$accessor->hasFieldError(self::CITY)) {
            try {
                $cityAccessor = $accessor->getNullableObject(self::CITY);
            } catch (MissingRequiredException) {
                $cityAccessor = null;
            }
            if (null !== $cityAccessor) {
                $cityAccessor->assertStringFilled(self::ID);
                if (!$cityAccessor->hasFieldError(self::ID)) {
                    $cityCode = $cityAccessor->getStringFilled(self::ID);
                    $city = $this->cities->findByCode($cityCode);
                    if (null === $city) {
                        $cityAccessor->addErrorInvalid(self::ID);
                    }
                }
            }
        }
        $accessor->assertOptionalObject(self::COMPANY);
        if (!$accessor->hasFieldError(self::COMPANY)) {
            try {
                $companyAccessor = $accessor->getNullableObject(self::COMPANY);
            } catch (MissingRequiredException) {
                $companyAccessor = null;
            }
            if (null !== $companyAccessor) {
                $hasName = false;
                $companyAccessor->assertNullableStringTrimmed(self::NAME);
                if (!$companyAccessor->hasFieldError(self::NAME)) {
                    $hasName = ('' !== $companyAccessor->getNullableStringTrimmed(self::NAME));
                }
                $hasPhosagro = false;
                $companyAccessor->assertNullableObject(self::PHOSAGRO);
                if (!$companyAccessor->hasFieldError(self::PHOSAGRO)) {
                    $phosagroAccessor = $companyAccessor->getNullableObject(self::PHOSAGRO);
                    if (null !== $phosagroAccessor) {
                        $phosagroAccessor->assertNullableIntParsed(self::ID);
                        if (!$phosagroAccessor->hasFieldError(self::ID)) {
                            $phosagroCompanyId = $phosagroAccessor->getNullableIntParsed(self::ID);
                            if (null !== $phosagroCompanyId) {
                                $phosagroCompany = $this->phosagroCompanies->findOne($phosagroCompanyId);
                                if (null === $phosagroCompany) {
                                    $phosagroAccessor->addErrorInvalid(self::ID);
                                } else {
                                    $hasPhosagro = true;
                                }
                            }
                        }
                    }
                }
                if ($hasName && $hasPhosagro) {
                    $companyAccessor->addErrorRequiredOne(self::NAME);
                    $companyAccessor->addErrorRequiredOne(self::PHOSAGRO);
                }
            }
        }
        $accessor->assertOptionalObject(self::EDU);
        if (!$accessor->hasFieldError(self::EDU)) {
            try {
                $eduAccessor = $accessor->getObject(self::EDU);
            } catch (MissingRequiredException) {
                $eduAccessor = null;
            }
            if (null !== $eduAccessor) {
                $hasName = false;
                $eduAccessor->assertNullableStringTrimmed(self::NAME);
                if (!$eduAccessor->hasFieldError(self::NAME)) {
                    $eduName = $eduAccessor->getNullableStringTrimmed(self::NAME);
                    if ('' !== $eduName) {
                        $hasName = true;
                    }
                }
                $hasType = false;
                $eduAccessor->assertNullableEnum(self::TYPE, Edu::class);
                if (!$eduAccessor->hasFieldError(self::TYPE)) {
                    $eduType = $eduAccessor->getNullableEnum(self::TYPE, Edu::class);
                    if (null !== $eduType) {
                        $hasType = true;
                    }
                }
                if ($hasName && !$hasType) {
                    $eduAccessor->addErrorRequired(self::TYPE);
                }
                if ($hasType && !$hasName) {
                    $eduAccessor->addErrorRequired(self::NAME);
                }
            }
        }
        $accessor->assertOptionalConstantTrimmed(self::EMAIL, $user->email);
        $accessor->assertOptionalEnum(self::GENDER, Gender::class);
        $accessor->assertOptionalStringFilled(self::LOGIN);
        $accessor->assertOptionalStringFilled(self::NAME);
        $accessor->assertNullableStringTrimmed(self::PASSWORD);
        if (!$accessor->hasFieldError(self::PASSWORD)) {
            $password = $accessor->getNullableStringTrimmed(self::PASSWORD);
            if ('' !== $password) {
                $accessor->assertStringFilled(self::PASSWORD_CONFIRM);
                $accessor->assertStringFilled(self::PASSWORD_OLD);
                if (!$accessor->hasFieldError(self::PASSWORD_OLD)) {
                    $passwordOld = $accessor->getStringFilled(self::PASSWORD_OLD);
                    $difference = levenshtein($passwordOld, $password);
                    if ($difference < 3) {
                        $accessor->addErrorInvalid(self::PASSWORD, [GetMessage('PASSWORD_SIMILAR')]);
                    }
                }
            }
        }
        $accessor->assertOptionalConstantTrimmed(self::PHONE, $user->phone);
        $accessor->assertOptionalStringFilled(self::SURNAME);
        $accessor->checkErrors();

        try {
            $birthday = $accessor->getDate(self::BIRTHDAY);
            $updateFields['PERSONAL_BIRTHDAY'] = ConvertTimeStamp($birthday->getTimestamp(), 'FULL');
        } catch (MissingRequiredException) {
            // no update
        }

        if (null !== $city) {
            $updateFields['UF_CITY'] = $this->cities->findBitrixId($city);
        }

        try {
            $companyName = $accessor->getObject(self::COMPANY)->getStringFilled(self::NAME);
            $updateFields['WORK_COMPANY'] = $companyName;
        } catch (MissingRequiredException) {
            // no update
        } catch (EmptyRequiredException) {
            $updateFields['WORK_COMPANY'] = '';
        }

        try {
            $companyPhosagro = $accessor->getObject(self::COMPANY)->getObject(self::PHOSAGRO)->getIntParsed(self::ID);
            $updateFields['UF_PHOSAGRO_COMPANY'] = $companyPhosagro;
        } catch (MissingRequiredException) {
            // no update
        } catch (EmptyRequiredException) {
            $updateFields['UF_PHOSAGRO_COMPANY'] = '';
        }

        try {
            $eduName = $accessor->getObject(self::EDU)->getStringTrimmed(self::NAME);
            $updateFields['UF_EDU_NAME'] = $eduName;
        } catch (MissingRequiredException) {
            // no update
        }

        try {
            $eduType = $accessor->getObject(self::EDU)->getEnum(self::TYPE, Edu::class);
            $updateFields['UF_EDU_TYPE'] = $this->userFields->getEnumIdByCode('USER', 'UF_EDU_TYPE', $eduType->value);
        } catch (MissingRequiredException) {
            // no update
        }

        try {
            $gender = $accessor->getEnum(self::GENDER, Gender::class);
            $updateFields['PERSONAL_GENDER'] = $gender->value;
        } catch (MissingRequiredException) {
            // no update
        }

        try {
            $login = $accessor->getStringFilled(self::LOGIN);
            $updateFields['LOGIN'] = $login;
        } catch (MissingRequiredException) {
            // no update
        }

        try {
            $name = $accessor->getStringFilled(self::NAME);
            $updateFields['NAME'] = $name;
        } catch (MissingRequiredException) {
            // no update
        }

        $password = $accessor->getNullableStringTrimmed(self::PASSWORD);

        if ('' !== $password) {
            $this->changePassword(
                $accessor,
                $this->bitrixUser,
                captchaCode: $accessor->getNullableCaptchaCode(self::CAPTCHA),
                captchaSid: $accessor->getNullableCaptchaId(self::CAPTCHA),
                login: $user->login,
                password: $password,
                passwordConfirm: $accessor->getStringFilled(self::PASSWORD_CONFIRM),
                passwordOld: $accessor->getStringFilled(self::PASSWORD_OLD),
                fieldPassword: self::PASSWORD,
                fieldPasswordConfirm: self::PASSWORD_CONFIRM,
                fieldPasswordOld: self::PASSWORD_OLD,
            );
        }

        try {
            $surname = $accessor->getStringFilled(self::SURNAME);
            $updateFields['LAST_NAME'] = $surname;
        } catch (MissingRequiredException) {
            // no update
        }

        $userIdentifier = $this->users->getId($user);

        if ([] !== $updateFields) {
            $result = $this->bitrixUser->Update($userIdentifier, $updateFields);

            if (!$result) {
                throw new ServerError([$this->bitrixUser->LAST_ERROR]);
            }

            if ($accessor->getNullableTrue(self::AGREEMENT)) {
                Consent::addByContext((int) Option::get('main', 'new_user_agreement'), 'user/profile', 'edit');
            }
        }

        $saved = $this->users->findById($userIdentifier);

        if (!$saved) {
            throw new ServerError([GetMessage('USER_NOT_FOUND')]);
        }

        return $saved->toApi();
    }
}
