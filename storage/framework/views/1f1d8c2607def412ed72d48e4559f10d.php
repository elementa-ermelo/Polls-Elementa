<?php $__env->startSection('content'); ?>


<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;">
    <div>
        <h1 style="margin: 0 0 6px; color: var(--text);"><?php echo e($poll->title); ?></h1>
        <?php if($poll->question): ?>
            <p class="meta" style="margin: 0 0 12px;"><?php echo e($poll->question); ?></p>
        <?php endif; ?>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                <?php echo e($poll->status === 'active' ? 'background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46;' : ($poll->status === 'archived' ? 'background: #f1f5f9; color: #64748b;' : 'background: #fef3c7; color: #92400e;')); ?>">
                <?php echo e(ucfirst($poll->status)); ?>

            </span>
            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                <?php echo e($poll->is_public ? 'background: linear-gradient(135deg, #dbeafe 0%, #bae6fd 100%); color: #0c4a6e;' : 'background: #f1f5f9; color: #64748b;'); ?>">
                <?php echo e($poll->is_public ? '🌐 Publiek' : '🔒 Privé'); ?>

            </span>
            <?php if($poll->closes_at): ?>
                <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    ⏰ <?php echo e($poll->closes_at->format('d-m-Y H:i')); ?>

                </span>
            <?php endif; ?>
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if($poll->status !== 'archived'): ?>
            <a class="btn" href="<?php echo e(route('polls.show', $poll)); ?>" target="_blank" style="font-size: 13px;">👁 Bekijk poll</a>
        <?php endif; ?>
        <?php if(!$poll->is_public): ?>
            <a class="btn" href="<?php echo e(route('admin.polls.edit', $poll)); ?>" style="font-size: 13px;">✏️ Bewerk</a>
        <?php else: ?>
            <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed; font-size: 13px;" title="Actieve polls kunnen niet bewerkt worden">✏️ Bewerk</button>
        <?php endif; ?>
        <form method="post" action="<?php echo e(route('admin.polls.toggle-active', $poll)); ?>" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn" style="font-size:13px;">
                <?php echo e($poll->is_public ? 'Deactiveer' : 'Activeer'); ?>

            </button>
        </form>
        <form method="post" action="<?php echo e(route('admin.polls.archive', $poll)); ?>" style="display:inline;" onsubmit="return confirm('Poll archiveren?');">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn" style="font-size:13px; color:var(--muted);">Archiveer</button>
        </form>
    </div>
</div>


<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="card" style="text-align: center; padding: 24px; border-left: 5px solid var(--primary); background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);">
        <p style="margin: 0 0 8px; font-size: 32px; font-weight: 800; color: var(--primary);"><?php echo e($totalRespondents); ?></p>
        <p class="meta" style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Totaal Respondenten</p>
    </div>
    <div class="card" style="text-align: center; padding: 24px; border-left: 5px solid var(--success); background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);">
        <p style="margin: 0 0 8px; font-size: 32px; font-weight: 800; color: var(--success);"><?php echo e($confirmedRespondents); ?></p>
        <p class="meta" style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Bevestigd</p>
    </div>
    <div class="card" style="text-align: center; padding: 24px; border-left: 5px solid #9333ea; background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);">
        <p style="margin: 0 0 8px; font-size: 32px; font-weight: 800; color: #9333ea;"><?php echo e($poll->questions->count()); ?></p>
        <p class="meta" style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Vragen</p>
    </div>
</div>


<div style="display: flex; gap: 0; margin-bottom: 24px; border-bottom: 2px solid var(--line); flex-wrap: wrap;">
    <button onclick="showTab('beheer')" id="tab-beheer" class="tab-btn" style="padding: 14px 20px; border: none; background: none; cursor: pointer; font-weight: 700; color: var(--primary); border-bottom: 3px solid var(--primary); font-size: 15px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;">⚙️ Beheer</button>
    <button onclick="showTab('resultaten')" id="tab-resultaten" class="tab-btn" style="padding: 14px 20px; border: none; background: none; cursor: pointer; font-weight: 700; color: var(--muted); border-bottom: 3px solid transparent; font-size: 15px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;">📊 Resultaten</button>
    <button onclick="showTab('stemmen')" id="tab-stemmen" class="tab-btn" style="padding: 14px 20px; border: none; background: none; cursor: pointer; font-weight: 700; color: var(--muted); border-bottom: 3px solid transparent; font-size: 15px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;">📋 Alle Stemmen</button>
</div>

