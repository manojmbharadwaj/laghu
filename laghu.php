<?php

/**
 * Simple Migration Tool
 * @author Manoj
 * Create Migration File
 * ---------------------
 * create table - php laghu.php create ct-<table-name>
 * alter table  - php laghu.php create mt-<table-name>
 * dump data    - php laghu.php create dt-<table-name>
 * 
 * Run Migration
 * -------------
 * php laghu.php migrate
 */

$pdo = connect();

$tbl_migration = 'laghu_migrations';

// check weather migration table exists or not. create if not. make file name as index
$ts = tableExists($pdo, $tbl_migration);

$action = (isset($argv[1])) ? $argv[1] : die('Action needed!');

$migration = 'migration';
create_migration_directory($migration);
$time      = time();

if ( 'create' == $action ) {
    $file_suffix = (isset($argv[2])) ? '-'.$argv[2] : '';   // ct-> create table, mt->modify table, dt->data
    $file_name = $time.$file_suffix.'.sql';
    $new_migration = $migration.'/'.$file_name;
    $myfile = fopen($new_migration, "w");
    echo 'File created - '.$file_name;
}
else if ( 'migrate' == $action ) {
    // 1. list all files in migrate directory
    // 2. list all migrated file name, id from db
    // 3. if the file exists in Files, not in DB -> execute it. Insert to DB.

    $migrated_files = get_migrated_files($pdo, $tbl_migration);
    $all_migration_files = get_all_migration_files($migration);

    $success_count = 0;
    $failure_count = 0;
    $total_count   = 0;

    if ( !empty($all_migration_files) ) {
        foreach ($all_migration_files as $file_key => $m_file) {
            if ( !isset($migrated_files[$m_file]) ) {
                $res = migrate_this($pdo, $migration, $m_file, $tbl_migration);
                if ( 1 == $res ) $success_count++;
                else $failure_count++;
                $total_count++;
            }
        }

        if ( $total_count > 0 ) echo 'Migrated! Success - '.$success_count.' ; Failure - '.$failure_count;
        else echo 'Looks like nothing to migrate!';
    }

}
else {
    echo 'action un-known!';
}

function connect() {
    try {
        include 'config.php';
        $dbh = new PDO("mysql:host=$host;dbname=$database", $user, $password, array(PDO::ATTR_PERSISTENT => true));
        return $dbh;
    } catch (PDOException  $e) {
        echo "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
    
}


function tableExists($pdo, $table) {

    $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");

    if (false == $result) {
        $create_migration_table = "CREATE TABLE  $table ( `id` INT NOT NULL AUTO_INCREMENT , `file_name` VARCHAR(1800) NOT NULL , `status` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'migration status. 1->success, 0->failed' , `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`file_name`)) ENGINE = InnoDB;";
        $res = $pdo->query($create_migration_table);
    }

}

function create_migration_directory($migration) {
    if ( !file_exists($migration) ) {
        mkdir($migration);
    }
}

function get_migrated_files($pdo, $table) {
    $migrated_files = array();
    $stmt = $pdo->query("SELECT id, file_name FROM $table WHERE status = 1");
    $mt   = $stmt->fetchAll();
    if ( !empty($mt) ) {
        foreach ($mt as $key => $value) {
            $migrated_files[$value['file_name']] = $value['id'];
        }
    }
    return $migrated_files;
}

function get_all_migration_files($dir) {
    $scanned_directory = array_diff(scandir($dir), array('..', '.'));
    return $scanned_directory;
}

function migrate_this($pdo, $migration, $migration_file, $migration_table) {
    $delta = $migration.'/'.$migration_file;
    $sql = file_get_contents($delta);
    $qr = $pdo->exec($sql);
        
    $status = (false === $qr) ? 0 : 1;

    $query = "INSERT INTO $migration_table(file_name, status) VALUES('$migration_file', $status)";

    $pdo->query($query);

    return $status;
}