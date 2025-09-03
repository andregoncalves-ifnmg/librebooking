<?php

require_once(ROOT_DIR . 'lib/Config/PluginConfigKeys.php');

class LdapConfigKeys extends PluginConfigKeys
{
    public const CONFIG_ID = 'ldap';

    public const HOST = [
        'key' => 'host',
        'type' => 'string',
        'default' => '',
        'label' => 'LDAP Host',
        'description' => 'Hostname or IP address of LDAP server',
    ];

    public const PORT = [
        'key' => 'port',
        'type' => 'integer',
        'default' => 389,
        'label' => 'LDAP Port',
        'description' => 'Port of LDAP server (usually 389 or 636 for SSL)'
    ];

    public const VERSION = [
        'key' => 'version',
        'type' => 'integer',
        'default' => 3,
        'label' => 'LDAP Version',
        'description' => 'LDAP protocol version (usually 3)'
    ];

    public const STARTTLS = [
        'key' => 'starttls',
        'type' => 'boolean',
        'default' => false,
        'label' => 'Use StartTLS',
        'description' => 'Whether to use StartTLS encryption'
    ];

    public const BINDDN = [
        'key' => 'binddn',
        'type' => 'string',
        'default' => '',
        'label' => 'Bind DN',
        'description' => 'DN to bind to LDAP server'
    ];

    public const BINDPW = [
        'key' => 'bindpw',
        'type' => 'string',
        'default' => '',
        'label' => 'Bind Password',
        'description' => 'Password to bind to LDAP server',
        'is_protected' => true
    ];

    public const BASEDN = [
        'key' => 'basedn',
        'type' => 'string',
        'default' => '',
        'label' => 'Base DN',
        'description' => 'Base DN for LDAP searches'
    ];

    public const FILTER = [
        'key' => 'filter',
        'type' => 'string',
        'default' => '(uid=%s)',
        'label' => 'Search Filter',
        'description' => 'LDAP search filter (use %s for username)'
    ];

    public const SCOPE = [
        'key' => 'scope',
        'type' => 'string',
        'default' => 'sub',
        'label' => 'Search Scope',
        'description' => 'LDAP search scope (base, one, or sub)',
        'choices' => [
            'base' => 'Base',
            'one' => 'One Level',
            'sub' => 'Subtree'
        ]
    ];

    public const RETRY_AGAINST_DATABASE = [
        'key' => 'database.auth.when.ldap.user.not.found',
        'type' => 'boolean',
        'default' => false,
        'label' => 'Retry Against Database',
        'description' => 'Try to authenticate against the database if LDAP authentication fails'
    ];

    public const ATTRIBUTE_MAPPING = [
        'key' => 'attribute.mapping',
        'type' => 'string',
        'default' => 'sn=sn,givenname=givenname,mail=mail,telephonenumber=telephonenumber,physicaldeliveryofficename=physicaldeliveryofficename,title=title',
        'label' => 'Attribute Mapping',
        'description' => 'Mapping of LibreBooking attributes to LDAP attributes'
    ];

    public const USER_ID_ATTRIBUTE = [
        'key' => 'user.id.attribute',
        'type' => 'string',
        'default' => 'uid',
        'label' => 'User ID Attribute',
        'description' => 'LDAP attribute to use as user ID'
    ];

    public const REQUIRED_GROUP = [
        'key' => 'required.group',
        'type' => 'string',
        'default' => '',
        'label' => 'Required Group',
        'description' => 'LDAP group required for authentication'
    ];

    public const SYNC_GROUPS = [
        'key' => 'sync.groups',
        'type' => 'boolean',
        'default' => false,
        'label' => 'Sync Groups',
        'description' => 'Synchronize LDAP groups with LibreBooking groups'
    ];

    public const PREVENT_CLEAN_USERNAME = [
        'key' => 'prevent.clean.username',
        'type' => 'boolean',
        'default' => false,
        'label' => 'Prevent Clean Username',
        'description' => 'Do not clean username from domain or email format'
    ];

    // Adding the debug setting that's referenced in LdapOptions::IsLdapDebugOn()
    public const DEBUG_ENABLED = [
        'key' => 'ldap.debug.enabled',
        'type' => 'boolean',
        'default' => false,
        'label' => 'Enable LDAP Debug',
        'description' => 'Enable debugging for LDAP authentication'
    ];
}
