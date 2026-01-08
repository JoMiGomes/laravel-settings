<?php

namespace JomiGomes\LaravelSettings\Tests\Feature;

use JomiGomes\LaravelSettings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use JomiGomes\LaravelSettings\SettingsServiceProvider;

class NonModelSettingsTest extends TestCase
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

        config()->set('settings', [
            'system' => [
                'features' => [
                    'enable_notifications' => [
                        'value' => true,
                        'type' => Setting::TYPE_BOOLEAN,
                    ],
                    'notification_threshold' => [
                        'value' => 5,
                        'type' => Setting::TYPE_INTEGER,
                    ],
                ],
                'appearance' => [
                    'theme_color' => [
                        'value' => 'blue',
                        'type' => Setting::TYPE_STRING,
                    ],
                ],
            ],
            'app' => [
                'maintenance' => [
                    'enabled' => [
                        'value' => false,
                        'type' => Setting::TYPE_BOOLEAN,
                    ],
                    'message' => [
                        'value' => 'Under maintenance',
                        'type' => Setting::TYPE_STRING,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_get_default_setting_from_manifesto()
    {
        $setting = Setting::get('features.enable_notifications', 'system');

        $this->assertNotNull($setting);
        $this->assertEquals('features.enable_notifications', $setting->setting);
        $this->assertEquals(true, $setting->value);
        $this->assertEquals(Setting::TYPE_BOOLEAN, $setting->type);
        $this->assertEquals('system', $setting->scope);
    }

    /** @test */
    public function it_can_set_non_model_setting()
    {
        $setting = Setting::set('features.enable_notifications', false, 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals(false, $setting->value);
        $this->assertEquals(Setting::TYPE_BOOLEAN, $setting->type);
        $this->assertFalse($setting->isDefault);

        $this->assertDatabaseHas('settings', [
            'scope' => 'system',
            'setting' => 'features.enable_notifications',
            'type' => Setting::TYPE_BOOLEAN,
        ]);
    }

    /** @test */
    public function it_returns_default_when_setting_back_to_default_value()
    {
        Setting::set('features.enable_notifications', false, 'system');
        
        $this->assertDatabaseHas('settings', [
            'scope' => 'system',
            'setting' => 'features.enable_notifications',
        ]);

        $setting = Setting::set('features.enable_notifications', true, 'system');

        $this->assertDatabaseMissing('settings', [
            'scope' => 'system',
            'setting' => 'features.enable_notifications',
        ]);
        
        $this->assertEquals(true, $setting->value);
    }

    /** @test */
    public function it_can_get_all_scoped_settings()
    {
        $settings = Setting::getAllScoped('system');

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(3, $settings);
        
        $notificationSetting = $settings->firstWhere('setting', 'features.enable_notifications');
        $this->assertNotNull($notificationSetting);
        $this->assertEquals(true, $notificationSetting->value);
    }

    /** @test */
    public function it_merges_default_and_non_default_settings_in_get_all_scoped()
    {
        Setting::set('features.enable_notifications', false, 'system');
        
        $settings = Setting::getAllScoped('system');

        $this->assertCount(3, $settings);
        
        $notificationSetting = $settings->firstWhere('setting', 'features.enable_notifications');
        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $notificationSetting);
        $this->assertFalse($notificationSetting->isDefault);
        $this->assertEquals(false, $notificationSetting->value);
        
        $thresholdSetting = $settings->firstWhere('setting', 'features.notification_threshold');
        $this->assertTrue($thresholdSetting->isDefault);
        $this->assertEquals(5, $thresholdSetting->value);
    }

    /** @test */
    public function it_can_get_filtered_settings()
    {
        $settings = Setting::getFiltered('system', 'features');

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(2, $settings);
        
        $this->assertTrue($settings->contains('setting', 'features.enable_notifications'));
        $this->assertTrue($settings->contains('setting', 'features.notification_threshold'));
    }

    /** @test */
    public function it_filters_nested_groups_correctly()
    {
        config()->set('settings.system.nested', [
            'level1' => [
                'level2' => [
                    'setting1' => [
                        'value' => 'test',
                        'type' => Setting::TYPE_STRING,
                    ],
                    'setting2' => [
                        'value' => 123,
                        'type' => Setting::TYPE_INTEGER,
                    ],
                ],
            ],
        ]);

        $settings = Setting::getFiltered('system', 'nested.level1.level2');

        $this->assertCount(2, $settings);
        $this->assertTrue($settings->contains('setting', 'nested.level1.level2.setting1'));
        $this->assertTrue($settings->contains('setting', 'nested.level1.level2.setting2'));
    }

    /** @test */
    public function it_throws_exception_for_non_existent_scope()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Setting 'Scope' not found in the config for scope 'non_existent'");

        Setting::get('some.setting', 'non_existent');
    }

    /** @test */
    public function it_throws_exception_for_non_existent_setting()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Setting 'non.existent' not found in the config for scope 'system'");

        Setting::get('non.existent', 'system');
    }

    /** @test */
    public function it_returns_null_for_setting_not_in_manifesto_and_not_in_database()
    {
        $setting = Setting::get('features.enable_notifications', 'system');
        $this->assertNotNull($setting);
    }

    /** @test */
    public function it_validates_type_when_setting_value()
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('features.enable_notifications', 'not_a_boolean', 'system');
    }

    /** @test */
    public function it_handles_integer_type()
    {
        $setting = Setting::set('features.notification_threshold', 10, 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals(10, $setting->value);
        $this->assertEquals(Setting::TYPE_INTEGER, $setting->type);
    }

    /** @test */
    public function it_handles_string_type()
    {
        $setting = Setting::set('appearance.theme_color', 'red', 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals('red', $setting->value);
        $this->assertEquals(Setting::TYPE_STRING, $setting->type);
    }

    /** @test */
    public function it_handles_array_type()
    {
        config()->set('settings.system.test_array', [
            'value' => [1, 2, 3],
            'type' => Setting::TYPE_ARRAY,
        ]);

        $setting = Setting::set('test_array', [4, 5, 6], 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals([4, 5, 6], $setting->value);
        $this->assertEquals(Setting::TYPE_ARRAY, $setting->type);
    }

    /** @test */
    public function it_handles_collection_type()
    {
        config()->set('settings.system.test_collection', [
            'value' => collect(['a' => 1]),
            'type' => Setting::TYPE_COLLECTION,
        ]);

        $newCollection = collect(['b' => 2]);
        $setting = Setting::set('test_collection', $newCollection, 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertInstanceOf(Collection::class, $setting->value);
        $this->assertEquals(['b' => 2], $setting->value->toArray());
    }

    /** @test */
    public function it_handles_datetime_type()
    {
        config()->set('settings.system.test_datetime', [
            'value' => Carbon::now(),
            'type' => Setting::TYPE_DATETIME,
        ]);

        $newDate = Carbon::tomorrow();
        $setting = Setting::set('test_datetime', $newDate, 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertInstanceOf(Carbon::class, $setting->value);
        $this->assertTrue($newDate->isSameDay($setting->value));
    }

    /** @test */
    public function it_handles_object_type()
    {
        config()->set('settings.system.test_object', [
            'value' => (object) ['key' => 'value'],
            'type' => Setting::TYPE_OBJECT,
        ]);

        $newObject = (object) ['new_key' => 'new_value'];
        $setting = Setting::set('test_object', $newObject, 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertIsObject($setting->value);
        $this->assertEquals('new_value', $setting->value->new_key);
    }

    /** @test */
    public function it_handles_double_type()
    {
        config()->set('settings.system.test_double', [
            'value' => 3.14,
            'type' => Setting::TYPE_DOUBLE,
        ]);

        $setting = Setting::set('test_double', 2.71, 'system');

        $this->assertInstanceOf(\JomiGomes\LaravelSettings\DataTransferObjects\SettingData::class, $setting);
        $this->assertEquals(2.71, $setting->value);
        $this->assertEquals(Setting::TYPE_DOUBLE, $setting->type);
    }
}
