(function () {
    'use strict';

    // Click-select cards (method radios). Hide the native radio, click anywhere
    // on the card to select. If there's only one option, auto-select it.
    var clickSelects = document.querySelectorAll('div.click-select');
    clickSelects.forEach(function (card) {
        var radio = card.querySelector('input[type=radio]');
        if (radio) radio.style.display = 'none';
        card.addEventListener('click', function () {
            clickSelects.forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            if (radio) radio.checked = true;
        });
    });
    if (clickSelects.length === 1) clickSelects[0].click();

    // Show/hide password toggle. Each .password-toggle button carries its own
    // localised labels via data-text-show / data-text-hide so the JS stays language-neutral.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.password-toggle');
        if (!btn) return;
        e.preventDefault();
        var input = document.getElementById(btn.dataset.target);
        if (!input) return;
        var isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        btn.textContent = isPwd ? btn.dataset.textHide : btn.dataset.textShow;
    });

    // Drop the required-error highlight as soon as the user starts fixing the field.
    function clearError(e) {
        if (e.target.classList && e.target.classList.contains('required-error')) {
            e.target.classList.remove('required-error');
        }
    }
    document.addEventListener('input', clearError);
    document.addEventListener('change', clearError);

    // Admin password strength meter + submit gate.
    // Score = (length tier 0-2) + (number of distinct char classes 0-4). Threshold:
    //   <3 weak, 3-4 fair, ≥5 strong. Submit blocked unless 'strong'.
    // Rules are read from data-min-length / data-min-classes on the input so PHP
    // (or future translations) can override without touching JS.
    var adminPwd = document.getElementById('form-password');
    var pwdMeter = document.getElementById('form-password-strength');
    function classifyPassword(pwd) {
        var classes = 0;
        if (/[a-z]/.test(pwd))      classes++;
        if (/[A-Z]/.test(pwd))      classes++;
        if (/[0-9]/.test(pwd))      classes++;
        if (/[^a-zA-Z0-9]/.test(pwd)) classes++;
        var len = pwd.length;
        var lengthTier = len >= 14 ? 2 : len >= 10 ? 1 : 0;
        return { score: lengthTier + classes, classes: classes, length: len };
    }
    function passwordStrong(pwd) {
        var min = parseInt(adminPwd.dataset.minLength, 10) || 10;
        var minClasses = parseInt(adminPwd.dataset.minClasses, 10) || 3;
        var info = classifyPassword(pwd);
        return info.length >= min && info.classes >= minClasses;
    }
    if (adminPwd && pwdMeter) {
        var fill  = pwdMeter.querySelector('.password-meter-fill');
        var label = pwdMeter.querySelector('.password-meter-label');
        adminPwd.addEventListener('input', function () {
            var pwd = adminPwd.value;
            pwdMeter.classList.remove('weak', 'fair', 'strong');
            if (!pwd.length) {
                if (label) label.textContent = '';
                return;
            }
            var info = classifyPassword(pwd);
            var tier = info.score >= 5 ? 'strong' : info.score >= 3 ? 'fair' : 'weak';
            pwdMeter.classList.add(tier);
            if (label) {
                label.textContent = adminPwd.dataset['text' + tier.charAt(0).toUpperCase() + tier.slice(1)] || tier;
            }
        });
    }

    var testBtn    = document.getElementById('test-connection-btn');
    var testResult = document.getElementById('test-connection-result');
    var dbTestPassed = false;

    // POST the install form's DB fields with test_connection=1 so setup/index.php
    // can short-circuit with a JSON reply. Shared by the manual button and the
    // pre-submit gate. onSuccess only fires on res.ok — failures stop the chain.
    function runDbTest(onSuccess) {
        if (!testBtn || !testResult) return;
        testResult.classList.remove('pass', 'fail');
        testResult.textContent = '…';
        testBtn.disabled = true;

        var params = new URLSearchParams();
        document.querySelectorAll('input[name^="global["]').forEach(function (input) {
            params.append(input.name, input.value);
        });
        params.append('test_connection', '1');

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                testResult.classList.add(res.ok ? 'pass' : 'fail');
                testResult.textContent = res.message;
                if (res.ok && onSuccess) onSuccess();
            })
            .catch(function () {
                testResult.classList.add('fail');
                testResult.textContent = testBtn.dataset.textError || 'Test failed.';
            })
            .finally(function () {
                testBtn.disabled = false;
            });
    }

    if (testBtn) {
        testBtn.addEventListener('click', function (e) {
            e.preventDefault();
            runDbTest();
        });
    }

    // Required-field check on every form, plus an auto DB test before the install
    // form actually submits. The auto-test only kicks in when #test-connection-btn
    // is present (i.e. install step, !PRESET_DB).
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            // Cancel/restart button bypasses all validation and the DB test — the
            // user is bailing out, no point checking credentials.
            if (e.submitter && e.submitter.classList.contains('cancel')) return;

            var ok = true;

            form.querySelectorAll('.required').forEach(function (el) {
                el.classList.remove('required-error');
                if (el.value.trim() === '') {
                    el.classList.add('required-error');
                    ok = false;
                }
            });
            form.querySelectorAll('.error').forEach(function (el) {
                el.classList.add('required-error');
                ok = false;
            });

            // Block on weak admin password — only when the field is present in this form
            // and has a value. Empty case is already caught by the required check above.
            if (adminPwd && form.contains(adminPwd) && adminPwd.value && !passwordStrong(adminPwd.value)) {
                adminPwd.classList.add('required-error');
                if (pwdMeter) {
                    var msg = adminPwd.dataset.textError;
                    if (msg) {
                        var lbl = pwdMeter.querySelector('.password-meter-label');
                        if (lbl) lbl.textContent = msg;
                    }
                }
                ok = false;
            }

            if (!ok) {
                e.preventDefault();
                return;
            }

            if (dbTestPassed || !testBtn) return;

            e.preventDefault();
            runDbTest(function () {
                dbTestPassed = true;
                form.submit();
            });
        });
    });
})();
