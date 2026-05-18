

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔐 Toegangscode Vereist</h1>
            <p class="text-gray-600">Deze poll is beschermd. Voer de toegangscode in om verder te gaan.</p>
        </div>

        <?php if($errors->has('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo e($errors->first('error')); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('polls.verify-access-code', $poll)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="mb-6">
                <label for="access_code" class="block text-gray-700 font-semibold mb-2">
                    Toegangscode
                </label>
                <input
                    type="text"
                    id="access_code"
                    name="access_code"
                    placeholder="Voer de code in"
                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 uppercase"
                    required
                    autofocus
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition"
            >
                Toegang Verlenen
            </button>
        </form>

        <div class="mt-6 pt-6 border-t text-center">
            <a href="<?php echo e(route('polls.index')); ?>" class="text-blue-600 hover:text-blue-700">
                ← Terug naar polls
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\polls\access-code.blade.php ENDPATH**/ ?>