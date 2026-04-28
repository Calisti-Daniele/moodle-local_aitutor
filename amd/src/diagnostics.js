define(['core/ajax'], function(Ajax) {

    var init = function() {
        var container = document.getElementById('aitutor-diagnostics');
        if (!container) {
            return;
        }

        // Bottone "Riesegui diagnostica"
        var rerunBtn = document.getElementById('aitutor-diag-rerun');
        if (rerunBtn) {
            rerunBtn.addEventListener('click', function() {
                runDiagnostics(false, '');
            });
        }

        // Bottone "Correggi tutto"
        var fixAllBtn = document.getElementById('aitutor-diag-fix-all');
        if (fixAllBtn) {
            fixAllBtn.addEventListener('click', function() {
                runDiagnostics(false, 'all');
            });
        }

        // Bottoni autofix singoli
        container.addEventListener('click', function(e) {
            var btn = e.target.closest('.aitutor-diag-autofix');
            if (btn) {
                var action = btn.dataset.action;
                runDiagnostics(false, action);
            }
        });
    };

    var runDiagnostics = function(quickcheck, fix) {
        var loading = document.getElementById('aitutor-diag-loading');
        var checks  = document.getElementById('aitutor-diag-checks');
        var result  = document.getElementById('aitutor-diag-result');
        var rerun   = document.getElementById('aitutor-diag-rerun');

        // Mostra loading
        if (loading) {
            loading.classList.remove('d-none');
        }
        if (checks) {
            checks.style.opacity = '0.4';
        }
        if (rerun) {
            rerun.disabled = true;
        }

        Ajax.call([{
            methodname: 'local_aitutor_run_diagnostics',
            args: {
                quickcheck: quickcheck || false,
                fix: fix || '',
            },
            done: function(response) {
                // Nascondi loading
                if (loading) {
                    loading.classList.add('d-none');
                }
                if (checks) {
                    checks.style.opacity = '1';
                }
                if (rerun) {
                    rerun.disabled = false;
                }

                // Ricarica la pagina per mostrare i nuovi risultati
                window.location.reload();
            },
            fail: function(error) {
                if (loading) {
                    loading.classList.add('d-none');
                }
                if (checks) {
                    checks.style.opacity = '1';
                }
                if (rerun) {
                    rerun.disabled = false;
                }

                if (result) {
                    result.innerHTML =
                        '<div class="alert alert-danger">' +
                        '❌ ' + (error.message || 'Error running diagnostics') +
                        '</div>';
                    result.classList.remove('d-none');
                }
            },
        }]);
    };

    return {
        init: init,
    };
});