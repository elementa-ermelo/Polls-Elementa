

<?php $__env->startSection('content'); ?>
<div style="margin-bottom: 32px;">
    <h1 style="margin-bottom: 6px;">Rapportage & Analytics</h1>
    <p class="meta">Overzicht van al je polls en stemmen</p>
</div>

<div class="grid grid-2" style="margin-bottom: 28px;">
    <div class="card" style="border-left: 5px solid var(--primary); background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);">
        <div style="font-size: 28px; font-weight: 800; color: var(--primary); margin-bottom: 6px;"><?php echo e($totals['polls']); ?></div>
        <div style="color: var(--muted); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Totaal Polls</div>
    </div>
    <div class="card" style="border-left: 5px solid var(--accent); background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);">
        <div style="font-size: 28px; font-weight: 800; color: var(--accent); margin-bottom: 6px;">
            <?php echo e($totals['active']); ?> <span style="color: var(--muted); font-size: 14px; margin-left: 4px;">/ <?php echo e($totals['archived']); ?></span>
        </div>
        <div style="color: var(--muted); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Actief / Archief</div>
    </div>
</div>

<div class="card">
    <h2>Polloverzicht</h2>
    <p><a class="btn" href="<?php echo e(route('admin.reports.archive')); ?>">Open archiefpagina</a></p>
    <table>
        <thead>
        <tr>
            <th>Poll</th>
            <th>Status</th>
            <th>Bevestigd/totaal</th>
            <th>Open tot</th>
        </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $polls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><a href="<?php echo e(route('admin.polls.show', $poll)); ?>"><?php echo e($poll->title); ?></a></td>
                <td><?php echo e($poll->status); ?></td>
                <td><?php echo e($poll->confirmed_respondents); ?> / <?php echo e($poll->total_respondents); ?></td>
                <td><?php echo e($poll->closes_at?->format('d-m-Y H:i') ?? '-'); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4">Nog geen data.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\reports\index.blade.php ENDPATH**/ ?>