<script>
function showTab(tab) {
    document.getElementById('beheer-content').style.display = tab === 'beheer' ? 'block' : 'none';
    document.getElementById('resultaten-content').style.display = tab === 'resultaten' ? 'block' : 'none';
    document.getElementById('stemmen-content').style.display = tab === 'stemmen' ? 'block' : 'none';

    document.getElementById('tab-beheer').style.color = tab === 'beheer' ? 'var(--primary)' : 'var(--muted)';
    document.getElementById('tab-beheer').style.borderColor = tab === 'beheer' ? 'var(--primary)' : 'transparent';
    document.getElementById('tab-resultaten').style.color = tab === 'resultaten' ? 'var(--primary)' : 'var(--muted)';
    document.getElementById('tab-resultaten').style.borderColor = tab === 'resultaten' ? 'var(--primary)' : 'transparent';
    document.getElementById('tab-stemmen').style.color = tab === 'stemmen' ? 'var(--primary)' : 'var(--muted)';
    document.getElementById('tab-stemmen').style.borderColor = tab === 'stemmen' ? 'var(--primary)' : 'transparent';
}
</script>


<div id="beheer-content" style="display:block;">
<div class="card" style="margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="margin:0; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted);">Vragen</h2>
        <a class="btn btn-primary" href="<?php echo e(route('admin.polls.questions.create', $poll)); ?>" style="font-size:13px;">+ Vraag toevoegen</a>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $poll->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="padding:14px; border:1px solid var(--line); border-radius:10px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div style="flex:1; min-width:0;">
                <p style="margin:0 0 4px; font-weight:600; font-size:15px;"><?php echo e($question->question); ?></p>
                <p class="meta" style="margin:0; font-size:13px;">
                    <?php echo e($question->type); ?> &bull; <?php echo e($question->options->count()); ?> opties &bull; <?php echo e($question->votes->whereNotNull('confirmed_at')->count()); ?> bevestigde stemmen
                </p>
            </div>
            <div style="display:flex; gap:6px; flex-shrink:0;">
                <a class="btn" href="<?php echo e(route('admin.polls.questions.edit', [$poll, $question])); ?>" style="font-size:13px; padding:6px 12px;">Bewerk</a>
                <form method="post" action="<?php echo e(route('admin.polls.questions.destroy', [$poll, $question])); ?>" onsubmit="return confirm('Vraag verwijderen?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-danger" type="submit" style="font-size:13px; padding:6px 12px;">Verwijder</button>
                </form>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="meta" style="text-align:center; padding:20px 0;">Nog geen vragen. <a href="<?php echo e(route('admin.polls.questions.create', $poll)); ?>" style="color:var(--accent);">Eerste vraag toevoegen →</a></p>
    <?php endif; ?>
</div>

</div>


<div id="resultaten-content" style="display:none;">
<?php if($poll->questions->count() > 0): ?>
<div class="card" style="margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <h2 style="margin:0; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted);">Resultaten</h2>
    </div>
    <?php $__currentLoopData = $poll->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qi => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $qTotal = $question->votes->whereNotNull('confirmed_at')->count();
        ?>
        <div style="<?php echo e(!$loop->first ? 'margin-top:24px; padding-top:20px; border-top:1px solid var(--line);' : ''); ?>">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                <div style="width:22px;height:22px;border-radius:50%;background:var(--accent);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?php echo e($qi+1); ?></div>
                <p style="margin:0; font-weight:600; font-size:15px;"><?php echo e($question->question); ?></p>
                <span class="meta" style="margin-left:auto; font-size:13px; white-space:nowrap;"><?php echo e($qTotal); ?> stem<?php echo e($qTotal !== 1 ? 'men' : ''); ?></span>
            </div>
            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $cnt = $option->votes->whereNotNull('confirmed_at')->count();
                    $pct = $qTotal > 0 ? round(($cnt / $qTotal) * 100, 1) : 0;
                ?>
                <div style="margin-bottom:10px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:14px;">
                        <span><?php echo e($option->label); ?></span>
                        <span style="color:var(--muted);"><?php echo e($cnt); ?> &bull; <?php echo e($pct); ?>%</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <span style="width:<?php echo e($pct); ?>%; transition:width .4s;"></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <?php
        $openQuestions = $poll->questions->filter(fn($q) => \App\Support\PollType::isOpenTextType($q->type));
    ?>
    <?php if($openQuestions->count() > 0): ?>
        <h3 style="margin: 24px 0 16px; font-size: 14px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em;">💬 Open Antwoorden</h3>
        <?php $__currentLoopData = $openQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qindex => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $answers = $question->votes->whereNotNull('open_answer')->unique('email');
            ?>
            <?php if($answers->count() > 0): ?>
                <div style="margin-bottom: 12px; border: 1px solid var(--line); border-radius: 10px; overflow: hidden;">
                    
                    <button onclick="document.getElementById('qa-<?php echo e($question->id); ?>').style.display = document.getElementById('qa-<?php echo e($question->id); ?>').style.display === 'none' ? 'block' : 'none';"
                            style="width: 100%; padding: 14px; background: var(--bg); border: none; text-align: left; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 600; transition: background .15s;">
                        <span><?php echo e($question->question); ?> <span style="color: var(--muted); font-size: 12px; font-weight: 400;">(<?php echo e($answers->count()); ?> antwoorden)</span></span>
                        <span style="font-size: 18px; color: var(--muted);">▼</span>
                    </button>

                    
                    <div id="qa-<?php echo e($question->id); ?>" style="display: none; padding: 14px; background: #fff; max-height: 500px; overflow-y: auto;">
                        <div style="display: grid; gap: 10px;">
                            <?php $__currentLoopData = $answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div style="padding: 12px; background: var(--bg); border-radius: 6px; border-left: 3px solid var(--primary);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                        <div style="flex: 1;">
                                            <p style="margin: 0 0 4px; font-weight: 500; font-size: 13px;"><?php echo e($vote->respondent_name); ?></p>
                                            <p style="margin: 0 0 8px; color: var(--text); font-size: 13px; line-height: 1.5; word-break: break-word;"><?php echo e($vote->open_answer); ?></p>
                                            <p style="margin: 0; color: var(--muted); font-size: 11px;"><?php echo e($vote->created_at->format('d-m H:i')); ?>

                                                <?php if($vote->confirmed_at): ?>
                                                    <span style="color: var(--success);"> • ✓</span>
                                                <?php else: ?>
                                                    <span style="color: #ea580c;"> • ⏳</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <style>
            button[onclick*="qa-"]:hover { background: var(--line) !important; }
        </style>
    <?php endif; ?>
