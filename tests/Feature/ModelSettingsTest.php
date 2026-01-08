<?php

namespace JomiGomes\LaravelSettings\Tests\Feature;

use JomiGomes\LaravelSettings\Models\Setting;
use JomiGomes\LaravelSettings\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use JomiGomes\LaravelSettings\SettingsServiceProvider;

class ModelSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [SettingsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->setUpUserTable();

        config()->set('settings', [
            'user' => [
                'preferences' => [
                    'theme' => [
                        'value' => 'light',
                        'type' => Setting::TYPE_STRING,
                    ],
                    'language' => [
                        'value' => 'en',
                        'type' => Setting::TYPE_STRING,
                    ],
                ],
                'notifications' => [
                    'email_enabled' => [
                        'value' => true,
                        'type' => Setting::TYPE_BOOLEAN,
                    ],
                    'frequency' => [
                        'value' => 'daily',
                        'type' => Setting::TYPE_STRING,
                    ],
                ],
            ],
        ]);
    }

    protected function setUpUserTable()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_get_model_setting_using_static_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $setting = Setting::get('preferences.theme', $user);

        $this->assertNotNull($setting);
        $this->assertEquals('preferences.theme', $setting->setting);
        $this->assertEquals('light', $setting->value);
        $this->assertEquals(Setting::TYPE_STRING, $setting->type);
    }

    /** @test */
    public function it_can_get_model_setting_using_trait_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $setting = $user->getSetting('preferences.theme');

        $this->assertNotNull($setting);
        $this->assertEquals('light', $setting->value);
    }

    /** @test */
    public function it_can_set_model_setting_using_static_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $setting = Setting::set('preferences.theme', 'dark', $user);

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals('dark', $setting->value);
        $this->assertFalse($setting->isDefault);
        
        $this->assertDatabaseHas('settings', [
            'settingable_id' => $user->id,
            'settingable_type' => User::class,
            'setting' => 'preferences.theme',
        ]);
    }

    /** @test */
    public function it_can_set_model_setting_using_trait_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $setting = $user->setSetting('preferences.theme', 'dark');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals('dark', $setting->value);
    }

    /** @test */
    public function it_can_get_all_model_settings_using_static_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $settings = Setting::getAllScoped($user);

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(4, $settings);
    }

    /** @test */
    public function it_can_get_all_model_settings_using_trait_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $settings = $user->getAllSettings();

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(4, $settings);
    }

    /** @test */
    public function it_can_get_filtered_model_settings_using_static_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $settings = Setting::getFiltered($user, 'preferences');

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(2, $settings);
        $this->assertTrue($settings->contains('setting', 'preferences.theme'));
        $this->assertTrue($settings->contains('setting', 'preferences.language'));
    }

    /** @test */
    public function it_can_get_filtered_model_settings_using_trait_method()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $settings = $user->getFilteredSettings('preferences');

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(2, $settings);
    }

    /** @test */
    public function it_isolates_settings_between_different_model_instances()
    {
        $user1 = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $user2 = User::create(['name' => 'Jane', 'email' => 'jane@example.com']);

        $user1->setSetting('preferences.theme', 'dark');
        $user2->setSetting('preferences.theme', 'light');

        $user1Setting = $user1->getSetting('preferences.theme');
        $user2Setting = $user2->getSetting('preferences.theme');

        $this->assertEquals('dark', $user1Setting->value);
        $this->assertEquals('light', $user2Setting->value);
    }

    /** @test */
    public function it_merges_default_and_custom_settings_for_model()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('preferences.theme', 'dark');

        $settings = $user->getAllSettings();

        $this->assertCount(4, $settings);
        
        $themeSetting = $settings->firstWhere('setting', 'preferences.theme');
        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $themeSetting);
        $this->assertFalse($themeSetting->isDefault);
        $this->assertEquals('dark', $themeSetting->value);
        
        $languageSetting = $settings->firstWhere('setting', 'preferences.language');
        $this->assertTrue($languageSetting->isDefault);
        $this->assertEquals('en', $languageSetting->value);
    }

    /** @test */
    public function it_deletes_setting_when_set_back_to_default_for_model()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('preferences.theme', 'dark');
        
        $this->assertDatabaseHas('settings', [
            'settingable_id' => $user->id,
            'settingable_type' => User::class,
            'setting' => 'preferences.theme',
        ]);

        $setting = $user->setSetting('preferences.theme', 'light');

        $this->assertDatabaseMissing('settings', [
            'settingable_id' => $user->id,
            'settingable_type' => User::class,
            'setting' => 'preferences.theme',
        ]);
        
        $this->assertEquals('light', $setting->value);
    }

    /** @test */
    public function it_validates_model_has_settings_trait()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The model must use the HasSettings trait');

        $modelWithoutTrait = new class extends \Illuminate\Database\Eloquent\Model {};

        Setting::get('some.setting', $modelWithoutTrait);
    }

    /** @test */
    public function it_uses_snake_case_for_model_scope()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $this->assertEquals('user', $user->getSettingsScope());
    }

    /** @test */
    public function it_handles_all_types_for_model_settings()
    {
        config()->set('settings.user.test_types', [
            'integer_val' => ['value' => 42, 'type' => Setting::TYPE_INTEGER],
            'double_val' => ['value' => 3.14, 'type' => Setting::TYPE_DOUBLE],
            'boolean_val' => ['value' => true, 'type' => Setting::TYPE_BOOLEAN],
            'array_val' => ['value' => [1, 2, 3], 'type' => Setting::TYPE_ARRAY],
            'collection_val' => ['value' => collect(['a' => 1]), 'type' => Setting::TYPE_COLLECTION],
            'datetime_val' => ['value' => Carbon::now(), 'type' => Setting::TYPE_DATETIME],
            'object_val' => ['value' => (object)['key' => 'val'], 'type' => Setting::TYPE_OBJECT],
        ]);

        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('test_types.integer_val', 100);
        $user->setSetting('test_types.double_val', 2.71);
        $user->setSetting('test_types.boolean_val', false);
        $user->setSetting('test_types.array_val', [4, 5, 6]);
        $user->setSetting('test_types.collection_val', collect(['b' => 2]));
        $user->setSetting('test_types.datetime_val', Carbon::tomorrow());
        $user->setSetting('test_types.object_val', (object)['new' => 'obj']);

        $this->assertEquals(100, $user->getSetting('test_types.integer_val')->value);
        $this->assertEquals(2.71, $user->getSetting('test_types.double_val')->value);
        $this->assertEquals(false, $user->getSetting('test_types.boolean_val')->value);
        $this->assertEquals([4, 5, 6], $user->getSetting('test_types.array_val')->value);
        $this->assertInstanceOf(Collection::class, $user->getSetting('test_types.collection_val')->value);
        $this->assertInstanceOf(Carbon::class, $user->getSetting('test_types.datetime_val')->value);
        $this->assertIsObject($user->getSetting('test_types.object_val')->value);
    }

    /** @test */
    public function it_updates_existing_model_setting()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('preferences.theme', 'dark');
        $user->setSetting('preferences.theme', 'blue');

        $setting = $user->getSetting('preferences.theme');
        
        $this->assertEquals('blue', $setting->value);
        
        $this->assertEquals(1, Setting::where('settingable_id', $user->id)
            ->where('setting', 'preferences.theme')
            ->count());
    }

    /** @test */
    public function it_handles_nested_groups_for_model_settings()
    {
        config()->set('settings.user.features', [
            'editor' => [
                'advanced' => [
                    'auto_save' => ['value' => true, 'type' => Setting::TYPE_BOOLEAN],
                    'interval' => ['value' => 30, 'type' => Setting::TYPE_INTEGER],
                ],
            ],
        ]);

        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $settings = $user->getFilteredSettings('features.editor.advanced');

        $this->assertCount(2, $settings);
        $this->assertTrue($settings->contains('setting', 'features.editor.advanced.auto_save'));
        $this->assertTrue($settings->contains('setting', 'features.editor.advanced.interval'));
    }
}
