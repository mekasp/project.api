<?php

$pdo = new PDO('mysql:dbname=project.api;host=localhost', 'homestead', 'secret');

$pdo->beginTransaction();

try {
    $user = $pdo->prepare('INSERT INTO users
    (`name`, `email`, `verified_at`, `token`, `country_id`, `created_at`, `updated_at`)
    VALUES (:name, :email, :verified_at, :token, :country_id, :created_at, :updated_at)');

    $user->bindValue('name', 'maxim');
    $user->bindValue('email', 'maxsim@gmail.com');
    $user->bindValue('verified_at', '2022-12-06 09:45:48');
    $user->bindValue('token', password_hash('password', PASSWORD_BCRYPT));
    $user->bindValue('country_id', 1);
    $user->bindValue('created_at', '2022-12-06 09:45:48');
    $user->bindValue('updated_at', '2022-12-06 09:45:48');

    $user->execute();

    $userId = $pdo->lastInsertId();

    $project = $pdo->prepare('INSERT INTO project_user
    (`project_id`, `user_id`, `created_at`, `updated_at`)
    VALUES (:project_id, :user_id, :created_at, :updated_at)');

    $project->bindValue('project_id', rand(1,10));
    $project->bindParam('user_id', $userId);
    $project->bindValue('created_at', '2022-12-06 09:45:48');
    $project->bindValue('updated_at', '2022-12-06 09:45:48');

    $project->execute();

    $label = $pdo->prepare('INSERT INTO labels
    (`name`, `user_id`, `created_at`, `updated_at`)
    VALUES (:name, :user_id, :created_at, :updated_at)');

    $label->bindValue('name', 'label123');
    $label->bindParam('user_id', $userId);
    $label->bindValue('created_at', '2022-12-06 09:45:48');
    $label->bindValue('updated_at', '2022-12-06 09:45:48');

    $label->execute();

    $pdo->commit();
} catch (\Exception $exception) {
    $pdo->rollBack();
    echo $exception->getMessage();
}

