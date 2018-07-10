<?php
$sql = "CREATE TABLE public.accounts
(
    user_id serial PRIMARY KEY NOT NULL,
    amount numeric DEFAULT 0 NOT NULL
);";

$dbh = new PDO(
    sprintf(
        'pgsql:dbname=%s;host=%s;user=%s;password=%s',
        getenv('POSTGRES_DB'),
        getenv('POSTGRES_HOST'),
        getenv('POSTGRES_USER'),
        getenv('POSTGRES_PASSWORD')
)
);

$dbh->exec($sql);

for ($i=0; $i<100; $i++) {
    $st = $dbh->prepare('INSERT INTO accounts (amount) VALUES (:amount)');
    $st->execute([':amount' => rand(0, 100000000)]);
}
