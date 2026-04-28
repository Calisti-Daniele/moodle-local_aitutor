// amd/src/widget.js
define(['core/ajax'], function(Ajax) {

    /**
     * Inizializza il widget AI Assistant.
     *
     * @param {Object} config
     * @param {number} config.sessionid
     * @param {number} config.userid
     * @param {string} config.wwwroot
     * @param {string} config.sesskey
     */
    var init = function(config) {
        var widget = document.getElementById('local-aitutor-widget');
        if (!widget) {
            return;
        }

        var state = {
            sessionid: config.sessionid,
            userid:    config.userid,
            wwwroot:   config.wwwroot,
            sesskey:   config.sesskey,
            isOpen:    false,
            isLoading: false,
        };

        var els = {
            bubble:      widget.querySelector('.aitutor-bubble'),
            bubbleOpen:  widget.querySelector('.aitutor-bubble-icon--open'),
            bubbleClose: widget.querySelector('.aitutor-bubble-icon--close'),
            panel:       widget.querySelector('.aitutor-panel'),
            messages:    widget.querySelector('.aitutor-messages'),
            input:       widget.querySelector('.aitutor-input'),
            sendBtn:     widget.querySelector('.aitutor-btn-send'),
            clearBtn:    widget.querySelector('.aitutor-btn-clear'),
            closeBtn:    widget.querySelector('.aitutor-btn-close'),
            thinking:    widget.querySelector('.aitutor-thinking'),
            errorBox:    widget.querySelector('.aitutor-error'),
            errorText:   widget.querySelector('.aitutor-error-text'),
            suggestions: widget.querySelector('.aitutor-suggestions'),
        };

        bindEvents(state, els);
        restoreState(state, els);
        autoResize(els.input);
    };

    // =========================================================================
    // EVENTI
    // =========================================================================

    var bindEvents = function(state, els) {
        els.bubble.addEventListener('click', function() {
            togglePanel(state, els);
        });

        els.closeBtn.addEventListener('click', function() {
            closePanel(state, els);
        });

        els.sendBtn.addEventListener('click', function() {
            handleSend(state, els);
        });

        els.input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSend(state, els);
            }
        });

        els.input.addEventListener('input', function() {
            autoResize(els.input);
            hideError(els);
        });

        if (els.clearBtn) {
            els.clearBtn.addEventListener('click', function() {
                handleClear(state, els);
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && state.isOpen) {
                closePanel(state, els);
            }
        });

        // Suggerimenti cliccabili
        if (els.suggestions) {
            els.suggestions.addEventListener('click', function(e) {
                var btn = e.target.closest('.aitutor-suggestion-btn');
                if (btn) {
                    els.input.value = btn.textContent.trim();
                    handleSend(state, els);
                }
            });
        }
    };

    // =========================================================================
    // PANEL OPEN / CLOSE
    // =========================================================================

    var togglePanel = function(state, els) {
        if (state.isOpen) {
            closePanel(state, els);
        } else {
            openPanel(state, els);
        }
    };

    var openPanel = function(state, els) {
        state.isOpen = true;
        els.panel.removeAttribute('hidden');
        els.bubbleOpen.classList.add('d-none');
        els.bubbleClose.classList.remove('d-none');
        els.bubble.setAttribute('aria-expanded', 'true');
        scrollToBottom(els.messages, false);
        setTimeout(function() { els.input.focus(); }, 100);
        sessionStorage.setItem('aitutor_open', '1');
    };

    var closePanel = function(state, els) {
        state.isOpen = false;
        els.panel.setAttribute('hidden', '');
        els.bubbleOpen.classList.remove('d-none');
        els.bubbleClose.classList.add('d-none');
        els.bubble.setAttribute('aria-expanded', 'false');
        sessionStorage.removeItem('aitutor_open');
    };

    var restoreState = function(state, els) {
        if (sessionStorage.getItem('aitutor_open') === '1') {
            openPanel(state, els);
        }
    };

    // =========================================================================
    // SEND MESSAGE
    // =========================================================================

    var handleSend = function(state, els) {
        var message = els.input.value.trim();

        if (!message || state.isLoading) {
            return;
        }

        els.input.value = '';
        autoResize(els.input);
        hideError(els);

        if (els.suggestions) {
            els.suggestions.remove();
        }

        appendMessage(els.messages, 'user', message);
        scrollToBottom(els.messages);
        setLoading(state, els, true);

        callWebService('local_aitutor_send_message', {
            sessionid: state.sessionid,
            message:   message,
        }).then(function(response) {
            setLoading(state, els, false);
            if (response.success) {
                appendMessage(els.messages, 'assistant', response.content, true);
            } else {
                showError(els, response.error || 'Unknown error');
            }
            scrollToBottom(els.messages);
            return response;
        }).catch(function(error) {
            setLoading(state, els, false);
            showError(els, error.message || 'Connection error');
        });
    };

    // =========================================================================
    // CLEAR
    // =========================================================================

    var handleClear = function(state, els) {
        var confirmed = window.confirm(els.clearBtn.dataset.confirm);

        if (!confirmed) {
            return;
        }

        callWebService('local_aitutor_clear_session', {
            sessionid: state.sessionid,
        }).then(function(response) {
            if (response.success) {
                state.sessionid = response.new_sessionid;
                var widget = document.getElementById('local-aitutor-widget');
                if (widget) {
                    widget.dataset.sessionid = response.new_sessionid;
                }
                var msgs = els.messages.querySelectorAll(
                    '.aitutor-message:not(.aitutor-thinking)'
                );
                msgs.forEach(function(m) { m.remove(); });
            }
            return response;
        }).catch(function(error) {
            showError(els, error.message);
        });
    };

    // =========================================================================
    // WEB SERVICE
    // =========================================================================

    var callWebService = function(method, args) {
        return new Promise(function(resolve, reject) {
            Ajax.call([{
                methodname: method,
                args:       args,
                done:       resolve,
                fail:       function(err) {
                    reject(new Error(err.message || 'Server error'));
                },
            }]);
        });
    };

    // =========================================================================
    // DOM HELPERS
    // =========================================================================

    var appendMessage = function(container, role, content) {
        var div = document.createElement('div');
        div.className = 'aitutor-message aitutor-message--' + role;

        var avatarHtml = role === 'assistant'
            ? '<div class="aitutor-msg-avatar" aria-hidden="true">' +
              '<svg viewBox="0 0 24 24" fill="currentColor">' +
              '<path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1' +
              'a7 7 0 0 1 7 7H3a7 7 0 0 1 7-7h1V5.73A2 2 0 0 1 10 4a2 2 0 0 1 2-2z"/>' +
              '</svg></div>'
            : '';

        var bubble = document.createElement('div');
        bubble.className = 'aitutor-msg-bubble';
        bubble.innerHTML = formatContent(content);

        div.innerHTML = avatarHtml;
        div.appendChild(bubble);

        var thinking = container.querySelector('.aitutor-thinking');
        container.insertBefore(div, thinking);
    };

    var formatContent = function(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g,     '<em>$1</em>')
            .replace(/`(.+?)`/g,       '<code>$1</code>')
            .replace(/\n/g,            '<br>');
    };

    var scrollToBottom = function(container, smooth) {
        if (smooth === undefined) {
            smooth = true;
        }
        container.scrollTo({
            top:      container.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant',
        });
    };

    var autoResize = function(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    };

    var setLoading = function(state, els, loading) {
        state.isLoading       = loading;
        els.thinking.classList.toggle('d-none', !loading);
        els.thinking.setAttribute('aria-hidden', String(!loading));
        els.sendBtn.disabled  = loading;
        els.input.disabled    = loading;
        if (!loading) {
            els.input.focus();
        }
    };

    var showError = function(els, message) {
        els.errorText.textContent = message;
        els.errorBox.classList.remove('d-none');
    };

    var hideError = function(els) {
        els.errorBox.classList.add('d-none');
    };

    return {
        init: init,
    };
});