<?php
/**
 * Applique les modifications restantes (fichiers existants uniquement).
 * A executer depuis la racine du projet Laravel :
 *   php apply_remaining_changes.php
 *
 * Ce script est sur (str_replace exact), il ne touche que le texte trouve.
 * Si un texte n'est pas trouve (fichier deja modifie autrement), il previent
 * et NE TOUCHE PAS au fichier.
 */

$edits = [];

// ---------- 1) bootstrap/app.php ----------
$edits[] = [
    'file' => 'bootstrap/app.php',
    'search' => "'admin' => \\App\\Http\\Middleware\\EnsureUserIsAdmin::class,\n        ]);",
    'replace' => "'admin' => \\App\\Http\\Middleware\\EnsureUserIsAdmin::class,\n            'affectation.access' => \\App\\Http\\Middleware\\EnsureUserCanAccessAffectation::class,\n        ]);",
];

// ---------- 2) app/Models/User.php ----------
$edits[] = [
    'file' => 'app/Models/User.php',
    'search' => "        'role',\n        'service',\n    ];",
    'replace' => "        'role',\n        'service',\n        'can_affectation',\n    ];",
];
$edits[] = [
    'file' => 'app/Models/User.php',
    'search' => "            'role' => UserRole::class,\n        ];",
    'replace' => "            'role' => UserRole::class,\n            'can_affectation' => 'boolean',\n        ];",
];
$edits[] = [
    'file' => 'app/Models/User.php',
    'search' => "    public function isAdmin(): bool\n    {\n        return \$this->role === UserRole::Admin;\n    }\n}",
    'replace' => "    public function isAdmin(): bool\n    {\n        return \$this->role === UserRole::Admin;\n    }\n\n    /**\n     * Whether this user is allowed to access the Affectation module.\n     * True for admins, users with a service, or users explicitly granted this permission.\n     */\n    public function canAccessAffectation(): bool\n    {\n        return \$this->isAdmin() || ! empty(\$this->service) || (bool) \$this->can_affectation;\n    }\n}",
];

// ---------- 3) app/Http/Controllers/UserController.php ----------
$edits[] = [
    'file' => 'app/Http/Controllers/UserController.php',
    'search' => "        'service' => ['nullable', 'string', 'max:255'],\n    ], [",
    'replace' => "        'service' => ['nullable', 'string', 'max:255'],\n        'can_affectation' => ['nullable', 'boolean'],\n    ], [",
];
$edits[] = [
    'file' => 'app/Http/Controllers/UserController.php',
    'search' => "    \$service = \$request->role === 'admin' ? null : \$request->service;\n\n    \$user->update([\n        'name' => \$request->name,\n        'role' => UserRole::from(\$request->role),\n        'service' => \$service,\n    ]);",
    'replace' => "    \$service = \$request->role === 'admin' ? null : \$request->service;\n    \$canAffectation = \$request->role === 'admin' ? false : \$request->boolean('can_affectation');\n\n    \$user->update([\n        'name' => \$request->name,\n        'role' => UserRole::from(\$request->role),\n        'service' => \$service,\n        'can_affectation' => \$canAffectation,\n    ]);",
];

// ---------- 4) routes/web.php ----------
$edits[] = [
    'file' => 'routes/web.php',
    'search' => "    Route::get('affectation', [AffectationController::class, 'index'])->name('affectation.index');\n    Route::get('affectation/export', [AffectationController::class, 'exportExcel'])->name('affectation.export');\n    Route::post('affectation', [AffectationController::class, 'store'])->name('affectation.store');\n    Route::put('affectation/{affectation}', [AffectationController::class, 'update'])->name('affectation.update');\n    Route::delete('affectation/{affectation}', [AffectationController::class, 'destroy'])->name('affectation.destroy');",
    'replace' => "    Route::middleware('affectation.access')->group(function () {\n        Route::get('affectation', [AffectationController::class, 'index'])->name('affectation.index');\n        Route::get('affectation/export', [AffectationController::class, 'exportExcel'])->name('affectation.export');\n        Route::post('affectation', [AffectationController::class, 'store'])->name('affectation.store');\n        Route::put('affectation/{affectation}', [AffectationController::class, 'update'])->name('affectation.update');\n        Route::delete('affectation/{affectation}', [AffectationController::class, 'destroy'])->name('affectation.destroy');\n    });",
];

