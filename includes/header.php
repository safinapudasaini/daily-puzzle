<?php
/**
 * Banana Quiz Game - Common Header Component
 * CIS046-3 Software For Enterprise
 * 
 * Sets document metadata, links stylesheets, renders navigation bar and flash alerts.
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

init_session();
$currentUser = current_user();
$pageTitle = isset($pageTitle) ? sanitize($pageTitle) . ' | Banana Quiz Game' : 'Banana Quiz Game - Distributed Web Application';
$activePage = $activePage ?? '';
$flashes = get_all_flashes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $pageTitle ?></title>

    <!-- Base Configuration for JavaScript Interoperability -->
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="base-url" content="<?= base_url() ?>">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/logo.svg') ?>">
</head>
<body>

<!-- Navigation Header -->
<header class="navbar">
    <div class="nav-container">
        <a href="<?= $currentUser ? base_url('dashboard/index.php') : base_url('index.php') ?>" class="brand-link" aria-label="Banana Quiz Home">
            <img src="<?= base_url('assets/images/logo.svg') ?>" alt="Banana Quiz Logo" class="brand-logo">
        </a>

        <nav aria-label="Main Navigation">
            <ul class="nav-menu">
                <?php if ($currentUser): ?>
                    <li>
                        <a href="<?= base_url('dashboard/index.php') ?>" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                            📊 Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('game/play.php') ?>" class="nav-link <?= $activePage === 'play' ? 'active' : '' ?>">
                            🎮 Play Game
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('game/history.php') ?>" class="nav-link <?= $activePage === 'history' ? 'active' : '' ?>">
                            📜 Game History
                        </a>
                    </li>
                    <li class="user-pill">
                        <span class="avatar-badge"><?= strtoupper(substr($currentUser['username'], 0, 1)) ?></span>
                        <span><?= sanitize($currentUser['username']) ?></span>
                    </li>
                    <li>
                        <a href="<?= base_url('auth/logout.php') ?>" class="btn btn-secondary" style="padding: 0.4rem 0.85rem; font-size: 0.85rem;">
                            Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?= base_url('index.php') ?>" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('auth/login.php') ?>" class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>">
                            Sign In
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('auth/register.php') ?>" class="btn btn-primary" style="padding: 0.5rem 1rem;">
                            Get Started
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<!-- Main Application Content Container -->
<main class="main-wrapper">
    <!-- Flash Messages -->
    <?php if (!empty($flashes)): ?>
        <div class="alert-container" id="flash-container">
            <?php foreach ($flashes as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" role="alert">
                        <span><?= sanitize($msg) ?></span>
                        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:1.1rem; color:inherit; margin-left:1rem;">&times;</button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
