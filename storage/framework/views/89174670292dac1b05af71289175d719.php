

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold"><?php echo e($organization->name); ?></h1>
            <p class="text-gray-600"><?php echo e($organization->description); ?></p>
        </div>
        <div>
            <a href="<?php echo e(route('admin.organizations.edit', $organization)); ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                Bewerk
            </a>
            <a href="<?php echo e(route('admin.organizations.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Terug
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Logo -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h3 class="text-lg font-bold mb-4">Logo</h3>
            <?php if($organization->logo): ?>
                <img src="<?php echo e(asset('storage/' . $organization->logo)); ?>" alt="<?php echo e($organization->name); ?>" class="max-w-full h-auto rounded">
            <?php else: ?>
                <div class="h-40 bg-gray-300 rounded flex items-center justify-center">
                    <span class="text-gray-600">Geen logo</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contactgegevens -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h3 class="text-lg font-bold mb-4">Contactgegevens</h3>
            <div class="space-y-2">
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

        <!-- Statistieken -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h3 class="text-lg font-bold mb-4">Statistieken</h3>
            <div class="space-y-2">
                <p><strong>Gebruikers:</strong> <?php echo e($organization->users->count()); ?></p>
                <p><strong>Polls:</strong> <?php echo e($organization->polls->count()); ?></p>
                <p><strong>Aangemaakt:</strong> <?php echo e($organization->created_at->format('d-m-Y')); ?></p>
            </div>
        </div>
    </div>

    <!-- Gebruikers -->
    <div class="bg-white p-6 rounded border border-gray-300 mb-8">
        <h3 class="text-lg font-bold mb-4">Gebruikers (<?php echo e($organization->users->count()); ?>)</h3>
        <?php if($organization->users->isEmpty()): ?>
            <p class="text-gray-600">Geen gebruikers.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="px-4 py-2 text-left">Naam</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Lid sinds</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $organization->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?php echo e($user->name); ?></td>
                                <td class="px-4 py-2"><?php echo e($user->email); ?></td>
                                <td class="px-4 py-2"><?php echo e($user->created_at->format('d-m-Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Polls -->
    <div class="bg-white p-6 rounded border border-gray-300">
        <h3 class="text-lg font-bold mb-4">Polls (<?php echo e($organization->polls->count()); ?>)</h3>
        <?php if($organization->polls->isEmpty()): ?>
            <p class="text-gray-600">Geen polls.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php $__currentLoopData = $organization->polls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border border-gray-200 p-4 rounded hover:bg-gray-50">
                        <h4 class="font-bold text-lg mb-2"><?php echo e($poll->title); ?></h4>
                        <p class="text-gray-600 mb-2"><?php echo e($poll->description); ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500"><?php echo e($poll->votes->count()); ?> antwoorden</span>
                            <a href="<?php echo e(route('polls.show', $poll)); ?>" class="text-blue-500 hover:text-blue-700">Bekijk Poll</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\organizations\show.blade.php ENDPATH**/ ?>