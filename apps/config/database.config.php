<?php
// Connector Settings / Database Settings
$settings = new ConnectorSettings();
$settings->DriverType = "mysqli";
$settings->Username = "root";
$settings->Password = "";
$settings->Host = "127.0.0.1";
$settings->Port = 3308;
$settings->DatabaseName = "db_mka_exlive";
// These settings will affect error reporting and will set to true for development debugging. Please change to false to production
$settings->SuppressPhpError = true;
$settings->RaiseConnectionError = true;
$settings->RaiseQueryError = true;
$settings->UseSqlException = false;
$settings->DuplicateRaiseError = false;

/**
 * Create Default Database Connection
 * This Connection is accessible from any Controller or Model by using '$this->connector'
 *
 * You can find another driver in core/connector folder.
 * Driver(s) are packaged in single folder under core/connector folder. They must consists of driver.php and reader.php
 */
ConnectorManager::CreateDefaultConnector($settings);

/**
 * Since version 3.3.1 this framework support multiple instance of Connector.
 * Example bellow will create another Connector with name 'other' using Mysqli Driver and $settings as ConnectionSettings
 * For example bellow it will have same database server but different connection id ! (Just open the comment sign)
 *
 * HOW TO LOAD IN CONTROLLER / MODEL: $otherConnector = ConnectorManager::GetPool("other");
 */

//ConnectorManager::CreatePool("other", $settings);

$settings = new ConnectorSettings();
$settings->DriverType = "mysqli";
$settings->Username = "root";
$settings->Password = "";
$settings->Host = "127.0.0.1";
$settings->Port = 3308;
$settings->DatabaseName = "db_corporate";
// These settings will affect error reporting and will set to true for development debugging. Please change to false to production
$settings->SuppressPhpError = true;
$settings->RaiseConnectionError = true;
$settings->RaiseQueryError = true;
$settings->UseSqlException = false;
$settings->DuplicateRaiseError = false;

// OK pool #2 untuk ke DBase corp
ConnectorManager::CreatePool("corp", $settings);
