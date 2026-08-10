<?php

namespace zap\db;

use zap\DB;
use zap\console\Output;

/**
 * Static Schema Builder — unified entry point for table migrations.
 *
 * Supports create / alter / drop / rename with optional console output.
 *
 * @example
 *   // Create a table
 *   Schema::create('articles', function (TableSchema $t) {
 *       $t->increments('id');
 *       $t->varchar('title', 200);
 *       $t->text('content');
 *       $t->timestamps();
 *   });
 *
 *   // Alter a table
 *   Schema::alter('articles', function (AlertTable $t) {
 *       $t->varchar('slug', 100);
 *       $t->addIndex('idx_slug', 'slug');
 *   });
 *
 *   // Drop a table
 *   Schema::dropIfExists('old_logs');
 */
class Schema
{
    protected static ?Output $output = null;

    /** @var string|null Override the default connection name */
    protected static ?string $connectionName = null;

    /**
     * Set a console Output instance for SQL logging.
     */
    public static function setOutput(Output $output): void
    {
        static::$output = $output;
    }

    /**
     * Set the database connection name to use for subsequent operations.
     *
     * @param string|null $name  e.g. 'sqlite' — see database.php connections
     */
    public static function connection(?string $name): void
    {
        static::$connectionName = $name;
    }

    // ─── Create Table ────────────────────────────────────────────

    /**
     * Build and execute a CREATE TABLE statement.
     *
     * @param callable(TableSchema): void $callback
     */
    public static function create(string $tableName, callable $callback): bool
    {
        $verbose = static::verbose() >= 1;

        $table = new TableSchema($tableName, $verbose);
        $callback($table);

        $sql      = $table->toSql();
        $execTime = static::time();
        $result   = DB::getPDO(static::$connectionName)->rawExec($sql);
        $execTime = static::time() - $execTime;

        static::log($sql, $execTime);

        return $result;
    }

    // ─── Alter Table ─────────────────────────────────────────────

    /**
     * Build and execute ALTER TABLE + data seeding.
     *
     * @param callable(AlertTable): void $callback
     */
    public static function alter(string $tableName, callable $callback): bool
    {
        $verbose = static::verbose() >= 1;

        $table = new AlertTable($tableName, $verbose);
        $callback($table);

        $ddl       = $table->toSql();
        $dataSql   = $table->toDataSql();
        $execTime  = static::time();

        if ($ddl !== '') {
            DB::getPDO(static::$connectionName)->rawExec($ddl);
        }
        if ($dataSql !== '') {
            DB::getPDO(static::$connectionName)->rawExec($dataSql);
        }

        $execTime = static::time() - $execTime;
        static::log($ddl . "\n" . $dataSql, $execTime);

        return true;
    }

    /**
     * Check if a table exists.
     */
    public static function hasTable(string $tableName): bool
    {
        $pdo   = DB::getPDO(static::$connectionName);
        $table = $pdo->quoteTable($tableName);

        try {
            $pdo->rawExec("SELECT 1 FROM {$table} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ─── Drop Table ──────────────────────────────────────────────

    public static function drop(string $tableName): bool
    {
        return DB::getPDO(static::$connectionName)->dropTable($tableName);
    }

    public static function dropIfExists(string $tableName): bool
    {
        if (static::hasTable($tableName)) {
            return static::drop($tableName);
        }
        return false;
    }

    // ─── Rename Table ────────────────────────────────────────────

    public static function rename(string $from, string $to): bool
    {
        return DB::getPDO(static::$connectionName)->renameTable($from, $to);
    }

    // ─── Console Logging ─────────────────────────────────────────

    protected static function log(string $sql, float $execTime): void
    {
        if (static::verbose() < 1) {
            return;
        }

        if (static::$output) {
            $timeStr = number_format($execTime, 4);
            static::$output->info("{$sql} \n -- [{$timeStr}ms]");
        } else {
            echo "{$sql}\n-- [{$execTime}ms]\n";
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────

    protected static function verbose(): int
    {
        return static::$output ? static::$output->getVerbose() : 0;
    }

    protected static function time(): float
    {
        return microtime(true);
    }
}