// ---------- 5) resources/views/layouts/master.blade.php ----------
$edits[] = [
    'file' => 'resources/views/layouts/master.blade.php',
    'search' => "@if(auth()->user()->isAdmin() || !empty(auth()->user()->service))",
    'replace' => "@if(auth()->user()->canAccessAffectation())",
];

// ---------- 6) resources/views/users/index.blade.php ----------
$edits[] = [
    'file' => 'resources/views/users/index.blade.php',
    'search' => "                                       data-service=\"{{ \$u->service }}\"\n                                       data-toggle=\"modal\" data-target=\"#editUserModal\"",
    'replace' => "                                       data-service=\"{{ \$u->service }}\"\n                                       data-can-affectation=\"{{ \$u->can_affectation ? '1' : '0' }}\"\n                                       data-toggle=\"modal\" data-target=\"#editUserModal\"",
];
$edits[] = [
    'file' => 'resources/views/users/index.blade.php',
    'search' => "                            @endforeach\n                        </select>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">",
    'replace' => "                            @endforeach\n                        </select>\n                    </div>\n                    <div class=\"form-check mt-3\" id=\"edit_can_affectation_group\">\n                        <input type=\"checkbox\" class=\"form-check-input\" name=\"can_affectation\" id=\"edit_can_affectation\" value=\"1\">\n                        <label class=\"form-check-label\" for=\"edit_can_affectation\">\n                            Accès au module Affectation (sans besoin d'un Service)\n                        </label>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">",
];
$edits[] = [
    'file' => 'resources/views/users/index.blade.php',
    'search' => "    \$('#edit_service').val(button.data('service'));\n\n    var isLastAdmin",
    'replace' => "    \$('#edit_service').val(button.data('service'));\n    \$('#edit_can_affectation').prop('checked', button.data('can-affectation') == '1');\n\n    var isLastAdmin",
];
$edits[] = [
    'file' => 'resources/views/users/index.blade.php',
    'search' => "    if (role === 'admin') {\n        \$('#edit_service_group').hide();\n        \$('#edit_service').val('');\n    } else {\n        \$('#edit_service_group').show();\n    }\n}",
    'replace' => "    if (role === 'admin') {\n        \$('#edit_service_group').hide();\n        \$('#edit_service').val('');\n        \$('#edit_can_affectation_group').hide();\n        \$('#edit_can_affectation').prop('checked', false);\n    } else {\n        \$('#edit_service_group').show();\n        \$('#edit_can_affectation_group').show();\n    }\n}",
];

// ---------- Application ----------
$ok = 0; $fail = 0;
foreach ($edits as $e) {
    $path = $e['file'];
    if (!file_exists($path)) {
        echo "[SKIP] Fichier introuvable : $path\n";
        $fail++;
        continue;
    }
    $content = file_get_contents($path);
    if (strpos($content, $e['search']) === false) {
        echo "[ATTENTION] Motif non trouve dans $path — fichier deja modifie ou different, AUCUN changement applique pour cet extrait.\n";
        $fail++;
        continue;
    }
    $new = str_replace($e['search'], $e['replace'], $content, $count);
    file_put_contents($path, $new);
    echo "[OK] $path (extrait applique)\n";
    $ok++;
}

echo "\nTermine : $ok extraits appliques, $fail non appliques.\n";
if ($fail > 0) {
    echo "Regardez les lignes [ATTENTION] ci-dessus et envoyez-les a Claude pour ajuster le script.\n";
}
