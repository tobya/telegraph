<?php

use DefStudio\Telegraph\Facades\Telegraph as Facade;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Telegraph;

use function Pest\Laravel\artisan;

use Symfony\Component\Console\Command\Command;

test('bot token is required', function () {
    artisan('telegraph:new-bot')
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "")
        ->expectsOutput('Token cannot be empty')
        ->assertExitCode(Command::FAILURE);
});

it('can create a new bot', function () {
    artisan('telegraph:new-bot')
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "foo")
        ->expectsQuestion("Do you want to add a chat to this bot?", false)
        ->expectsQuestion("Do you want to setup a webhook for this bot?", false)
        ->assertExitCode(Command::SUCCESS);


    expect(TelegraphBot::first())
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('foo');
});

it('assigns a default name if not provided', function () {
    artisan('telegraph:new-bot')
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "")
        ->expectsQuestion("Do you want to add a chat to this bot?", false)
        ->expectsQuestion("Do you want to setup a webhook for this bot?", false)
        ->assertExitCode(Command::SUCCESS);


    expect(TelegraphBot::first())
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('Bot #1');
});

it('can assign a chat to the new bot', function () {
    artisan('telegraph:new-bot')
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "foo")
        ->expectsQuestion("Do you want to add a chat to this bot?", true)
        ->expectsQuestion("Enter the chat ID - press [x] to abort", "888999444")
        ->expectsQuestion("Enter the chat name (optional)", 'bar')
        ->expectsQuestion("Do you want to setup a webhook for this bot?", false)
        ->assertExitCode(Command::SUCCESS);


    /** @var TelegraphBot|null $bot */
    $bot = TelegraphBot::first();

    expect($bot)
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('foo');

    expect($bot->chats()->first())
        ->chat_id->toBe('888999444');
});

it('keeps asking for the chat name until pressed x', function () {
    artisan('telegraph:new-bot')
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "foo")
        ->expectsQuestion("Do you want to add a chat to this bot?", true)
        ->expectsQuestion("Enter the chat ID - press [x] to abort", "")
        ->expectsQuestion("Enter the chat ID - press [x] to abort", "")
        ->expectsQuestion("Enter the chat ID - press [x] to abort", "x")
        ->expectsQuestion("Do you want to setup a webhook for this bot?", false)
        ->assertExitCode(Command::SUCCESS);


    /** @var TelegraphBot|null $bot */
    $bot = TelegraphBot::first();

    expect($bot)
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('foo');

    expect($bot->chats()->count())->toBe(0);
});

it('can register the new bot webhook', function () {
    withfakeUrl();

    Facade::fake([
        Telegraph::ENDPOINT_SET_WEBHOOK => [
            'ok' => true,
        ],
    ]);

    artisan('telegraph:new-bot')
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "")
        ->expectsQuestion("Do you want to add a chat to this bot?", false)
        ->expectsQuestion("Do you want to setup a webhook for this bot?", true)
        ->assertExitCode(Command::SUCCESS);


    expect(TelegraphBot::first())
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('Bot #1');

    Facade::assertRegisteredWebhook();
});

it('can register the new bot webhook dropping pending updates', function () {
    withfakeUrl();

    Facade::fake([
        Telegraph::ENDPOINT_SET_WEBHOOK => [
            'ok' => true,
        ],
    ]);

    artisan('telegraph:new-bot', ['--drop-pending-updates' => true])
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "")
        ->expectsQuestion("Do you want to add a chat to this bot?", false)
        ->expectsQuestion("Do you want to setup a webhook for this bot?", true)
        ->assertExitCode(Command::SUCCESS);


    expect(TelegraphBot::first())
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('Bot #1');

    Facade::assertRegisteredWebhook([
        'drop_pending_updates' => true,
    ], false);
});

it('can register the new bot webhook settings its max connections', function () {
    withfakeUrl();

    Facade::fake([
        Telegraph::ENDPOINT_SET_WEBHOOK => [
            'ok' => true,
        ],
    ]);

    artisan('telegraph:new-bot', ['--max-connections' => 99])
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "")
        ->expectsQuestion("Do you want to add a chat to this bot?", false)
        ->expectsQuestion("Do you want to setup a webhook for this bot?", true)
        ->assertExitCode(Command::SUCCESS);


    expect(TelegraphBot::first())
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('Bot #1');

    Facade::assertRegisteredWebhook([
        'max_connections' => 99,
    ], false);
});

it('can register the new bot webhook settings its secret token', function () {
    withfakeUrl();

    Facade::fake([
        Telegraph::ENDPOINT_SET_WEBHOOK => [
            'ok' => true,
        ],
    ]);

    artisan('telegraph:new-bot', ['--secret' => 'foo'])
        ->expectsOutput('You are about to create a new Telegram Bot')
        ->expectsQuestion("Please, enter the bot token", "123456789")
        ->expectsQuestion("Enter the bot name (optional)", "")
        ->expectsQuestion("Do you want to add a chat to this bot?", false)
        ->expectsQuestion("Do you want to setup a webhook for this bot?", true)
        ->assertExitCode(Command::SUCCESS);


    expect(TelegraphBot::first())
        ->not->toBeNull()
        ->token->toBe('123456789')
        ->name->toBe('Bot #1');

    Facade::assertRegisteredWebhook([
        'secret_token' => 'foo',
    ], false);
});
