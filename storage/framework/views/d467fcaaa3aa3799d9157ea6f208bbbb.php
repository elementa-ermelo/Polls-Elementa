<?php $__env->startSection('content'); ?>


<?php if($allPolls->count() > 1): ?>
<div style="margin-bottom: 28px;">
    <p style="margin: 0 0 12px; font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Beschikbare Polls</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php $__currentLoopData = $allPolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('polls.show', $p)); ?>"
               style="padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.2s;
                      <?php echo e($p->id === $poll->id
                            ? 'background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);'
                            : 'background: var(--surface); color: var(--primary); border: 1.5px solid var(--primary);'); ?>

               ">
                <?php echo e($p->title); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>


<div class="card" style="margin-bottom:24px; padding:24px; background:linear-gradient(135deg, var(--accent) 0%, #a7d8de 100%); color:#fff; border:none;">
    <h2 style="margin:0 0 12px; font-size:18px; font-weight:700; color:#fff;">❓ Welkom bij deze poll</h2>
    <p style="margin:0 0 8px; line-height:1.6; font-size:15px; color:#fff;">
        We willen graag jouw eerlijke mening horen. Door deze vragen in te vullen, help je ons inzichten te verzamelen.
    </p>
</div>


<div class="card" style="margin-bottom:16px; padding:20px; background:#dbeafe; border:1px solid #bfdbfe;">
    <h3 style="margin:0 0 12px; color:var(--primary); display:flex; align-items:center; gap:8px;">
        <span>💡</span> Hoe werkt het indienen?
    </h3>
    <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
        <strong>1. Vul je gegevens in</strong> — We vragen je naam, e-mailadres, telefoonnummer en leeftijd zodat we kunnen verifiëren dat je echt bent.
    </p>
    <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
        <strong>2. Beantwoord de vragen</strong> — Geef je eerlijke mening.
    </p>
    <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
        <strong>3. Verstuur en bevestig</strong> — Na verzending krijg je direct een e-mail met een bevestigingslink. 
        Klik erop binnen 24 uur en je stem telt mee! 📧
    </p>
    <p style="margin:0; color:#1e40af; font-size:13px; opacity:0.9; font-style:italic;">
        ✨ Jouw perspective is waardevol — bedankt dat je meedoet!
    </p>
</div>


<div class="card" style="margin-bottom:16px; padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1 style="margin:0 0 6px; font-size:24px;"><?php echo e($poll->title); ?></h1>
            <?php if($poll->question): ?>
                <p style="margin:0; color:var(--muted); font-size:15px;"><?php echo e($poll->question); ?></p>
            <?php endif; ?>
        </div>
        <?php if($poll->user): ?>
            <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                <?php if($poll->user->logo): ?>
                    <img src="<?php echo e(asset('storage/' . $poll->user->logo)); ?>" alt="<?php echo e($poll->user->name); ?>"
                         style="height:44px; width:44px; border-radius:50%; object-fit:cover; border:2px solid var(--line);">
                <?php else: ?>
                    <div style="height:44px; width:44px; border-radius:50%; background:var(--accent-soft); display:flex; align-items:center; justify-content:center;">
                        <span style="font-size:17px; color:var(--accent); font-weight:700;"><?php echo e(substr($poll->user->name,0,1)); ?></span>
                    </div>
                <?php endif; ?>
                <div>
                    <p style="margin:0; font-size:11px; color:var(--muted);">Aangemaakt door</p>
                    <p style="margin:0; font-weight:600; font-size:14px;"><?php echo e($poll->user->name); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if($poll->closes_at && $poll->isOpen()): ?>
        <div style="margin-top:14px; padding:8px 12px; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; font-size:13px; color:#9a3412;">
            Sluit op <?php echo e($poll->closes_at->format('d-m-Y \o\m H:i')); ?>

        </div>
    <?php endif; ?>
</div>

