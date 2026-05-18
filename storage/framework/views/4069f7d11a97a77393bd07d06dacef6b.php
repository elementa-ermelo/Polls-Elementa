<?php $__env->startSection('content'); ?>
<div style="max-width: 480px; margin: 60px auto;">
    <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px; background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Maak je account aan</h1>
        <p class="meta">Begin met polls creëren en beheren</p>
    </div>

    <div class="card" style="padding: 40px;">
        <form method="POST" action="<?php echo e(route('register')); ?>">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="name">Volledige naam *</label>
                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>"
                       placeholder="Uw naam" required autofocus>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="field">
                <label for="email">E-mailadres *</label>
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>"
                       placeholder="naam@voorbeeld.nl" required>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="field">
                <label for="password">Wachtwoord *</label>
                <input id="password" type="password" name="password"
                       placeholder="Minimaal 8 tekens" required>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="field" style="margin-bottom: 28px;">
                <label for="password-confirm">Wachtwoord bevestigen *</label>
                <input id="password-confirm" type="password" name="password_confirmation"
                       placeholder="Herhaal wachtwoord" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px; font-weight: 700;">
                Account aanmaken
            </button>
        </form>

        <div style="border-top: 1px solid var(--line); margin: 28px 0;"></div>

        <p style="margin: 0; text-align: center; font-size: 14px; color: var(--muted);">
            Al een account? <a href="<?php echo e(route('login')); ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">Inloggen</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\auth\register.blade.php ENDPATH**/ ?>