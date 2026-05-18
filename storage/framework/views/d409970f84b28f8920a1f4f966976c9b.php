

<?php $__env->startSection('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px;">
    <div>
        <h1 style="margin-bottom: 6px;">📦 Archiefpolls</h1>
        <p class="meta">Gearchiveerde polls beheren en herstellen</p>
    </div>
    <a class="btn" href="<?php echo e(route('admin.reports.index')); ?>">← Terug</a>
</div>

<div class="card">
    <h2>Archiefpolls</h2>
    <table>
        <thead>
        <tr>
            <th>Poll</th>
            <th>Status</th>
            <th>Bevestigd/totaal</th>
            <th>Gearchiveerd op</th>
            <th>Actie</th>
        </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $archivedPolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><a href="<?php echo e(route('admin.polls.show', $poll)); ?>"><?php echo e($poll->title); ?></a></td>
                <td><?php echo e($poll->status); ?></td>
                <td><?php echo e($poll->confirmed_votes_count); ?> / <?php echo e($poll->unconfirmed_votes_count); ?></td>
                <td><?php echo e($poll->updated_at?->format('d-m-Y H:i') ?? '-'); ?></td>
                <td>
                    <form method="POST" action="<?php echo e(route('admin.reports.reactivate', $poll)); ?>" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn" style="font-size:13px; padding:6px 12px;">Activeren</button>
                    </form>
                    <form method="POST" action="<?php echo e(route('admin.polls.destroy', $poll)); ?>" style="display:inline;" onsubmit="return confirm('Poll definitief verwijderen?');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn" style="font-size:13px; padding:6px 12px; color:#b91c1c;">Verwijderen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4">Nog geen gearchiveerde polls.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\reports\archive.blade.php ENDPATH**/ ?>