<?php

declare(strict_types=1);

namespace Phosagro;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Phosagro\Event\Listeners\AccrueTaskScore;
use Phosagro\Event\Listeners\FillParticipantNameAndCode;
use Phosagro\Event\Listeners\SendParticipationRequestEmails;
use Phosagro\Log\Listeners\RegisterLogEventTypes;
use Phosagro\Sms\MegafonSmsServiceListener;
use Phosagro\Sms\TestSmsServiceListener;
use Phosagro\System\Api\Listeners\OutputStaticPage;
use Phosagro\System\Iblock\WebFormPropertyRegistrator;
use Phosagro\System\Iblock\WebFormResultPropertyRegistrator;
use Phosagro\System\ListenerManager;
use Phosagro\User\Constraints\PreventUserActivationWhenChangingPassword;
use Phosagro\User\Constraints\UserEducationalInstitutionNameMustBeFilledIfSelectedType;
use Phosagro\User\Constraints\UserEducationalInstitutionTypeMustBeSelectedIfFilledName;
use Phosagro\User\Listeners\InsertUserToTheServiceContainer;
use Phosagro\User\Listeners\InsertUserToUserGroup;
use Phosagro\User\Listeners\LogSessionLength;
use Phosagro\Voting\SubscriptionConditionsChecker;

require __DIR__.\DIRECTORY_SEPARATOR.'phosagro'.\DIRECTORY_SEPARATOR.'ClassAutoloader.php';

const UTF8 = 'UTF-8';

ClassAutoloader::register();
BitrixAutoloader::register();

$container = ServiceContainer::getInstance();

$container->set(EventManager::class, EventManager::getInstance());
$container->set(\CMain::class, $GLOBALS['APPLICATION']);
$container->set(Connection::class, Application::getConnection());

$listener = $container->get(ListenerManager::class);
$listener->register(AccrueTaskScore::class);
$listener->register(FillParticipantNameAndCode::class);
$listener->register(InsertUserToTheServiceContainer::class);
$listener->register(InsertUserToUserGroup::class);
$listener->register(LogSessionLength::class);
$listener->register(MegafonSmsServiceListener::class);
$listener->register(OutputStaticPage::class);
$listener->register(PreventUserActivationWhenChangingPassword::class);
$listener->register(RegisterLogEventTypes::class);
$listener->register(SendParticipationRequestEmails::class);
$listener->register(SubscriptionConditionsChecker::class);
$listener->register(TestSmsServiceListener::class);
$listener->register(UserEducationalInstitutionNameMustBeFilledIfSelectedType::class);
$listener->register(UserEducationalInstitutionTypeMustBeSelectedIfFilledName::class);
$listener->register(WebFormPropertyRegistrator::class);
$listener->register(WebFormResultPropertyRegistrator::class);

Loc::loadMessages(__FILE__);

function get_bitrix_error(): string
{
    $app = ServiceContainer::getInstance()->get(\CMain::class);

    $exception = $app->GetException();

    if (!$exception instanceof \CApplicationException) {
        return '';
    }

    $error = $exception->GetString();

    if (!\is_string($error)) {
        return '';
    }

    return $error;
}

$events = EventManager::getInstance();

$events->addEventHandler('main', 'OnBuildGlobalMenu', static function (&$globalMenu, &$moduleMenu): void {
    global $USER;

    if (!$USER->IsAdmin()) {
        return;
    }

    $moduleMenu[] = [
        'parent_menu' => 'global_menu_settings',
        'section' => 'picom_migrations',
        'sort' => 2100,
        'text' => Loc::getMessage('MIGRATIONS'),
        'title' => Loc::getMessage('MIGRATIONS_TITLE'),
        'url' => '/bitrix/admin/migrations.php?lang='.LANG,
        'icon' => 'highloadblock_menu_icon',
        'page_icon' => 'highloadblock_menu_icon',
    ];
});
