# Settings Feature Documentation  
  
## Table of Contents  

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Supported Types](#supported-types)
4. [The Manifesto](#the-manifesto)  
   4.1 [Customizing the Manifesto File Name](#customizing-the-manifesto-file-name)  
   4.2 [Defining Scopes](#defining-scopes)  
   4.3 [Defining Groups](#defining-groups)  
   4.4 [Populating Groups with Settings](#populating-groups-with-settings)
5. [Setting Settings](#setting-settings)  
   5.1 [Non-Model Related Scopes](#non-model-related-scopes)  
   5.2 [Model Related Scopes](#model-related-scopes)  
   5.3 [Validation](#validation)
6. [Getting Settings](#getting-settings)  
   6.1 [Retrieve a Single Setting](#retrieve-a-single-setting)  
   6.2 [Retrieve All Settings Within a Scope](#retrieve-all-settings-within-a-scope)  
   6.3 [Retrieve Filtered Settings](#retrieve-filtered-settings)
7. [Advanced Features](#advanced-features)  
   7.1 [Events System](#events-system)  
   7.2 [Settings Facade](#settings-facade)  
   7.3 [Artisan Commands](#artisan-commands)  
   7.4 [Caching](#caching)
8. [Conclusion](#conclusion)


## Introduction  
  
Our settings package revolutionizes the way you manage configuration values in your application.  
  
It introduces the concept of scoping settings, allowing you to associate them with specific models or application segments.  
Whether you need global settings for the entire system or user-specific configurations, our package provides a streamlined solution.  
  
The key innovation lies in the "manifesto", a configuration file that serves as the single source of truth for structure and default values. This approach ensures organised, scoped, and typed settings, preventing the database from being cluttered with redundant records.  

In essence, our settings package offers a flexible and efficient way to manage, scope, and organise application configurations, bringing clarity and structure to your settings. 

Get set to set some settings! 😉 

<small>[Back to top](#table-of-contents)</small>

## Installation

Install the package via Composer:

```bash
composer require jomigomes/laravel-settings
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=settings-config
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag=settings-migrations
php artisan migrate
```

## Supported Types  
  
The settings package supports various data types, providing versatility for different use cases. Here are the supported types:  
  
- **Integer**  
- **Double**  
- **Boolean**  
- **String**  
- **Array**  
- **Collection:** Instances of `Illuminate\Support\Collection`.  
- **Datetime:** Instances of `Carbon`, representing date and time values.
- **Object:** While you can technically save any kind of object, it's strongly recommended to avoid saving complex objects like models or other class instances that might undergo changes elsewhere in the application. If you choose to save an object, make sure you know what you are doing.  

<small>[Back to top](#table-of-contents)</small>
  
## The Manifesto

The manifesto stands as a crucial cornerstone within the settings package, offering a centralized configuration hub for your Laravel application.  
  
It's here that you articulate the blueprint for every setting, defining their default values and specifying the types of values they can hold.  

<br>

### Customizing the Manifesto File Name  
  
By default, the manifesto file is named `settings.php`. However, you have the flexibility to customize this filename according to your preferences. This customization is achieved by modifying a constant value in the `Settings` class.  
  
```php  
// Namespace: JomiGomes\LaravelSettings\Models\Setting
  
class Setting  
{  
  
     /** * The filename for the settings manifesto.
     *
     * @var string
     * */
     public const SETTINGS_MANIFESTO_FILE_NAME = 'custom_manifesto_filename';  
 
}  
```  

<br>

### Defining Scopes  
  
Scopes are defined as associative arrays within the manifesto file (`settings.php`). Each scope acts as a container for related settings and is identified by a unique key. Here's an example structure:  
  
```php  
// config/settings.php  
  
'system' => [ // Scope not related to any model  
	 // Placeholder for settings in the 'system' scope
        ],  
'user' => [ // Scope related to the User model  
	// Placeholder for settings in the 'user' scope
	],  
'working_session' => [ // Scope related to the WorkingSession model (use snake case)  
	// Placeholder for settings in the 'working_session' scope
	],  
```  
  
In this example:  
  
- The `'system'` scope is not related to any specific model and serves as a container for settings not tied to model entities.  
- The `'user'` and `'working_session'` scopes are associated with the User and WorkingSession models, respectively.  
  
**⚠️ Important:** When scoping settings related to models, it is a requirement to use the snake case name of the corresponding model as the scope identifier.  

<br>

### Defining Groups  
  
Within each scope of the manifesto, groups act as containers for organising related settings. Groups help maintain a structured and logical organisation of configuration values specific to a given scope.  
  
Groups are defined as associative arrays within a scope in the manifesto file (`settings.php`). Each group serves as a placeholder for settings and is identified by a unique key. Here's an example structure:  
  
```php  
// config/settings.php  
  
'user' => [ // Scope related to the User model  
'personal' => [ // Group for personal settings
	'setting_1' => [],
	'setting_2' => [],
	],
	'feature_name' => [ // Group for settings related to a specific feature
		'setting_1' => [],
		'setting_2' => [],
		],
	],  
```  
  
In this example:  
  
- The `'personal'` group is a container for settings specific to personal configurations within the 'user' scope.  
- The `'feature_name'` group is a container for settings related to a specific feature within the 'user' scope.  
  
The manifesto supports multiple levels of groups, allowing you to create nested structures for even more organised settings. Each level represents a hierarchy within a scope, enhancing the flexibility of your configuration. Here's an example:  
  
```php  
// config/settings.php  
  
'user' => [ // Scope related to the User model  
	'personal' => [ // First-level group for personal settings
		'setting_1' => [],
		'setting_2' => [],
		'nested_group' => [ // Second-level nested group
			'setting_1' => [],
			'setting_2' => [],
			],
		],
		'feature_name' => [ // Another first-level group for settings related to a specific feature
			'setting_1' => [],
			],
		],  
```  

<br>

### Populating Groups with Settings  
  
Within each group, you can populate settings with their default values. This creates a clear structure for managing and maintaining configuration values.  
  
Note that each setting must consist of a type and a value. The type is defined by utilizing constants provided by the `Setting` class, ensuring consistency in type handling. Here's an example:  
  
```php  
// config/settings.php  
  
use App\Models\Settings\Setting;  
  
'user' => [ // Scope related to model user  
	'personal' => [
		'setting_1' => [
			'value' => 1, // Value declared in the proper type
			'type' => Setting::TYPE_INTEGER, // Type declared using constants
			],
		'setting_2' => [
			'value' => 'hello',
			'type' => Setting::TYPE_STRING,
			],
		],
	],  
```  
  
<br>  
  
**⚠️ Important:** When dealing with special types such as `Carbon`, collections, or custom objects, it's crucial to declare the setting's value as an instance of the respective object. For example:  
  
```php  
// config/settings.php  
  
use App\Models\Settings\Setting;  
  
'user' => [ // Scope related to model user  
	'feature_name' => [
		'setting_1' => [
			'value' => Carbon::tomorrow(), // Value must be an instance of Carbon
			'type' => Setting::TYPE_DATETIME,
			],
		'setting_2' => [
			'value' => collect([1, 2, 3]), // Value must be an instance of Illuminate\Support\Collection
			'type' => Setting::TYPE_COLLECTION,
			],
		],
	],  
```  

<small>[Back to top](#table-of-contents)</small>

## Setting Settings  

### Non-Model Related Scopes  


To illustrate the process of saving non-model related settings, let's consider a manifesto example where we define these settings :  

```php  
// config/settings.php  
  
use App\Models\Settings\Setting;  
  
'app' => [ // Non-model related scope  
	'features' => [
		'enable_notifications' => [ // The target setting to be updated
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
```  


```php
use JomiGomes\LaravelSettings\Models\Setting;

$setting = Setting::set('features.enable_notifications', false, 'app');  
```  

In this example:  
  
- The first parameter, `'features.enable_notifications'`, uses dot notation to navigate to the specific setting within the 'app' scope.  
- The second parameter, `false`, represents the new value you want to set for the specified setting.  
- The third parameter, `'app'`, specifies the scope in which the setting should be updated.  

The method returns a `SettingData` DTO (Data Transfer Object), so you can access its properties like so:  

```php
$setting->value // false

$setting->type // 'boolean'

$setting->setting // 'features.enable_notifications'

$setting->scope // 'app'

$setting->isDefault // false (true if using default value from config)
```

<br>

### Model Related Scopes  

For model-related scopes, the process involves associating settings directly with a specific model instance.  

Before using model-related scopes, ensure that the model implements the `HasSettings` trait. This trait provides the necessary methods for working with settings in the context of a model:  

```php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JomiGomes\LaravelSettings\Traits\HasSettings;

class User extends Model
{
	use HasSettings;

	// ...

}
```
<br>  
  
To illustrate the process of saving model related settings, let's consider a manifesto example where we define these settings :  
  
```php  
// config/settings.php  
  
use App\Models\Settings\Setting;  
  
'user' => [ // Scope related to model user  
	'personal' => [
		'setting_1' => [ // The target setting to be updated
			'value' => 1,
			'type' => Setting::TYPE_INTEGER,
			],
		'setting_2' => [
			'value' => 'hello',
			'type' => Setting::TYPE_STRING,
			],
		],
	],
```  
  
Now you can use it like:  
  
```php  
use JomiGomes\LaravelSettings\Models\Setting;  
use App\Models\User;  
  
$user = User::find(1);  
  
$setting = Setting::set('personal.setting_1', 10, $user);  
```  
In this example:  
  
- The first parameter, `'personal.setting_1'`, uses dot notation to navigate to the specific setting within the 'app' scope.  
- The second parameter, `10`, represents the new value you want to set for the specified setting.  
- The third parameter, `$user`, specifies the scope in which the setting should be updated.  
    
<br>  
  
or you can do it like:  
  
```php  
use JomiGomes\LaravelSettings\Models\Setting;  
use App\Models\User;  
  
$user = User::find(1);  
  
$setting = $user->setSetting('personal.setting_1', 10);  
```  
  
In this example:  
  
- The first parameter, `'personal.setting_1'`, uses dot notation to navigate to the specific setting within the 'app' scope.  
- The second parameter, `10`, represents the new value you want to set for the specified setting.  
  
*Note that there's no need to specify a scope as we are already using the user instance.*  
  
The method returns a `SettingData` DTO, so you can access its properties like so:  
  
```php  
$setting->value // 10  
  
$setting->type // 'integer'  

$setting->setting // 'personal.setting_1'

$setting->scope // 'user:1' (scope string with model ID)

$setting->isDefault // false
```  
  
  
### Validation  
  
Settings values are validated only by type, to ensure correct *casting*.  

You should validate in your app every value you plan to store in a setting if it's coming from user input.  

 <small>[Back to top](#table-of-contents)</small>
<br>  
  
## Getting Settings  
  
Now we come to the part of retrieving those settings you structured on the manifesto. Either they are defaults and the package will fetch them from the manifesto, or someone somewhere already changed the default values and they are retrieved from the database.  
  
Either way, it's a breeze fetching those settings.  
  
Consider the following manifesto example where settings are organized under both non-model and model-related scopes:  
  
```php  
// config/settings.php  
  
use App\Models\Settings\Setting;  
  
'app' => [ // Non-model related scope  
	'features' => [
		'enable_notifications' => [
			'value' => true,
			'type' => Setting::TYPE_BOOLEAN,
			],
		],
	],  
  
'user' => [ // Model related scope 
	'personal' => [
		'setting_1' => [
			'value' => 1,
			'type' => Setting::TYPE_INTEGER,
			],
		],
	],  
```  
  
### Retrieve a single setting  
To retrieve a single setting, you can use the Settings::get method:  
  
```php  
use JomiGomes\LaravelSettings\Models\Setting;  
use App\Models\User;  
  
// Non-model related scope  
$setting = Settings::get('features.enable_notifications', 'app');  
  
// Model related scope  
$user = User::find(1);  
  
$setting = Settings::get('personal.setting_1', $user);  
  
// or  
  
$setting = $user->getSetting('personal.setting_1');  
```  
  
The method returns a `SettingData` DTO with the following properties:

```php
$setting->value      // The setting value (properly cast to its type)
$setting->type       // The setting type (e.g., 'integer', 'string', 'boolean')
$setting->setting    // The setting key (e.g., 'personal.setting_1')
$setting->scope      // The scope string (e.g., 'app' or 'user:1')
$setting->isDefault  // Boolean indicating if using default value from config
$setting->id         // Database ID (null for default settings)
```

Returns `null` if the setting doesn't exist in the manifesto and database  
  
<br>

### Retrieve all settings within a scope  
  
To retrieve all settings within a specific scope, you can use the Settings::getAllScoped method.  
  
```php  
use JomiGomes\LaravelSettings\Models\Setting;  
use App\Models\User;  
  
// Non-model related scope  
$settingsCollection = Setting::getAllScoped('app');  
  
// Model related scope  
$user = User::find(1); 

$settingsCollection = Setting::getAllScoped($user);

// or

$settingsCollection = $user->getAllSettings();
```

The method returns a `Collection` of `SettingData` DTOs for all settings in that scope. Each item in the collection has the same properties as shown above (`value`, `type`, `setting`, `scope`, `isDefault`, `id`).  

<br>

### Retrieve Filtered Settings  
  
When structuring settings in the manifesto, you will likely use multi-level associative arrays to group settings that are related to some feature or section in your app.  
  
Imagine that you want to retrieve all the settings for a specific group. What about when a group is nested in another group?  
  
Filters to the rescue, here's how it works:  
  
  
Consider the following manifesto example, where we place under the scope `'user'`, a group `'personal'` and nested in that group, another group `'nested_group'`.  
```php  
// config/settings.php  
  
'user' => [ // Scope related to the User model  
	'personal' => [ // First-level group for personal settings
		'setting_1' => [],
		'setting_2' => [],
		'nested_group' => [ // Second-level nested group
			'setting_1' => [],
			'setting_2' => [],
			],
		],
	],
	'feature_name' => [ // Another first-level group for settings related to a specific feature
			'setting_1' => [],
		],
	]
```  
We could retrieve all the settings under the group `'personal'` like so:  
  
```php  
use JomiGomes\LaravelSettings\Models\Setting;  
use App\Models\User;  
  
$user = User::find(1);  
  
$settingsCollection = Setting::getFiltered($user, 'personal');  
  
// or  
  
$user->getFilteredSettings('personal');  
```  

The method returns a `Collection` of `SettingData` DTOs for all settings under the `'personal'` level, so a total of 4 settings would be retrieved. Each item in the collection has the same properties (`value`, `type`, `setting`, `scope`, `isDefault`, `id`).

<br>

And we could then retrieve all settings under the group `'nested_group'` like so:

```php
use JomiGomes\LaravelSettings\Models\Setting;
use App\Models\User;

$user = User::find(1);

$settingsCollection = Setting::getFiltered($user, 'personal.nested_group');

// or

$user->getFilteredSettings('personal.nested_group');
```

The method returns a `Collection` of `SettingData` DTOs for all settings under `'nested_group'` level, so a total of 2 settings would be retrieved.

So as you can see, we make use of dot-notation to filter the levels, just like we do when retrieving a single setting.

<small>[Back to top](#table-of-contents)</small>

## Advanced Features

### Events System

The package fires events during setting lifecycle operations, allowing you to listen and react to changes:

```php
// In your EventServiceProvider
use JomiGomes\LaravelSettings\Events\SettingUpdated;
use JomiGomes\LaravelSettings\Events\SettingCreated;
use JomiGomes\LaravelSettings\Events\SettingDeleted;
use JomiGomes\LaravelSettings\Events\SettingRetrieved;

protected $listen = [
    SettingUpdated::class => [
        LogSettingChange::class,
        NotifyAdministrators::class,
    ],
    SettingCreated::class => [
        AuditNewSetting::class,
    ],
];
```

**Available Events:**

- **SettingRetrieved**: Fired when a setting is accessed via `get()`
  - Properties: `$setting` (SettingData)
  
- **SettingCreated**: Fired when a new setting is stored for the first time
  - Properties: `$setting` (SettingData), `$previousValue` (mixed)
  
- **SettingUpdated**: Fired when an existing setting value changes
  - Properties: `$setting` (SettingData), `$previousValue` (mixed)
  
- **SettingDeleted**: Fired when a setting reverts to its default value
  - Properties: `$setting` (string), `$scope` (string), `$previousValue` (mixed)

All events include the previous value for auditing and rollback purposes.

### Settings Facade

For cleaner syntax, use the `Settings` facade instead of the `Setting` model:

```php
use JomiGomes\LaravelSettings\Facades\Settings;

// Instead of Setting::get()
$value = Settings::get('preferences.theme', 'user');

// Instead of Setting::set()
Settings::set('preferences.theme', 'dark', 'user');

// Works with all methods
$all = Settings::getAllScoped('user');
$filtered = Settings::getFiltered('user', 'preferences');
```

The facade provides the same functionality with a cleaner API and full IDE support.

### Artisan Commands

Manage settings via the command line:

#### List Settings

```bash
# View all available scopes
php artisan settings:list

# List all settings for a scope
php artisan settings:list user

# Filter by group
php artisan settings:list user --filter=preferences

# Show only customized (non-default) settings
php artisan settings:list user --only-custom
```

The command displays settings in a formatted table showing:
- Setting name (dot notation path)
- Type
- Current value
- Status (default or custom)

#### Clear Settings

```bash
# Clear all customized settings (requires confirmation)
php artisan settings:clear

# Clear settings for a specific scope
php artisan settings:clear user

# Clear with filter
php artisan settings:clear user --filter=preferences

# Force without confirmation
php artisan settings:clear user --force
```

Clearing settings reverts them to their default values defined in the manifesto.

### Caching

Enable caching to improve performance for frequently accessed settings:

**Configuration in `.env`:**

```env
SETTINGS_CACHE_ENABLED=true
SETTINGS_CACHE_TTL=3600
```

**Or in `config/settings.php`:**

```php
return [
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Cache duration in seconds
    ],
    
    // ... rest of your settings
];
```

**How it works:**

- When enabled, all `get()`, `getAllScoped()`, and `getFiltered()` operations are cached
- Cache is automatically invalidated when settings are updated via `set()`
- Works with all Laravel cache drivers (Redis, Memcached, File, etc.)
- Each setting is cached independently with a unique key
- TTL (Time To Live) is configurable per your needs

**Performance Impact:**

With caching enabled, repeated reads of the same setting can be 10-100x faster, depending on your cache driver and database load.

<small>[Back to top](#table-of-contents)</small>

## Conclusion

Congratulations! You've now explored the ins and outs of our Settings Feature, gaining a comprehensive understanding of how to manage, customize, and retrieve configuration values in your Laravel application.

### Key Takeaways
  
1. **Manifesto Mastery:** The manifesto serves as the blueprint for your settings, providing a centralized configuration hub. You've learned how to customize its filename, define scopes, and organize settings within groups.  
  
2. **Setting Settings with Style:** Whether dealing with non-model related scopes or model-related scopes, you now have the tools to set and update settings efficiently. Remember to validate input values when necessary.  
  
3. **Getting Settings Like a Pro:** Retrieving settings, whether for a single setting, all settings within a scope, or filtered settings, has become a breeze. Use this newfound knowledge to enhance the flexibility and efficiency of your application's configuration.  
  
### Next Steps  
  
As you embark on implementing the Settings Feature in your Laravel project, don't forget to leverage the power of scoping settings for a more organized and streamlined configuration management.  
  
For any additional questions or clarifications, feel free to refer to this documentation or reach out to our dedicated support team.  
  
Thank you for choosing our Settings Feature. We hope it brings clarity, structure, and efficiency to your application configurations. Happy coding!  
  
<small>[Back to top](#table-of-contents)</small>
