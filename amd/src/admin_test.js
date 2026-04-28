// amd/src/admin_test.js
define(['core/ajax'], function(Ajax) {

    var init = function() {
        var btn     = document.getElementById('aitutor-test-btn');
        var spinner = document.getElementById('aitutor-test-spinner');
        var result  = document.getElementById('aitutor-test-result');

        if (!btn) {
            return;
        }

        btn.addEventListener('click', function() {
            var providerSelect = document.querySelector(
                '[name="s_local_aitutor_provider"]'
            );
            var provider = providerSelect ? providerSelect.value : 'ollama';

            btn.disabled = true;
            spinner.classList.remove('d-none');
            result.classList.add('d-none');
            result.innerHTML = '';

            Ajax.call([{
                methodname: 'local_aitutor_test_connection',
                args: {provider: provider},
                done: function(response) {
                    var alertClass = response.success
                        ? 'alert-success'
                        : 'alert-danger';
                    var icon = response.success ? '✅' : '❌';
                    var html = '<div class="alert ' + alertClass + ' mb-0">' +
                               '<strong>' + icon + ' ' + response.message + '</strong>';

                    if (response.success && response.models && response.models.length > 0) {
                        var modelList = response.models.slice(0, 8).join(', ');
                        if (response.models.length > 8) {
                            modelList += ' (+' + (response.models.length - 8) + ' more)';
                        }
                        html += '<br><small>Available models: ' + modelList + '</small>';
                    }

                    html += '</div>';
                    result.innerHTML = html;
                    result.classList.remove('d-none');
                    btn.disabled = false;
                    spinner.classList.add('d-none');
                },
                fail: function(error) {
                    result.innerHTML = '<div class="alert alert-danger mb-0">' +
                                       '❌ <strong>Connection failed:</strong> ' +
                                       (error.message || 'Unknown error') +
                                       '</div>';
                    result.classList.remove('d-none');
                    btn.disabled = false;
                    spinner.classList.add('d-none');
                },
            }]);
        });
    };

    return {
        init: init,
    };
});