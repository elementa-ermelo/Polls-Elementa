

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Organisatie Bewerken</h1>

    <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.organizations.update', $organization)); ?>" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded border border-gray-300">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Naam *</label>
            <input type="text" id="name" name="name" value="<?php echo e(old('name', $organization->name)); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Beschrijving</label>
            <textarea id="description" name="description" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" rows="4"><?php echo e(old('description', $organization->description)); ?></textarea>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e(old('email', $organization->email)); ?>" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-gray-700 font-bold mb-2">Telefoonnummer</label>
            <input type="text" id="phone" name="phone" value="<?php echo e(old('phone', $organization->phone)); ?>" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="website" class="block text-gray-700 font-bold mb-2">Website</label>
            <input type="url" id="website" name="website" value="<?php echo e(old('website', $organization->website)); ?>" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="logo" class="block text-gray-700 font-bold mb-2">Logo</label>
            <?php if($organization->logo): ?>
                <div class="mb-3">
                    <p class="text-gray-600 text-sm mb-2">Huidige logo:</p>
                    <img src="<?php echo e(asset('storage/' . $organization->logo)); ?>" alt="<?php echo e($organization->name); ?>" class="h-40 w-40 object-cover rounded">
                </div>
            <?php endif; ?>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            <p class="text-gray-600 text-sm mt-2">JPG, PNG of GIF. Maximaal 2MB. (Leeg laten om huidig logo te behouden)</p>
        </div>

        <div class="flex justify-between">
            <a href="<?php echo e(route('admin.organizations.show', $organization)); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Annuleren
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Wijzigingen Opslaan
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\organizations\edit.blade.php ENDPATH**/ ?>