<?php
namespace TZ\Persister;


class Persister
{
    /**
     * @var \PDO
     */
    protected $database;

    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }

    public function transactionBegin()
    {
        $this->database->exec('BEGIN;');
    }

    public function transactionCommit()
    {
        $this->database->exec('COMMIT;');
    }

    public function transactionRollback()
    {
        $this->database->exec('ROLLBACK;');
    }


}