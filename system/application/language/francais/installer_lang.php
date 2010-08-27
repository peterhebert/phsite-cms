<?php
$lang['installer'] = array(
'install_phsite' => "Installation phSite CMS",
'install' => "Installation",
'db_exists' => "Le logiciel est d&eacute;j&agrave; install&eacute;.",
'db_hostname' => "Nom du serveur de la base de donn&eacute;es",
'hint_hostname' => "Le nom du serveur est normalement 'localhost', mais peut-&ecirc;tre auusi un adresse URL 'mon-serveur-donnees.com' ou IP '192.192.0.100'. Les pages de soutien de votre fournisseur des services d'h&eacute;bergement web vont sp&eacute;cifier la valeur.",
'db_username' => "Nom d'utilisateur de la base de donn&eacute;es",
'db_password' => "Mot de passe de la base de donn&eacute;es",
'db_database' => "Nom de la base de donn&eacute;es",
'db_driver' => "Type de logiciel de base de donn&eacute;es",
'db_prefix' => "Pr&eacute;fixe des tables de donn&eacute;es",
'hint_prefix' => "Une pr&eacute;fixe n'est pas requis, mais si vous partager cette base de donn&eacute;es avec autres lociciels, il est recommand&eacute; de utliser une pr&eacute;fixe pour &eacute;viter des conflits dans les nome tes tables. Une pr&eacute;fixe sugg&eacute;r&eacute; est 'phsite_'.",
'db_config' => "Configuration de la connection &agrave; la base de donn&eacute;es",
'create_admin_user' => "Cr&eacute;ation du compte d'utilisateur administratif",
'install_step' => "&Eacute;tape %s",
'installer_languages' => "Langues de l'installateur",
'install_success' => 'Installation compl&egrave;te',
);

$lang['status_labels'] = array(
0 => "Le logiciel est d&eacute;j&agrave; install&eacute;.",
1 => "Configuration de la connection &agrave; la base de donn&eacute;es n'existe pas ou n'est pas valide",
2 => "La connection &agrave; la base de donn&eacute;es existe, mais il n'existe aucuns tables &agrave; donn&eacute;es",
3 => "Les tables &agrave; donn&eacute;es sont pr&eacute;sents, mais il n'existe aucuns comptes d'utilisateurs ni des pr&eacute;ferences de site",
4 => "Les tables &agrave; donn&eacute;es sont pr&eacute;sents, mais il n'existe aucuns comptes d'utilisateurs",
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