

<?php $__env->startSection('content'); ?>
<div class="inline" style="justify-content: space-between; margin-bottom: 24px; align-items: flex-start;">
    <div>
        <h1 style="margin-bottom: 4px;">Mijn Polls</h1>
        <p class="meta">Beheer en analyseer je polls</p>
    </div>
    <a class="btn btn-primary" href="<?php echo e(route('admin.polls.create')); ?>">+ Nieuwe poll</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Maker</th>
            <th>Titel</th>
            <th>Type</th>
            <th>Status</th>
            <th>Publiek</th>
            <th>Stemmen (bevestigd/totaal)</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $polls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>
                    <?php if($poll->user): ?>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <?php if($poll->user->logo): ?>
                                <img src="<?php echo e(asset('storage/' . $poll->user->logo)); ?>" alt="<?php echo e($poll->user->name); ?>" style="height: 48px; width: 48px; border-radius: 50%; object-fit: cover; border: 1px solid #e5e7eb;">
                            <?php else: ?>
                                <div style="height: 32px; width: 32px; border-radius: 50%; background: #d1d5db; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 12px; color: #6b7280; font-weight: bold;"><?php echo e(substr($poll->user->name, 0, 1)); ?></span>
                                </div>
                            <?php endif; ?>
                            <span><?php echo e($poll->user->name); ?></span>
                        </div>
                    <?php else: ?>
                        <span style="color: #9ca3af;">Onbekend</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($poll->title); ?></td>
                <td><?php echo e($typeLabels[$poll->type] ?? $poll->type); ?></td>
                <td><?php echo e($poll->status); ?></td>
                <td><?php echo e($poll->is_public ? 'ja' : 'nee'); ?></td>
                <td><?php echo e($poll->confirmed_votes_count); ?> / <?php echo e($poll->votes_count); ?></td>
                <td class="inline">
                    <?php if($poll->status !== 'archived'): ?>
                        <a class="btn" href="<?php echo e(route('admin.polls.show', $poll)); ?>">Bekijk</a>
                    <?php endif; ?>
                    <?php if(!$poll->is_public): ?>
                        <a class="btn" href="<?php echo e(route('admin.polls.edit', $poll)); ?>">Bewerk</a>
                    <?php else: ?>
                        <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed;" title="Actieve polls kunnen niet bewerkt worden">Bewerk</button>
                    <?php endif; ?>
                    <form method="post" action="<?php echo e(route('admin.polls.toggle-active', $poll)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn" type="submit"><?php echo e($poll->is_public ? 'Zet onactief' : 'Zet actief'); ?></button>
                    </form>
                    <form method="post" action="<?php echo e(route('admin.polls.archive', $poll)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn" type="submit">Archiveer</button>
                    </form>
                    <form method="post" action="<?php echo e(route('admin.polls.destroy', $poll)); ?>" onsubmit="return confirm('Weet je het zeker?');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-danger" type="submit">Verwijder</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7">Nog geen polls.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo e($polls->links()); ?>

<?php $__env->stopSection(); ?>






<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\polls\index.blade.php ENDPATH**/ ?>