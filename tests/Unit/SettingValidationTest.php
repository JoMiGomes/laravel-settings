<?php

namespace JomiGomes\LaravelSettings\Tests\Unit;

use JomiGomes\LaravelSettings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use JomiGomes\LaravelSettings\SettingsServiceProvider;

class SettingValidationTest extends TestCase
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
            'test_scope' => [
                'integer_setting' => [
                    'value' => 42,
                    'type' => Setting::TYPE_INTEGER,
                ],
                'double_setting' => [
                    'value' => 3.14,
                    'type' => Setting::TYPE_DOUBLE,
                ],
                'boolean_setting' => [
                    'value' => true,
                    'type' => Setting::TYPE_BOOLEAN,
                ],
                'string_setting' => [
                    'value' => 'hello',
                    'type' => Setting::TYPE_STRING,
                ],
                'array_setting' => [
                    'value' => [1, 2, 3],
                    'type' => Setting::TYPE_ARRAY,
                ],
                'collection_setting' => [
                    'value' => collect(['key' => 'value']),
                    'type' => Setting::TYPE_COLLECTION,
                ],
                'object_setting' => [
                    'value' => (object) ['key' => 'value'],
                    'type' => Setting::TYPE_OBJECT,
                ],
                'datetime_setting' => [
                    'value' => Carbon::now(),
                    'type' => Setting::TYPE_DATETIME,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_throws_exception_for_invalid_integer_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('integer_setting', 'not_an_integer', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_double_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('double_setting', 'not_a_double', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_boolean_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('boolean_setting', 'not_a_boolean', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_string_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('string_setting', 123, 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_array_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('array_setting', 'not_an_array', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_collection_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('collection_setting', 'not_a_collection', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_object_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('object_setting', 'not_an_object', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_for_invalid_datetime_type()
    {
        $this->expectException(InvalidArgumentException::class);
        Setting::set('datetime_setting', 'not_a_valid_date', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_when_setting_not_found_in_manifesto()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Setting 'non_existent' not found in the config for scope 'test_scope'");
        
        Setting::get('non_existent', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_when_scope_not_found_in_manifesto()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Setting 'Scope' not found in the config for scope 'non_existent_scope'");
        
        Setting::get('some_setting', 'non_existent_scope');
    }

    /** @test */
    public function it_throws_exception_when_type_missing_in_manifesto()
    {
        config()->set('settings.test_scope.no_type_setting', [
            'value' => 'test',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Type not found for setting 'no_type_setting'");
        
        Setting::get('no_type_setting', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_when_value_missing_in_manifesto()
    {
        config()->set('settings.test_scope.no_value_setting', [
            'type' => Setting::TYPE_STRING,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Default value not found for setting 'no_value_setting'");
        
        Setting::get('no_value_setting', 'test_scope');
    }

    /** @test */
    public function it_throws_exception_when_declared_type_mismatches_default_value()
    {
        config()->set('settings.test_scope.mismatched_setting', [
            'value' => 'string_value',
            'type' => Setting::TYPE_INTEGER,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Declared type 'integer' for setting 'mismatched_setting' does not match the actual default value type ('string')");
        
        Setting::get('mismatched_setting', 'test_scope');
    }

    /** @test */
    public function it_accepts_valid_integer_values()
    {
        $setting = Setting::set('integer_setting', 100, 'test_scope');
        $this->assertEquals(100, $setting->value);
    }

    /** @test */
    public function it_accepts_valid_double_values()
    {
        $setting = Setting::set('double_setting', 2.71, 'test_scope');
        $this->assertEquals(2.71, $setting->value);
    }

    /** @test */
    public function it_accepts_valid_boolean_values()
    {
        $setting = Setting::set('boolean_setting', false, 'test_scope');
        $this->assertEquals(false, $setting->value);
    }

    /** @test */
    public function it_accepts_valid_string_values()
    {
        $setting = Setting::set('string_setting', 'new_value', 'test_scope');
        $this->assertEquals('new_value', $setting->value);
    }

    /** @test */
    public function it_accepts_valid_array_values()
    {
        $setting = Setting::set('array_setting', [4, 5, 6], 'test_scope');
        $this->assertEquals([4, 5, 6], $setting->value);
    }

    /** @test */
    public function it_accepts_valid_collection_values()
    {
        $collection = collect(['new' => 'data']);
        $setting = Setting::set('collection_setting', $collection, 'test_scope');
        $this->assertInstanceOf(Collection::class, $setting->value);
    }

    /** @test */
    public function it_accepts_valid_object_values()
    {
        $object = (object) ['new' => 'object'];
        $setting = Setting::set('object_setting', $object, 'test_scope');
        $this->assertIsObject($setting->value);
    }

    /** @test */
    public function it_accepts_valid_datetime_values()
    {
        $date = Carbon::tomorrow();
        $setting = Setting::set('datetime_setting', $date, 'test_scope');
        $this->assertInstanceOf(Carbon::class, $setting->value);
    }
}
