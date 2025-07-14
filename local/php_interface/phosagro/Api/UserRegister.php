<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserConsent\Consent;
use Phosagro\Enum\Edu;
use Phosagro\Enum\Gender;
use Phosagro\Manager\Bitrix\UserFieldManager;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\CityManager;
use Phosagro\Manager\PhosagroCompanyManager;
use Phosagro\System\Api\Accessor;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;
use Phosagro\System\Array\AccessorException;
use Phosagro\System\Array\EmptyRequiredException;
use Phosagro\System\Array\WrongTypeException;

final class UserRegister
{
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
    private const PHONE = 'phone';
    private const PHOSAGRO = 'phosagro';
    private const SURNAME = 'surname';
    private const TYPE = 'type';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CMain $bitrix,
        private readonly CityManager $cityManager,
        private readonly EventManager $eventManager,
        private readonly PhosagroCompanyManager $phosagroCompanyManager,
        private readonly UserFieldManager $userFieldManager,
        private readonly UserManager $userManager,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/register/$~')]
    public function execute(): array
    {
        $accessor = $this->accessorFactory->createFromRequest();

        $accessor->assertTrue(self::AGREEMENT);
        $accessor->assertDate(self::BIRTHDAY);
        $accessor->assertNullableCaptchaObject(self::CAPTCHA);
        $accessor->assertEmail(self::EMAIL);
        $accessor->assertStringFilled(self::LOGIN);
        $accessor->assertStringFilled(self::NAME);
        $accessor->assertStringFilled(self::PASSWORD);
        if (!$accessor->hasFieldError(self::PASSWORD)) {
            $accessor->assertConstantTrimmed(self::PASSWORD_CONFIRM, $accessor->getStringFilled(self::PASSWORD));
        }
        $accessor->assertPhoneNumber(self::PHONE);
        $accessor->assertEnum(self::GENDER, Gender::class);
        $accessor->assertStringFilled(self::SURNAME);

        $companyName = null;
        $companyPhosagroCode = null;

        $cityCode = $this->validateCity($accessor);
        [$companyName, $companyPhosagroCode] = $this->validateCompany($accessor);
        [$eduName, $eduType] = $this->validateEdu($accessor);

        $accessor->checkErrors();

        $birthday = $accessor->getDate(self::BIRTHDAY);
        $email = $accessor->getEmail(self::EMAIL);
        $login = $accessor->getStringFilled(self::LOGIN);
        $name = $accessor->getStringFilled(self::NAME);
        $password = $accessor->getStringFilled(self::PASSWORD);
        $phone = $accessor->getPhoneNumber(self::PHONE);
        $gender = $accessor->getEnum(self::GENDER, Gender::class);
        $surname = $accessor->getStringFilled(self::SURNAME);

        if (null !== $this->userManager->findByEmail($email)) {
            $accessor->addErrorDuplicate(self::EMAIL);
        }

        if (null !== $this->userManager->findByPhone($phone)) {
            $accessor->addErrorDuplicate(self::PHONE);
        }

        $accessor->checkErrors();

        $this->eventManager->addEventHandler('main', 'OnBeforeUserRegister', static function (
            array &$fields,
        ) use (
            $birthday,
            $cityCode,
            $companyName,
            $companyPhosagroCode,
            $eduName,
            $eduType,
            $gender,
        ): void {
            $fields['PERSONAL_BIRTHDAY'] = ConvertTimeStamp($birthday->getTimestamp(), 'FULL');
            $fields['PERSONAL_GENDER'] = $gender->value;
            $fields['UF_CITY'] = $cityCode;
            $fields['UF_EDU_NAME'] = $eduName;
            $fields['UF_EDU_TYPE'] = $eduType;
            $fields['UF_PHOSAGRO_COMPANY'] = $companyPhosagroCode;
            $fields['WORK_COMPANY'] = $companyName;
        });

        $userManager = new \CUser();

        $messageListOrUserId = $this->parseRegisterResult($userManager->Register(
            $login,
            $name,
            $surname,
            $password,
            $password,
            $email,
            's1',
            $accessor->getNullableCaptchaCode(self::CAPTCHA),
            $accessor->getNullableCaptchaId(self::CAPTCHA),
            false,
            $phone,
        ));

        if (\is_array($messageListOrUserId)) {
            $messageList = $messageListOrUserId;

            $captchMessages = $this->fetchMessages($messageList, [
                GetMessage('MAIN_FUNCTION_REGISTER_CAPTCHA'),
            ]);

            if ([] !== $captchMessages) {
                $accessor->addErrorInvalid(self::CAPTCHA);
                $accessor->throwErrors();
            }

            throw new ServerError($messageList);
        }

        $userId = $messageListOrUserId;

        $user = $this->userManager->findById($userId);

        if (null === $user) {
            \CHTTP::SetStatus(500);

            return [
                'error' => 'system_error',
                'message' => Loc::getMessage('USER_NOT_FOUND'),
            ];
        }

        Consent::addByContext((int) Option::get('main', 'new_user_agreement'), 'main/reg', 'register');

        return $user->toApi();
    }

    /**
     * @param string[] $messageList
     * @param string[] $fetchList
     *
     * @return string[]
     */
    private function fetchMessages(array &$messageList, array $fetchList): array
    {
        /** @var string[] $foundList */
        $foundList = [];

        foreach ($fetchList as $search) {
            $index = array_search($search, $messageList, true);

            if (false !== $index) {
                foreach (array_splice($messageList, $index, 1) as $found) {
                    $foundList[] = $found;
                }
            }
        }

        return $foundList;
    }

