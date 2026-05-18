

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Organisaties</h1>

    <?php if($organizations->isEmpty()): ?>
        <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded text-center">
            Geen organisaties beschikbaar.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('organizations.show', $organization->slug)); ?>" class="bg-white rounded border border-gray-300 p-6 hover:shadow-lg transition">
                    <div class="mb-4">
                        <?php if($organization->logo): ?>
                            <img src="<?php echo e(asset('storage/' . $organization->logo)); ?>" alt="<?php echo e($organization->name); ?>" class="w-full h-40 object-cover rounded">
                        <?php else: ?>
                            <div class="w-full h-40 bg-gray-300 rounded flex items-center justify-center">
                                <span class="text-gray-600">Geen logo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-bold mb-2 hover:text-blue-500"><?php echo e($organization->name); ?></h3>
                    <?php if($organization->description): ?>
                        <p class="text-gray-600 text-sm mb-4"><?php echo e(Str::limit($organization->description, 100)); ?></p>
                    <?php endif; ?>
                    <div class="text-sm text-gray-500">
                        <?php echo e($organization->polls()->where('status', 'active')->count()); ?> actieve polls
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\organizations\index.blade.php ENDPATH**/ ?>