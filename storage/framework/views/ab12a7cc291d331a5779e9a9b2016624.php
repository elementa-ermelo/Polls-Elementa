<?php $__env->startSection('content'); ?>
<div style="max-width:480px; margin:60px auto;">
    <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Welkom terug</h1>
        <p class="meta">Log in op je account om verder te gaan</p>
    </div>

    <div class="card" style="padding: 40px;">
        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="email">E-mailadres</label>
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>"
                       placeholder="naam@voorbeeld.nl" required autofocus>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="field" style="margin-bottom: 28px;">
                <label for="password">Wachtwoord</label>
                <input id="password" type="password" name="password"
                       placeholder="Uw wachtwoord" required>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px; font-weight: 700;">
                Inloggen
            </button>
        </form>

        <div style="border-top: 1px solid var(--line); margin: 28px 0;"></div>

        <p style="margin: 0; text-align: center; font-size: 14px; color: var(--muted);">
            Nog geen account? <a href="<?php echo e(route('register')); ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">Account aanmaken</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\auth\login.blade.php ENDPATH**/ ?>