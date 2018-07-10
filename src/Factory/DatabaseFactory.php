<?php
namespace TZ\Factory;

class DatabaseFactory
{
    public function getDatabase()
    {
        return new \PDO(
            sprintf(
                'pgsql:dbname=%s;host=%s;user=%s;password=%s',
                getenv('POSTGRES_DB'),
                getenv('POSTGRES_HOST'),
                getenv('POSTGRES_USER'),
                getenv('POSTGRES_PASSWORD')
            )
        );
    }
}