    /**
     * @return array<int,string>|int
     */
    private function parseRegisterResult(mixed $result): array|int
    {
        if (!\is_array($result)) {
            throw new \LogicException(sprintf('Unknown result type "%s".', get_debug_type($result)));
        }

        $type = $result['TYPE'] ?? null;

        if (!\is_string($type)) {
            throw new \LogicException(sprintf('Unknown result.TYPE type "%s".', get_debug_type($type)));
        }

        if ('OK' === $type) {
            $id = $result['ID'] ?? null;

            $userId = (\is_string($id) ? filter_var($id, FILTER_VALIDATE_INT) : $id);

            if (!\is_int($userId)) {
                throw new \LogicException(sprintf('Unknown result.ID type "%s".', get_debug_type($id)));
            }

            return $userId;
        }

        if ('ERROR' !== $type) {
            throw new \LogicException(sprintf('Unknown result.TYPE "%s".', $type));
        }

        /** @var string[] $messageList */
        $messageList = [];

        $message = $result['MESSAGE'] ?? null;

        foreach (\is_array($message) ? $message : [$message] as $item) {
            if (!\is_string($item)) {
                throw new \LogicException(sprintf('Unknown result.MESSAGE type "%s".', get_debug_type($item)));
            }
            foreach (explode('<br>', $item) as $error) {
                $error = trim($error);
                if ('' !== $error) {
                    $messageList[] = $error;
                }
            }
        }

        return $messageList;
    }

    private function validateCity(Accessor $accessor): string
    {
        $accessor->assertObject(self::CITY);

        try {
            $city = $accessor->getObject(self::CITY);
            $city->assertStringFilled(self::ID);

            $cityObject = $this->cityManager->findByCode($city->getStringFilled(self::ID));

            if (null === $cityObject) {
                $accessor->addErrorInvalid(self::CITY);

                return '';
            }

            return $city->getStringFilled(self::ID);
        } catch (AccessorException) {
            return '';
        }
    }

    /**
     * @return string[]
     */
    private function validateCompany(Accessor $accessor): array
    {
        try {
            $company = $accessor->getObject(self::COMPANY);
        } catch (EmptyRequiredException) {
            return ['', ''];
        } catch (WrongTypeException) {
            $accessor->addErrorInvalid(self::COMPANY);

            return ['', ''];
        }

        try {
            $companyName = $company->getStringFilled(self::NAME);
        } catch (EmptyRequiredException) {
            $companyName = '';
        } catch (WrongTypeException) {
            $company->addErrorInvalid(self::NAME);
            $companyName = '';
        }

        try {
            $companyPhosagro = $company->getObject(self::PHOSAGRO);
        } catch (EmptyRequiredException) {
            $companyPhosagro = null;
        } catch (WrongTypeException) {
            $company->addErrorInvalid(self::PHOSAGRO);
            $companyPhosagro = null;
        }

        $companyPhosagroId = null;

        if (null !== $companyPhosagro) {
            try {
                $companyPhosagroId = $companyPhosagro->getIntParsed(self::ID);
            } catch (EmptyRequiredException) {
                $companyPhosagroId = null;
            } catch (WrongTypeException) {
                $companyPhosagro->addErrorInvalid(self::ID);
                $companyPhosagroId = null;
            }
        }

        $companyPhosagroCode = '';

        if (!$company->hasErrors()) {
            if (('' !== $companyName) && (null !== $companyPhosagroId)) {
                $company->addErrorRequiredOne(self::NAME);
                $company->addErrorRequiredOne(self::PHOSAGRO);
            }

            if (null !== $companyPhosagroId) {
                $phosagroObject = $this->phosagroCompanyManager->findOne($companyPhosagroId);

                if (null === $phosagroObject) {
                    $company->addErrorInvalid(self::PHOSAGRO);

                    $companyPhosagroCode = '';
                } else {
                    $companyPhosagroCode = sprintf('%d', $phosagroObject->bitrixId);
                }
            }
        }

        return [$companyName, $companyPhosagroCode];
    }

    /**
     * @return string[]
     */
    private function validateEdu(Accessor $accessor): array
    {
        try {
            $edu = $accessor->getObject(self::EDU);
        } catch (EmptyRequiredException) {
            // edu is optional
            return ['', ''];
        } catch (WrongTypeException) {
            $accessor->addErrorInvalid(self::EDU);

            return ['', ''];
        }

        try {
            $name = $edu->getStringFilled(self::NAME);
        } catch (EmptyRequiredException) {
            $name = '';
        } catch (WrongTypeException) {
            $edu->addErrorInvalid(self::NAME);
            $name = '';
        }

        try {
            $type = $edu->getEnum(self::TYPE, Edu::class)->value;
        } catch (EmptyRequiredException) {
            $type = '';
        } catch (WrongTypeException) {
            $edu->addErrorInvalid(self::TYPE);
            $type = '';
        }

        if (!$edu->hasErrors()) {
            if (('' === $name) && ('' !== $type)) {
                $edu->addErrorRequired(self::NAME);
            }

            if (('' !== $name) && ('' === $type)) {
                $edu->addErrorRequired(self::TYPE);
            }
        }

        if ('' !== $type) {
            $type = sprintf('%d', $this->userFieldManager->getEnumIdByCode('USER', 'UF_EDU_TYPE', $type));
        }

        return [$name, $type];
    }
}
