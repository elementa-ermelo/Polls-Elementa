<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Polls Elementa'); ?></title>
    <style>
        :root {
            --bg: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);
            --bg-color: #f0f9ff;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --muted: #94a3b8;
            --line: #e2e8f0;
            --primary: #3b82f6;
            --primary-dark: #1e40af;
            --accent: #06b6d4;
            --accent-dark: #0891b2;
            --accent-soft: #cffafe;
            --success: #10b981;
            --danger: #ef4444;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 13px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px rgba(0, 0, 0, 0.1);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
            line-height: 1.6;
        }
        a { color: inherit; }

        /* Nav */
        .topnav {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid var(--line);
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 4px;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .topnav-brand {
            font-weight: 800;
            font-size: 18px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            margin-right: 16px;
            flex-shrink: 0;
            letter-spacing: -0.5px;
        }
        .topnav a.nav-link {
            color: var(--muted);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all .2s;
        }
        .topnav a.nav-link:hover {
            color: var(--primary);
            background: rgba(59, 130, 246, 0.08);
        }
        .nav-spacer { flex: 1; }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all .2s;
            color: var(--text);
        }
        .nav-user:hover { background: rgba(59, 130, 246, 0.08); }
        .nav-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--line);
        }
        .nav-avatar-placeholder {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-soft) 0%, #e0f2fe 100%);
            color: var(--accent-dark);
            font-size: 14px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border: 2px solid var(--line);
        }
        .nav-divider {
            width: 1px; height: 24px; background: var(--line); margin: 0 6px;
        }

        /* Layout */
        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px 80px;
        }

        /* Cards */
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 24px;
            background: var(--surface);
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            transition: all .3s ease;
        }
        .card:hover {
            border-color: #cbd5e1;
            box-shadow: var(--shadow-lg);
        }

        /* Grid */
        .grid { display: grid; gap: 20px; }
        .grid-2 { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }

        /* Buttons */
        .btn {
            border: 1.5px solid var(--line);
            padding: 10px 16px;
            border-radius: 10px;
            background: var(--surface);
            color: var(--text);
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            transition: all .2s;
        }
        .btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border-color: transparent;
            color: #fff;
            font-weight: 700;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1d4ed8 100%);
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
            border-color: transparent;
            color: #fff;
        }
        .btn-danger {
            color: var(--danger);
            border-color: #fecaca;
        }
        .btn-danger:hover {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Typography */
        h1 {
            font-size: 28px;
            margin: 0 0 12px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
        }
        h2 {
            font-size: 20px;
            margin: 0 0 16px;
            font-weight: 700;
            color: var(--text);
        }
        h3 {
            font-size: 17px;
            margin: 0 0 10px;
            font-weight: 700;
            color: var(--text);
        }
        .meta { color: var(--muted); font-size: 14px; font-weight: 500; }

        /* Flash */
        .flash {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid;
            font-weight: 600;
            animation: slideDown .3s ease;
        }
        .flash.success {
            border-color: #86efac;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            color: #166534;
        }
        .flash.error {
            border-color: #fca5a5;
            background: linear-gradient(135deg, #fef2f2 0%, #fef2f2 100%);
            color: #be123c;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Forms */
        input, textarea, select {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            padding: 11px 13px;
            font: inherit;
            color: var(--text);
            background: var(--surface);
            transition: all .2s;
        }
        input::placeholder, textarea::placeholder {
            color: var(--muted);
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: #f8fafc;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 14px;
            color: var(--text);
        }
        .field { margin-bottom: 18px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th {
            border-bottom: 2px solid var(--line);
            text-align: left;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--muted);
            font-weight: 700;
            background: #f8fafc;
        }
        td {
            border-bottom: 1px solid var(--line);
            text-align: left;
            padding: 12px 14px;
            font-size: 14px;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fafb; }

        /* Progress */
        .progress {
            width: 100%;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            height: 10px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .progress > span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 999px;
            transition: width .3s ease;
        }

        /* Util */
        .inline {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
    </style>
</head>
<body>

<nav class="topnav">
    <a href="<?php echo e(auth()->check() ? route('admin.polls.index') : route('polls.index')); ?>" class="topnav-brand">
        Polls Elementa
    </a>

    <?php if(auth()->guard()->check()): ?>
        <a href="<?php echo e(route('admin.polls.index')); ?>" class="nav-link">Polls</a>
        <a href="<?php echo e(route('admin.reports.index')); ?>" class="nav-link">Rapportage</a>
        <?php if(auth()->user()->is_admin): ?>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-link">Gebruikers</a>
        <?php endif; ?>

        <div class="nav-spacer"></div>

        <a href="<?php echo e(route('profile.edit')); ?>" class="nav-user">
            <?php if(auth()->user()->logo): ?>
                <img src="<?php echo e(asset('storage/' . auth()->user()->logo)); ?>" alt="<?php echo e(auth()->user()->name); ?>" class="nav-avatar">
            <?php else: ?>
                <div class="nav-avatar-placeholder"><?php echo e(substr(auth()->user()->name, 0, 1)); ?></div>
            <?php endif; ?>
            <?php echo e(auth()->user()->name); ?>

        </a>

        <div class="nav-divider"></div>

        <form action="<?php echo e(route('logout')); ?>" method="POST" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn" style="border:none; background:none; color:var(--muted); padding:6px 10px; font-size:14px;">Uitloggen</button>
        </form>
    <?php else: ?>
        <div class="nav-spacer"></div>
        <a href="<?php echo e(route('login')); ?>" class="btn" style="font-size:14px;">Inloggen</a>
        <a href="<?php echo e(route('register')); ?>" class="btn btn-primary" style="font-size:14px;">Registreren</a>
    <?php endif; ?>
</nav>

<div class="wrap">
    <?php if(session('success')): ?>
        <div class="flash success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="flash error"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="flash error">
            <strong>Er zijn fouten:</strong>
            <ul style="margin:6px 0 0; padding-left:20px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views/layouts/app.blade.php ENDPATH**/ ?>