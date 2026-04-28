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
            card.classList.remove('faded');
            if (radio) radio.checked = true;
        });
    });
    if (clickSelects.length === 1) clickSelects[0].click();

    // Cancel button: drop the `required` flag so the submit handler doesn't bounce.
    document.querySelectorAll('input.cancel[type=submit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.required').forEach(function (el) {
                el.classList.remove('required');
            });
        });
    });

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
