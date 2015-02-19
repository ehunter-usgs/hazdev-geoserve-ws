<?php

include_once 'DatabaseInstaller.class.php';


/**
 * Mysql implementation of DatabaseInstaller.
 */
class PostgresDatabaseInstaller extends DatabaseInstaller {

  // database name, parsed from $url.
  private $dbname;

  /**
   * Called by DatabaseInstaller::getInstaller().
   */
  protected function __construct ($url, $user, $pass) {
    parent::__construct($url, $user, $pass);
    preg_match('/dbname=([^;]+)/', $url, $matches);
    if (count($matches) < 2) {
      throw new Exception('"dbname" is required in a postgress connection url');
    }
    $this->dbname = $matches[1];
  }

  /**
   * Check if database exists.
   *
   * @return {Boolean} true if able to connect to database, false otherwise.
   */
  public function databaseExists () {
    try {
      $this->connect();
      $this->dbh = null;
      return true;
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * Drop the database referred to by $url.
   */
  public function dropDatabase () {
    $db = $this->connectWithoutDbname();
    $db->exec('DROP DATABASE IF EXISTS ' . $dbname);
    $db = null;
  }

  /**
   * Create the database referred to by $url.
   */
  public function createDatabase () {
    $db = $this->connectWithoutDbname();
    $setupdb->exec('CREATE DATABASE IF NOT EXISTS ' . $this->dbname);
    $db = null;
  }

  /**
   * Create user with $roles
   */
  public function createUser ($roles, $user, $password) {
    // create read/write user for save
    $this->run('CREATE USER ' . $user . ' WITH PASSWORD \'' . $password . '\'');
    $this->run('GRANT ' . implode(',', $roles) . ' ON ALL TABLES IN SCHEMA ' . $this->dbname);
  }

  /**
   * Connect to the mysql server without specifying a database name.
   * Used by dropDatabase() and createDatabase().
   */
  protected function connectWithoutDbname() {
    $db = new PDO(str_replace('dbname=' . $this->dbname, 'dbname=postgres', $this->url),
        $this->user, $this->pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }

}
