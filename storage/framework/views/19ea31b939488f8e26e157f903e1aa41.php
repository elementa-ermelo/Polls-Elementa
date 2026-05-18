

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Organisaties</h1>
        <a href="<?php echo e(route('admin.organizations.create')); ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Nieuwe Organisatie
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($organizations->isEmpty()): ?>
        <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded text-center">
            Geen organisaties gevonden. <a href="<?php echo e(route('admin.organizations.create')); ?>" class="text-blue-500 hover:text-blue-700 font-bold">Maak er één aan</a>.
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 border border-gray-300 text-left">Logo</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Naam</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Email</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Gebruikers</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Polls</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-100 border-b border-gray-300">
                            <td class="px-4 py-2 border border-gray-300">
                                <?php if($organization->logo): ?>
                                    <img src="<?php echo e(asset('storage/' . $organization->logo)); ?>" alt="<?php echo e($organization->name); ?>" class="h-20 w-20 object-cover rounded">
                                <?php else: ?>
                                    <div class="h-12 w-12 bg-gray-300 rounded flex items-center justify-center">
                                        <span class="text-gray-600 text-xs">Geen logo</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo e($organization->name); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo e($organization->email ?? '-'); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo e($organization->users_count ?? 0); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo e($organization->polls_count ?? 0); ?></td>
                            <td class="px-4 py-2 border border-gray-300">
                                <a href="<?php echo e(route('admin.organizations.show', $organization)); ?>" class="text-blue-500 hover:text-blue-700 mr-2">Bekijk</a>
                                <a href="<?php echo e(route('admin.organizations.edit', $organization)); ?>" class="text-yellow-500 hover:text-yellow-700 mr-2">Bewerk</a>
                                <form action="<?php echo e(route('admin.organizations.destroy', $organization)); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Weet je het zeker?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-500 hover:text-red-700">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\organizations\index.blade.php ENDPATH**/ ?>