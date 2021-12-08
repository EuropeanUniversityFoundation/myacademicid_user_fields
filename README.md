# MyAcademicID user fields

Drupal 9 module providing base fields for the User entity to store MyAcademicID data.

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

## Usage

The MyAcademicID user fields settings will be available at `/admin/config/people/accounts/myacademicid`. The corresponding permissions are grouped under _MyAcademicID user fields_ at `/admin/people/permissions`.

## Tests

Assuming there is a properly configured `phpunit.xml` at the project root, that Drupal is inside the `web` directory and that this module is in `web/modules/contrib`, run the following commands:

    cd web/core
    ../../vendor/bin/phpunit -c $PWD/../../phpunit.xml ../modules/contrib/myacademicid_user_fields/tests
