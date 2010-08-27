<?php
$lang['installer'] = array(
'install_phsite' => "Install phSite CMS",
'install' => "Install",
'db_exists' => "The CMS has already been installed; you cannot run this script.",
'db_hostname' => 'Database host name',
'hint_hostname' => "The database host name is usually 'localhost', but can be in the form of a URL 'my-database-server.com' or IP address '192.192.0.100'. Refer to the support pages of your web hosting provider for the correct settings.",
'db_username' => 'Database username',
'db_password' => 'Database password',
'db_database' => 'Database name',
'db_driver' => 'Database type',
'db_prefix' => 'Database table prefix',
'hint_prefix' => "A table prefix is optional &mdash; however if you are sharing this database with other applications, it is advised to use a prefix so that the database table names do not conflict with the other applications. A suggested prefix is 'phsite_'.",
'db_config' => 'Database connection information',
'create_admin_user' => "Create administrator account",
'install_step' => "Step %s",
'installer_languages' => 'Installer languages',
'install_success' => 'Installation completed successfully',
);

$lang['status_labels'] = array(
0 => "The CMS has already been installed; you cannot run this script.",
1 => "Database config missing or invalid",
2 => "Database connection exists, but no tables",
3 => "Tables exist, but no user accounts or site preferences found",
4 => "Tables exist, but no user accounts found",
);

$lang['db_drivers'] = array(
'mssql' => 'MS SQL',
'mysql' => 'MySQL',
'mysqli' => 'MySQLi',
'oci8' => 'Oracle',
'odbc' => 'ODBC',
'postgre' => 'PostgreSQL',
'sqlite' => 'SQLite',
);

/* End of file phsite_lang.php */
/* Location: ./system/application/language/english/phsite_lang.php */