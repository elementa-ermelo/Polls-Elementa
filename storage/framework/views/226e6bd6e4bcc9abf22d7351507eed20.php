

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <!-- Organization Header -->
    <div class="bg-white rounded border border-gray-300 p-6 mb-8">
        <div class="flex items-start gap-6">
            <div class="flex-shrink-0">
                <?php if($organization->logo): ?>
                    <img src="<?php echo e(asset('storage/' . $organization->logo)); ?>" alt="<?php echo e($organization->name); ?>" class="h-32 w-32 object-cover rounded">
                <?php else: ?>
                    <div class="h-32 w-32 bg-gray-300 rounded flex items-center justify-center">
                        <span class="text-gray-600">Geen logo</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-grow">
                <h1 class="text-4xl font-bold mb-2"><?php echo e($organization->name); ?></h1>
                <?php if($organization->description): ?>
                    <p class="text-gray-600 mb-4"><?php echo e($organization->description); ?></p>
                <?php endif; ?>
                <div class="space-y-2 text-sm text-gray-600">
                    <?php if($organization->email): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo e($organization->email); ?>" class="text-blue-500 hover:text-blue-700"><?php echo e($organization->email); ?></a></p>
                    <?php endif; ?>
                    <?php if($organization->phone): ?>
                        <p><strong>Telefoon:</strong> <?php echo e($organization->phone); ?></p>
                    <?php endif; ?>
                    <?php if($organization->website): ?>
                        <p><strong>Website:</strong> <a href="<?php echo e($organization->website); ?>" target="_blank" class="text-blue-500 hover:text-blue-700"><?php echo e($organization->website); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Polls -->
    <div>
        <h2 class="text-2xl font-bold mb-6">Polls van <?php echo e($organization->name); ?></h2>

        <?php if($polls->isEmpty()): ?>
            <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded text-center">
                Deze organisatie heeft nog geen polls gepubliceerd.
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php $__currentLoopData = $polls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded border border-gray-300 p-6 hover:shadow-lg transition">
                        <h3 class="text-xl font-bold mb-2"><?php echo e($poll->title); ?></h3>
                        <?php if($poll->description): ?>
                            <p class="text-gray-600 mb-4"><?php echo e($poll->description); ?></p>
                        <?php endif; ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500"><?php echo e($poll->votes->count()); ?> antwoorden</span>
                            <a href="<?php echo e(route('polls.show', $poll)); ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Doe mee
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\organizations\show.blade.php ENDPATH**/ ?>