{if $RECAPTCHA}
<script src="https://www.google.com/recaptcha/api.js{if $RECAPTCHA=='2'}?onload=CaptchaCallback&render=explicit{/if}" async defer></script>
{/if}
{if $RECAPTCHA=='3'}
<script>
function recaptchaSubmit(token) {
    $('.g-recaptcha').closest("form").submit();
}
</script>
{/if}