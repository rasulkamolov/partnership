<?php
// app/pages/schools.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }

// Handle Edit Mode (if edit_id is set)
$edit_mode = false;
$school_to_edit = [];
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM schools WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $school_to_edit = $stmt->fetch();
    if ($school_to_edit) $edit_mode = true;
}
?>

<div class="grid lg:grid-cols-3 gap-8">
    <!-- Left Column: Form -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm sticky top-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-indigo-50 p-2.5 rounded-xl text-indigo-600"><i data-lucide="<?= $edit_mode ? 'edit-2' : 'plus-circle' ?>" class="w-6 h-6"></i></div>
                <h3 class="text-xl font-black text-slate-900"><?= $edit_mode ? 'Edit Partner' : 'Register Partner' ?></h3>
            </div>

            <form method="POST" class="space-y-4">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="edit_school" value="1">
                    <input type="hidden" name="school_id" value="<?= $school_to_edit['id'] ?>">
                <?php else: ?>
                    <input type="hidden" name="add_school" value="1">
                <?php endif; ?>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Institution Name</label>
                        <input type="text" name="sch_name" value="<?= $edit_mode ? htmlspecialchars($school_to_edit['name']) : '' ?>" placeholder="e.g. Cambridge School" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Username</label>
                        <input type="text" name="sch_user" value="<?= $edit_mode ? htmlspecialchars($school_to_edit['username']) : '' ?>" placeholder="Login identifier" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Password <?= $edit_mode ? '(Leave blank to keep)' : '' ?></label>
                        <input type="password" name="sch_pass" placeholder="Secure Access Key" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" <?= $edit_mode ? '' : 'required' ?>>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Contact Info</label>
                        <input type="text" name="sch_contact" value="<?= $edit_mode ? htmlspecialchars($school_to_edit['contact']) : '' ?>" placeholder="Phone or Email" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <?php if($edit_mode): ?>
                        <a href="?p=schools" class="w-full bg-slate-100 text-slate-600 font-bold py-3.5 rounded-xl hover:bg-slate-200 transition flex justify-center items-center text-sm">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-slate-900 transition shadow-lg shadow-indigo-500/30 flex justify-center gap-2 items-center text-sm">
                        <span><?= $edit_mode ? 'Update Details' : 'Establish Partnership' ?></span>
                        <i data-lucide="<?= $edit_mode ? 'check' : 'arrow-right' ?>" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm min-h-[600px]">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Active Partners</h3>
                <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-500">
                    <?= $db->query("SELECT COUNT(*) FROM schools WHERE id > 1")->fetchColumn() ?> Schools
                </div>
            </div>

            <div class="grid gap-4">
                <?php
                $schools = $db->query("
                    SELECT s.*, COUNT(st.id) as student_count
                    FROM schools s
                    LEFT JOIN students st ON s.id = st.school_id
                    WHERE s.id > 1
                    GROUP BY s.id
                    ORDER BY s.id DESC
                ")->fetchAll();

                foreach($schools as $s):
                ?>
                <div class="group bg-slate-50 hover:bg-white border border-slate-200/60 hover:border-indigo-200 hover:shadow-lg transition-all duration-300 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center font-black text-lg text-indigo-600 shadow-sm group-hover:scale-110 transition-transform">
                            <?= strtoupper(substr($s['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-lg mb-1 group-hover:text-indigo-600 transition"><?= htmlspecialchars($s['name']) ?></h4>
                            <div class="flex items-center gap-3 text-xs font-semibold text-slate-500">
                                <span class="flex items-center gap-1.5 bg-slate-200/50 px-2 py-1 rounded-md"><i data-lucide="user" class="w-3 h-3"></i> <?= htmlspecialchars($s['username']) ?></span>
                                <span class="flex items-center gap-1.5 bg-slate-200/50 px-2 py-1 rounded-md"><i data-lucide="phone" class="w-3 h-3"></i> <?= htmlspecialchars($s['contact']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                        <div class="text-right mr-2 hidden sm:block">
                            <p class="text-2xl font-black text-slate-900 leading-none"><?= $s['student_count'] ?></p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Students</p>
                        </div>

                        <a href="?p=monthly&school_id=<?= $s['id'] ?>" class="flex-1 sm:flex-none bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white p-3 rounded-xl transition-colors duration-300 flex justify-center items-center" title="View Stats">
                            <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                        </a>
                        <a href="?p=schools&edit_id=<?= $s['id'] ?>" class="flex-1 sm:flex-none bg-slate-100 text-slate-600 hover:bg-indigo-500 hover:text-white p-3 rounded-xl transition-colors duration-300 flex justify-center items-center" title="Edit">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </a>
                        <form method="POST" onsubmit="return confirm('Delete this school? All associated students and data will be removed.');" class="flex-1 sm:flex-none">
                            <input type="hidden" name="delete_school" value="1">
                            <input type="hidden" name="school_id" value="<?= $s['id'] ?>">
                            <button class="w-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white p-3 rounded-xl transition-colors duration-300 flex justify-center items-center" title="Delete">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
