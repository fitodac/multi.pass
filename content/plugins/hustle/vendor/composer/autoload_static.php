<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitda98371940d11703c56dee923bbb392f
{
    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/aweber/aweber/aweber_api',
    );

    public static $prefixesPsr0 = array (
        'M' =>
        array (
            'Mailchimp' =>
            array (
                0 => __DIR__ . '/..' . '/mailchimp/mailchimp/src',
            ),
        ),
    );

    public static $classMap = array (
        'CS_REST_Administrators' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_administrators.php',
        'CS_REST_Campaigns' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_campaigns.php',
        'CS_REST_Clients' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_clients.php',
        'CS_REST_General' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_general.php',
        'CS_REST_Lists' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_lists.php',
        'CS_REST_People' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_people.php',
        'CS_REST_Segments' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_segments.php',
        'CS_REST_Subscribers' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_subscribers.php',
        'CS_REST_Templates' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_templates.php',
        'CS_REST_Transactional_ClassicEmail' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_transactional_classicemail.php',
        'CS_REST_Transactional_SmartEmail' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_transactional_smartemail.php',
        'CS_REST_Transactional_Timeline' => __DIR__ . '/..' . '/campaignmonitor/createsend-php/csrest_transactional_timeline.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->fallbackDirsPsr4 = ComposerStaticInitda98371940d11703c56dee923bbb392f::$fallbackDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitda98371940d11703c56dee923bbb392f::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitda98371940d11703c56dee923bbb392f::$classMap;

        }, null, ClassLoader::class);
    }
}