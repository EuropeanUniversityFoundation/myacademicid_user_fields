# MyAcademicID user fields

This module adds funcionality related to **MyAcademicID** data points that can be leveraged in Drupal applications.

## Installation

Include the repository in your project's `composer.json` file:

    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/EuropeanUniversityFoundation/myacademicid_user_fields"
        }
    ],

Then you can require the package as usual:

    composer require euf/myacademicid_user_fields

Finally, install the module:

    drush en myacademicid_user_fields

## Features and usage

### Base fields for the _User_ entity

This module adds new base fields to the _User_ entity that match OpenID Connect claims from MyAcademicID:

- `schac_home_organization`;
- `schac_personal_unique_code`;
- `voperson_external_affilliation`.

The field names follow the convention `myaid_{claim}`.

### Event based functionality

This module defines two groups of `Event`s and corresponding `EventSubscriber`s to handle the main use cases:

- `User{Claim}ChangeEvent`s are dispatched when the corresponding _User_ fields change;
- the built-in `MyacademicidUserFieldsSubscriber` issues messages accordingly;
- `SetUser{Claim}Event`s are dispatched when the corresponding user fields should be set to a given value;
- the built-in `SetUser{Claim}EventSubscriber`s set the field values if the User entity passes validation.

### Access to user fields

This module adds one permission: _Administer MyAcademicID user fields_.

Users with this permission can edit MyAcademicID user fields (their own or any user's, depending on other core permissions).

Users without this permission will see their MyAcademicID claims printed as plain text in their user account form.

With this permission, a user can also trigger the `SetUser{Claim}Event`s via a form located at `admin/config/services/myacademicid/trigger-set-events`.

### Populating MyAcademicID user fields

For applications relying on the _OpenID Connect_ protocol, the MyAcademicID claims should be mapped to the new base fields, so that any change upon login will propagate across the system.

For applications implementing an _OAuth2 Server_ or similar, this module can be used to set the claims based on the relevant business logic.

## Tests

Assuming there is a properly configured `phpunit.xml` at the project root, run the tests with the following command:

    vendor/bin/phpunit web/modules/contrib/myacademicid_user_fields/tests