<?php if($needsAccessCode): ?>
    
    <div class="card" style="margin-bottom:16px; padding:20px; background:#fef3c7; border:2px solid #fbbf24;">
        <h2 style="margin:0 0 12px; font-size:16px; color:#92400e; font-weight:600;">🔐 Toegangscode vereist</h2>
        <p style="margin:0 0 16px; color:#b45309; font-size:14px;">Deze poll is beveiligd. Voer de toegangscode in om deel te nemen.</p>
        
        <?php if($errors->has('error')): ?>
            <div style="padding:12px; background:#fee2e2; border:1px solid #fecaca; border-radius:8px; margin-bottom:12px; color:#991b1b; font-size:14px;">
                <?php echo e($errors->first('error')); ?>

            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo e(route('polls.verify-access-code', $poll)); ?>" style="display:flex; gap:8px;">
            <?php echo csrf_field(); ?>
            <input type="text" name="access_code" placeholder="Voer code in..." 
                   style="flex:1; padding:10px 12px; border:1px solid #fbbf24; border-radius:6px; font-size:14px; text-transform:uppercase;"
                   required autofocus>
            <button type="submit" class="btn btn-primary" style="padding:10px 20px; white-space:nowrap;">Verifiëren</button>
        </form>
    </div>
<?php elseif($poll->isOpen()): ?>

    
    <?php if($errors->any()): ?>
        <div style="padding:14px 16px; background:#fee2e2; border:1px solid #fecaca; border-radius:10px; margin-bottom:16px;">
            <p style="margin:0 0 8px; font-weight:700; color:#991b1b;">Let op:</p>
            <ul style="margin:0; padding-left:20px; color:#991b1b; font-size:14px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(route('polls.vote', $poll)); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="card" style="margin-bottom:16px; padding:20px;">
            <h2 style="margin:0 0 16px; font-size:16px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Uw gegevens</h2>
            <div class="grid grid-2">
                <div class="field">
                    <label for="respondent_name">Naam *</label>
                    <input id="respondent_name" name="respondent_name" value="<?php echo e(old('respondent_name')); ?>" placeholder="Uw volledige naam" required>
                    <?php $__errorArgs = ['respondent_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="vote-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="field">
                    <label for="email">E-mailadres *</label>
                    <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="naam@voorbeeld.nl" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="vote-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label for="phone">Telefoonnummer *</label>
                    <input id="phone" type="tel" name="phone" value="<?php echo e(old('phone')); ?>" placeholder="+31 6 12345678" required>
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="vote-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="field">
                    <label for="age">Leeftijd *</label>
                    <input id="age" type="number" name="age" min="16" max="120" value="<?php echo e(old('age')); ?>" placeholder="bv. 28" required>
                    <?php $__errorArgs = ['age'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="vote-err"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <?php if($poll->questions && $poll->questions->count() > 0): ?>

            <?php $__currentLoopData = $poll->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card" style="margin-bottom:16px; padding:20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div style="width:28px; height:28px; border-radius:50%; background:var(--accent); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><?php echo e($index+1); ?></div>
                    <h3 style="margin:0; font-size:17px; line-height:1.3;"><?php echo e($question->question); ?></h3>
                </div>

                <?php if(\App\Support\PollType::isOpenTextType($question->type)): ?>
                    <div class="field" style="margin:0;">
                        <textarea name="question_<?php echo e($question->id); ?>" rows="4"
                                  placeholder="Typ hier uw antwoord…"
                                  style="resize:vertical;"><?php echo e(old('question_'.$question->id)); ?></textarea>
                    </div>

                <?php elseif($question->type === \App\Support\PollType::RATING_STARS): ?>
                    <div class="star-wrap">
                        <?php $__currentLoopData = range(5,1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($sid = 'q'.$question->id.'_r'.$val); ?>
                            <input id="<?php echo e($sid); ?>" class="star-inp" type="radio"
                                   name="question_<?php echo e($question->id); ?>" value="<?php echo e($val); ?>"
                                   <?php if((string) old('question_'.$question->id) === (string) $val): echo 'checked'; endif; ?>>
                            <label for="<?php echo e($sid); ?>" class="star-lbl" title="<?php echo e($val); ?> ster<?php echo e($val > 1 ? 'ren' : ''); ?>">★</label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                <?php else: ?>
                    <div class="choice-grid">
                        <?php if($question->options && $question->options->count() > 0): ?>
                            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($oid = 'q'.$question->id.'_o'.$option->id); ?>
                                <div>
                                    <input class="choice-inp" id="<?php echo e($oid); ?>" type="radio"
                                           name="question_<?php echo e($question->id); ?>" value="<?php echo e($option->id); ?>"
                                           <?php if((string) old('question_'.$question->id) === (string) $option->id): echo 'checked'; endif; ?>>
                                    <label class="choice-lbl" for="<?php echo e($oid); ?>"><?php echo e($option->label); ?></label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <p style="color:var(--muted); font-size:14px;">Geen antwoordopties beschikbaar.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php $__errorArgs = ['question_'.$question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="vote-err" style="margin-top:10px;">Dit antwoord is verplicht.</p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <div class="card" style="padding:20px; text-align:center;">
                <p style="margin:0 0 14px; color:var(--muted); font-size:14px;">
                    Na het versturen ontvangt u een bevestigingsmail om uw stem te bevestigen.
                </p>
                <button type="submit" class="btn btn-primary"
                        style="padding:12px 40px; font-size:16px; border-radius:10px; width:100%; max-width:360px;">
                    Verstuur mijn antwoorden →
                </button>
            </div>
        <?php else: ?>
            <div class="card" style="padding:24px; text-align:center;">
                <p style="font-size:32px; margin:0 0 8px;">⚠️</p>
                <p style="margin:0; font-size:16px;">Deze poll bevat nog geen vragen.</p>
            </div>
        <?php endif; ?>
    </form>

<?php elseif($poll->opens_at && now()->lt($poll->opens_at)): ?>
    <div class="card" style="padding:24px; text-align:center;">
        <p style="font-size:32px; margin:0 0 8px;">📅</p>
        <p style="margin:0; font-size:16px;">Deze poll is nog niet open.<br>
           <strong>Start op <?php echo e($poll->opens_at->format('d-m-Y \o\m H:i')); ?></strong></p>
    </div>
<?php else: ?>
    <div class="card" style="padding:24px; text-align:center;">
        <p style="font-size:32px; margin:0 0 8px;">🔒</p>
        <p style="margin:0; font-size:16px;">Deze poll is gesloten. Bedankt voor uw interesse.</p>
    </div>
<?php endif; ?>

<style>
.vote-err { color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600; }

/* Radio keuze */
.choice-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}
.choice-inp {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}
.choice-lbl {
    display: block;
    margin: 0;
    padding: 12px 16px;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    background: var(--surface);
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
    text-align: center;
}
.choice-lbl:hover {
    border-color: var(--primary);
    background: rgba(59, 130, 246, 0.08);
    transform: translateY(-2px);
}
.choice-inp:checked + .choice-lbl {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    font-weight: 700;
    color: var(--primary);
}

/* Sterren */
.star-wrap {
    display: inline-flex;
    flex-direction: row-reverse;
    gap: 8px;
    margin: 0;
}
.star-inp {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}
.star-lbl {
    margin: 0;
    font-size: 40px;
    line-height: 1;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.15s;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.05));
}
.star-lbl:hover,
.star-lbl:hover ~ .star-lbl,
.star-inp:checked ~ .star-lbl {
    color: #f59e0b;
    filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.4));
    transform: scale(1.1);
}
.star-inp:checked ~ .star-lbl {
    transform: scale(1.15);
}
</style>

<script>
// Additional functionality can be added here
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\polls\show.blade.php ENDPATH**/ ?>