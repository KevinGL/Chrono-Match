<?php require "includes/header.php" ?>
<?php require_once "config/db.php"; ?>

<?php

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

?>

LISTE DES SESSIONS

<?php if(isset($_SESSION["flash"])): ?>
    <p>
        <?= $_SESSION["flash"]["content"] ?>
    </p>
<?php unset($_SESSION["flash"]) ?>
<?php endif ?>

<?php $sessions = getNextSessions() ?>

<ul>
    <?php foreach($sessions as $s): ?>
        <li>
            Le <?= $s->format("d/m/Y") ?> à <?= $s->format("H:i") ?>
            <?php if(!registered($pdo, $s)): ?>
                <a href="register.php?session=<?= $s->getTimestamp() ?>">S'inscrire</a>
            <?php else: ?>
                <a href="unregister.php?session=<?= $s->getTimestamp() ?>">Se désinscrire</a>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>

<?php require "includes/footer.php" ?>