</div>
<?php endif; ?>
</div>


<div id="stemmen-content" style="display:none;">
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="margin:0; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted);">Binnengekomen stemmen</h2>
        <span class="meta" style="font-size:13px;"><?php echo e($totalRespondents); ?> respondenten</span>
    </div>


<?php if($poll->votes->count() > 0): ?>
    <h3 style="margin: 20px 0 16px; font-size: 14px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em;">📋 Alle Respondenten</h3>
    <div style="display: grid; gap: 12px;">
        <?php
            $respondents = $poll->votes->groupBy('email')->map(function($group) {
                return [
                    'name' => $group->first()->respondent_name,
                    'email' => $group->first()->email,
                    'age' => $group->first()->age,
                    'created_at' => $group->first()->created_at,
                    'confirmed' => $group->first()->confirmed_at,
                    'vote_id' => $group->first()->id
                ];
            })->values();
        ?>
        <?php $__currentLoopData = $respondents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $respondent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="respondent-item" style="border: 1px solid var(--line); border-radius: 10px; background: var(--surface); overflow: hidden; transition: all 0.2s;">
                
                <button onclick="this.closest('.respondent-item').querySelector('.respondent-expanded').style.display = this.closest('.respondent-item').querySelector('.respondent-expanded').style.display === 'none' ? 'block' : 'none'; this.querySelector('.toggle-icon').style.transform = this.closest('.respondent-item').querySelector('.respondent-expanded').style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';" style="width: 100%; padding: 12px 16px; border: none; background: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='none'">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; text-align: left;">
                        <span style="font-size: 16px; font-weight: 700; color: var(--primary); min-width: 32px;"><?php echo e($index + 1); ?></span>
                        <div>
                            <p style="margin: 0; font-weight: 700; font-size: 14px;"><?php echo e($respondent['name']); ?></p>
                            <p style="margin: 0; color: var(--muted); font-size: 12px;"><?php echo e($respondent['email']); ?></p>
                        </div>
                    </div>
                    <span class="toggle-icon" style="font-size: 18px; color: var(--muted); transition: transform 0.2s;">▼</span>
                </button>

                
                <div class="respondent-expanded" style="display: none; padding: 12px 16px; border-top: 1px solid var(--line); background: var(--bg);">
                    <div style="display: flex; gap: 16px; margin-bottom: 12px; font-size: 13px; flex-wrap: wrap;">
                        <span><strong>Leeftijd:</strong> <?php echo e($respondent['age']); ?></span>
                        <span><strong>Datum:</strong> <?php echo e($respondent['created_at']->format('d-m-Y H:i')); ?></span>
                        <?php if($respondent['confirmed']): ?>
                            <span style="color: var(--success); font-weight: 700;">✓ Bevestigd</span>
                        <?php else: ?>
                            <span style="color: #ea580c; font-weight: 700;">⏳ Wacht</span>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="<?php echo e(route('admin.polls.votes.destroy', [$poll, $respondent['vote_id']])); ?>" onsubmit="return confirm('Alle stemmen van deze persoon verwijderen?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-danger" type="submit" style="font-size: 12px; padding: 6px 10px;">✕ Verwijder</button>
                    </form>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php else: ?>
    <p class="meta" style="text-align:center; padding:20px 0;">Nog geen stemmen binnengekomen.</p>
<?php endif; ?>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Elementa-polls\Polls-Elementa\resources\views\admin\polls\show.blade.php ENDPATH**/ ?>