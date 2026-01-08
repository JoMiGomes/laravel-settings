<?php

namespace YellowParadox\LaravelSettings\Tests;

use YellowParadox\LaravelSettings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use stdClass;
use Orchestra\Testbench\TestCase;
use YellowParadox\LaravelSettings\SettingsServiceProvider;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [SettingsServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

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
                    'value' => collect([
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ]),
                    'type' => Setting::TYPE_COLLECTION,
                ],

                'object_setting' => [
                    'value' => (object) ['key' => 'value'],
                    'type' => Setting::TYPE_OBJECT,
                ],

                'datetime_setting' => [
                    'value' => Carbon::tomorrow(),
                    'type' => Setting::TYPE_DATETIME,
                ],
            ],
            'grouped_nested_settings' => [
                'feature_1' => [
                    'general' => [
                        'general_1' => [
                            'value' => 1,
                            'type' => Setting::TYPE_INTEGER,
                        ],
                        'general_2' => [
                            'value' => 2,
                            'type' => Setting::TYPE_INTEGER,
                        ],
                    ],
                    'setting_1' => [
                        'value' => 1,
                        'type' => Setting::TYPE_INTEGER,
                    ],
                    'setting_2' => [
                        'value' => 2,
                        'type' => Setting::TYPE_INTEGER,
                    ],
                ],
                'feature_2' => [
                    'setting_1' => [
                        'value' => 3,
                        'type' => Setting::TYPE_INTEGER,
                    ],
                    'setting_2' => [
                        'value' => 4,
                        'type' => Setting::TYPE_INTEGER,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_allows_valid_integer_setting(): void
    {
        $settingBeforeChange = Setting::get('integer_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);
        $this->assertTrue($settingBeforeChange->isDefault);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('integer_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);
        $this->assertFalse($newSetting->isDefault);

        $settingAfterChange = Setting::get('integer_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_integer_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('integer_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_allows_valid_double_setting(): void
    {
        $settingBeforeChange = Setting::get('double_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('double_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('double_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_double_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('double_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_allows_valid_boolean_setting(): void
    {
        $settingBeforeChange = Setting::get('boolean_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('boolean_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('boolean_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_boolean_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('boolean_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_allows_valid_string_setting(): void
    {
        $settingBeforeChange = Setting::get('string_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('string_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('string_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_string_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('string_setting', 123, 'test_scope');
    }

    /** @test */
    public function it_allows_valid_array_setting(): void
    {
        $settingBeforeChange = Setting::get('array_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('array_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('array_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_array_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('array_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_allows_valid_collection_setting(): void
    {
        $settingBeforeChange = Setting::get('collection_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('collection_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('collection_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_collection_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('collection_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_allows_valid_object_setting(): void
    {
        $settingBeforeChange = Setting::get('object_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('object_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('object_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_object_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('object_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_allows_valid_datetime_setting(): void
    {
        $settingBeforeChange = Setting::get('datetime_setting', 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $settingBeforeChange);

        $newValue = $this->getDifferentValue($settingBeforeChange->value);
        $newSetting = Setting::set('datetime_setting', $newValue, 'test_scope');

        $this->assertInstanceOf(\YellowParadox\LaravelSettings\DataTransferObjects\SettingData::class, $newSetting);

        $settingAfterChange = Setting::get('datetime_setting', 'test_scope');

        $this->assertEquals($newValue, $settingAfterChange->value);
    }

    /** @test */
    public function it_throws_exception_for_invalid_datetime_setting(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Setting::set('datetime_setting', 'invalid_value', 'test_scope');
    }

    /** @test */
    public function it_retrieves_all_scoped_settings_from_manifesto_and_database(): void
    {
        $defaultSettings = Setting::getAllScoped('grouped_nested_settings');

        $newSetting1 = Setting::set('feature_1.general.general_1', 42, 'grouped_nested_settings');
        $newSetting2 = Setting::set('feature_2.setting_2', 12, 'grouped_nested_settings');

        $updatedSettings = Setting::getAllScoped('grouped_nested_settings');

        $this->assertCount(count($defaultSettings), $updatedSettings);

        $this->assertSettingEquals($updatedSettings, 'feature_1.general.general_1', $newSetting1->value, Setting::TYPE_INTEGER);
        $this->assertSettingEquals($updatedSettings, 'feature_2.setting_2', $newSetting2->value, Setting::TYPE_INTEGER);
    }

    /**
     * Get a value different from the provided value.
     */
    protected function getDifferentValue(mixed $value): mixed
    {
        if (is_numeric($value)) {

            return is_int($value) ? $value + 1 : -$value;
        }

        if (is_string($value)) {

            return $value.'_changed';
        }

        if (is_bool($value)) {

            return ! $value;
        }

        if (is_array($value)) {

            return array_merge($value, ['new_element' => 'new_value']);
        }

        if ($value instanceof Collection) {

            return $value->merge(['new_key' => 'new_value']);
        }

        if (is_object($value) && ! ($value instanceof Carbon) && ! ($value instanceof Collection)) {

            return (object) ['key' => 'new_value'];
        }

        if ($value instanceof Carbon) {
            return $value->copy()->addDay();
        }

        return $value;
    }

    /**
     * Assert that a setting is present in the collection with the correct value and type.
     */
    protected function assertSettingEquals(Collection $settings, string $settingName, mixed $expectedValue, string $expectedType): void
    {
        $setting = $settings->firstWhere('setting', $settingName);

        $this->assertNotNull($setting, "Setting '$settingName' not found in the collection.");

        $this->assertSame($expectedValue, $setting->value, "Setting '$settingName' has incorrect value.");
        $this->assertSame($expectedType, $setting->type, "Setting '$settingName' has incorrect type.");
    }

    /** @test */
    public function it_retrieves_filtered_settings_from_manifesto_and_database(): void
    {
        $scope = 'grouped_nested_settings';
        $filter = 'feature_1.general';

        $filteredSettings = Setting::getFiltered($scope, $filter);

        $this->assertEquals(2, $filteredSettings->count());
        $this->assertTrue($filteredSettings->every(fn ($setting) => $setting instanceof \YellowParadox\LaravelSettings\DataTransferObjects\SettingData));

        Setting::set('feature_1.general.general_1', 10, $scope);
        Setting::set('feature_1.general.general_2', 20, $scope);

        $filteredSettingsWithNonDefaults = Setting::getFiltered($scope, $filter);

        $this->assertEquals(2, $filteredSettingsWithNonDefaults->count());
        $this->assertTrue($filteredSettingsWithNonDefaults->every(fn ($setting) => $setting instanceof \YellowParadox\LaravelSettings\DataTransferObjects\SettingData));
    }
}
